<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\AttendanceAlert;
use App\Models\AttendanceReport;
use App\Models\AttendanceSubmission;
use App\Models\BlockProctorAssignment;
use App\Models\DormitoryBlock;
use App\Models\LeaveRequest;
use App\Models\Room;
use App\Models\StudentAllocation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DailyAttendanceTrackingTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $manager;
    protected User $proctor;
    protected User $student1;
    protected User $student2;
    protected DormitoryBlock $block;
    protected Room $room;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'is_active' => true,
        ]);

        $this->manager = User::factory()->create([
            'role' => User::ROLE_PROCTOR_MANAGER,
            'is_active' => true,
        ]);

        $this->proctor = User::factory()->create([
            'role' => User::ROLE_PROCTOR,
            'is_active' => true,
        ]);

        $this->student1 = User::factory()->create([
            'role' => User::ROLE_STUDENT,
            'gender' => 'male',
            'department' => 'Software Engineering',
            'academic_year' => 3,
            'is_active' => true,
        ]);

        $this->student2 = User::factory()->create([
            'role' => User::ROLE_STUDENT,
            'gender' => 'male',
            'department' => 'Computer Science',
            'academic_year' => 2,
            'is_active' => true,
        ]);

        $this->block = DormitoryBlock::create([
            'name' => 'Fasiledes Hall',
            'block_code' => 'B-101',
            'gender_category' => 'male',
            'status' => 'active',
        ]);

        $this->room = Room::create([
            'block_id' => $this->block->id,
            'room_number' => '101',
            'floor' => 1,
            'capacity' => 4,
            'status' => 'available',
        ]);

        // Allocate students
        StudentAllocation::create([
            'student_id' => $this->student1->id,
            'room_id' => $this->room->id,
            'allocated_by' => $this->proctor->id,
            'allocated_at' => now(),
            'status' => 'active',
        ]);

        StudentAllocation::create([
            'student_id' => $this->student2->id,
            'room_id' => $this->room->id,
            'allocated_by' => $this->proctor->id,
            'allocated_at' => now(),
            'status' => 'active',
        ]);

        // Assign block to proctor
        BlockProctorAssignment::create([
            'block_id' => $this->block->id,
            'proctor_id' => $this->proctor->id,
            'assigned_by' => $this->manager->id,
            'assigned_at' => now(),
        ]);
    }

    public function test_student_can_request_gate_pass_and_proctor_approves(): void
    {
        $today = Carbon::today()->toDateString();
        $tomorrow = Carbon::tomorrow()->toDateString();

        // 1. Student requests gate pass
        $response = $this->actingAs($this->student1)->post(route('student.leaves.store'), [
            'request_type' => LeaveRequest::TYPE_GATE_PASS,
            'start_date' => $today,
            'end_date' => $tomorrow,
            'destination' => 'Woldia Center',
            'emergency_phone' => '+251911223344',
            'reason' => 'Family visit and personal errand',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('leave_requests', [
            'student_id' => $this->student1->id,
            'destination' => 'Woldia Center',
            'status' => LeaveRequest::STATUS_PENDING,
        ]);

        $leave = LeaveRequest::first();

        // 2. Proctor approves leave request
        $approveResp = $this->actingAs($this->proctor)->post(route('proctor.leaves.approve', $leave), [
            'remarks' => 'Approved. Return on time.',
        ]);

        $approveResp->assertRedirect();
        $this->assertDatabaseHas('leave_requests', [
            'id' => $leave->id,
            'status' => LeaveRequest::STATUS_APPROVED,
            'approved_by' => $this->proctor->id,
        ]);

        // 3. Verify student received notification
        $this->assertDatabaseHas('attendance_alerts', [
            'user_id' => $this->student1->id,
            'type' => AttendanceAlert::TYPE_LEAVE_APPROVED,
        ]);
    }

    public function test_roster_auto_excuses_student_with_approved_gate_pass(): void
    {
        $today = Carbon::today()->toDateString();

        // Create approved leave for student1
        LeaveRequest::create([
            'student_id' => $this->student1->id,
            'request_type' => LeaveRequest::TYPE_OVERNIGHT,
            'start_date' => $today,
            'end_date' => $today,
            'destination' => 'Dessie',
            'reason' => 'Medical appointment',
            'status' => LeaveRequest::STATUS_APPROVED,
            'approved_by' => $this->proctor->id,
            'approved_at' => now(),
        ]);

        $response = $this->actingAs($this->proctor)->get(route('proctor.attendance.index', [
            'block_id' => $this->block->id,
            'date' => $today,
        ]));

        $response->assertOk();
        $response->assertSee('On Leave');
        $response->assertSee('#LR-');
    }

    public function test_proctor_can_save_room_attendance(): void
    {
        $today = Carbon::today()->toDateString();

        $response = $this->actingAs($this->proctor)->postJson(route('proctor.attendance.save_room'), [
            'block_id' => $this->block->id,
            'room_id' => $this->room->id,
            'date' => $today,
            'students' => [
                [
                    'student_id' => $this->student1->id,
                    'status' => Attendance::STATUS_PRESENT,
                    'remarks' => 'In dorm',
                ],
                [
                    'student_id' => $this->student2->id,
                    'status' => Attendance::STATUS_ABSENT,
                    'remarks' => 'Unexcused room absence',
                ],
            ],
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('attendances', [
            'student_id' => $this->student1->id,
            'status' => Attendance::STATUS_PRESENT,
        ]);

        $this->assertDatabaseHas('attendances', [
            'student_id' => $this->student2->id,
            'status' => Attendance::STATUS_ABSENT,
        ]);

        $this->assertEquals($today, Attendance::where('student_id', $this->student1->id)->first()->date->toDateString());
    }

    public function test_proctor_can_finalize_attendance_and_alerts_are_dispatched(): void
    {
        $today = Carbon::today()->toDateString();

        // student1 present, student2 absent
        Attendance::create([
            'student_id' => $this->student1->id,
            'proctor_id' => $this->proctor->id,
            'block_id' => $this->block->id,
            'room_id' => $this->room->id,
            'date' => $today,
            'status' => Attendance::STATUS_PRESENT,
            'recorded_at' => now(),
        ]);

        Attendance::create([
            'student_id' => $this->student2->id,
            'proctor_id' => $this->proctor->id,
            'block_id' => $this->block->id,
            'room_id' => $this->room->id,
            'date' => $today,
            'status' => Attendance::STATUS_ABSENT,
            'remarks' => 'Not present at curfew',
            'recorded_at' => now(),
        ]);

        $response = $this->actingAs($this->proctor)->post(route('proctor.attendance.finalize'), [
            'block_id' => $this->block->id,
            'date' => $today,
            'notes' => 'Rounds completed at 21:50',
        ]);

        $response->assertRedirect();

        // 1. Submission created
        $this->assertDatabaseHas('attendance_submissions', [
            'block_id' => $this->block->id,
            'total_students' => 2,
            'present_count' => 1,
            'absent_count' => 1,
        ]);
        $this->assertEquals($today, AttendanceSubmission::first()->date->toDateString());

        // 2. Absentee warning alert sent to absent student (student2)
        $this->assertDatabaseHas('attendance_alerts', [
            'user_id' => $this->student2->id,
            'type' => AttendanceAlert::TYPE_ABSENTEE_WARNING,
        ]);

        // 3. Submission alert sent to Proctor Manager
        $this->assertDatabaseHas('attendance_alerts', [
            'user_id' => $this->manager->id,
            'type' => AttendanceAlert::TYPE_ATTENDANCE_SUBMITTED,
        ]);
    }

    public function test_student_can_view_own_attendance_history(): void
    {
        $today = Carbon::today()->toDateString();

        Attendance::create([
            'student_id' => $this->student1->id,
            'proctor_id' => $this->proctor->id,
            'block_id' => $this->block->id,
            'room_id' => $this->room->id,
            'date' => $today,
            'status' => Attendance::STATUS_PRESENT,
            'recorded_at' => now(),
        ]);

        $response = $this->actingAs($this->student1)->get(route('student.attendance.index'));

        $response->assertOk();
        $response->assertSee('My Attendance History');
        $response->assertSee('Present');
        $response->assertSee('100%');
    }

    public function test_manager_and_admin_can_view_curfew_monitoring_and_absentee_list(): void
    {
        $today = Carbon::today()->toDateString();

        Attendance::create([
            'student_id' => $this->student2->id,
            'proctor_id' => $this->proctor->id,
            'block_id' => $this->block->id,
            'room_id' => $this->room->id,
            'date' => $today,
            'status' => Attendance::STATUS_ABSENT,
            'remarks' => 'Missing at curfew',
            'recorded_at' => now(),
        ]);

        $response = $this->actingAs($this->manager)->get(route('manager.attendance.index', ['date' => $today]));
        $response->assertOk();
        $response->assertSee('Daily Absentee List');
        $response->assertSee($this->student2->name);

        $adminResponse = $this->actingAs($this->admin)->get(route('admin.attendance.index', ['date' => $today]));
        $adminResponse->assertOk();
        $adminResponse->assertSee('Daily Absentee List');
        $adminResponse->assertSee($this->student2->name);
    }

    public function test_can_export_absentee_csv(): void
    {
        $today = Carbon::today()->toDateString();

        Attendance::create([
            'student_id' => $this->student2->id,
            'proctor_id' => $this->proctor->id,
            'block_id' => $this->block->id,
            'room_id' => $this->room->id,
            'date' => $today,
            'status' => Attendance::STATUS_ABSENT,
            'remarks' => 'Unexcused room absence',
            'recorded_at' => now(),
        ]);

        $response = $this->actingAs($this->admin)->get(route('attendance.export_csv', [
            'date' => $today,
            'type' => 'absentees',
        ]));

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }

    public function test_absent_student_is_preserved_and_not_changed_to_present_on_submit(): void
    {
        $today = Carbon::today()->toDateString();

        // Proctor submits daily record with student2 marked as absent
        $response = $this->actingAs($this->proctor)->post(route('proctor.attendance.finalize'), [
            'block_id' => $this->block->id,
            'date' => $today,
            'students' => [
                [
                    'student_id' => $this->student2->id,
                    'room_id' => $this->room->id,
                    'status' => Attendance::STATUS_ABSENT,
                    'remarks' => 'Absent during evening inspection',
                ],
            ],
            'notes' => 'Rounds completed.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertStringContainsString('1 absent', session('success'));
        $this->assertStringNotContainsString('0 absent', session('success'));

        // Verify student2 is recorded as absent in DB (not changed to present)
        $this->assertDatabaseHas('attendances', [
            'student_id' => $this->student2->id,
            'status' => Attendance::STATUS_ABSENT,
        ]);
        $this->assertEquals($today, Carbon::parse(Attendance::where('student_id', $this->student2->id)->value('date'))->toDateString());

        // Verify student1 (unrecorded) is left blank at that date (not automatically marked present)
        $this->assertDatabaseMissing('attendances', [
            'student_id' => $this->student1->id,
            'date' => $today,
        ]);

        // Verify submission totals
        $sub = AttendanceSubmission::where('block_id', $this->block->id)->first();
        $this->assertNotNull($sub);
        $this->assertEquals(1, $sub->absent_count);
        $this->assertEquals(0, $sub->present_count);
        $this->assertEquals(1, $sub->total_students);
        $this->assertEquals($today, $sub->date->toDateString());
    }

    public function test_save_room_returns_next_room_for_sequential_navigation(): void
    {
        $today = Carbon::today()->toDateString();

        // Create Room 102 in same block
        $room2 = Room::create([
            'block_id' => $this->block->id,
            'room_number' => '102',
            'floor' => 1,
            'capacity' => 4,
            'status' => 'available',
        ]);

        // Save Room 101 via JSON
        $response = $this->actingAs($this->proctor)->postJson(route('proctor.attendance.save_room'), [
            'block_id' => $this->block->id,
            'room_id' => $this->room->id,
            'date' => $today,
            'students' => [
                [
                    'student_id' => $this->student1->id,
                    'status' => Attendance::STATUS_PRESENT,
                ],
            ],
        ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'next_room_id' => $room2->id,
            'next_room_number' => '102',
            'is_last_room' => false,
        ]);

        // Save Room 102 (last room)
        $response2 = $this->actingAs($this->proctor)->postJson(route('proctor.attendance.save_room'), [
            'block_id' => $this->block->id,
            'room_id' => $room2->id,
            'date' => $today,
            'students' => [],
        ]);

        $response2->assertOk();
        $response2->assertJson([
            'success' => true,
            'next_room_id' => null,
            'is_last_room' => true,
        ]);
    }

    public function test_chronic_absentee_badge_appears_for_students_with_multiple_absences(): void
    {
        $yesterday = Carbon::yesterday()->toDateString();
        $twoDaysAgo = Carbon::today()->subDays(2)->toDateString();
        $today = Carbon::today()->toDateString();

        // Student2 absent twice in recent days
        Attendance::create([
            'student_id' => $this->student2->id,
            'proctor_id' => $this->proctor->id,
            'block_id' => $this->block->id,
            'room_id' => $this->room->id,
            'date' => $twoDaysAgo,
            'status' => Attendance::STATUS_ABSENT,
        ]);

        Attendance::create([
            'student_id' => $this->student2->id,
            'proctor_id' => $this->proctor->id,
            'block_id' => $this->block->id,
            'room_id' => $this->room->id,
            'date' => $yesterday,
            'status' => Attendance::STATUS_ABSENT,
        ]);

        $response = $this->actingAs($this->proctor)->get(route('proctor.attendance.index', [
            'block_id' => $this->block->id,
            'date' => $today,
        ]));

        $response->assertOk();
        $response->assertSee('2x Absent this week');
    }

    public function test_proctor_can_generate_and_submit_weekly_report_to_manager(): void
    {
        $startDate = Carbon::today()->subDays(6)->toDateString();
        $endDate = Carbon::today()->toDateString();

        // Seed some attendance records in range
        Attendance::create([
            'student_id' => $this->student1->id,
            'proctor_id' => $this->proctor->id,
            'block_id' => $this->block->id,
            'room_id' => $this->room->id,
            'date' => $startDate,
            'status' => Attendance::STATUS_PRESENT,
        ]);

        Attendance::create([
            'student_id' => $this->student2->id,
            'proctor_id' => $this->proctor->id,
            'block_id' => $this->block->id,
            'room_id' => $this->room->id,
            'date' => $startDate,
            'status' => Attendance::STATUS_ABSENT,
        ]);

        // 1. Proctor views weekly report
        $reportView = $this->actingAs($this->proctor)->get(route('proctor.attendance.reports', [
            'block_id' => $this->block->id,
            'type' => 'weekly',
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]));

        $reportView->assertOk();
        $reportView->assertSee('Weekly Curfew Attendance Report');

        // 2. Proctor submits weekly report to Proctor Manager
        $submitResponse = $this->actingAs($this->proctor)->post(route('proctor.attendance.reports.submit'), [
            'block_id' => $this->block->id,
            'report_type' => 'weekly',
            'start_date' => $startDate,
            'end_date' => $endDate,
            'notes' => 'Weekly checks finished across all floors.',
        ]);

        $submitResponse->assertRedirect();
        $this->assertDatabaseHas('attendance_reports', [
            'block_id' => $this->block->id,
            'proctor_id' => $this->proctor->id,
            'report_type' => 'weekly',
            'status' => AttendanceReport::STATUS_SUBMITTED,
        ]);

        // Verify alert sent to Proctor Manager
        $this->assertDatabaseHas('attendance_alerts', [
            'user_id' => $this->manager->id,
            'type' => 'report_submitted',
        ]);
    }

    public function test_manager_can_review_proctor_submitted_report(): void
    {
        $report = AttendanceReport::create([
            'block_id' => $this->block->id,
            'proctor_id' => $this->proctor->id,
            'report_type' => 'weekly',
            'start_date' => Carbon::today()->subDays(6)->toDateString(),
            'end_date' => Carbon::today()->toDateString(),
            'tracked_days_count' => 7,
            'total_residents' => 2,
            'total_present' => 12,
            'total_absent' => 2,
            'status' => AttendanceReport::STATUS_SUBMITTED,
            'submitted_at' => now(),
        ]);

        $response = $this->actingAs($this->manager)->post(route('manager.attendance.reports.review', $report), [
            'manager_remarks' => 'Report verified. Disciplinary notices served to frequent absentees.',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('attendance_reports', [
            'id' => $report->id,
            'status' => AttendanceReport::STATUS_REVIEWED,
            'reviewed_by' => $this->manager->id,
            'manager_remarks' => 'Report verified. Disciplinary notices served to frequent absentees.',
        ]);

        // Alert sent back to proctor
        $this->assertDatabaseHas('attendance_alerts', [
            'user_id' => $this->proctor->id,
            'type' => 'report_reviewed',
        ]);
    }

    public function test_rooms_are_ordered_naturally_and_unchecked_attendance_remains_blank(): void
    {
        $today = Carbon::today()->toDateString();

        // Create rooms with out-of-order room numbers: 1, 10, 2, 20
        $r10 = Room::create(['block_id' => $this->block->id, 'room_number' => '10', 'capacity' => 4]);
        $r2 = Room::create(['block_id' => $this->block->id, 'room_number' => '2', 'capacity' => 4]);
        $r20 = Room::create(['block_id' => $this->block->id, 'room_number' => '20', 'capacity' => 4]);

        $ordered = Room::where('block_id', $this->block->id)->orderByRoomNumber()->pluck('room_number')->all();
        // Since $this->room was 101, natural order of ['101', '10', '2', '20'] is 2, 10, 20, 101
        $this->assertEquals(['2', '10', '20', '101'], $ordered);

        // Test saving room with pending/empty status leaves student blank (no record created)
        $response = $this->actingAs($this->proctor)->postJson(route('proctor.attendance.save_room'), [
            'block_id' => $this->block->id,
            'room_id' => $this->room->id,
            'date' => $today,
            'students' => [
                [
                    'student_id' => $this->student1->id,
                    'status' => 'pending',
                ],
            ],
        ]);

        $response->assertOk();
        $this->assertDatabaseMissing('attendances', [
            'student_id' => $this->student1->id,
            'date' => $today,
        ]);
    }
}
