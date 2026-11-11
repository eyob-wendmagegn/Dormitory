<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\AttendanceAlert;
use App\Models\AttendanceReport;
use App\Models\AttendanceSubmission;
use App\Models\AuditLog;
use App\Models\DormitoryBlock;
use App\Models\LeaveRequest;
use App\Models\Room;
use App\Models\StudentAllocation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AttendanceService
{
    /**
     * Curfew window details
     */
    public const CURFEW_START = '21:00';
    public const CURFEW_END = '22:00';

    /**
     * Check if currently within or past curfew
     */
    public function getCurfewStatus(): array
    {
        $now = Carbon::now();
        $startTime = Carbon::createFromTimeString(self::CURFEW_START);
        $endTime = Carbon::createFromTimeString(self::CURFEW_END);

        $isActive = $now->between($startTime, $endTime);
        $isPast = $now->isAfter($endTime);

        return [
            'window' => self::CURFEW_START . ' - ' . self::CURFEW_END,
            'is_active' => $isActive,
            'is_past' => $isPast,
            'current_time' => $now->format('H:i'),
            'date' => $now->toDateString(),
        ];
    }

    /**
     * Build attendance roster for a block on a given date.
     * Auto-detects approved leaves to mark students as "Excused" with details.
     */
    public function getBlockRoster(int $blockId, string $date, ?int $roomId = null): array
    {
        $block = DormitoryBlock::findOrFail($blockId);

        $roomsQuery = Room::where('block_id', $blockId)
            ->with([
                'activeAllocations' => function ($q) {
                    $q->with('student');
                },
            ])
            ->orderByRoomNumber();

        if ($roomId) {
            $roomsQuery->where('id', $roomId);
        }

        $rooms = $roomsQuery->get()->sortBy('room_number', SORT_NATURAL)->values();

        // Get all active allocations in these rooms
        $allAllocations = $rooms->flatMap->activeAllocations;
        $studentIds = $allAllocations->pluck('student_id')->unique()->filter()->values();

        // Existing attendances for this date
        $existingAttendances = Attendance::where('block_id', $blockId)
            ->whereDate('date', $date)
            ->get()
            ->keyBy('student_id');

        // Active approved leave requests covering this date
        $activeLeaves = LeaveRequest::whereIn('student_id', $studentIds)
            ->activeOnDate($date)
            ->get()
            ->keyBy('student_id');

        // Weekly absence counts for identifying chronic/frequent absentees (past 7 days up to date)
        $weekStartDate = Carbon::parse($date)->subDays(6)->toDateString();
        $weeklyAbsenceCounts = Attendance::whereIn('student_id', $studentIds)
            ->whereBetween('date', [$weekStartDate, $date])
            ->where('status', Attendance::STATUS_ABSENT)
            ->groupBy('student_id')
            ->selectRaw('student_id, COUNT(*) as count')
            ->pluck('count', 'student_id');

        // Check if block attendance has been finalized for this date
        $submission = AttendanceSubmission::where('block_id', $blockId)
            ->whereDate('date', $date)
            ->first();

        // Structure roster per room
        $rosterRooms = [];
        $summary = [
            'total' => 0,
            'present' => 0,
            'absent' => 0,
            'excused' => 0,
            'sick' => 0,
            'pending' => 0,
        ];

        foreach ($rooms as $room) {
            $studentsData = [];

            foreach ($room->activeAllocations as $allocation) {
                $student = $allocation->student;
                if (! $student) {
                    continue;
                }

                $summary['total']++;

                $attendance = $existingAttendances->get($student->id);
                $leave = $activeLeaves->get($student->id);

                if ($attendance) {
                    $status = $attendance->status;
                    $remarks = $attendance->remarks;
                    $leaveId = $attendance->leave_request_id;
                    $isAutoExcused = false;
                } elseif ($leave) {
                    // Auto-flag as Excused on Leave!
                    $status = Attendance::STATUS_EXCUSED;
                    $remarks = "Approved {$leave->type_label} (#LR-{$leave->id}) to {$leave->destination}";
                    $leaveId = $leave->id;
                    $isAutoExcused = true;
                } else {
                    $status = 'pending';
                    $remarks = null;
                    $leaveId = null;
                    $isAutoExcused = false;
                }

                if (isset($summary[$status])) {
                    $summary[$status]++;
                } else {
                    $summary['pending']++;
                }

                $weeklyAbsences = (int) ($weeklyAbsenceCounts->get($student->id, 0));

                $studentsData[] = [
                    'student_id' => $student->id,
                    'name' => $student->name,
                    'university_id' => $student->university_id,
                    'avatar_url' => $student->avatar_url,
                    'has_photo' => $student->has_custom_photo,
                    'gender' => $student->gender,
                    'department' => $student->department ?? 'General',
                    'academic_year' => $student->academic_year_label ?? 'Undergrad',
                    'allocation_id' => $allocation->id,
                    'status' => $status,
                    'remarks' => $remarks,
                    'attendance_id' => $attendance?->id,
                    'has_leave' => ! empty($leave),
                    'leave_request' => $leave ? [
                        'id' => $leave->id,
                        'type' => $leave->type_label,
                        'start_date' => $leave->start_date->toDateString(),
                        'end_date' => $leave->end_date->toDateString(),
                        'destination' => $leave->destination,
                        'reason' => $leave->reason,
                    ] : null,
                    'is_auto_excused' => $isAutoExcused,
                    'absent_count_this_week' => $weeklyAbsences,
                    'is_frequent_absentee' => ($weeklyAbsences >= 2),
                ];
            }

            $rosterRooms[] = [
                'id' => $room->id,
                'room_number' => $room->room_number,
                'floor' => $room->floor,
                'capacity' => $room->capacity,
                'occupied' => count($studentsData),
                'students' => $studentsData,
            ];
        }

        // Add sequential room navigation links
        $roomCount = count($rosterRooms);
        for ($i = 0; $i < $roomCount; $i++) {
            $rosterRooms[$i]['prev_room_id'] = $i > 0 ? $rosterRooms[$i - 1]['id'] : null;
            $rosterRooms[$i]['next_room_id'] = $i < $roomCount - 1 ? $rosterRooms[$i + 1]['id'] : null;
            $rosterRooms[$i]['is_last_room'] = ($i === $roomCount - 1);
            $rosterRooms[$i]['room_index'] = $i + 1;
            $rosterRooms[$i]['total_rooms'] = $roomCount;
        }

        return [
            'block' => $block,
            'date' => $date,
            'submission' => $submission,
            'is_finalized' => (bool) $submission,
            'rooms' => $rosterRooms,
            'summary' => $summary,
            'curfew' => $this->getCurfewStatus(),
            'floors' => collect($rosterRooms)->pluck('floor')->unique()->filter()->values()->all(),
        ];
    }

    /**
     * Save attendance records for one or more students (e.g. room round).
     */
    public function saveAttendance(int $blockId, int $roomId, string $date, array $records, User $proctor): array
    {
        $saved = [];

        DB::transaction(function () use ($blockId, $roomId, $date, $records, $proctor, &$saved) {
            foreach ($records as $record) {
                $studentId = $record['student_id'] ?? null;
                $status = $record['status'] ?? null;
                $remarks = $record['remarks'] ?? null;
                $leaveRequestId = $record['leave_request_id'] ?? null;

                if (! $studentId) {
                    continue;
                }

                // If proctor did not check attendance (pending, empty, or null):
                // Leave it blank for this date! If an existing attendance record was present, delete it.
                if (empty($status) || $status === 'pending') {
                    Attendance::where('student_id', $studentId)
                        ->whereDate('date', $date)
                        ->delete();
                    continue;
                }

                // If no leave_request_id passed, check if one exists for auto-linking
                if (! $leaveRequestId && $status === Attendance::STATUS_EXCUSED) {
                    $activeLeave = LeaveRequest::where('student_id', $studentId)
                        ->activeOnDate($date)
                        ->first();
                    $leaveRequestId = $activeLeave?->id;
                }

                $attendance = Attendance::updateOrCreate(
                    [
                        'student_id' => $studentId,
                        'date' => $date,
                    ],
                    [
                        'proctor_id' => $proctor->id,
                        'block_id' => $blockId,
                        'room_id' => $roomId,
                        'leave_request_id' => $leaveRequestId,
                        'status' => $status,
                        'remarks' => $remarks,
                        'recorded_at' => now(),
                    ]
                );

                $saved[] = $attendance;
            }
        });

        return $saved;
    }

    /**
     * Finalize & Save daily attendance records for a block.
     * Persists any checked student records submitted from client.
     * When proctors do NOT check attendance for a student, they are left blank at that date.
     */
    public function finalizeBlockAttendance(
        int $blockId,
        string $date,
        User $proctor,
        ?string $notes = null,
        ?array $submittedRecords = null
    ): AttendanceSubmission {
        return DB::transaction(function () use ($blockId, $date, $proctor, $notes, $submittedRecords) {
            $block = DormitoryBlock::findOrFail($blockId);

            // Fetch all active student allocations for this block
            $allocations = StudentAllocation::whereHas('room', function ($q) use ($blockId) {
                $q->where('block_id', $blockId);
            })->where('status', StudentAllocation::STATUS_ACTIVE)->with('student')->get();

            // First, if client submitted records, persist checked records and remove pending ones
            if (! empty($submittedRecords)) {
                foreach ($submittedRecords as $rec) {
                    $studentId = $rec['student_id'] ?? null;
                    $status = $rec['status'] ?? null;
                    $remarks = $rec['remarks'] ?? null;
                    $roomId = $rec['room_id'] ?? null;

                    if (! $studentId) {
                        continue;
                    }

                    if (empty($status) || $status === 'pending') {
                        // Proctor did NOT check attendance: leave blank at that date
                        Attendance::where('student_id', $studentId)
                            ->whereDate('date', $date)
                            ->delete();
                        continue;
                    }

                    if (! $roomId) {
                        $alloc = $allocations->firstWhere('student_id', $studentId);
                        $roomId = $alloc?->room_id;
                    }

                    if ($roomId) {
                        $leaveRequestId = $rec['leave_request_id'] ?? null;
                        if (! $leaveRequestId && $status === Attendance::STATUS_EXCUSED) {
                            $activeLeave = LeaveRequest::where('student_id', $studentId)
                                ->activeOnDate($date)
                                ->first();
                            $leaveRequestId = $activeLeave?->id;
                        }

                        Attendance::updateOrCreate(
                            [
                                'student_id' => $studentId,
                                'date' => $date,
                            ],
                            [
                                'proctor_id' => $proctor->id,
                                'block_id' => $blockId,
                                'room_id' => $roomId,
                                'leave_request_id' => $leaveRequestId,
                                'status' => $status,
                                'remarks' => $remarks,
                                'recorded_at' => now(),
                                'is_finalized' => true,
                            ]
                        );
                    }
                }
            }

            // Note: If proctor did NOT check attendance for a student,
            // we leave it blank at that date (no automatic creation of "present" records).

            // Mark all attendances for this block and date as finalized
            Attendance::where('block_id', $blockId)
                ->whereDate('date', $date)
                ->update(['is_finalized' => true]);

            // Compute counts
            $counts = Attendance::where('block_id', $blockId)
                ->whereDate('date', $date)
                ->selectRaw("
                    COUNT(*) as total,
                    COUNT(CASE WHEN status = 'present' THEN 1 END) as present,
                    COUNT(CASE WHEN status = 'absent' THEN 1 END) as absent,
                    COUNT(CASE WHEN status = 'excused' THEN 1 END) as excused,
                    COUNT(CASE WHEN status = 'sick' THEN 1 END) as sick
                ")->first();

            $total = (int) ($counts->total ?? 0);
            $present = (int) ($counts->present ?? 0);
            $absent = (int) ($counts->absent ?? 0);
            $excused = (int) ($counts->excused ?? 0);
            $sick = (int) ($counts->sick ?? 0);

            // Create or update submission record
            $submission = AttendanceSubmission::updateOrCreate(
                [
                    'block_id' => $blockId,
                    'date' => $date,
                ],
                [
                    'proctor_id' => $proctor->id,
                    'total_students' => $total,
                    'present_count' => $present,
                    'absent_count' => $absent,
                    'excused_count' => $excused,
                    'sick_count' => $sick,
                    'submitted_at' => now(),
                    'notes' => $notes,
                    'status' => 'submitted',
                ]
            );

            // Send warning alerts to absent students
            $absentAttendances = Attendance::where('block_id', $blockId)
                ->whereDate('date', $date)
                ->where('status', Attendance::STATUS_ABSENT)
                ->with(['student', 'room'])
                ->get();

            foreach ($absentAttendances as $att) {
                AttendanceAlert::sendAlert(
                    user: $att->student_id,
                    type: AttendanceAlert::TYPE_ABSENTEE_WARNING,
                    title: 'Dormitory Curfew Absence Warning',
                    message: "You were logged as ABSENT during nightly curfew inspection in {$block->name}, Room {$att->room->room_number} on {$date}. If this is an error, present yourself to Proctor {$proctor->name} immediately.",
                    date: $date,
                    actionUrl: route('student.attendance.index')
                );
            }

            // Alert Administrators & Managers of daily curfew submission
            $administrators = User::whereIn('role', [User::ROLE_ADMIN, User::ROLE_PROCTOR_MANAGER])
                ->where('is_active', true)
                ->get();

            foreach ($administrators as $adminUser) {
                AttendanceAlert::sendAlert(
                    user: $adminUser->id,
                    type: AttendanceAlert::TYPE_ATTENDANCE_SUBMITTED,
                    title: "Curfew Rounds Saved: {$block->name}",
                    message: "Proctor {$proctor->name} saved curfew attendance for {$block->name} on {$date}. Total: {$total}, Present: {$present}, Absent: {$absent}, Excused: {$excused}, Sick: {$sick}.",
                    date: $date,
                    actionUrl: route('manager.attendance.index', ['date' => $date, 'block_id' => $blockId])
                );
            }

            AuditLog::log(
                action: 'attendance.daily_saved',
                description: "Proctor {$proctor->name} saved daily attendance for Block {$block->block_code} on {$date}. Total: {$total}, Present: {$present}, Absent: {$absent}.",
                payload: [
                    'block_id' => $blockId,
                    'date' => $date,
                    'total' => $total,
                    'present' => $present,
                    'absent' => $absent,
                    'excused' => $excused,
                    'sick' => $sick,
                ],
                user: $proctor
            );

            return $submission;
        });
    }

    /**
     * Generate aggregated Weekly or Monthly report summary for a block.
     */
    public function getPeriodicReport(int $blockId, string $startDate, string $endDate): array
    {
        $block = DormitoryBlock::findOrFail($blockId);

        $allocations = StudentAllocation::whereHas('room', function ($q) use ($blockId) {
            $q->where('block_id', $blockId);
        })->where('status', StudentAllocation::STATUS_ACTIVE)
          ->with(['student', 'room'])
          ->get();

        $totalResidents = $allocations->count();
        $studentMap = $allocations->keyBy('student_id');

        $attendances = Attendance::where('block_id', $blockId)
            ->whereBetween('date', [$startDate, $endDate])
            ->with(['student', 'room'])
            ->orderBy('date')
            ->get();

        // Distinct dates tracked
        $trackedDates = $attendances->pluck('date')
            ->map(fn($d) => Carbon::parse($d)->toDateString())
            ->unique()
            ->sort()
            ->values();

        $trackedDaysCount = $trackedDates->count();

        // Total aggregates
        $totalPresent = $attendances->where('status', Attendance::STATUS_PRESENT)->count();
        $totalAbsent = $attendances->where('status', Attendance::STATUS_ABSENT)->count();
        $totalExcused = $attendances->where('status', Attendance::STATUS_EXCUSED)->count();
        $totalSick = $attendances->where('status', Attendance::STATUS_SICK)->count();
        $totalEntries = $totalPresent + $totalAbsent + $totalExcused + $totalSick;
        $attendanceRate = $totalEntries > 0 ? round(($totalPresent / $totalEntries) * 100, 1) : 100.0;

        // Daily breakdown
        $dailyBreakdown = [];
        foreach ($trackedDates as $dStr) {
            $dayRecords = $attendances->filter(fn($a) => Carbon::parse($a->date)->toDateString() === $dStr);
            $dayPresent = $dayRecords->where('status', Attendance::STATUS_PRESENT)->count();
            $dayAbsent = $dayRecords->where('status', Attendance::STATUS_ABSENT)->count();
            $dayExcused = $dayRecords->where('status', Attendance::STATUS_EXCUSED)->count();
            $daySick = $dayRecords->where('status', Attendance::STATUS_SICK)->count();
            $dayTotal = $dayPresent + $dayAbsent + $dayExcused + $daySick;

            $dailyBreakdown[] = [
                'date' => $dStr,
                'total' => $dayTotal,
                'present' => $dayPresent,
                'absent' => $dayAbsent,
                'excused' => $dayExcused,
                'sick' => $daySick,
                'rate' => $dayTotal > 0 ? round(($dayPresent / $dayTotal) * 100, 1) : 100.0,
            ];
        }

        // Frequent absentees list (Students with >= 2 absences in the period)
        $absencesByStudent = $attendances->where('status', Attendance::STATUS_ABSENT)->groupBy('student_id');

        $frequentAbsentees = [];
        foreach ($absencesByStudent as $studentId => $records) {
            $count = $records->count();
            $alloc = $studentMap->get($studentId);
            $student = $alloc?->student ?? $records->first()?->student;
            $room = $alloc?->room ?? $records->first()?->room;

            if (! $student) {
                continue;
            }

            // Count unexcused vs excused remarks
            $unexcused = $records->filter(function ($r) {
                return empty($r->remarks) || stripos($r->remarks, 'excused') === false;
            })->count();

            $frequentAbsentees[] = [
                'student_id' => $student->id,
                'name' => $student->name,
                'university_id' => $student->university_id,
                'avatar_url' => $student->avatar_url,
                'has_photo' => $student->has_custom_photo,
                'department' => $student->department ?? 'General',
                'room_number' => $room?->room_number ?? 'N/A',
                'floor' => $room?->floor ?? 1,
                'total_absences' => $count,
                'unexcused_absences' => $unexcused,
                'last_absence_date' => $records->sortByDesc('date')->first()?->date?->toDateString(),
                'absence_dates' => $records->pluck('date')->map(fn($d) => Carbon::parse($d)->format('M d'))->values()->all(),
                'is_chronic' => $count >= 3,
            ];
        }

        // Sort frequent absentees by total absences descending
        usort($frequentAbsentees, fn($a, $b) => $b['total_absences'] <=> $a['total_absences']);

        // Check for existing submitted report in this range
        $submittedReport = AttendanceReport::where('block_id', $blockId)
            ->where('start_date', $startDate)
            ->where('end_date', $endDate)
            ->latest()
            ->first();

        return [
            'block' => $block,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'tracked_days_count' => $trackedDaysCount,
            'total_residents' => $totalResidents,
            'total_present' => $totalPresent,
            'total_absent' => $totalAbsent,
            'total_excused' => $totalExcused,
            'total_sick' => $totalSick,
            'total_entries' => $totalEntries,
            'attendance_rate' => $attendanceRate,
            'daily_breakdown' => $dailyBreakdown,
            'frequent_absentees' => $frequentAbsentees,
            'submitted_report' => $submittedReport,
            'is_submitted' => ! empty($submittedReport),
        ];
    }

    /**
     * Proctor submits weekly or monthly aggregated report to Proctor Manager.
     */
    public function submitPeriodicReport(
        int $blockId,
        User $proctor,
        string $reportType,
        string $startDate,
        string $endDate,
        ?string $notes = null
    ): AttendanceReport {
        $summary = $this->getPeriodicReport($blockId, $startDate, $endDate);
        $block = DormitoryBlock::findOrFail($blockId);

        $report = AttendanceReport::updateOrCreate(
            [
                'block_id' => $blockId,
                'report_type' => $reportType,
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
            [
                'proctor_id' => $proctor->id,
                'tracked_days_count' => $summary['tracked_days_count'],
                'total_residents' => $summary['total_residents'],
                'total_present' => $summary['total_present'],
                'total_absent' => $summary['total_absent'],
                'total_excused' => $summary['total_excused'],
                'total_sick' => $summary['total_sick'],
                'frequent_absentees_count' => count($summary['frequent_absentees']),
                'notes' => $notes,
                'status' => AttendanceReport::STATUS_SUBMITTED,
                'submitted_at' => now(),
            ]
        );

        // Alert Proctor Managers and Admins
        $managers = User::whereIn('role', [User::ROLE_PROCTOR_MANAGER, User::ROLE_ADMIN])
            ->where('is_active', true)
            ->get();

        $typeLabel = ucfirst($reportType);
        foreach ($managers as $mgr) {
            AttendanceAlert::sendAlert(
                user: $mgr->id,
                type: 'report_submitted',
                title: "{$typeLabel} Attendance Report Submitted: {$block->name}",
                message: "Proctor {$proctor->name} submitted the {$typeLabel} Curfew Attendance Report for {$block->name} ({$startDate} to {$endDate}). Tracked Days: {$summary['tracked_days_count']}, Absences: {$summary['total_absent']}, Frequent Absentees: " . count($summary['frequent_absentees']) . ".",
                date: $endDate,
                actionUrl: route('manager.attendance.reports')
            );
        }

        AuditLog::log(
            action: 'attendance_report.submitted',
            description: "Proctor {$proctor->name} submitted {$typeLabel} report for Block {$block->block_code} ({$startDate} to {$endDate}).",
            payload: [
                'report_id' => $report->id,
                'block_id' => $blockId,
                'report_type' => $reportType,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'total_absent' => $summary['total_absent'],
                'frequent_absentees' => count($summary['frequent_absentees']),
            ],
            user: $proctor
        );

        return $report;
    }

    /**
     * Get Daily Absentee List across blocks with search and filter capabilities.
     */
    public function getDailyAbsenteeList(string $date, ?int $blockId = null, ?string $search = null): Collection
    {
        $query = Attendance::whereDate('date', $date)
            ->where('status', Attendance::STATUS_ABSENT)
            ->with(['student', 'room', 'block', 'proctor'])
            ->orderBy('block_id')
            ->orderBy('room_id');

        if ($blockId) {
            $query->where('block_id', $blockId);
        }

        if ($search) {
            $s = trim($search);
            $query->whereHas('student', function ($sq) use ($s) {
                $sq->where('name', 'ilike', "%{$s}%")
                   ->orWhere('university_id', 'ilike', "%{$s}%")
                   ->orWhere('department', 'ilike', "%{$s}%");
            });
        }

        return $query->get();
    }

    /**
     * Get campus-wide Curfew Monitoring overview for a given date.
     */
    public function getCurfewMonitoringOverview(string $date): array
    {
        $blocks = DormitoryBlock::withCount('activeAllocations')
            ->orderBy('block_code')
            ->get();

        $submissions = AttendanceSubmission::whereDate('date', $date)
            ->with('proctor')
            ->get()
            ->keyBy('block_id');

        $attendancesSummary = Attendance::whereDate('date', $date)
            ->selectRaw("
                block_id,
                COUNT(*) as total_recorded,
                COUNT(CASE WHEN status = 'present' THEN 1 END) as present_count,
                COUNT(CASE WHEN status = 'absent' THEN 1 END) as absent_count,
                COUNT(CASE WHEN status = 'excused' THEN 1 END) as excused_count,
                COUNT(CASE WHEN status = 'sick' THEN 1 END) as sick_count
            ")
            ->groupBy('block_id')
            ->get()
            ->keyBy('block_id');

        $blockStatuses = [];
        $totals = [
            'total_residents' => 0,
            'total_recorded' => 0,
            'present' => 0,
            'absent' => 0,
            'excused' => 0,
            'sick' => 0,
            'submitted_blocks' => 0,
            'pending_blocks' => 0,
        ];

        foreach ($blocks as $block) {
            $activeCount = $block->active_allocations_count;
            $totals['total_residents'] += $activeCount;

            $sub = $submissions->get($block->id);
            $att = $attendancesSummary->get($block->id);

            $present = $sub ? $sub->present_count : ($att?->present_count ?? 0);
            $absent = $sub ? $sub->absent_count : ($att?->absent_count ?? 0);
            $excused = $sub ? $sub->excused_count : ($att?->excused_count ?? 0);
            $sick = $sub ? $sub->sick_count : ($att?->sick_count ?? 0);
            $recorded = $sub ? $sub->total_students : ($att?->total_recorded ?? 0);

            $totals['present'] += $present;
            $totals['absent'] += $absent;
            $totals['excused'] += $excused;
            $totals['sick'] += $sick;
            $totals['total_recorded'] += $recorded;

            if ($sub) {
                $totals['submitted_blocks']++;
            } else {
                $totals['pending_blocks']++;
            }

            $blockStatuses[] = [
                'block' => $block,
                'active_residents' => $activeCount,
                'recorded_count' => $recorded,
                'present_count' => $present,
                'absent_count' => $absent,
                'excused_count' => $excused,
                'sick_count' => $sick,
                'is_submitted' => ! empty($sub),
                'submission' => $sub,
                'proctor' => $sub?->proctor,
            ];
        }

        return [
            'date' => $date,
            'curfew' => $this->getCurfewStatus(),
            'totals' => $totals,
            'blocks' => $blockStatuses,
            'overall_rate' => $totals['total_recorded'] > 0
                ? round(($totals['present'] / $totals['total_recorded']) * 100, 1)
                : 0,
        ];
    }

    /**
     * Get Student Attendance Profile / Analytics.
     */
    public function getStudentAttendanceSummary(User $student): array
    {
        $attendances = Attendance::where('student_id', $student->id)
            ->with(['block', 'room', 'proctor', 'leaveRequest'])
            ->orderByDesc('date')
            ->get();

        $totalDays = $attendances->count();
        $presentDays = $attendances->where('status', Attendance::STATUS_PRESENT)->count();
        $absentDays = $attendances->where('status', Attendance::STATUS_ABSENT)->count();
        $excusedDays = $attendances->where('status', Attendance::STATUS_EXCUSED)->count();
        $sickDays = $attendances->where('status', Attendance::STATUS_SICK)->count();

        $attendanceRate = $totalDays > 0 ? round(($presentDays / $totalDays) * 100, 1) : 100.0;

        // Chronic absenteeism flag (e.g. 3 or more unexcused absences)
        $isFlagged = $absentDays >= 3;

        return [
            'total_days' => $totalDays,
            'present_days' => $presentDays,
            'absent_days' => $absentDays,
            'excused_days' => $excusedDays,
            'sick_days' => $sickDays,
            'attendance_rate' => $attendanceRate,
            'is_flagged' => $isFlagged,
            'recent_records' => $attendances,
        ];
    }
}
