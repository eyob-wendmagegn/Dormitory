<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\BlockProctorAssignment;
use App\Models\Comment;
use App\Models\DormitoryBlock;
use App\Models\Room;
use App\Models\StudentAllocation;
use App\Models\User;
use App\Services\RoomAllocationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProctorManagerController extends Controller
{
    protected RoomAllocationService $allocationService;

    public function __construct(RoomAllocationService $allocationService)
    {
        $this->allocationService = $allocationService;
    }

    /**
     * Blocks Management (with search and category filters)
     */
    public function blocks(Request $request): View
    {
        $query = DormitoryBlock::withCount(['rooms', 'activeAllocations']);
        $like = DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s, $like) {
                $q->where('block_code', $like, "%{$s}%")
                  ->orWhere('name', $like, "%{$s}%")
                  ->orWhere('location', $like, "%{$s}%");
            });
        }

        if ($request->filled('gender')) {
            $query->where('gender_category', $request->gender);
        }

        $blocks = \App\Services\QuerySortService::sortBlocks($query, $request)->get();

        return view('manager.blocks', compact('blocks'));
    }

    /**
     * Create Block
     */
    public function storeBlock(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'block_code' => ['required', 'string', 'max:50', 'unique:dormitory_blocks,block_code'],
            'gender_category' => ['required', Rule::in(['male', 'female'])],
            'location' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        $validated['image'] = DormitoryBlock::determineBlockImage($validated['block_code']);
        $block = DormitoryBlock::create($validated);

        AuditLog::log('block_created', "Proctor Manager registered block {$block->name} ({$block->gender_category}).", null, auth()->user());

        return back()->with('success', "Dormitory block {$block->name} created successfully.");
    }

    /**
     * Update Dormitory Block (Manager can edit block details)
     */
    public function updateBlock(Request $request, DormitoryBlock $block): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'block_code' => ['required', 'string', 'max:50', Rule::unique('dormitory_blocks', 'block_code')->ignore($block->id)],
            'gender_category' => ['required', Rule::in(['male', 'female'])],
            'location' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        if ($block->gender_category !== $validated['gender_category']) {
            $activeCount = $block->activeAllocations()->count();
            if ($activeCount > 0) {
                return back()->with('error', "Cannot change accommodation category because this block currently houses {$activeCount} resident students. Reallocate or release students first.");
            }
        }

        $validated['image'] = DormitoryBlock::determineBlockImage($validated['block_code']);
        $block->update($validated);

        AuditLog::log('block_updated', "Proctor Manager updated block {$block->block_code} ({$block->name}).", null, auth()->user());

        return back()->with('success', "Dormitory block {$block->block_code} updated successfully.");
    }

    /**
     * Delete Dormitory Block (Manager can delete block if no active occupants)
     */
    public function destroyBlock(DormitoryBlock $block): RedirectResponse
    {
        $activeCount = $block->activeAllocations()->count();
        if ($activeCount > 0) {
            return back()->with('error', "Cannot delete block {$block->block_code} because it currently houses {$activeCount} resident students. Please reallocate them first.");
        }

        $code = $block->block_code;
        $name = $block->name;
        $block->delete();

        AuditLog::log('block_deleted', "Proctor Manager deleted block {$code} ({$name}).", null, auth()->user());

        return back()->with('success', "Dormitory block {$code} ({$name}) removed successfully.");
    }

    /**
     * Update Block Accommodation Category
     */
    public function updateCategory(Request $request, DormitoryBlock $block): RedirectResponse
    {
        $request->validate(['gender_category' => ['required', Rule::in(['male', 'female'])]]);

        $this->allocationService->updateBlockCategory($block, $request->gender_category, auth()->user());

        return back()->with('success', "Block accommodation category updated to {$request->gender_category}.");
    }

    /**
     * University Academic Departments catalog
     */
    public static function getAvailableDepartments(): array
    {
        return \App\Services\DepartmentCatalog::all();
    }

    /**
     * University Academic Divisions / Colleges
     */
    public static function getDivisions(): array
    {
        return \App\Services\DepartmentCatalog::DIVISIONS;
    }

    /**
     * University Batches (1st to 7th Academic Years)
     */
    public static function getBatches(): array
    {
        return [
            1 => '1st Year',
            2 => '2nd Year',
            3 => '3rd Year',
            4 => '4th Year',
            5 => '5th Year',
            6 => '6th Year',
            7 => '7th Year',
        ];
    }

    /**
     * Proctor Assignments Management
     */
    public function assignments(): View
    {
        $assignments = BlockProctorAssignment::with(['block', 'proctor'])->whereNull('ended_at')->latest()->get();
        $blocks = DormitoryBlock::with(['activeProctorAssignment.proctor'])->where('status', 'active')->orderBy('block_code')->get();
        $proctors = User::where('role', User::ROLE_PROCTOR)->with(['activeProctorAssignments.block'])->orderBy('name')->get();
        $divisions = self::getDivisions();
        $allDepartments = self::getAvailableDepartments();
        $allBatches = self::getBatches();

        return view('manager.assignments', compact('assignments', 'blocks', 'proctors', 'divisions', 'allDepartments', 'allBatches'));
    }

    /**
     * Assign Proctor to Block (Strict 1-to-1: One proctor per block, one block per proctor, with up to 10 departments and 5 batches)
     */
    public function storeAssignment(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'block_id' => ['required', 'exists:dormitory_blocks,id'],
            'proctor_id' => ['required', 'exists:users,id'],
            'assigned_departments' => ['nullable', 'array', 'max:10'],
            'assigned_departments.*' => ['string', 'max:100'],
            'assigned_years' => ['nullable', 'array', 'max:5'],
            'assigned_years.*' => ['integer', 'between:1,7'],
        ], [
            'assigned_departments.max' => 'A proctor can be assigned at most 10 departments.',
            'assigned_years.max' => 'A proctor can be assigned at most 5 batches (academic years).',
        ]);

        // 1. Check if the block already has an active proctor assigned
        $blockAssignment = BlockProctorAssignment::where('block_id', $validated['block_id'])
            ->whereNull('ended_at')
            ->with(['block', 'proctor'])
            ->first();

        if ($blockAssignment) {
            return back()->with('error', "Block {$blockAssignment->block->block_code} is already assigned to proctor {$blockAssignment->proctor->name}. Each block can only have one assigned proctor. End the current assignment before reassigning.");
        }

        // 2. Check if the proctor is already actively assigned to any block
        $proctorAssignment = BlockProctorAssignment::where('proctor_id', $validated['proctor_id'])
            ->whereNull('ended_at')
            ->with(['block', 'proctor'])
            ->first();

        if ($proctorAssignment) {
            return back()->with('error', "Proctor {$proctorAssignment->proctor->name} is already assigned to block {$proctorAssignment->block->block_code} ({$proctorAssignment->block->name}). A proctor cannot be assigned to more than one block.");
        }

        $departments = !empty($validated['assigned_departments'])
            ? array_values(array_unique(array_filter(array_map([\App\Services\DepartmentCatalog::class, 'normalize'], $validated['assigned_departments']))))
            : [];
        $years = !empty($validated['assigned_years']) ? array_values(array_map('intval', array_filter($validated['assigned_years']))) : [];

        $assignment = BlockProctorAssignment::create([
            'block_id' => $validated['block_id'],
            'proctor_id' => $validated['proctor_id'],
            'assigned_departments' => $departments,
            'assigned_years' => $years,
            'assigned_by' => auth()->id(),
            'assigned_at' => now(),
        ]);

        $block = DormitoryBlock::find($validated['block_id']);
        $proctor = User::find($validated['proctor_id']);

        $deptCount = count($departments);
        $yearCount = count($years);
        AuditLog::log('proctor_assigned', "Proctor Manager assigned proctor {$proctor->name} to block {$block->block_code} with {$deptCount} departments and {$yearCount} batches.", null, auth()->user());

        return back()->with('success', "Proctor {$proctor->name} assigned to Block {$block->block_code} ({$block->gender_category}) with {$deptCount} department(s) and {$yearCount} batch(es).");
    }

    /**
     * Update Proctor Assignment (Strict 1-to-1: One proctor per block, one block per proctor, with up to 10 departments and 5 batches)
     */
    public function updateAssignment(Request $request, BlockProctorAssignment $assignment): RedirectResponse
    {
        $validated = $request->validate([
            'block_id' => ['required', 'exists:dormitory_blocks,id'],
            'proctor_id' => ['required', 'exists:users,id'],
            'assigned_departments' => ['nullable', 'array', 'max:10'],
            'assigned_departments.*' => ['string', 'max:100'],
            'assigned_years' => ['nullable', 'array', 'max:5'],
            'assigned_years.*' => ['integer', 'between:1,7'],
        ], [
            'assigned_departments.max' => 'A proctor can be assigned at most 10 departments.',
            'assigned_years.max' => 'A proctor can be assigned at most 5 batches (academic years).',
        ]);

        // 1. Check if the block already has another active assignment
        $existingBlockAsg = BlockProctorAssignment::where('block_id', $validated['block_id'])
            ->where('id', '!=', $assignment->id)
            ->whereNull('ended_at')
            ->with(['block', 'proctor'])
            ->first();

        if ($existingBlockAsg) {
            return back()->with('error', "Block {$existingBlockAsg->block->block_code} is already assigned to proctor {$existingBlockAsg->proctor->name}. Each block can only have one assigned proctor.");
        }

        // 2. Check if the proctor already has another active assignment
        $existingProctorAsg = BlockProctorAssignment::where('proctor_id', $validated['proctor_id'])
            ->where('id', '!=', $assignment->id)
            ->whereNull('ended_at')
            ->with(['block', 'proctor'])
            ->first();

        if ($existingProctorAsg) {
            return back()->with('error', "Proctor {$existingProctorAsg->proctor->name} is already assigned to block {$existingProctorAsg->block->block_code}. A proctor cannot be assigned to more than one block.");
        }

        $departments = !empty($validated['assigned_departments'])
            ? array_values(array_unique(array_filter(array_map([\App\Services\DepartmentCatalog::class, 'normalize'], $validated['assigned_departments']))))
            : [];
        $years = !empty($validated['assigned_years']) ? array_values(array_map('intval', array_filter($validated['assigned_years']))) : [];

        $assignment->update([
            'block_id' => $validated['block_id'],
            'proctor_id' => $validated['proctor_id'],
            'assigned_departments' => $departments,
            'assigned_years' => $years,
        ]);

        AuditLog::log('proctor_assignment_updated', "Proctor Manager updated assignment #{$assignment->id} (Proctor: {$validated['proctor_id']}, Block: {$validated['block_id']}, Depts: " . count($departments) . ", Batches: " . count($years) . ").", null, auth()->user());

        return back()->with('success', 'Proctor assignment updated successfully with assigned departments and batches.');
    }

    /**
     * Delete Proctor Assignment
     */
    public function destroyAssignment(BlockProctorAssignment $assignment): RedirectResponse
    {
        $blockCode = $assignment->block?->block_code ?? 'Block';
        $proctorName = $assignment->proctor?->name ?? 'Proctor';
        $assignment->delete();

        AuditLog::log('proctor_assignment_deleted', "Proctor Manager deleted assignment of {$proctorName} to {$blockCode}.", null, auth()->user());

        return back()->with('success', "Proctor assignment for {$proctorName} ({$blockCode}) has been deleted.");
    }

    /**
     * End Proctor Assignment
     */
    public function endAssignment(BlockProctorAssignment $assignment): RedirectResponse
    {
        $assignment->update(['ended_at' => now()]);
        return back()->with('success', 'Proctor assignment ended.');
    }

    /**
     * Students Overview (with search and filters)
     */
    public function students(Request $request): View
    {
        $query = User::where('role', User::ROLE_STUDENT)
            ->with(['activeAllocation.room.block']);
        $like = DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s, $like) {
                $q->where('name', $like, "%{$s}%")
                  ->orWhere('university_id', $like, "%{$s}%")
                  ->orWhere('email', $like, "%{$s}%")
                  ->orWhere('department', $like, "%{$s}%")
                  ->orWhereHas('activeAllocation.room.block', function ($bq) use ($s, $like) {
                      $bq->where('name', $like, "%{$s}%")
                         ->orWhere('block_code', $like, "%{$s}%");
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
            $query->where('academic_year', $request->year);
        }

        if ($request->filled('status')) {
            if ($request->status === 'allocated') {
                $query->whereHas('activeAllocation');
            } elseif ($request->status === 'unallocated') {
                $query->whereDoesntHave('activeAllocation');
            }
        }

        $students = \App\Services\QuerySortService::sortUsers($query, $request)->paginate(12)->withQueryString();
        $departments = \App\Services\DepartmentCatalog::all();

        return view('manager.students', compact('students', 'departments'));
    }

    /**
     * Comments & Issue Tracking:
     * Proctor Manager views proctor comments and their own submitted reports
     */
    public function comments(Request $request): View
    {
        $manager = auth()->user();
        $query = Comment::with(['user', 'responder']);
        $like = DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';

        // Filter / Scope: Proctor comments or Manager's own comments
        if ($request->filled('scope')) {
            if ($request->scope === 'proctors') {
                $query->whereHas('user', fn($uq) => $uq->where('role', User::ROLE_PROCTOR));
            } elseif ($request->scope === 'mine') {
                $query->where('user_id', $manager->id);
            }
        } else {
            // Default: Proctor Manager views proctor comments and their own submitted comments
            $query->where(function ($q) use ($manager) {
                $q->whereHas('user', fn($uq) => $uq->where('role', User::ROLE_PROCTOR))
                  ->orWhere('target_role', 'proctor_manager')
                  ->orWhere('user_id', $manager->id);
            });
        }

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

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        $comments = $query->latest()->paginate(10)->withQueryString();

        return view('manager.comments', compact('comments'));
    }

    /**
     * Proctor Manager gives comments for Administrator
     */
    public function storeComment(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'category' => ['required', 'string', 'in:maintenance,complaint,inquiry,suggestion,general'],
            'title' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string'],
        ]);

        $comment = Comment::create([
            'user_id' => auth()->id(),
            'category' => $validated['category'],
            'target_role' => 'admin', // Proctor manager gives comments for Administrator
            'title' => $validated['title'],
            'message' => $validated['message'],
            'status' => Comment::STATUS_PENDING,
        ]);

        AuditLog::log('manager_comment_submitted', "Proctor Manager submitted comment to administrator: {$comment->title}", null, auth()->user());

        return back()->with('success', 'Your comment has been submitted to the Institutional Administrator.');
    }

    /**
     * Proctor Manager replies to proctor comment
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

        AuditLog::log('comment_replied', "Proctor Manager replied to comment #{$comment->id}.", null, auth()->user());

        return back()->with('success', 'Official response submitted successfully.');
    }

    /**
     * Delete comment
     */
    public function destroyComment(Comment $comment): RedirectResponse
    {
        $title = $comment->title;
        $comment->delete();

        AuditLog::log('comment_deleted', "Proctor Manager deleted comment report: '{$title}'.", null, auth()->user());

        return back()->with('success', "Comment '{$title}' removed successfully.");
    }

    /**
     * Reports (Magic calculation: 50 rooms * 6 students = 300 students per block)
     */
    public function reports(): View
    {
        $blocks = DormitoryBlock::withCount(['rooms', 'activeAllocations'])->get();
        $totalBlocks = $blocks->count();
        $totalCapacity = $totalBlocks * DormitoryBlock::STANDARD_BLOCK_CAPACITY;
        $totalAllocated = StudentAllocation::where('status', StudentAllocation::STATUS_ACTIVE)->count();

        return view('manager.reports', compact('blocks', 'totalBlocks', 'totalCapacity', 'totalAllocated'));
    }

    /**
     * Rooms Inventory Oversight for Proctor Manager (List View with pagination)
     */
    public function rooms(Request $request): View
    {
        $blocks = DormitoryBlock::orderBy('block_code')->get();
        $like = DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';

        $query = Room::with(['block', 'activeAllocations.student'])->withCount('activeAllocations');

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

        if ($request->filled('gender')) {
            $query->whereHas('block', fn($bq) => $bq->where('gender_category', $request->gender));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $rooms = \App\Services\QuerySortService::sortRooms($query, $request)->paginate(16)->withQueryString();

        return view('manager.rooms', compact('blocks', 'rooms'));
    }
}
