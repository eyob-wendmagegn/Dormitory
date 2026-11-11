<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Comment;
use App\Models\StudentAllocation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudentController extends Controller
{
    /**
     * My Dormitory Allocation Details
     */
    public function myDorm(): View
    {
        $student = auth()->user();
        $allocation = $student->activeAllocation()->with(['room.block', 'allocator'])->first();
        $room = $allocation?->room;
        $block = $room?->block;

        $roommates = collect();
        if ($room) {
            $roommates = StudentAllocation::with('student')
                ->where('room_id', $room->id)
                ->where('status', StudentAllocation::STATUS_ACTIVE)
                ->where('student_id', '!=', $student->id)
                ->get()
                ->pluck('student');
        }

        return view('student.my-dorm', compact('student', 'allocation', 'room', 'block', 'roommates'));
    }

    /**
     * Room Information & Housing Guidelines
     */
    public function roomInfo(): View
    {
        $student = auth()->user();
        $allocation = $student->activeAllocation()->with(['room.block'])->first();

        return view('student.room-info', compact('student', 'allocation'));
    }

    /**
     * Submit Feedback / Comment Form
     */
    public function createComment(): View
    {
        return view('student.submit-comment');
    }

    /**
     * Store Comment
     */
    public function storeComment(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'category' => ['required', 'string', 'in:maintenance,complaint,inquiry,suggestion,other'],
            'title' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string'],
        ]);

        $student = auth()->user();
        $blockId = $student->activeAllocation?->room?->block_id;

        $comment = Comment::create([
            'user_id' => $student->id,
            'category' => $validated['category'],
            'target_role' => 'proctor', // Students give comments for their block proctor
            'block_id' => $blockId,
            'title' => $validated['title'],
            'message' => $validated['message'],
            'status' => Comment::STATUS_PENDING,
        ]);

        AuditLog::log('comment_submitted', "Student submitted {$comment->category} feedback for block proctor: {$comment->title}", null, $student);

        return redirect()->route('student.comments.index')->with('success', 'Your feedback has been submitted to your block proctor.');
    }

    /**
     * View My Comments
     */
    public function comments(): View
    {
        $comments = auth()->user()->comments()->with('responder')->latest()->paginate(10);
        return view('student.my-comments', compact('comments'));
    }
}
