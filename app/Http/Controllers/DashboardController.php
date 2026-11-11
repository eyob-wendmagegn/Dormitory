<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\Attendance;
use App\Models\BackupLog;
use App\Models\BlockProctorAssignment;
use App\Models\Comment;
use App\Models\DormitoryBlock;
use App\Models\LeaveRequest;
use App\Models\ProctorAssetAssignment;
use App\Models\Room;
use App\Models\StudentAllocation;
use App\Models\StudentAssetIssue;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Build a chronological list of Carbon dates ending today.
     *
     * @return array<int, Carbon>
     */
    private function buildDateRange(int $days): array
    {
        $dates = [];
        $today = Carbon::today();

        for ($i = $days - 1; $i >= 0; $i--) {
            $dates[] = $today->copy()->subDays($i);
        }

        return $dates;
    }

    /**
     * Normalize an array of numeric counts into an SVG polyline path within 0..75 x 2..24.
     *
     * @param  array<int, int|float>  $values
     */
    private function buildSparklineSvgPath(array $values): string
    {
        $count = count($values);
        if ($count === 0) {
            return 'M 2 21 L 73 21';
        }

        $max = max($values);
        if ($max <= 0) {
            return 'M 2 21 L 73 21';
        }

        $points = [];
        $denominator = max(1, $count - 1);

        foreach (array_values($values) as $idx => $val) {
            $x = round(2 + ($idx / $denominator) * 71, 1);
            $y = round(22 - (((float) $val / (float) $max) * 18), 1);
            $prefix = $idx === 0 ? 'M' : 'L';
            $points[] = "{$prefix} {$x} {$y}";
        }

        return implode(' ', $points);
    }

    /**
     * Resolve effective bed capacity for a dormitory block (registered room capacity or standard 300).
     */
    private function resolveBlockCapacity(DormitoryBlock $block): int
    {
        $sum = (int) ($block->rooms_sum_capacity ?? 0);

        return $sum > 0 ? $sum : DormitoryBlock::STANDARD_BLOCK_CAPACITY;
    }

    /**
     * Count active or historical allocations recorded on a specific date.
     */
    private function countAllocationsOnDate(string $dateString, $roomIds = null): int
    {
        $query = StudentAllocation::query()
            ->where(function ($q) use ($dateString) {
                $q->whereDate('allocated_at', $dateString)
                    ->orWhere(function ($q2) use ($dateString) {
                        $q2->whereNull('allocated_at')
                            ->whereDate('created_at', $dateString);
                    });
            });

        if ($roomIds !== null) {
            $query->whereIn('room_id', $roomIds);
        }

        return $query->count();
    }

    /**
     * Administrator Dashboard
     */
    public function admin(): View
    {
        $allBlocks = DormitoryBlock::withCount(['rooms', 'activeAllocations'])
            ->withSum('rooms', 'capacity')
            ->orderBy('block_code')
            ->get();

        $totalBlocks = $allBlocks->count();
        $activeBlocks = $allBlocks->where('status', 'active')->count();
        $totalCapacity = $allBlocks->sum(fn (DormitoryBlock $blk) => $this->resolveBlockCapacity($blk));
        $maxCampusBlocks = DormitoryBlock::MAX_CAMPUS_BLOCKS; // 70 blocks maximum
        $maxCampusRooms = DormitoryBlock::TOTAL_CAMPUS_ROOMS; // 70 * 50 = 3,500 rooms
        $maxCampusBeds = DormitoryBlock::TOTAL_CAMPUS_BEDS; // 70 * 50 * 6 = 21,000 beds

        $registeredRooms = Room::count();
        $availableRooms = Room::where('status', 'available')->count();
        $registeredBeds = (int) Room::sum('capacity');

        $allocatedStudents = StudentAllocation::where('status', StudentAllocation::STATUS_ACTIVE)->count();
        $totalUsers = User::count();
        $activeUsers = User::where('is_active', true)->count();
        $totalStudents = User::where('role', User::ROLE_STUDENT)->count();
        $totalProctors = User::where('role', User::ROLE_PROCTOR)->count();
        $totalManagers = User::where('role', User::ROLE_PROCTOR_MANAGER)->count();
        $totalAdmins = User::where('role', User::ROLE_ADMIN)->count();
        $unallocatedStudents = User::where('role', User::ROLE_STUDENT)
            ->whereDoesntHave('activeAllocation')
            ->count();
        $totalAttendanceLogs = Attendance::count();

        $occupancyRate = $totalCapacity > 0 ? round(($allocatedStudents / $totalCapacity) * 100, 1) : 0.0;
        $placementPct = $totalStudents > 0 ? round(($allocatedStudents / $totalStudents) * 100, 1) : 0.0;
        $unallocatedPct = $totalStudents > 0 ? round(($unallocatedStudents / $totalStudents) * 100, 1) : 0.0;

        $maleCount = User::where('role', User::ROLE_STUDENT)->where('gender', 'male')->count();
        $femaleCount = User::where('role', User::ROLE_STUDENT)->where('gender', 'female')->count();
        $totalGender = $maleCount + $femaleCount;
        $malePct = $totalGender > 0 ? round(($maleCount / $totalGender) * 100, 1) : 0.0;
        $femalePct = $totalGender > 0 ? round(($femaleCount / $totalGender) * 100, 1) : 0.0;

        // 1. Dynamic 7-Day Activity & Registration Timeline
        $days7 = $this->buildDateRange(7);
        $dateRangeLabel = $days7[0]->format('M d') . ' - ' . end($days7)->format('M d, Y');

        $timelineLabels = [];
        $zigLabels = [];
        $dailyUsers = [];
        $dailyStudents = [];
        $dailyAllocations = [];
        $dailyAttendance = [];

        foreach ($days7 as $day) {
            $dStr = $day->toDateString();
            $timelineLabels[] = $day->format('M d');
            $zigLabels[] = $day->format('d M');
            $dailyUsers[] = User::whereDate('created_at', $dStr)->count();
            $dailyStudents[] = User::where('role', User::ROLE_STUDENT)->whereDate('created_at', $dStr)->count();
            $dailyAllocations[] = $this->countAllocationsOnDate($dStr);
            $dailyAttendance[] = Attendance::whereDate('date', $dStr)->count();
        }

        $newStudents7d = array_sum($dailyStudents);
        $prevStart = Carbon::today()->subDays(13)->startOfDay();
        $prevEnd = Carbon::today()->subDays(7)->endOfDay();
        $prevStudents7d = User::where('role', User::ROLE_STUDENT)
            ->whereBetween('created_at', [$prevStart, $prevEnd])
            ->count();

        if ($prevStudents7d > 0) {
            $diffPct = round((($newStudents7d - $prevStudents7d) / $prevStudents7d) * 100, 1);
            $studentGrowthBadge = ($diffPct >= 0 ? '+' : '') . $diffPct . '% vs prev 7d';
        } elseif ($newStudents7d > 0) {
            $studentGrowthBadge = '+' . $newStudents7d . ' new';
        } else {
            $studentGrowthBadge = '0 new (7d)';
        }

        $stats = [
            'total_users' => $totalUsers,
            'active_users' => $activeUsers,
            'total_students' => $totalStudents,
            'new_students_7d' => $newStudents7d,
            'total_proctors' => $totalProctors,
            'total_managers' => $totalManagers,
            'staff_count' => $totalProctors + $totalManagers,
            'admin_count' => $totalAdmins,
            'total_blocks' => $totalBlocks,
            'active_blocks' => $activeBlocks,
            'max_campus_blocks' => $maxCampusBlocks,
            'total_rooms' => $totalBlocks * DormitoryBlock::ROOMS_PER_BLOCK,
            'registered_rooms' => $registeredRooms,
            'available_rooms' => $availableRooms,
            'registered_beds' => $registeredBeds,
            'max_campus_rooms' => $maxCampusRooms,
            'allocated_students' => $allocatedStudents,
            'unallocated_students' => $unallocatedStudents,
            'total_capacity' => $totalCapacity,
            'max_campus_beds' => $maxCampusBeds,
            'occupancy_rate' => $occupancyRate,
            'campus_occupancy_rate' => $maxCampusBeds > 0 ? round(($allocatedStudents / $maxCampusBeds) * 100, 2) : 0.0,
            'male_count' => $maleCount,
            'female_count' => $femaleCount,
            'male_pct' => $malePct,
            'female_pct' => $femalePct,
            'placement_pct' => $placementPct,
            'unallocated_pct' => $unallocatedPct,
            'total_attendance_logs' => $totalAttendanceLogs,
        ];

        $activityTrend = [
            'labels' => $timelineLabels,
            'users' => $dailyUsers,
            'allocations' => $dailyAllocations,
            'attendance' => $dailyAttendance,
        ];

        $mainDatasets = [
            [
                'label' => 'User Signups',
                'data' => $dailyUsers,
                'borderColor' => '#06B6D4',
                'borderWidth' => 2.5,
            ],
            [
                'label' => 'Bed Allocations',
                'data' => $dailyAllocations,
                'borderColor' => '#10B981',
                'borderWidth' => 2.5,
            ],
            [
                'label' => 'Curfew Checks',
                'data' => $dailyAttendance,
                'borderColor' => '#8B5CF6',
                'borderWidth' => 2.5,
            ],
        ];

        $mainLegend = [
            ['label' => 'User Signups', 'value' => $totalUsers . ' Total (' . array_sum($dailyUsers) . ' in 7d)', 'color' => '#06B6D4'],
            ['label' => 'Allocations', 'value' => $allocatedStudents . ' Housed (' . array_sum($dailyAllocations) . ' in 7d)', 'color' => '#10B981'],
            ['label' => 'Curfew Logs', 'value' => $totalAttendanceLogs . ' Recorded (' . array_sum($dailyAttendance) . ' in 7d)', 'color' => '#8B5CF6'],
        ];

        // Mini bar chart: registered rooms per active DB block
        $chartBlocks = $allBlocks->count() > 12
            ? $allBlocks->sortByDesc('rooms_count')->take(12)->sortBy('block_code')->values()
            : $allBlocks;

        $miniBarData = [
            'labels' => $chartBlocks->pluck('block_code')->all(),
            'values' => $chartBlocks->pluck('rooms_count')->map(fn ($c) => (int) $c)->all(),
        ];

        $designRooms = $stats['total_rooms'];
        $configuredPct = $designRooms > 0 ? min(100, round(($registeredRooms / $designRooms) * 100, 1)) : 0.0;
        $remainingPct = $designRooms > 0 ? max(0, round(100 - $configuredPct, 1)) : 0.0;

        $miniBarLegend = [
            ['label' => 'Registered', 'value' => $registeredRooms . ' (' . $configuredPct . '%)', 'color' => '#06B6D4'],
            ['label' => 'Pending Setup', 'value' => max(0, $designRooms - $registeredRooms) . ' (' . $remainingPct . '%)', 'color' => '#334155'],
        ];

        // Mini zigzag line: student signups over the last 7 days
        $miniZigzagData = [
            'labels' => $zigLabels,
            'values' => $dailyStudents,
        ];

        $availableBeds = max(0, $totalCapacity - $allocatedStudents);
        $donutData = [$allocatedStudents, $availableBeds, $unallocatedStudents];
        $donutLegend = [
            ['label' => 'Allocated beds', 'value' => $allocatedStudents . ' (' . $occupancyRate . '%)', 'color' => '#06B6D4'],
            ['label' => 'Unallocated students', 'value' => $unallocatedStudents . ' (' . $unallocatedPct . '%)', 'color' => '#8B5CF6'],
        ];

        $gaugeLegend = [
            ['label' => 'Male students', 'value' => $maleCount . ' (' . $malePct . '%)', 'color' => '#06B6D4'],
            ['label' => 'Female students', 'value' => $femaleCount . ' (' . $femalePct . '%)', 'color' => '#F43F5E'],
        ];

        // 2. Role Distribution
        $roleDistribution = [
            'labels' => ['Students', 'Block Proctors', 'Proctor Managers', 'Administrators'],
            'data' => [
                $totalStudents,
                $totalProctors,
                $totalManagers,
                $totalAdmins,
            ],
        ];

        // 3. Dormitory Block Bed Occupancy
        $blockLabels = [];
        $blockOccupied = [];
        $blockAvailable = [];
        $blockCapacity = [];

        foreach ($allBlocks as $blk) {
            $cap = $this->resolveBlockCapacity($blk);
            $occ = (int) $blk->active_allocations_count;
            $blockLabels[] = $blk->name . ' (' . $blk->block_code . ')';
            $blockOccupied[] = $occ;
            $blockCapacity[] = $cap;
            $blockAvailable[] = max(0, $cap - $occ);
        }

        $blockOccupancy = [
            'labels' => $blockLabels,
            'occupied' => $blockOccupied,
            'available' => $blockAvailable,
            'capacity' => $blockCapacity,
        ];

        // 4. Academic Department Distribution
        $deptCounts = User::whereNotNull('department')
            ->where('department', '!=', '')
            ->selectRaw('department, count(*) as count')
            ->groupBy('department')
            ->orderByDesc('count')
            ->take(7)
            ->pluck('count', 'department')
            ->all();

        $departmentDistribution = [
            'labels' => array_keys($deptCounts),
            'data' => array_values($deptCounts),
        ];

        $recentUsers = User::with('activeAllocation.room.block')->latest()->take(7)->get();
        $recentAllocations = StudentAllocation::with(['student', 'room.block', 'allocator'])->latest()->take(6)->get();
        // Administrator views proctor manager and proctor comments
        $recentComments = Comment::with('user')
            ->where(function ($q) {
                $q->whereHas('user', fn ($uq) => $uq->whereIn('role', [User::ROLE_PROCTOR, User::ROLE_PROCTOR_MANAGER]))
                    ->orWhere('target_role', 'admin');
            })
            ->latest()
            ->take(5)
            ->get();
        $recentBlocks = DormitoryBlock::withCount(['rooms', 'activeAllocations'])
            ->withSum('rooms', 'capacity')
            ->orderBy('block_code')
            ->take(8)
            ->get();

        $latestBackupLog = Schema::hasTable('backup_logs')
            ? BackupLog::latest()->first()
            : null;
        $latestBackupFailure = ($latestBackupLog && $latestBackupLog->status === BackupLog::STATUS_FAILED)
            ? $latestBackupLog
            : null;

        return view('dashboards.admin', compact(
            'stats',
            'dateRangeLabel',
            'studentGrowthBadge',
            'activityTrend',
            'mainDatasets',
            'mainLegend',
            'miniBarData',
            'miniBarLegend',
            'miniZigzagData',
            'donutData',
            'donutLegend',
            'gaugeLegend',
            'roleDistribution',
            'blockOccupancy',
            'departmentDistribution',
            'recentUsers',
            'recentAllocations',
            'recentComments',
            'recentBlocks',
            'latestBackupFailure'
        ));
    }

    /**
     * Proctor Manager Dashboard
     */
    public function manager(): View
    {
        $blocksList = DormitoryBlock::withCount(['rooms', 'activeAllocations'])
            ->withSum('rooms', 'capacity')
            ->orderBy('block_code')
            ->get();

        $totalBlocks = $blocksList->count();
        $activeBlocks = $blocksList->where('status', 'active')->count();
        $totalCapacity = $blocksList->sum(fn (DormitoryBlock $b) => $this->resolveBlockCapacity($b));
        $maxCampusBlocks = DormitoryBlock::MAX_CAMPUS_BLOCKS; // 70 blocks max
        $maxCampusRooms = DormitoryBlock::TOTAL_CAMPUS_ROOMS; // 70 * 50 = 3,500 rooms
        $maxCampusBeds = DormitoryBlock::TOTAL_CAMPUS_BEDS; // 70 * 50 * 6 = 21,000 beds

        $totalAllocated = StudentAllocation::where('status', StudentAllocation::STATUS_ACTIVE)->count();
        $totalUsers = User::count();
        $totalStudents = User::where('role', User::ROLE_STUDENT)->count();
        $totalProctors = User::where('role', User::ROLE_PROCTOR)->count();
        $registeredRooms = Room::count();

        $activeDutyProctors = BlockProctorAssignment::whereNull('ended_at')->distinct('proctor_id')->count('proctor_id');
        $assignedBlocksCount = BlockProctorAssignment::whereNull('ended_at')->distinct('block_id')->count('block_id');

        $occupancyPct = $totalCapacity > 0 ? round(($totalAllocated / $totalCapacity) * 100, 1) : 0.0;
        $placementPct = $totalStudents > 0 ? round(($totalAllocated / $totalStudents) * 100, 1) : 0.0;
        $supervisionPct = $totalBlocks > 0 ? round(($assignedBlocksCount / $totalBlocks) * 100, 1) : 0.0;
        $activeBlocksPct = $totalBlocks > 0 ? round(($activeBlocks / $totalBlocks) * 100, 1) : 0.0;

        $stats = [
            'total_users' => $totalUsers,
            'total_blocks' => $totalBlocks,
            'active_blocks' => $activeBlocks,
            'active_blocks_pct' => $activeBlocksPct,
            'max_campus_blocks' => $maxCampusBlocks,
            'total_proctors' => $totalProctors,
            'active_duty_proctors' => $activeDutyProctors,
            'total_students' => $totalStudents,
            'total_rooms' => $totalBlocks * DormitoryBlock::ROOMS_PER_BLOCK,
            'registered_rooms' => $registeredRooms,
            'max_campus_rooms' => $maxCampusRooms,
            'total_capacity' => $totalCapacity,
            'max_campus_beds' => $maxCampusBeds,
            'total_allocated' => $totalAllocated,
            'available_beds' => max(0, $totalCapacity - $totalAllocated),
            'unresolved_comments' => Comment::whereIn('status', [Comment::STATUS_PENDING, Comment::STATUS_IN_PROGRESS])->count(),
            'occupancy_pct' => $occupancyPct,
            'placement_pct' => $placementPct,
            'growth_pct' => $occupancyPct,
            'supervision_pct' => $supervisionPct,
        ];

        // 1. Dual Bar Chart Data (Real Block Capacity & Bed Occupancy)
        $chartBlocks = $blocksList->take(12);
        $blockBarLabels = $chartBlocks->pluck('block_code')->all();
        $occupiedSeries = [];
        $availableSeries = [];

        foreach ($chartBlocks as $blk) {
            $cap = $this->resolveBlockCapacity($blk);
            $occ = (int) $blk->active_allocations_count;
            $occupiedSeries[] = $occ;
            $availableSeries[] = max(0, $cap - $occ);
        }

        $dualBarDatasets = [
            [
                'label' => 'Occupied Beds',
                'data' => $occupiedSeries,
                'backgroundColor' => '#06B6D4',
                'borderRadius' => 6,
            ],
            [
                'label' => 'Available Beds',
                'data' => $availableSeries,
                'backgroundColor' => '#10B981',
                'borderRadius' => 6,
            ],
        ];

        // 2 & 3. Dynamic 7-Day Supervision & Residential Flow Activity
        $days7 = $this->buildDateRange(7);
        $waveLabels = [];
        $supervisionLabels = [];
        $supervisionLineData = [];
        $waveAllocations = [];
        $waveLeaves = [];
        $waveAttendance = [];

        foreach ($days7 as $day) {
            $dStr = $day->toDateString();
            $label = $day->format('M d');
            $waveLabels[] = $label;
            $supervisionLabels[] = $label;

            $allocDay = $this->countAllocationsOnDate($dStr);
            $leaveDay = LeaveRequest::whereDate('created_at', $dStr)->count();
            $attDay = Attendance::whereDate('date', $dStr)->count();

            $waveAllocations[] = $allocDay;
            $waveLeaves[] = $leaveDay;
            $waveAttendance[] = $attDay;
            $supervisionLineData[] = $attDay;
        }

        $waveData = [
            'allocations' => $waveAllocations,
            'leaves' => $waveLeaves,
            'attendance' => $waveAttendance,
        ];

        $assignments = BlockProctorAssignment::with(['block', 'proctor'])->whereNull('ended_at')->latest()->take(4)->get();
        // Proctor Manager views proctor comments
        $recentComments = Comment::with('user')
            ->where(function ($q) {
                $q->whereHas('user', fn ($uq) => $uq->where('role', User::ROLE_PROCTOR))
                    ->orWhere('target_role', 'proctor_manager');
            })
            ->latest()
            ->take(5)
            ->get();

        $blocks = $blocksList;
        $proctors = User::where('role', User::ROLE_PROCTOR)
            ->with(['activeProctorAssignments.block'])
            ->orderBy('name')
            ->get();

        return view('dashboards.proctor-manager', compact(
            'stats',
            'blockBarLabels',
            'dualBarDatasets',
            'supervisionLineData',
            'supervisionLabels',
            'waveLabels',
            'waveData',
            'assignments',
            'recentComments',
            'blocks',
            'proctors'
        ));
    }

    /**
     * Proctor Dashboard
     */
    public function proctor(): View
    {
        $proctor = Auth::user();
        $assignments = $proctor->activeProctorAssignments()->with('block')->get();
        $assignedBlockIds = $assignments->pluck('block_id');

        $managedBlocks = DormitoryBlock::whereIn('id', $assignedBlockIds)
            ->withCount(['rooms', 'activeAllocations'])
            ->withSum('rooms', 'capacity')
            ->orderBy('block_code')
            ->get();
        $totalBlocks = $managedBlocks->count();

        $totalCapacity = $managedBlocks->sum(fn (DormitoryBlock $b) => $this->resolveBlockCapacity($b));
        $totalRooms = $totalBlocks * DormitoryBlock::ROOMS_PER_BLOCK;
        $registeredRooms = Room::whereIn('block_id', $assignedBlockIds)->count();

        $managedRoomIds = Room::whereIn('block_id', $assignedBlockIds)->pluck('id');
        $totalAllocated = StudentAllocation::whereIn('room_id', $managedRoomIds)
            ->where('status', StudentAllocation::STATUS_ACTIVE)
            ->count();
        $availableBeds = max(0, $totalCapacity - $totalAllocated);

        $blockGenders = $managedBlocks->pluck('gender_category')->filter()->unique()->values()->all();
        $assignedDepartments = $assignments->pluck('assigned_departments')->flatten()->filter()->unique()->values()->all();
        $assignedYears = $assignments->pluck('assigned_years')->flatten()->filter()->map(fn ($y) => (int) $y)->unique()->values()->all();

        // Count unallocated students in proctor's department/batch/gender scope
        $unallocatedScopedQuery = User::where('role', User::ROLE_STUDENT)
            ->whereDoesntHave('activeAllocation');

        if (! empty($blockGenders)) {
            $unallocatedScopedQuery->whereIn('gender', $blockGenders);
        }
        if (! empty($assignedDepartments)) {
            $deptsLower = array_map('strtolower', $assignedDepartments);
            $unallocatedScopedQuery->whereIn(\Illuminate\Support\Facades\DB::raw('LOWER(department)'), $deptsLower);
        }
        if (! empty($assignedYears)) {
            $unallocatedScopedQuery->whereIn('academic_year', $assignedYears);
        }

        $unallocatedStudentsCount = $unallocatedScopedQuery->count();

        $managedStudentIds = StudentAllocation::whereIn('room_id', $managedRoomIds)
            ->where('status', StudentAllocation::STATUS_ACTIVE)
            ->pluck('student_id');

        $pendingCommentsCount = Comment::where('status', Comment::STATUS_PENDING)
            ->where(function ($q) use ($managedStudentIds, $proctor) {
                $q->whereIn('user_id', $managedStudentIds)
                    ->orWhere('user_id', $proctor->id);
            })
            ->count();

        $stats = [
            'total_users' => User::count(),
            'assigned_blocks' => $totalBlocks,
            'total_rooms' => $totalRooms,
            'registered_rooms' => $registeredRooms,
            'total_capacity' => $totalCapacity,
            'total_allocated' => $totalAllocated,
            'available_beds' => $availableBeds,
            'unallocated_students' => $unallocatedStudentsCount,
            'pending_feedback' => $pendingCommentsCount,
            'assigned_depts_count' => count($assignedDepartments),
            'assigned_years_count' => count($assignedYears),
            'max_campus_blocks' => DormitoryBlock::MAX_CAMPUS_BLOCKS,
            'max_campus_rooms' => DormitoryBlock::TOTAL_CAMPUS_ROOMS,
            'max_campus_beds' => DormitoryBlock::TOTAL_CAMPUS_BEDS,
        ];

        $allocatedStudents = StudentAllocation::with(['student', 'room.block', 'allocator'])
            ->whereIn('room_id', $managedRoomIds)
            ->where('status', StudentAllocation::STATUS_ACTIVE)
            ->latest('allocated_at')
            ->get();

        $recentAllocations = $allocatedStudents->take(5);

        // Eligible unallocated students for quick allocate box
        $eligibleStudents = (clone $unallocatedScopedQuery)
            ->where('is_active', true)
            ->orderBy('name')
            ->take(50)
            ->get();

        $availableRooms = Room::whereIn('block_id', $assignedBlockIds)
            ->where('status', 'available')
            ->with('block')
            ->withCount('activeAllocations')
            ->get()
            ->filter(fn ($r) => $r->active_allocations_count < $r->capacity);

        $recentRooms = Room::with('block')
            ->whereIn('block_id', $assignedBlockIds)
            ->withCount('activeAllocations')
            ->latest()
            ->take(6)
            ->get();

        // 1. Curfew Check-ins Today (100% Real DB)
        $todayStr = Carbon::today()->toDateString();
        $todayRecorded = $assignedBlockIds->isNotEmpty()
            ? Attendance::whereIn('block_id', $assignedBlockIds)->whereDate('date', $todayStr)->count()
            : 0;
        $inDormCount = $assignedBlockIds->isNotEmpty()
            ? Attendance::whereIn('block_id', $assignedBlockIds)->whereDate('date', $todayStr)->where('status', Attendance::STATUS_PRESENT)->count()
            : 0;
        $activeLeavesToday = $managedStudentIds->isNotEmpty()
            ? LeaveRequest::whereIn('student_id', $managedStudentIds)->activeOnDate($todayStr)->count()
            : 0;

        $checkinTotal = $todayRecorded;
        $curfewRate = $todayRecorded > 0 ? round(($inDormCount / $todayRecorded) * 100, 1) : 0.0;
        $checkinSubMetrics = [
            ['label' => 'In-Dorm', 'value' => $inDormCount],
            ['label' => 'On Leave', 'value' => $activeLeavesToday],
            ['label' => 'Curfew Rate', 'value' => $curfewRate . '%'],
        ];

        // 2. Bed Status Breakdown (100% Real DB)
        $maintenanceBeds = $assignedBlockIds->isNotEmpty()
            ? (int) Room::whereIn('block_id', $assignedBlockIds)->where('status', 'maintenance')->sum('capacity')
            : 0;

        $bedBreakdownData = [$totalAllocated, $availableBeds, $activeLeavesToday, $maintenanceBeds];
        $bedBreakdownLabels = ['Allocated Beds', 'Available Slots', 'Approved Leaves', 'Maintenance'];
        $bedBreakdownColors = ['#10B981', '#06B6D4', '#F59E0B', '#8B5CF6'];
        $bedBreakdownLegend = [
            ['label' => 'Allocated Beds', 'value' => $totalAllocated, 'color' => '#10B981'],
            ['label' => 'Available Slots', 'value' => $availableBeds, 'color' => '#06B6D4'],
            ['label' => 'Approved Leaves', 'value' => $activeLeavesToday, 'color' => '#F59E0B'],
            ['label' => 'Maintenance', 'value' => $maintenanceBeds, 'color' => '#8B5CF6'],
        ];

        // 3. Block Operations & 7-Day Curfew Compliance (100% Real DB)
        $bedOccupancyPct = $totalCapacity > 0 ? (int) min(100, round(($totalAllocated / $totalCapacity) * 100)) : 0;
        $sevenDayStart = Carbon::today()->subDays(6)->toDateString();
        $att7Total = $assignedBlockIds->isNotEmpty()
            ? Attendance::whereIn('block_id', $assignedBlockIds)->whereDate('date', '>=', $sevenDayStart)->count()
            : 0;
        $att7Present = $assignedBlockIds->isNotEmpty()
            ? Attendance::whereIn('block_id', $assignedBlockIds)->whereDate('date', '>=', $sevenDayStart)->where('status', Attendance::STATUS_PRESENT)->count()
            : 0;
        $curfew7Pct = $att7Total > 0 ? (int) min(100, round(($att7Present / $att7Total) * 100)) : 0;

        $blockComplianceProgress = [
            ['label' => 'Bed Occupancy', 'value' => $bedOccupancyPct, 'color' => '#06B6D4', 'secondaryColor' => '#10B981'],
            ['label' => 'Curfew Compliance (7d)', 'value' => $curfew7Pct, 'color' => '#F59E0B', 'secondaryColor' => '#FF8C00'],
        ];
        $blockComplianceLegend = [
            ['label' => 'Occupancy', 'color' => '#06B6D4'],
            ['label' => 'Curfew Rate', 'color' => '#F59E0B'],
        ];

        // 4. Last 7-day per-day series (used by Mini Bar cards and Sparkline Monitors)
        $days7 = $this->buildDateRange(7);
        $last7Labels = [];
        $miniBarData1 = [];
        $miniBarData2 = [];
        $miniBarData3 = [];

        foreach ($days7 as $day) {
            $dStr = $day->toDateString();
            $last7Labels[] = $day->format('M d');

            $miniBarData1[] = $managedRoomIds->isNotEmpty() ? $this->countAllocationsOnDate($dStr, $managedRoomIds) : 0;
            $miniBarData2[] = $assignedBlockIds->isNotEmpty()
                ? Attendance::whereIn('block_id', $assignedBlockIds)->whereDate('date', $dStr)->count()
                : 0;
            $miniBarData3[] = $managedStudentIds->isNotEmpty()
                ? LeaveRequest::whereIn('student_id', $managedStudentIds)
                    ->where('status', LeaveRequest::STATUS_APPROVED)
                    ->whereDate('created_at', $dStr)
                    ->count()
                : 0;
        }

        $weeklyScansCount = array_sum($miniBarData2);
        $activeLeavesCount = $managedStudentIds->isNotEmpty()
            ? LeaveRequest::whereIn('student_id', $managedStudentIds)
                ->where('status', LeaveRequest::STATUS_APPROVED)
                ->whereDate('end_date', '>=', $todayStr)
                ->count()
            : 0;

        // 5. Managed Block Monitors (Sparkline Rows)
        $blockTag = $managedBlocks->isNotEmpty() ? $managedBlocks->pluck('block_code')->join(', ') : 'No Block';
        $wingSparklines = [
            [
                'name' => $blockTag . ' Beds',
                'metric' => "{$totalAllocated}/{$totalCapacity} ({$bedOccupancyPct}%)",
                'color' => '#06B6D4',
                'svgPath' => $this->buildSparklineSvgPath($miniBarData1),
            ],
            [
                'name' => 'Curfew (7d)',
                'metric' => "{$att7Present}/{$att7Total} ({$curfew7Pct}%)",
                'color' => '#10B981',
                'svgPath' => $this->buildSparklineSvgPath($miniBarData2),
            ],
            [
                'name' => 'Rooms & Leaves',
                'metric' => "{$registeredRooms} Rms • {$activeLeavesCount} Passes",
                'color' => '#F59E0B',
                'svgPath' => $this->buildSparklineSvgPath($miniBarData3),
            ],
        ];

        // 6. 30-Day Curfew & Gate Pass Line Chart (100% Real DB)
        $days30 = $this->buildDateRange(30);
        $dailyLineLabels = [];
        $dailyCheckinsSeries = [];
        $dailyGatePassesSeries = [];

        foreach ($days30 as $day) {
            $dStr = $day->toDateString();
            $dailyLineLabels[] = $day->format('M d');
            $dailyCheckinsSeries[] = $assignedBlockIds->isNotEmpty()
                ? Attendance::whereIn('block_id', $assignedBlockIds)->whereDate('date', $dStr)->where('status', Attendance::STATUS_PRESENT)->count()
                : 0;
            $dailyGatePassesSeries[] = $managedStudentIds->isNotEmpty()
                ? LeaveRequest::whereIn('student_id', $managedStudentIds)->activeOnDate($dStr)->count()
                : 0;
        }

        $dailyLineLegend = [
            ['label' => 'Check-ins', 'color' => '#F59E0B'],
            ['label' => 'Gate Passes', 'color' => '#06B6D4'],
        ];

        // 7. Assets Workflow (Stage 1 Handover & Proctor Stock)
        $pendingHandovers = ProctorAssetAssignment::with(['manager', 'block'])
            ->where('proctor_id', $proctor->id)
            ->where('status', ProctorAssetAssignment::STATUS_PENDING)
            ->latest('transfer_date')
            ->get();
        $pendingHandoverCount = $pendingHandovers->count();

        $confirmedTransfers = ProctorAssetAssignment::where('proctor_id', $proctor->id)
            ->where('status', ProctorAssetAssignment::STATUS_CONFIRMED)
            ->get();
        $totalMattressesReceived = (int) $confirmedTransfers->where('item_type', 'mattress')->sum('confirmed_quantity');
        $totalPillowsReceived = (int) $confirmedTransfers->where('item_type', 'pillow')->sum('confirmed_quantity');

        $activeIssues = StudentAssetIssue::where('proctor_id', $proctor->id)
            ->whereIn('status', [StudentAssetIssue::STATUS_PENDING, StudentAssetIssue::STATUS_CONFIRMED])
            ->get();
        $mattressesIssued = (int) $activeIssues->where('item_type', 'mattress')->sum('quantity');
        $pillowsIssued = (int) $activeIssues->where('item_type', 'pillow')->sum('quantity');

        $proctorStock = [
            'mattresses_available' => max(0, $totalMattressesReceived - $mattressesIssued),
            'pillows_available' => max(0, $totalPillowsReceived - $pillowsIssued),
            'mattresses_total' => $totalMattressesReceived,
            'pillows_total' => $totalPillowsReceived,
            'mattresses_issued' => $mattressesIssued,
            'pillows_issued' => $pillowsIssued,
        ];

        // 8. Manager Directives for Proctors
        $managerNotices = Announcement::forProctors($assignedBlockIds->all())
            ->latest('published_at')
            ->take(3)
            ->get();

        return view('dashboards.proctor', compact(
            'stats',
            'managedBlocks',
            'recentAllocations',
            'allocatedStudents',
            'eligibleStudents',
            'availableRooms',
            'recentRooms',
            'assignedDepartments',
            'assignedYears',
            'checkinTotal',
            'checkinSubMetrics',
            'bedBreakdownData',
            'bedBreakdownLabels',
            'bedBreakdownColors',
            'bedBreakdownLegend',
            'blockComplianceProgress',
            'blockComplianceLegend',
            'wingSparklines',
            'dailyLineLabels',
            'dailyCheckinsSeries',
            'dailyGatePassesSeries',
            'dailyLineLegend',
            'last7Labels',
            'miniBarData1',
            'miniBarData2',
            'miniBarData3',
            'weeklyScansCount',
            'activeLeavesCount',
            'pendingHandovers',
            'pendingHandoverCount',
            'proctorStock',
            'managerNotices'
        ));
    }

    /**
     * Student Dashboard
     */
    public function student(): View
    {
        $student = Auth::user();
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

        $myComments = $student->comments()->latest()->take(5)->get();
        $totalMyComments = $student->comments()->count();
        $resolvedMyComments = $student->comments()->where('status', Comment::STATUS_RESOLVED)->count();
        $announcements = Announcement::published()->latest('published_at')->take(3)->get();

        $todayStr = Carbon::today()->toDateString();

        // 1. Attendance & Curfew Metrics (100% Real DB)
        $totalAttendances = Attendance::where('student_id', $student->id)->count();
        $presentCount = Attendance::where('student_id', $student->id)->where('status', Attendance::STATUS_PRESENT)->count();
        $excusedCount = Attendance::where('student_id', $student->id)->whereIn('status', [Attendance::STATUS_EXCUSED, Attendance::STATUS_SICK])->count();
        $approvedLeaves = LeaveRequest::where('student_id', $student->id)->where('status', LeaveRequest::STATUS_APPROVED)->count();
        $pendingLeaves = LeaveRequest::where('student_id', $student->id)->where('status', LeaveRequest::STATUS_PENDING)->count();
        $activeApprovedLeaves = LeaveRequest::where('student_id', $student->id)->activeOnDate($todayStr)->count();

        $checkinTotal = $totalAttendances;
        $inDormCount = $presentCount;
        $onLeaveCount = $approvedLeaves + $excusedCount;
        $curfewRate = $totalAttendances > 0 ? round(($presentCount / $totalAttendances) * 100, 1) : 0.0;

        $checkinSubMetrics = [
            ['label' => 'IN-DORM', 'value' => (string) $inDormCount],
            ['label' => 'ON LEAVE', 'value' => (string) $onLeaveCount],
            ['label' => 'CURFEW RATE', 'value' => $curfewRate . '%'],
        ];

        // 2. Room & Bed Status Breakdown (100% Real DB)
        if ($room) {
            $roomCapacity = (int) ($room->capacity ?: DormitoryBlock::MAX_ROOM_CAPACITY);
            $activeOccupied = $roommates->count() + 1;
            $availableSlots = max(0, $roomCapacity - $activeOccupied);
        } else {
            $roomCapacity = 0;
            $activeOccupied = 0;
            $availableSlots = 0;
        }

        $bedBreakdownLabels = ['Occupied Beds', 'Available Beds', 'Active Leaves', 'Room Capacity'];
        $bedBreakdownData = [$activeOccupied, $availableSlots, $activeApprovedLeaves, $roomCapacity];
        $bedBreakdownColors = ['#10B981', '#06B6D4', '#F59E0B', '#8B5CF6'];
        $bedBreakdownLegend = [
            ['label' => 'Room Occupied', 'value' => $activeOccupied . ' Beds', 'color' => '#10B981'],
            ['label' => 'Available Beds', 'value' => $availableSlots . ' Free', 'color' => '#06B6D4'],
            ['label' => 'Active Leaves', 'value' => $activeApprovedLeaves . ' Active', 'color' => '#F59E0B'],
            ['label' => 'Room Capacity', 'value' => $roomCapacity . ' Beds', 'color' => '#8B5CF6'],
        ];

        // 3. Residence Compliance (100% Real DB)
        $roomOccupancyPct = $roomCapacity > 0 ? (int) min(100, round(($activeOccupied / $roomCapacity) * 100)) : 0;
        $complianceProgress = [
            ['label' => 'Curfew Compliance', 'value' => (int) min(100, round($curfewRate)), 'color' => '#06B6D4', 'secondaryColor' => '#10B981'],
            ['label' => 'Room Occupancy', 'value' => $roomOccupancyPct, 'color' => '#F59E0B', 'secondaryColor' => '#FF8C00'],
        ];
        $complianceLegend = [
            ['label' => 'Curfew Rate', 'color' => '#06B6D4'],
            ['label' => 'Room Load', 'color' => '#F59E0B'],
        ];

        // 4 & 6. Last 7-day student activity vectors
        $days7 = $this->buildDateRange(7);
        $last7Labels = [];
        $miniBarData1 = [];
        $miniBarData2 = [];
        $miniBarData3 = [];

        foreach ($days7 as $day) {
            $dStr = $day->toDateString();
            $last7Labels[] = $day->format('M d');
            $miniBarData1[] = Attendance::where('student_id', $student->id)->whereDate('date', $dStr)->count();
            $miniBarData2[] = LeaveRequest::where('student_id', $student->id)->whereDate('created_at', $dStr)->count();
            $miniBarData3[] = $student->comments()->whereDate('created_at', $dStr)->count();
        }

        // 4. Residence Summary Sparkline Rows (100% Real DB)
        $residentSparklines = [
            [
                'name' => 'Curfew Roll-call',
                'metric' => "{$presentCount}/{$totalAttendances} Present",
                'color' => '#F43F5E',
                'svgPath' => $this->buildSparklineSvgPath($miniBarData1),
            ],
            [
                'name' => 'Gate Passes',
                'metric' => "{$approvedLeaves} Approved • {$pendingLeaves} Pending",
                'color' => '#06B6D4',
                'svgPath' => $this->buildSparklineSvgPath($miniBarData2),
            ],
            [
                'name' => 'Feedback Tickets',
                'metric' => "{$resolvedMyComments}/{$totalMyComments} Resolved",
                'color' => '#F59E0B',
                'svgPath' => $this->buildSparklineSvgPath($miniBarData3),
            ],
        ];

        // 5. 30-Day Attendance & Leave Timeline (100% Real DB)
        $days30 = $this->buildDateRange(30);
        $dailyLineLabels = [];
        $dailyAttendanceSeries = [];
        $dailyGatePassesSeries = [];

        foreach ($days30 as $day) {
            $dStr = $day->toDateString();
            $dailyLineLabels[] = $day->format('M d');
            $dailyAttendanceSeries[] = Attendance::where('student_id', $student->id)
                ->whereDate('date', $dStr)
                ->where('status', Attendance::STATUS_PRESENT)
                ->count();
            $dailyGatePassesSeries[] = LeaveRequest::where('student_id', $student->id)
                ->activeOnDate($dStr)
                ->count();
        }

        $dailyLineLegend = [
            ['label' => 'Attendance Presence', 'color' => '#F59E0B'],
            ['label' => 'Approved Gate Passes', 'color' => '#06B6D4'],
        ];

        // 7. Woldia University Campus Standards
        $campusStats = [
            'max_blocks' => DormitoryBlock::MAX_CAMPUS_BLOCKS,
            'rooms_per_block' => DormitoryBlock::ROOMS_PER_BLOCK,
            'beds_per_room' => DormitoryBlock::MAX_ROOM_CAPACITY,
            'block_capacity' => DormitoryBlock::STANDARD_BLOCK_CAPACITY,
            'total_rooms' => DormitoryBlock::TOTAL_CAMPUS_ROOMS,
            'total_beds' => DormitoryBlock::TOTAL_CAMPUS_BEDS,
        ];

        // 8. Dormitory Assets & Clearance Workflow (Stage 2 & Stage 3)
        $pendingAssetConfirmations = StudentAssetIssue::with(['proctor'])
            ->where('student_id', $student->id)
            ->where('status', StudentAssetIssue::STATUS_PENDING)
            ->get();
        $pendingAssetConfirmationsCount = $pendingAssetConfirmations->count();

        $activeStudentAssets = StudentAssetIssue::with(['proctor'])
            ->where('student_id', $student->id)
            ->whereIn('status', [StudentAssetIssue::STATUS_PENDING, StudentAssetIssue::STATUS_CONFIRMED])
            ->get();

        $isClearanceEligible = StudentAssetIssue::isStudentEligibleForClearance($student->id);

        $studentTargetedNotices = Announcement::forStudents($block?->id)
            ->latest('published_at')
            ->take(3)
            ->get();

        return view('dashboards.student', compact(
            'student',
            'allocation',
            'room',
            'block',
            'roommates',
            'myComments',
            'totalMyComments',
            'announcements',
            'checkinTotal',
            'checkinSubMetrics',
            'bedBreakdownData',
            'bedBreakdownLabels',
            'bedBreakdownColors',
            'bedBreakdownLegend',
            'complianceProgress',
            'complianceLegend',
            'residentSparklines',
            'dailyLineLabels',
            'dailyAttendanceSeries',
            'dailyGatePassesSeries',
            'dailyLineLegend',
            'last7Labels',
            'miniBarData1',
            'miniBarData2',
            'miniBarData3',
            'onLeaveCount',
            'pendingLeaves',
            'approvedLeaves',
            'campusStats',
            'pendingAssetConfirmations',
            'pendingAssetConfirmationsCount',
            'activeStudentAssets',
            'isClearanceEligible',
            'studentTargetedNotices'
        ));
    }
}
