<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\AttendanceAlert;
use App\Models\BlockProctorAssignment;
use App\Models\DormitoryBlock;
use App\Models\StudentAllocation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class NoticeController extends Controller
{
    // =========================================================================
    // 1. PROCTOR MANAGER: Internal Announcements (Manager to Proctors)
    // =========================================================================

    public function managerIndex(Request $request): View
    {
        $blocks = DormitoryBlock::orderBy('name')->get();

        $noticesQuery = Announcement::where('target_role', Announcement::TARGET_PROCTOR)
            ->with(['author', 'block'])
            ->latest('published_at');

        if ($request->filled('block_id')) {
            $noticesQuery->where('block_id', $request->block_id);
        }

        $notices = $noticesQuery->paginate(10)->withQueryString();

        return view('manager.notices.index', compact('notices', 'blocks'));
    }

    public function managerStore(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'summary' => 'nullable|string|max:500',
            'content' => 'required|string',
            'block_id' => 'nullable|exists:dormitory_blocks,id',
            'priority' => 'required|in:normal,urgent,critical',
        ]);

        $notice = Announcement::create([
            'author_id' => Auth::id(),
            'title' => $validated['title'],
            'summary' => $validated['summary'] ?? null,
            'content' => $validated['content'],
            'category' => 'internal_proctor',
            'target_role' => Announcement::TARGET_PROCTOR,
            'target_audience' => (!empty($validated['block_id'])) ? 'block_proctors' : 'all_proctors',
            'block_id' => $validated['block_id'] ?? null,
            'priority' => $validated['priority'],
            'is_published' => true,
            'published_at' => now(),
        ]);

        // Send alerts to proctors
        $proctorsQuery = User::where('role', User::ROLE_PROCTOR);
        if (!empty($validated['block_id'])) {
            $proctorsQuery->whereHas('activeProctorAssignments', fn($q) => $q->where('block_id', $validated['block_id']));
        }

        $proctors = $proctorsQuery->get();
        foreach ($proctors as $proctor) {
            AttendanceAlert::create([
                'user_id' => $proctor->id,
                'alert_type' => 'internal_announcement',
                'title' => 'Internal Notice: ' . $notice->title,
                'message' => $notice->summary ?: substr(strip_tags($notice->content), 0, 120),
                'action_url' => route('proctor.notices.index'),
                'priority' => $notice->priority === 'critical' ? 'high' : 'normal',
            ]);
        }

        return back()->with('success', "Internal announcement broadcasted to " . (!empty($validated['block_id']) ? 'block proctors' : 'all proctors') . ".");
    }

    public function managerDestroy(Announcement $notice)
    {
        $notice->delete();
        return back()->with('success', 'Internal announcement removed.');
    }

    // =========================================================================
    // 2. PROCTOR: View Internal Notices & Post Student Notice (Block-targeted)
    // =========================================================================

    public function proctorIndex(): View
    {
        $proctor = Auth::user();
        $assignment = BlockProctorAssignment::with('block')->where('proctor_id', $proctor->id)->first();
        $block = $assignment?->block;

        // 1. Internal notices received from Manager (restricted to proctors)
        $internalNotices = Announcement::forProctors($block?->id)
            ->where('target_role', Announcement::TARGET_PROCTOR)
            ->with(['author', 'block'])
            ->latest('published_at')
            ->get();

        // 2. Notices posted by this proctor for student residents
        $studentNotices = Announcement::where('author_id', $proctor->id)
            ->where('target_role', Announcement::TARGET_STUDENT)
            ->with('block')
            ->latest('published_at')
            ->get();

        return view('proctor.notices.index', compact('internalNotices', 'studentNotices', 'block'));
    }

    public function proctorStoreStudentNotice(Request $request)
    {
        $proctor = Auth::user();
        $assignment = BlockProctorAssignment::where('proctor_id', $proctor->id)->first();

        if (!$assignment) {
            return back()->with('error', 'You are not assigned to manage a dormitory block.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'summary' => 'nullable|string|max:500',
            'content' => 'required|string',
            'priority' => 'required|in:normal,urgent,critical',
        ]);

        $notice = Announcement::create([
            'author_id' => $proctor->id,
            'title' => $validated['title'],
            'summary' => $validated['summary'] ?? null,
            'content' => $validated['content'],
            'category' => 'student_notice',
            'target_role' => Announcement::TARGET_STUDENT,
            'target_audience' => 'block_students',
            'block_id' => $assignment->block_id,
            'priority' => $validated['priority'],
            'is_published' => true,
            'published_at' => now(),
        ]);

        // Send alert to all active students in this block
        $studentAllocations = StudentAllocation::whereHas('room', fn($q) => $q->where('block_id', $assignment->block_id))
            ->where('status', StudentAllocation::STATUS_ACTIVE)
            ->get();

        foreach ($studentAllocations as $alloc) {
            AttendanceAlert::create([
                'user_id' => $alloc->student_id,
                'alert_type' => 'block_notice',
                'title' => 'Dorm Notice: ' . $notice->title,
                'message' => $notice->summary ?: substr(strip_tags($notice->content), 0, 120),
                'action_url' => route('student.notices.index'),
                'priority' => $notice->priority === 'critical' ? 'high' : 'normal',
            ]);
        }

        return back()->with('success', "Notice published to all residents of Block " . ($assignment->block?->block_code ?? '') . ".");
    }

    public function proctorDestroy(Announcement $notice)
    {
        if ($notice->author_id !== Auth::id()) {
            abort(403);
        }

        $notice->delete();
        return back()->with('success', 'Student notice removed.');
    }

    // =========================================================================
    // 3. STUDENT: Notices Feed (Campus & Block-specific announcements)
    // =========================================================================

    public function studentIndex(): View
    {
        $student = Auth::user();
        $allocation = $student->activeAllocation()->with('room.block')->first();
        $blockId = $allocation?->room?->block_id;

        // Fetch notices targeted to student role (matching student's block or campus-wide)
        $notices = Announcement::forStudents($blockId)
            ->with(['author', 'block'])
            ->latest('published_at')
            ->paginate(12);

        return view('student.notices.index', compact('notices', 'allocation'));
    }
}
