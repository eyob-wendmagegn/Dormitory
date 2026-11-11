<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\BlockProctorAssignment;
use App\Models\Comment;
use App\Models\DormitoryBlock;
use App\Models\LeaveRequest;
use App\Models\Room;
use App\Models\StudentAllocation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DynamicDashboardsTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $manager;

    protected User $proctor;

    protected User $maleStudent;

    protected User $femaleStudent;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'name' => 'Test Admin',
            'role' => User::ROLE_ADMIN,
            'gender' => 'male',
            'registration_status' => User::REGISTRATION_COMPLETED,
            'is_active' => true,
        ]);

        $this->manager = User::factory()->create([
            'name' => 'Test Manager',
            'role' => User::ROLE_PROCTOR_MANAGER,
            'gender' => 'male',
            'registration_status' => User::REGISTRATION_COMPLETED,
            'is_active' => true,
        ]);

        $this->proctor = User::factory()->create([
            'name' => 'Real Proctor One',
            'university_id' => 'EMP/9001',
            'role' => User::ROLE_PROCTOR,
            'gender' => 'male',
            'registration_status' => User::REGISTRATION_COMPLETED,
            'is_active' => true,
        ]);

        $this->maleStudent = User::factory()->create([
            'name' => 'Male Resident',
            'university_id' => 'WLD/1001/17',
            'role' => User::ROLE_STUDENT,
            'gender' => 'male',
            'registration_status' => User::REGISTRATION_COMPLETED,
            'is_active' => true,
        ]);

        $this->femaleStudent = User::factory()->create([
            'name' => 'Female Resident',
            'university_id' => 'WLD/1002/17',
            'role' => User::ROLE_STUDENT,
            'gender' => 'female',
            'registration_status' => User::REGISTRATION_COMPLETED,
            'is_active' => true,
        ]);
    }

    public function test_admin_dashboard_reflects_block_deletions_and_creations_and_total_users_dynamically(): void
    {
        $oldBlock = DormitoryBlock::create([
            'name' => 'Old Legacy Block',
            'block_code' => 'BLK-48',
            'gender_category' => 'male',
            'status' => 'active',
        ]);

        $initialResponse = $this->actingAs($this->admin)->get(route('admin.dashboard'));
        $initialResponse->assertOk();
        $initialResponse->assertSee('BLK-48');
        $initialResponse->assertSee('5 Total Users');
        $initialResponse->assertSee('2 Students');
        $initialResponse->assertSee('2 Staff');
        $initialResponse->assertSee('1 Admin');
        $initialResponse->assertDontSee('Oct 29 - Nov 04, 2026');
        $initialResponse->assertDontSee('+26.5%');
        $initialResponse->assertDontSee('Completed 52%');

        // Delete BLK-48 and create BLK-01 + BLK-02 with real rooms
        $this->actingAs($this->admin)->delete(route('admin.blocks.destroy', $oldBlock));

        $newBlock1 = DormitoryBlock::create([
            'name' => 'Axum Hall',
            'block_code' => 'BLK-01',
            'gender_category' => 'male',
            'status' => 'active',
        ]);
        $newBlock2 = DormitoryBlock::create([
            'name' => 'Lalibela Hall',
            'block_code' => 'BLK-02',
            'gender_category' => 'female',
            'status' => 'active',
        ]);

        Room::create([
            'block_id' => $newBlock1->id,
            'room_number' => '101',
            'capacity' => 4,
            'status' => 'available',
        ]);
        Room::create([
            'block_id' => $newBlock2->id,
            'room_number' => '201',
            'capacity' => 6,
            'status' => 'available',
        ]);

        $updatedResponse = $this->actingAs($this->admin)->get(route('admin.dashboard'));
        $updatedResponse->assertOk();
        $updatedResponse->assertSee(':labels=\'["BLK-01","BLK-02"]\'', false);
        $updatedResponse->assertSee('BLK-01');
        $updatedResponse->assertSee('BLK-02');
        $updatedResponse->assertSee('2 of 70 Blocks');
        $updatedResponse->assertSee('2 Reg');
        $updatedResponse->assertSee('5 Total Users');
        $updatedResponse->assertSee('Design Cap: 100 (2 Blks)');
        $updatedResponse->assertSee('Active Cap: 10 Beds');
        $updatedResponse->assertSee(Carbon::today()->format('M d, Y'));

        // After the one-time deletion flash banner clears, BLK-48 must not appear anywhere on the dashboard
        $steadyResponse = $this->actingAs($this->admin)->get(route('admin.dashboard'));
        $steadyResponse->assertOk();
        $steadyResponse->assertDontSee('BLK-48');
    }

    public function test_admin_dashboard_computes_exact_occupancy_and_gender_ratios_without_fake_fallbacks(): void
    {
        $block = DormitoryBlock::create([
            'name' => 'Abay Hall',
            'block_code' => 'BLK-10',
            'gender_category' => 'male',
            'status' => 'active',
        ]);
        $room = Room::create([
            'block_id' => $block->id,
            'room_number' => '105',
            'capacity' => 4,
            'status' => 'available',
        ]);

        // Initially 0 allocated out of 4 capacity => 0% occupancy, 1 male + 1 female => 50% each
        $emptyResponse = $this->actingAs($this->admin)->get(route('admin.dashboard'));
        $emptyResponse->assertOk();
        $emptyResponse->assertSee('center-text="0%"', false);
        $emptyResponse->assertSee('1 (50%)');

        // Allocate maleStudent => 1 / 4 = 25% occupancy
        StudentAllocation::create([
            'student_id' => $this->maleStudent->id,
            'room_id' => $room->id,
            'allocated_by' => $this->proctor->id,
            'allocated_at' => now(),
            'status' => StudentAllocation::STATUS_ACTIVE,
        ]);

        $occupiedResponse = $this->actingAs($this->admin)->get(route('admin.dashboard'));
        $occupiedResponse->assertOk();
        $occupiedResponse->assertSee('center-text="25%"', false);
        $occupiedResponse->assertSee('1 Allocated');
        $occupiedResponse->assertSee('1 Unallocated');
    }

    public function test_proctor_manager_dashboard_uses_live_blocks_proctors_and_rates(): void
    {
        $block = DormitoryBlock::create([
            'name' => 'Tana Hall',
            'block_code' => 'BLK-05',
            'gender_category' => 'male',
            'status' => 'active',
        ]);
        $room = Room::create([
            'block_id' => $block->id,
            'room_number' => '101',
            'capacity' => 4,
            'status' => 'available',
        ]);

        BlockProctorAssignment::create([
            'block_id' => $block->id,
            'proctor_id' => $this->proctor->id,
            'assigned_by' => $this->manager->id,
            'assigned_at' => now(),
        ]);

        StudentAllocation::create([
            'student_id' => $this->maleStudent->id,
            'room_id' => $room->id,
            'allocated_by' => $this->proctor->id,
            'allocated_at' => now(),
            'status' => StudentAllocation::STATUS_ACTIVE,
        ]);

        $response = $this->actingAs($this->manager)->get(route('manager.dashboard'));
        $response->assertOk();
        $response->assertSee('5 Total Users');
        $response->assertSee('Real Proctor One (EMP/9001)');
        $response->assertSee('BLK-05');
        $response->assertSee('1 Assigned');
        $response->assertSee('100% Active');
        $response->assertSee('100% Block Coverage');
        $response->assertSee('25% Dormitory Capacity Utilized');
        $response->assertSee('50% Placement');
        $response->assertDontSee('Tigist Haile');
        $response->assertDontSee('EMP/3001');
        $response->assertDontSee('68.2%');
        $response->assertDontSee('62% Dormitory Capacity Utilized');
    }

    public function test_proctor_dashboard_shows_real_zeroes_and_live_curfew_metrics(): void
    {
        $block = DormitoryBlock::create([
            'name' => 'Tekezze Hall',
            'block_code' => 'BLK-07',
            'gender_category' => 'male',
            'status' => 'active',
        ]);
        $room = Room::create([
            'block_id' => $block->id,
            'room_number' => '102',
            'capacity' => 5,
            'status' => 'available',
        ]);
        BlockProctorAssignment::create([
            'block_id' => $block->id,
            'proctor_id' => $this->proctor->id,
            'assigned_by' => $this->manager->id,
            'assigned_at' => now(),
        ]);

        // Unpopulated check: all counts must be 0, zero fake 24 / 27 / 96.5%
        $emptyResponse = $this->actingAs($this->proctor)->get(route('proctor.dashboard'));
        $emptyResponse->assertOk();
        $emptyResponse->assertSee('Curfew Check-ins Today');
        $emptyResponse->assertSee('Bed Status Breakdown');
        $emptyResponse->assertDontSee('Visits Today');
        $emptyResponse->assertDontSee('Revenue Breakdown');
        $emptyResponse->assertDontSee('96.5%');
        $emptyResponse->assertDontSee('value="27"', false);
        $emptyResponse->assertDontSee('value="24"', false);

        // Add allocation, attendance, and active leave
        StudentAllocation::create([
            'student_id' => $this->maleStudent->id,
            'room_id' => $room->id,
            'allocated_by' => $this->proctor->id,
            'allocated_at' => now(),
            'status' => StudentAllocation::STATUS_ACTIVE,
        ]);
        Attendance::create([
            'student_id' => $this->maleStudent->id,
            'proctor_id' => $this->proctor->id,
            'block_id' => $block->id,
            'room_id' => $room->id,
            'date' => Carbon::today()->toDateString(),
            'recorded_at' => now(),
            'status' => Attendance::STATUS_PRESENT,
        ]);
        LeaveRequest::create([
            'student_id' => $this->maleStudent->id,
            'request_type' => LeaveRequest::TYPE_GATE_PASS,
            'start_date' => Carbon::today()->toDateString(),
            'end_date' => Carbon::today()->addDays(2)->toDateString(),
            'reason' => 'Family visit',
            'status' => LeaveRequest::STATUS_APPROVED,
            'approved_by' => $this->proctor->id,
            'approved_at' => now(),
        ]);

        $activeResponse = $this->actingAs($this->proctor)->get(route('proctor.dashboard'));
        $activeResponse->assertOk();
        $activeResponse->assertSee('100%');
        $activeResponse->assertSee('value="1"', false);
        $activeResponse->assertSee('1/5 Beds');
    }

    public function test_student_dashboard_and_landing_page_show_accurate_live_metrics(): void
    {
        $block = DormitoryBlock::create([
            'name' => 'Guna Hall',
            'block_code' => 'BLK-09',
            'gender_category' => 'male',
            'status' => 'active',
        ]);
        $room = Room::create([
            'block_id' => $block->id,
            'room_number' => '301',
            'capacity' => 6,
            'status' => 'available',
        ]);

        // Unallocated & no attendance/comments => 0 everywhere, no fake 28 / 26 / 98.2%
        $emptyStudentRes = $this->actingAs($this->maleStudent)->get(route('student.dashboard'));
        $emptyStudentRes->assertOk();
        $emptyStudentRes->assertSee('My Curfew Check-ins');
        $emptyStudentRes->assertSee('Room & Bed Status', false);
        $emptyStudentRes->assertSee('0\/0 Present', false);
        $emptyStudentRes->assertDontSee('Visits Today');
        $emptyStudentRes->assertDontSee('98.2%');

        // Empty attendance on landing page => 0% compliance (never hardcoded 99.8%)
        $emptyLanding = $this->get(route('landing'));
        $emptyLanding->assertOk();
        $emptyLanding->assertSee('0%');
        $emptyLanding->assertDontSee('99.8%');

        // Allocate student + record 1 present & 1 absent attendance (50% compliance) + 6 comments
        StudentAllocation::create([
            'student_id' => $this->maleStudent->id,
            'room_id' => $room->id,
            'allocated_by' => $this->proctor->id,
            'allocated_at' => now(),
            'status' => StudentAllocation::STATUS_ACTIVE,
        ]);
        Attendance::create([
            'student_id' => $this->maleStudent->id,
            'proctor_id' => $this->proctor->id,
            'block_id' => $block->id,
            'room_id' => $room->id,
            'date' => Carbon::today()->toDateString(),
            'recorded_at' => now(),
            'status' => Attendance::STATUS_PRESENT,
        ]);
        Attendance::create([
            'student_id' => $this->maleStudent->id,
            'proctor_id' => $this->proctor->id,
            'block_id' => $block->id,
            'room_id' => $room->id,
            'date' => Carbon::yesterday()->toDateString(),
            'recorded_at' => now(),
            'status' => Attendance::STATUS_ABSENT,
        ]);

        for ($i = 1; $i <= 6; $i++) {
            Comment::create([
                'user_id' => $this->maleStudent->id,
                'category' => 'inquiry',
                'title' => "Inquiry {$i}",
                'message' => 'Test message',
                'status' => $i === 1 ? Comment::STATUS_RESOLVED : Comment::STATUS_PENDING,
            ]);
        }

        $activeStudentRes = $this->actingAs($this->maleStudent)->get(route('student.dashboard'));
        $activeStudentRes->assertOk();
        $activeStudentRes->assertSee('1\/2 Present', false);
        $activeStudentRes->assertSee('50%');
        $activeStudentRes->assertSee('1\/6 Resolved', false);
        $activeStudentRes->assertSee('BLK-09');
        $activeStudentRes->assertSee('301');

        $activeLanding = $this->get(route('landing'));
        $activeLanding->assertOk();
        $activeLanding->assertSee('50%');
        $activeLanding->assertSee('5 Users');
    }
}
