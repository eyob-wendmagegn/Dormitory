<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\AttendanceAlert;
use App\Models\AttendanceReport;
use App\Models\AttendanceSubmission;
use App\Models\AuditLog;
use App\Models\DormitoryBlock;
use App\Models\Room;
use App\Models\User;
use App\Services\AttendanceService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    protected AttendanceService $attendanceService;

    public function __construct(AttendanceService $attendanceService)
    {
        $this->attendanceService = $attendanceService;
    }

    /**
     * PROCTOR: Curfew Attendance Walk (Room-by-room Web App)
     */
    public function proctorIndex(Request $request): View|RedirectResponse
    {
        $proctor = Auth::user();
        $assignedBlockIds = $proctor->activeProctorAssignments()->pluck('block_id');

        if ($assignedBlockIds->isEmpty()) {
            return view('proctor.attendance.no-blocks');
        }

        $managedBlocks = DormitoryBlock::whereIn('id', $assignedBlockIds)
            ->withCount('activeAllocations')
            ->orderBy('block_code')
            ->get();

        $selectedBlockId = (int) $request->input('block_id', $managedBlocks->first()?->id);

        if (! $assignedBlockIds->contains($selectedBlockId)) {
            $selectedBlockId = (int) $managedBlocks->first()?->id;
        }

        $date = $request->input('date', Carbon::today()->toDateString());
        $roomId = $request->filled('room_id') ? (int) $request->input('room_id') : null;

        $rosterData = $this->attendanceService->getBlockRoster($selectedBlockId, $date, $roomId);

        // Fetch all rooms for quick room-switcher tabs
        $allRooms = Room::where('block_id', $selectedBlockId)
            ->withCount('activeAllocations')
            ->orderByRoomNumber()
            ->get()
            ->sortBy('room_number', SORT_NATURAL)
            ->values();

        return view('proctor.attendance.index', compact(
            'managedBlocks',
            'selectedBlockId',
            'date',
            'roomId',
            'allRooms',
            'rosterData'
        ));
    }

    /**
     * PROCTOR: Save attendance for a specific room or student batch.
     * Computes next room ID for direct sequential walkthrough navigation.
     */
    public function proctorSaveRoom(Request $request): JsonResponse|RedirectResponse
    {
        $request->validate([
            'block_id' => 'required|exists:dormitory_blocks,id',
            'room_id' => 'required|exists:rooms,id',
            'date' => 'required|date',
            'students' => 'nullable|array',
            'students.*.student_id' => 'required_with:students|exists:users,id',
            'students.*.status' => 'nullable|string|in:present,absent,excused,sick,pending',
            'students.*.remarks' => 'nullable|string|max:500',
        ]);

        $proctor = Auth::user();
        $blockId = (int) $request->input('block_id');
        $roomId = (int) $request->input('room_id');

        // Verify proctor assignment
        $hasAssignment = $proctor->activeProctorAssignments()
            ->where('block_id', $blockId)
            ->exists();

        if (! $hasAssignment && ! $proctor->isAdmin() && ! $proctor->isProctorManager()) {
            if ($request->wantsJson()) {
                return response()->json(['error' => 'Unauthorized block assignment.'], 403);
            }
            return back()->with('error', 'You are not assigned to this dormitory block.');
        }

        $this->attendanceService->saveAttendance(
            blockId: $blockId,
            roomId: $roomId,
            date: $request->input('date'),
            records: $request->input('students', []),
            proctor: $proctor
        );

        // Find next room in the block for instant sequential transition
        $allRooms = Room::where('block_id', $blockId)
            ->orderByRoomNumber()
            ->get()
            ->sortBy('room_number', SORT_NATURAL)
            ->values();
        $currentIndex = $allRooms->search(fn($r) => $r->id === $roomId);
        $nextRoom = ($currentIndex !== false && $currentIndex + 1 < $allRooms->count())
            ? $allRooms->get($currentIndex + 1)
            : null;

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Room attendance updated successfully.',
                'room_id' => $roomId,
                'next_room_id' => $nextRoom?->id,
                'next_room_number' => $nextRoom?->room_number,
                'is_last_room' => is_null($nextRoom),
            ]);
        }

        return back()->with('success', 'Room attendance updated successfully.');
    }

    /**
     * PROCTOR: Save / Submit Daily Curfew Record.
     * Guaranteed to persist any modified student records (including absent)
     * and save the record in database as daily log without broadcasting daily alerts.
     */
    public function proctorFinalize(Request $request): JsonResponse|RedirectResponse
    {
        $request->validate([
            'block_id' => 'required|exists:dormitory_blocks,id',
            'date' => 'required|date',
            'notes' => 'nullable|string|max:1000',
            'students' => 'nullable|array',
            'students_json' => 'nullable|string',
            'students.*.student_id' => 'required_with:students|exists:users,id',
            'students.*.status' => 'nullable|string|in:present,absent,excused,sick,pending',
            'students.*.remarks' => 'nullable|string|max:500',
            'students.*.room_id' => 'nullable|exists:rooms,id',
        ]);

        $proctor = Auth::user();
        $blockId = (int) $request->input('block_id');
        $date = $request->input('date');

        // Check assignment
        $hasAssignment = $proctor->activeProctorAssignments()
            ->where('block_id', $blockId)
            ->exists();

        if (! $hasAssignment && ! $proctor->isAdmin() && ! $proctor->isProctorManager()) {
            if ($request->wantsJson()) {
                return response()->json(['error' => 'You are not authorized to save attendance for this block.'], 403);
            }
            return back()->with('error', 'You are not authorized to save attendance for this block.');
        }

        $students = $request->input('students');
        if (! $students && $request->filled('students_json')) {
            $decoded = json_decode($request->input('students_json'), true);
            if (is_array($decoded)) {
                $students = $decoded;
            }
        }

        $submission = $this->attendanceService->finalizeBlockAttendance(
            blockId: $blockId,
            date: $date,
            proctor: $proctor,
            notes: $request->input('notes'),
            submittedRecords: $students
        );

        $msg = "Daily curfew record successfully saved for Block {$submission->block->name}! {$submission->present_count} present, {$submission->absent_count} absent, {$submission->excused_count} on leave.";

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $msg,
                'submission' => $submission,
            ]);
        }

        return redirect()->route('proctor.attendance.index', [
            'block_id' => $blockId,
            'date' => $date,
        ])->with('success', $msg);
    }

    /**
     * PROCTOR: Attendance Reports (Weekly, Monthly, and Frequent Absentees)
     */
    public function proctorReports(Request $request): View|RedirectResponse
    {
        $proctor = Auth::user();
        $assignedBlockIds = $proctor->activeProctorAssignments()->pluck('block_id');

        if ($assignedBlockIds->isEmpty()) {
            return view('proctor.attendance.no-blocks');
        }

        $managedBlocks = DormitoryBlock::whereIn('id', $assignedBlockIds)
            ->withCount('activeAllocations')
            ->orderBy('block_code')
            ->get();

        $selectedBlockId = (int) $request->input('block_id', $managedBlocks->first()?->id);
        if (! $assignedBlockIds->contains($selectedBlockId)) {
            $selectedBlockId = (int) $managedBlocks->first()?->id;
        }

        $reportType = $request->input('type', 'weekly'); // weekly, monthly, custom

        if ($reportType === 'monthly') {
            $startDate = $request->input('start_date', Carbon::today()->startOfMonth()->toDateString());
            $endDate = $request->input('end_date', Carbon::today()->toDateString());
        } elseif ($reportType === 'custom') {
            $startDate = $request->input('start_date', Carbon::today()->subDays(6)->toDateString());
            $endDate = $request->input('end_date', Carbon::today()->toDateString());
        } else {
            $reportType = 'weekly';
            $startDate = $request->input('start_date', Carbon::today()->subDays(6)->toDateString());
            $endDate = $request->input('end_date', Carbon::today()->toDateString());
        }

        $reportSummary = $this->attendanceService->getPeriodicReport(
            blockId: $selectedBlockId,
            startDate: $startDate,
            endDate: $endDate
        );

        $submittedReports = AttendanceReport::whereIn('block_id', $assignedBlockIds)
            ->with(['block', 'reviewer'])
            ->orderByDesc('submitted_at')
            ->take(15)
            ->get();

        return view('proctor.attendance.reports', compact(
            'managedBlocks',
            'selectedBlockId',
            'reportType',
            'startDate',
            'endDate',
            'reportSummary',
            'submittedReports'
        ));
    }

    /**
     * PROCTOR: Submit Weekly or Monthly Report to Proctor Manager
     */
    public function proctorSubmitReport(Request $request): RedirectResponse
    {
        $request->validate([
            'block_id' => 'required|exists:dormitory_blocks,id',
            'report_type' => 'required|in:weekly,monthly,custom',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'notes' => 'nullable|string|max:2000',
        ]);

        $proctor = Auth::user();
        $blockId = (int) $request->input('block_id');

        $hasAssignment = $proctor->activeProctorAssignments()->where('block_id', $blockId)->exists();
        if (! $hasAssignment && ! $proctor->isAdmin() && ! $proctor->isProctorManager()) {
            return back()->with('error', 'You are not assigned to this dormitory block.');
        }

        $report = $this->attendanceService->submitPeriodicReport(
            blockId: $blockId,
            proctor: $proctor,
            reportType: $request->input('report_type'),
            startDate: $request->input('start_date'),
            endDate: $request->input('end_date'),
            notes: $request->input('notes')
        );

        $typeLabel = ucfirst($report->report_type);
        return back()->with('success', "{$typeLabel} attendance report for Block {$report->block->block_code} successfully submitted to the Proctor Manager!");
    }

    /**
     * STUDENT: My Attendance History & Personal Records
     */
    public function studentIndex(Request $request): View
    {
        $student = Auth::user();
        $summary = $this->attendanceService->getStudentAttendanceSummary($student);
        $allocation = $student->activeAllocation()->with(['room.block'])->first();

        // Check for active approved leave
        $activeLeave = $student->leaveRequests()
            ->activeOnDate(Carbon::today()->toDateString())
            ->first();

        return view('student.attendance.index', compact('student', 'summary', 'allocation', 'activeLeave'));
    }

    /**
     * PROCTOR MANAGER / ADMIN: Curfew Monitoring & Daily Absentee List
     */
    public function managerIndex(Request $request): View
    {
        $date = $request->input('date', Carbon::today()->toDateString());
        $blockId = $request->filled('block_id') ? (int) $request->input('block_id') : null;
        $search = $request->input('search');

        $monitoring = $this->attendanceService->getCurfewMonitoringOverview($date);
        $absentees = $this->attendanceService->getDailyAbsenteeList($date, $blockId, $search);
        $blocks = DormitoryBlock::orderBy('block_code')->get();

        return view('manager.attendance.index', compact(
            'date',
            'blockId',
            'search',
            'monitoring',
            'absentees',
            'blocks'
        ));
    }

    /**
     * PROCTOR MANAGER / ADMIN: Bulk Notify Absent Students
     */
    public function managerNotifyAbsent(Request $request): RedirectResponse
    {
        $request->validate([
            'date' => 'required|date',
            'block_id' => 'nullable|exists:dormitory_blocks,id',
            'custom_message' => 'nullable|string|max:500',
        ]);

        $date = $request->input('date');
        $blockId = $request->input('block_id');
        $customMsg = $request->input('custom_message');

        $absentees = $this->attendanceService->getDailyAbsenteeList($date, $blockId ? (int) $blockId : null);

        $dispatched = 0;
        foreach ($absentees as $att) {
            $msg = $customMsg ?: "URGENT DORMITORY ALERT: You were flagged as ABSENT on {$date} in {$att->block->name}, Room {$att->room->room_number}. Immediate presence or written justification required at the Proctor Office.";

            AttendanceAlert::sendAlert(
                user: $att->student_id,
                type: AttendanceAlert::TYPE_ABSENTEE_WARNING,
                title: 'Official Attendance Curfew Notice',
                message: $msg,
                date: $date,
                actionUrl: route('student.attendance.index')
            );
            $dispatched++;
        }

        AuditLog::log(
            action: 'attendance.bulk_notified',
            description: "Administrator notified {$dispatched} absent student(s) for {$date} curfew.",
            payload: ['date' => $date, 'count' => $dispatched],
            user: Auth::user()
        );

        return back()->with('success', "Dispatched official attendance warnings to {$dispatched} absent resident(s).");
    }

    /**
     * PROCTOR MANAGER / ADMIN: Campus Attendance Analytics & Reports
     */
    public function managerReports(Request $request): View
    {
        $dateFrom = $request->input('date_from', Carbon::today()->subDays(14)->toDateString());
        $dateTo = $request->input('date_to', Carbon::today()->toDateString());
        $blockId = $request->filled('block_id') ? (int) $request->input('block_id') : null;

        $submissionsQuery = AttendanceSubmission::with(['block', 'proctor'])
            ->whereBetween('date', [$dateFrom, $dateTo])
            ->orderByDesc('date');

        if ($blockId) {
            $submissionsQuery->where('block_id', $blockId);
        }

        $submissions = $submissionsQuery->get();

        // Chronic absenteeism tracker: students with 3+ absences in the period
        $chronicAbsenteesQuery = Attendance::whereBetween('date', [$dateFrom, $dateTo])
            ->where('status', Attendance::STATUS_ABSENT)
            ->selectRaw('student_id, COUNT(*) as absence_count')
            ->groupBy('student_id')
            ->havingRaw('COUNT(*) >= 2')
            ->orderByDesc('absence_count')
            ->with(['student.activeAllocation.room.block']);

        $chronicAbsentees = $chronicAbsenteesQuery->take(15)->get();

        // Overall stats in range
        $totalRecorded = $submissions->sum('total_students');
        $totalPresent = $submissions->sum('present_count');
        $totalAbsent = $submissions->sum('absent_count');
        $totalExcused = $submissions->sum('excused_count');
        $totalSick = $submissions->sum('sick_count');
        $overallRate = $totalRecorded > 0 ? round(($totalPresent / $totalRecorded) * 100, 1) : 0;

        $blocks = DormitoryBlock::orderBy('block_code')->get();
        $submittedPeriodicReports = AttendanceReport::with(['block', 'proctor', 'reviewer'])
            ->orderByDesc('submitted_at')
            ->get();

        return view('manager.attendance.reports', compact(
            'dateFrom',
            'dateTo',
            'blockId',
            'submissions',
            'chronicAbsentees',
            'totalRecorded',
            'totalPresent',
            'totalAbsent',
            'totalExcused',
            'totalSick',
            'overallRate',
            'blocks',
            'submittedPeriodicReports'
        ));
    }

    /**
     * PROCTOR MANAGER / ADMIN: Review submitted periodic report from proctor
     */
    public function managerReviewReport(Request $request, AttendanceReport $report): RedirectResponse
    {
        $request->validate([
            'manager_remarks' => 'nullable|string|max:1000',
        ]);

        $report->update([
            'status' => AttendanceReport::STATUS_REVIEWED,
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
            'manager_remarks' => $request->input('manager_remarks'),
        ]);

        AttendanceAlert::sendAlert(
            user: $report->proctor_id,
            type: 'report_reviewed',
            title: 'Attendance Report Reviewed',
            message: "Proctor Manager " . Auth::user()->name . " has reviewed your {$report->report_type} report for {$report->block->name}.",
            date: $report->end_date->toDateString(),
            actionUrl: route('proctor.attendance.reports')
        );

        return back()->with('success', "Report #{$report->id} marked as reviewed.");
    }

    /**
     * Export Daily Attendance / Absentee List to CSV
     */
    public function exportCsv(Request $request)
    {
        $date = $request->input('date', Carbon::today()->toDateString());
        $type = $request->input('type', 'absentees'); // 'absentees' or 'all'

        $filename = "attendance_{$type}_{$date}.csv";

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($date, $type) {
            $handle = fopen('php://output', 'w');

            // Header line
            fputcsv($handle, [
                'Attendance ID',
                'Date',
                'University ID',
                'Student Name',
                'Gender',
                'Department',
                'Academic Year',
                'Block',
                'Room',
                'Status',
                'Recorded By (Proctor)',
                'Timestamp',
                'Remarks',
            ]);

            $query = Attendance::whereDate('date', $date)
                ->with(['student', 'room', 'block', 'proctor']);

            if ($type === 'absentees') {
                $query->where('status', Attendance::STATUS_ABSENT);
            }

            $query->chunk(200, function ($records) use ($handle) {
                foreach ($records as $att) {
                    fputcsv($handle, [
                        $att->id,
                        $att->date->toDateString(),
                        $att->student?->university_id ?? 'N/A',
                        $att->student?->name ?? 'Unknown',
                        $att->student?->gender ?? 'N/A',
                        $att->student?->department ?? 'General',
                        $att->student?->academic_year_label ?? 'N/A',
                        $att->block?->name . ' (' . ($att->block?->block_code ?? '') . ')',
                        $att->room?->room_number ?? 'N/A',
                        ucfirst($att->status),
                        $att->proctor?->name ?? 'System',
                        $att->recorded_at?->toDateTimeString() ?? 'N/A',
                        $att->remarks ?? '',
                    ]);
                }
            });

            fclose($handle);
        };

        return Response::stream($callback, 200, $headers);
    }
}
