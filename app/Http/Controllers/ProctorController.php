<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Comment;
use App\Models\DormitoryBlock;
use App\Models\Room;
use App\Models\StudentAllocation;
use App\Models\User;
use App\Services\RoomAllocationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ProctorController extends Controller
{
    protected RoomAllocationService $allocationService;

    public function __construct(RoomAllocationService $allocationService)
    {
        $this->allocationService = $allocationService;
    }

    /**
     * Proctor's Assigned Blocks
     */
    public function myBlocks(Request $request): View
    {
        $proctor = auth()->user();
        $assignedBlockIds = $proctor->activeProctorAssignments()->pluck('block_id');
        $query = DormitoryBlock::whereIn('id', $assignedBlockIds)->withCount(['rooms', 'activeAllocations']);
        $blocks = \App\Services\QuerySortService::sortBlocks($query, $request)->get();

        return view('proctor.my-blocks', compact('blocks'));
    }

    /**
     * Rooms within Proctor's Blocks (with search and filters)
     */
    public function rooms(Request $request): View
    {
        $proctor = auth()->user();
        $assignedBlockIds = $proctor->activeProctorAssignments()->pluck('block_id');
        $blocks = DormitoryBlock::whereIn('id', $assignedBlockIds)->orderBy('block_code')->get();

        $query = Room::whereIn('block_id', $assignedBlockIds)
            ->with(['block', 'activeAllocations.student'])
            ->withCount('activeAllocations');
        $like = DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s, $like) {
                $q->where('room_number', $like, "%{$s}%")
                  ->orWhereHas('block', function ($bq) use ($s, $like) {
                      $bq->where('block_code', $like, "%{$s}%")
                         ->orWhere('name', $like, "%{$s}%");
                  });
            });
        }

        if ($request->filled('block_id')) {
            $query->where('block_id', $request->block_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $rooms = \App\Services\QuerySortService::sortRooms($query, $request, 'room_number')->paginate(16)->withQueryString();

        return view('proctor.rooms', compact('blocks', 'rooms'));
    }

    /**
     * Store Room in Proctor's Block (Max 50 rooms per block, max 6 beds per room)
     */
    public function storeRoom(Request $request): RedirectResponse
    {
        $proctor = auth()->user();
        $assignedBlockIds = $proctor->activeProctorAssignments()->pluck('block_id')->toArray();

        $validated = $request->validate([
            'block_id' => ['required', 'in:' . implode(',', $assignedBlockIds)],
            'room_number' => ['required', 'string', 'max:50'],
            'capacity' => ['required', 'integer', 'min:1', 'max:' . DormitoryBlock::MAX_ROOM_CAPACITY],
        ]);

        $block = DormitoryBlock::findOrFail($validated['block_id']);
        if ($block->rooms()->count() >= DormitoryBlock::ROOMS_PER_BLOCK) {
            return back()->with('error', "Cannot register room. Block {$block->block_code} has reached its maximum design capacity of 50 rooms.")->withInput();
        }

        $exists = Room::where('block_id', $validated['block_id'])->where('room_number', $validated['room_number'])->exists();
        if ($exists) {
            return back()->with('error', "Room {$validated['room_number']} already exists in this block.")->withInput();
        }

        $validated['image'] = Room::determineRoomImage($validated['room_number']);
        $room = Room::create($validated);

        AuditLog::log('room_created', "Proctor registered room {$room->room_number} in block {$block->block_code} (Capacity: {$room->capacity} beds).", null, auth()->user());

        return back()->with('success', "Room {$validated['room_number']} registered successfully.");
    }

    /**
     * Student Allocation Form with Automatic Gender & Department/Batch Scope Filtering
     */
    public function allocateForm(Request $request): View
    {
        $proctor = auth()->user();
        $assignments = $proctor->activeProctorAssignments()->with('block')->get();
        $assignedBlockIds = $assignments->pluck('block_id');
        $blocks = DormitoryBlock::whereIn('id', $assignedBlockIds)->get();

        $blockGenders = $blocks->pluck('gender_category')->filter()->unique()->values()->all();
        $assignedDepartments = $assignments->pluck('assigned_departments')->flatten()->filter()->unique()->values()->all();
        $assignedYears = $assignments->pluck('assigned_years')->flatten()->filter()->map(fn($y) => (int)$y)->unique()->values()->all();

        // Get unallocated eligible students scoped to proctor's assigned block gender, departments, and batches
        $query = User::where('role', User::ROLE_STUDENT)
            ->where('is_active', true)
            ->whereDoesntHave('activeAllocation');

        // Automatic gender segregation: Male blocks get male students, female blocks get female students
        if (!empty($blockGenders)) {
            $query->whereIn('gender', $blockGenders);
        }

        // Scope to proctor's assigned departments (at most 10, case-insensitive match)
        if (!empty($assignedDepartments)) {
            $deptsLower = array_map('strtolower', $assignedDepartments);
            $query->whereIn(DB::raw('LOWER(department)'), $deptsLower);
        }

        // Scope to proctor's assigned batches (at most 5 years)
        if (!empty($assignedYears)) {
            $query->whereIn('academic_year', $assignedYears);
        }

        $eligibleStudents = $query->orderBy('name')->get();

        // Available rooms in proctor's assigned blocks
        $rooms = Room::whereIn('block_id', $assignedBlockIds)
            ->where('status', 'available')
            ->with('block')
            ->get();

        return view('proctor.allocate', compact('blocks', 'eligibleStudents', 'rooms', 'assignedDepartments', 'assignedYears', 'blockGenders'));
    }

    /**
     * Process Student Allocation (Single or Batch up to 6 students at once)
     */
    public function storeAllocation(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'student_id' => ['nullable', 'exists:users,id'],
            'student_ids' => ['nullable', 'array', 'min:1', 'max:6'],
            'student_ids.*' => ['exists:users,id'],
            'room_id' => ['required', 'exists:rooms,id'],
            'remarks' => ['nullable', 'string', 'max:255'],
        ]);

        $studentIds = $request->input('student_ids', []);
        if (empty($studentIds) && $request->filled('student_id')) {
            $studentIds = [(int) $request->input('student_id')];
        }

        if (empty($studentIds)) {
            return back()->with('error', 'Please select at least one student to allocate.')->withInput();
        }

        if (count($studentIds) > 6) {
            return back()->with('error', 'You can allocate at most 6 students at once.')->withInput();
        }

        $room = Room::with('block')->findOrFail($validated['room_id']);
        $freeBeds = $room->available_beds;

        if (count($studentIds) > $freeBeds) {
            return back()->with('error', "Cannot allocate " . count($studentIds) . " students. Room {$room->room_number} only has {$freeBeds} free bed(s).")->withInput();
        }

        $allocatedNames = [];
        try {
            DB::transaction(function () use ($studentIds, $room, &$allocatedNames, $validated) {
                foreach ($studentIds as $sId) {
                    $student = User::findOrFail($sId);
                    $this->allocationService->allocate($student, $room, auth()->user(), $validated['remarks'] ?? null);
                    $allocatedNames[] = $student->name;
                }
            });
        } catch (\Illuminate\Validation\ValidationException $ve) {
            return back()->withErrors($ve->errors())->withInput();
        } catch (\Exception $e) {
            return back()->with('error', 'Allocation failed: ' . $e->getMessage())->withInput();
        }

        $count = count($allocatedNames);
        $msg = $count === 1
            ? "Student {$allocatedNames[0]} allocated to Room {$room->room_number} ({$room->block->name}) successfully!"
            : "Successfully allocated {$count} students (" . implode(', ', $allocatedNames) . ") to Room {$room->room_number} ({$room->block->name})!";

        return redirect()->route('proctor.allocated.index')->with('success', $msg);
    }

    /**
     * List Active Allocations in Proctor's Blocks (with search and filters)
     */
    public function allocatedStudents(Request $request): View
    {
        $proctor = auth()->user();
        $assignedBlockIds = $proctor->activeProctorAssignments()->pluck('block_id');
        $blocks = DormitoryBlock::whereIn('id', $assignedBlockIds)->orderBy('block_code')->get();
        $managedRoomIds = Room::whereIn('block_id', $assignedBlockIds)->pluck('id');

        $query = StudentAllocation::with(['student', 'room.block'])
            ->whereIn('room_id', $managedRoomIds)
            ->where('status', StudentAllocation::STATUS_ACTIVE);
        $like = DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s, $like) {
                $q->whereHas('student', function ($sq) use ($s, $like) {
                    $sq->where('name', $like, "%{$s}%")
                       ->orWhere('university_id', $like, "%{$s}%")
                       ->orWhere('email', $like, "%{$s}%");
                })->orWhereHas('room', function ($rq) use ($s, $like) {
                    $rq->where('room_number', $like, "%{$s}%");
                });
            });
        }

        if ($request->filled('gender')) {
            $query->whereHas('student', fn($sq) => $sq->where('gender', $request->gender));
        }

        if ($request->filled('block_id')) {
            $query->whereHas('room', fn($rq) => $rq->where('block_id', $request->block_id));
        }

        $allocations = \App\Services\QuerySortService::sortAllocations($query, $request)->paginate(12)->withQueryString();

        return view('proctor.allocated', compact('allocations', 'blocks'));
    }

    /**
     * Proctor Student Information Directory (View department and batch students, and assign to room)
     */
    public function students(Request $request): View
    {
        $proctor = auth()->user();
        $assignments = $proctor->activeProctorAssignments()->with('block')->get();
        $assignedBlockIds = $assignments->pluck('block_id');
        $blocks = DormitoryBlock::whereIn('id', $assignedBlockIds)->orderBy('block_code')->get();
        $blockGenders = $blocks->pluck('gender_category')->filter()->unique()->values()->all();
        $assignedDepartments = $assignments->pluck('assigned_departments')->flatten()->filter()->unique()->values()->all();
        $assignedYears = $assignments->pluck('assigned_years')->flatten()->filter()->map(fn($y) => (int)$y)->unique()->values()->all();

        $query = User::where('role', User::ROLE_STUDENT)
            ->with(['activeAllocation.room.block']);

        // Segregate by gender matching proctor's block(s)
        if (!empty($blockGenders)) {
            $query->whereIn('gender', $blockGenders);
        }

        // Scope to proctor's assigned departments (case-insensitive)
        if (!empty($assignedDepartments)) {
            $deptsLower = array_map('strtolower', $assignedDepartments);
            $query->whereIn(DB::raw('LOWER(department)'), $deptsLower);
        }

        // Scope to proctor's assigned batches
        if (!empty($assignedYears)) {
            $query->whereIn('academic_year', $assignedYears);
        }

        $like = DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s, $like) {
                $q->where('name', $like, "%{$s}%")
                  ->orWhere('university_id', $like, "%{$s}%")
                  ->orWhere('email', $like, "%{$s}%")
                  ->orWhere('department', $like, "%{$s}%")
                  ->orWhereHas('activeAllocation.room', function ($rq) use ($s, $like) {
                      $rq->where('room_number', $like, "%{$s}%");
                  });
            });
        }

        if ($request->filled('department')) {
            $query->whereRaw('LOWER(department) = ?', [strtolower(trim($request->department))]);
        }

        if ($request->filled('gender')) {
            $query->where('gender', $request->gender);
        }

        if ($request->filled('year')) {
            $query->where('academic_year', (int)$request->year);
        }

        if ($request->filled('status')) {
            if ($request->status === 'allocated') {
                $query->whereHas('activeAllocation');
            } elseif ($request->status === 'unallocated') {
                $query->whereDoesntHave('activeAllocation');
            }
        }

        $students = \App\Services\QuerySortService::sortUsers($query, $request)->paginate(12)->withQueryString();

        $departments = !empty($assignedDepartments)
            ? collect($assignedDepartments)
            : collect(\App\Services\DepartmentCatalog::all());

        return view('proctor.students', compact('students', 'blocks', 'departments', 'assignedDepartments', 'assignedYears', 'blockGenders'));
    }

    /**
     * Release an allocation
     */
    public function releaseAllocation(StudentAllocation $allocation): RedirectResponse
    {
        $this->allocationService->release($allocation, auth()->user(), 'Released by proctor');
        return back()->with('success', 'Student allocation released safely.');
    }

    /**
     * Comments & Feedback for Proctor:
     * View comments from students in their assigned blocks and their own submitted comments in table format
     */
    public function comments(Request $request): View
    {
        $proctor = auth()->user();
        $assignedBlockIds = $proctor->activeProctorAssignments()->pluck('block_id');
        $managedRoomIds = Room::whereIn('block_id', $assignedBlockIds)->pluck('id');
        $managedStudentIds = StudentAllocation::whereIn('room_id', $managedRoomIds)
            ->where('status', StudentAllocation::STATUS_ACTIVE)
            ->pluck('student_id');

        $query = Comment::with(['user', 'responder'])
            ->where(function ($q) use ($managedStudentIds, $proctor) {
                $q->whereIn('user_id', $managedStudentIds)
                  ->orWhere('user_id', $proctor->id);
            });

        $like = DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s, $like) {
                $q->where('title', $like, "%{$s}%")
                  ->orWhere('message', $like, "%{$s}%")
                  ->orWhere('category', $like, "%{$s}%")
                  ->orWhereHas('user', function ($uq) use ($s, $like) {
                      $uq->where('name', $like, "%{$s}%")
                         ->orWhere('university_id', $like, "%{$s}%");
                  });
            });
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('scope')) {
            if ($request->scope === 'mine') {
                $query->where('user_id', $proctor->id);
            } elseif ($request->scope === 'students') {
                $query->whereIn('user_id', $managedStudentIds);
            }
        }

        $comments = $query->latest()->paginate(10)->withQueryString();
        $blocks = DormitoryBlock::whereIn('id', $assignedBlockIds)->get();

        return view('proctor.comments', compact('comments', 'blocks'));
    }

    /**
     * Proctor submits / gives a comment, maintenance report, or inquiry
     * (Proctor gives comments for Proctor Manager or Administrator)
     */
    public function storeComment(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'category' => ['required', 'string', 'in:maintenance,complaint,inquiry,suggestion,general'],
            'target_role' => ['nullable', 'string', 'in:proctor_manager,admin'],
            'title' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string'],
        ]);

        $target = $validated['target_role'] ?? 'proctor_manager';
        $assignedBlockId = auth()->user()->activeProctorAssignments()->value('block_id');

        $comment = Comment::create([
            'user_id' => auth()->id(),
            'category' => $validated['category'],
            'target_role' => $target,
            'block_id' => $assignedBlockId,
            'title' => $validated['title'],
            'message' => $validated['message'],
            'status' => Comment::STATUS_PENDING,
        ]);

        $recipientLabel = $target === 'admin' ? 'Institutional Administrator' : 'Proctor Manager';
        AuditLog::log('proctor_comment_submitted', "Proctor submitted {$comment->category} feedback to {$recipientLabel}: {$comment->title}", null, auth()->user());

        return back()->with('success', "Your comment / report has been submitted to {$recipientLabel} successfully.");
    }

    /**
     * Proctor responds to student comment
     */
    public function replyComment(Request $request, Comment $comment): RedirectResponse
    {
        $request->validate([
            'response' => 'required|string',
            'status' => 'required|in:in_progress,resolved,closed'
        ]);

        $comment->update([
            'response' => $request->response,
            'status' => $request->status,
            'responded_by' => auth()->id(),
            'responded_at' => now(),
        ]);

        AuditLog::log('comment_replied', "Proctor replied to comment #{$comment->id} ({$comment->title})", null, auth()->user());

        return back()->with('success', 'Official response submitted successfully.');
    }

    /**
     * Proctor deletes a comment
     */
    public function destroyComment(Comment $comment): RedirectResponse
    {
        $title = $comment->title;
        $comment->delete();

        AuditLog::log('comment_deleted', "Proctor deleted comment report: '{$title}'.", null, auth()->user());

        return back()->with('success', "Comment '{$title}' removed successfully.");
    }

    /**
     * Proctor Reports: Weekly & Monthly Attendance Aggregates, Frequent Absentees & Block Utilization
     */
    public function reports(Request $request): View|RedirectResponse
    {
        return app(AttendanceController::class)->proctorReports($request);
    }
}
