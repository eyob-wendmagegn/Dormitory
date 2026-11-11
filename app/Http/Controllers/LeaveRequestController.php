<?php

namespace App\Http\Controllers;

use App\Models\AttendanceAlert;
use App\Models\AuditLog;
use App\Models\LeaveRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LeaveRequestController extends Controller
{
    /**
     * STUDENT: View my Gate Passes & Leave Requests
     */
    public function studentIndex(Request $request): View
    {
        $student = Auth::user();
        $allocation = $student->activeAllocation()->with(['room.block'])->first();

        $leaves = LeaveRequest::where('student_id', $student->id)
            ->with('approver')
            ->orderByDesc('created_at')
            ->get();

        $activePass = $leaves->first(function ($leave) {
            return $leave->isApproved() && Carbon::today()->between($leave->start_date, $leave->end_date);
        });

        return view('student.leaves.index', compact('student', 'allocation', 'leaves', 'activePass'));
    }

    /**
     * STUDENT: Submit a new Gate Pass / Leave Request
     */
    public function studentStore(Request $request): RedirectResponse
    {
        $request->validate([
            'request_type' => 'required|in:gate_pass,overnight,weekend,medical,emergency',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'destination' => 'required|string|max:255',
            'emergency_phone' => 'nullable|string|max:30',
            'reason' => 'required|string|max:1000',
        ]);

        $student = Auth::user();

        // Check if student has an active allocation
        $allocation = $student->activeAllocation()->first();
        if (! $allocation) {
            return back()->with('error', 'You must be currently allocated to a dormitory room to request leave.');
        }

        // Check for duplicate pending requests for overlapping dates
        $overlap = LeaveRequest::where('student_id', $student->id)
            ->whereIn('status', [LeaveRequest::STATUS_PENDING, LeaveRequest::STATUS_APPROVED])
            ->where(function ($q) use ($request) {
                $q->whereBetween('start_date', [$request->start_date, $request->end_date])
                  ->orWhereBetween('end_date', [$request->start_date, $request->end_date])
                  ->orWhere(function ($sub) use ($request) {
                      $sub->where('start_date', '<=', $request->start_date)
                          ->where('end_date', '>=', $request->end_date);
                  });
            })
            ->exists();

        if ($overlap) {
            return back()->with('error', 'You already have an active or pending leave request for the selected dates.');
        }

        $leave = LeaveRequest::create([
            'student_id' => $student->id,
            'request_type' => $request->request_type,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'destination' => $request->destination,
            'emergency_phone' => $request->emergency_phone,
            'reason' => $request->reason,
            'status' => LeaveRequest::STATUS_PENDING,
        ]);

        // Notify proctor of the block
        $block = $allocation->room?->block;
        if ($block) {
            $proctorIds = $block->proctorAssignments()
                ->whereNull('ended_at')
                ->pluck('proctor_id');

            foreach ($proctorIds as $pId) {
                AttendanceAlert::sendAlert(
                    user: $pId,
                    type: AttendanceAlert::TYPE_GENERAL,
                    title: "New {$leave->type_label} Request",
                    message: "Student {$student->name} ({$block->block_code} Rm {$allocation->room->room_number}) requested leave from {$leave->start_date->format('M d')} to {$leave->end_date->format('M d')}.",
                    date: $leave->start_date->toDateString(),
                    actionUrl: route('proctor.leaves.index')
                );
            }
        }

        AuditLog::log(
            action: 'leave.submitted',
            description: "Student {$student->name} requested {$leave->type_label} for {$leave->start_date->toDateString()}.",
            payload: ['leave_id' => $leave->id],
            user: $student
        );

        return back()->with('success', 'Leave request submitted successfully. Awaiting proctor review.');
    }

    /**
     * STUDENT: Cancel pending request
     */
    public function studentCancel(LeaveRequest $leave): RedirectResponse
    {
        if ($leave->student_id !== Auth::id()) {
            abort(403);
        }

        if (! $leave->isPending()) {
            return back()->with('error', 'Only pending requests can be cancelled.');
        }

        $leave->update(['status' => LeaveRequest::STATUS_CANCELLED]);

        return back()->with('success', 'Leave request cancelled.');
    }

    /**
     * PROCTOR: View leave requests for assigned blocks
     */
    public function proctorIndex(Request $request): View
    {
        $proctor = Auth::user();
        $assignedBlockIds = $proctor->activeProctorAssignments()->pluck('block_id');

        $status = $request->input('status', 'all');

        $query = LeaveRequest::whereHas('student.activeAllocation.room', function ($q) use ($assignedBlockIds) {
            $q->whereIn('block_id', $assignedBlockIds);
        })->with(['student.activeAllocation.room.block', 'approver'])
          ->orderByDesc('created_at');

        if ($status !== 'all' && in_array($status, ['pending', 'approved', 'rejected'])) {
            $query->where('status', $status);
        }

        $leaves = $query->paginate(15);

        $counts = [
            'pending' => LeaveRequest::whereHas('student.activeAllocation.room', function ($q) use ($assignedBlockIds) {
                $q->whereIn('block_id', $assignedBlockIds);
            })->where('status', LeaveRequest::STATUS_PENDING)->count(),
            'approved' => LeaveRequest::whereHas('student.activeAllocation.room', function ($q) use ($assignedBlockIds) {
                $q->whereIn('block_id', $assignedBlockIds);
            })->where('status', LeaveRequest::STATUS_APPROVED)->count(),
            'rejected' => LeaveRequest::whereHas('student.activeAllocation.room', function ($q) use ($assignedBlockIds) {
                $q->whereIn('block_id', $assignedBlockIds);
            })->where('status', LeaveRequest::STATUS_REJECTED)->count(),
        ];

        return view('proctor.leaves.index', compact('leaves', 'counts', 'status'));
    }

    /**
     * PROCTOR / MANAGER: Approve Leave Request
     */
    public function approve(Request $request, LeaveRequest $leave): RedirectResponse
    {
        $request->validate([
            'remarks' => 'nullable|string|max:500',
        ]);

        $reviewer = Auth::user();

        $leave->update([
            'status' => LeaveRequest::STATUS_APPROVED,
            'approved_by' => $reviewer->id,
            'approved_at' => now(),
            'remarks' => $request->input('remarks'),
        ]);

        // Send confirmation alert to the student
        AttendanceAlert::sendAlert(
            user: $leave->student_id,
            type: AttendanceAlert::TYPE_LEAVE_APPROVED,
            title: "Gate Pass / Leave Approved! (#LR-{$leave->id})",
            message: "Your {$leave->type_label} from {$leave->start_date->format('M d, Y')} to {$leave->end_date->format('M d, Y')} was APPROVED by {$reviewer->name}. Present your digital pass if requested at campus gates.",
            date: $leave->start_date->toDateString(),
            actionUrl: route('student.leaves.index')
        );

        AuditLog::log(
            action: 'leave.approved',
            description: "Proctor/Manager {$reviewer->name} approved leave #{$leave->id} for {$leave->student->name}.",
            payload: ['leave_id' => $leave->id, 'student_id' => $leave->student_id],
            user: $reviewer
        );

        return back()->with('success', "Leave request approved! Student will automatically be marked 'On Leave' during curfew rounds.");
    }

    /**
     * PROCTOR / MANAGER: Reject Leave Request
     */
    public function reject(Request $request, LeaveRequest $leave): RedirectResponse
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $reviewer = Auth::user();

        $leave->update([
            'status' => LeaveRequest::STATUS_REJECTED,
            'approved_by' => $reviewer->id,
            'approved_at' => now(),
            'rejection_reason' => $request->input('rejection_reason'),
        ]);

        // Send alert to the student
        AttendanceAlert::sendAlert(
            user: $leave->student_id,
            type: AttendanceAlert::TYPE_LEAVE_REJECTED,
            title: "Leave Request Declined (#LR-{$leave->id})",
            message: "Your {$leave->type_label} was not approved. Reason: {$request->input('rejection_reason')}",
            date: $leave->start_date->toDateString(),
            actionUrl: route('student.leaves.index')
        );

        AuditLog::log(
            action: 'leave.rejected',
            description: "Proctor/Manager {$reviewer->name} rejected leave #{$leave->id} for {$leave->student->name}.",
            payload: ['leave_id' => $leave->id, 'reason' => $request->input('rejection_reason')],
            user: $reviewer
        );

        return back()->with('success', 'Leave request rejected and student notified.');
    }

    /**
     * PROCTOR MANAGER / ADMIN: Campus-Wide Leave Request Oversight
     */
    public function managerIndex(Request $request): View
    {
        $status = $request->input('status', 'all');

        $query = LeaveRequest::with(['student.activeAllocation.room.block', 'approver'])
            ->orderByDesc('created_at');

        if ($status !== 'all' && in_array($status, ['pending', 'approved', 'rejected'])) {
            $query->where('status', $status);
        }

        $leaves = $query->paginate(20);

        $counts = [
            'total' => LeaveRequest::count(),
            'pending' => LeaveRequest::where('status', LeaveRequest::STATUS_PENDING)->count(),
            'approved' => LeaveRequest::where('status', LeaveRequest::STATUS_APPROVED)->count(),
            'rejected' => LeaveRequest::where('status', LeaveRequest::STATUS_REJECTED)->count(),
        ];

        return view('manager.leaves.index', compact('leaves', 'counts', 'status'));
    }
}
