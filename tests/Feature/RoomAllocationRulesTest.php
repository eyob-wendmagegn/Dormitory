<?php

namespace Tests\Feature;

use App\Models\BlockProctorAssignment;
use App\Models\DormitoryBlock;
use App\Models\Room;
use App\Models\StudentAllocation;
use App\Models\User;
use App\Services\RoomAllocationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class RoomAllocationRulesTest extends TestCase
{
    use RefreshDatabase;

    private RoomAllocationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new RoomAllocationService();
    }

    public function test_can_allocate_student_to_available_room_with_matching_gender(): void
    {
        $block = DormitoryBlock::create([
            'name' => 'Male Block 1',
            'block_code' => 'B-01',
            'gender_category' => 'male',
            'status' => 'active',
        ]);

        $proctor = User::create([
            'name' => 'Proctor John',
            'university_id' => 'PR-01',
            'role' => User::ROLE_PROCTOR,
            'gender' => 'male',
            'registration_status' => User::REGISTRATION_COMPLETED,
        ]);

        BlockProctorAssignment::create([
            'block_id' => $block->id,
            'proctor_id' => $proctor->id,
            'assigned_at' => now(),
        ]);

        $room = Room::create([
            'block_id' => $block->id,
            'room_number' => '101',
            'capacity' => 2,
            'status' => 'available',
        ]);

        $student = User::create([
            'name' => 'Student Male',
            'university_id' => 'ST-01',
            'role' => User::ROLE_STUDENT,
            'gender' => 'male',
            'academic_year' => 2,
            'registration_status' => User::REGISTRATION_COMPLETED,
        ]);

        $allocation = $this->service->allocate($student, $room, $proctor, 'Bed 1');

        $this->assertDatabaseHas('student_allocations', [
            'id' => $allocation->id,
            'student_id' => $student->id,
            'room_id' => $room->id,
            'status' => StudentAllocation::STATUS_ACTIVE,
        ]);
    }

    public function test_gender_mismatch_is_strictly_rejected(): void
    {
        // Female block
        $femaleBlock = DormitoryBlock::create([
            'name' => 'Female Hall',
            'block_code' => 'B-FEMALE',
            'gender_category' => 'female',
            'status' => 'active',
        ]);

        $proctor = User::create([
            'name' => 'Proctor Tigist',
            'university_id' => 'PR-02',
            'role' => User::ROLE_PROCTOR,
            'gender' => 'female',
            'registration_status' => 'completed',
        ]);

        BlockProctorAssignment::create([
            'block_id' => $femaleBlock->id,
            'proctor_id' => $proctor->id,
            'assigned_at' => now(),
        ]);

        $room = Room::create([
            'block_id' => $femaleBlock->id,
            'room_number' => '201',
            'capacity' => 2,
            'status' => 'available',
        ]);

        // Male student attempting allocation to Female block
        $maleStudent = User::create([
            'name' => 'Student Daniel',
            'university_id' => 'ST-MALE-01',
            'role' => User::ROLE_STUDENT,
            'gender' => 'male',
            'academic_year' => 1,
            'registration_status' => 'completed',
        ]);

        try {
            $this->service->allocate($maleStudent, $room, $proctor);
            $this->fail('Expected ValidationException was not thrown on gender mismatch.');
        } catch (ValidationException $e) {
            $this->assertStringContainsString("Allocation denied. This student's recorded gender does not match the accommodation category of the selected block.", $e->getMessage());
        }
    }

    public function test_student_cannot_have_multiple_active_allocations(): void
    {
        $block = DormitoryBlock::create(['name' => 'Block 1', 'block_code' => 'B-01', 'gender_category' => 'male']);
        $proctor = User::create(['name' => 'Proctor', 'university_id' => 'PR-01', 'role' => User::ROLE_PROCTOR, 'gender' => 'male', 'registration_status' => 'completed']);
        BlockProctorAssignment::create(['block_id' => $block->id, 'proctor_id' => $proctor->id, 'assigned_at' => now()]);

        $room1 = Room::create(['block_id' => $block->id, 'room_number' => '101', 'capacity' => 2, 'status' => 'available']);
        $room2 = Room::create(['block_id' => $block->id, 'room_number' => '102', 'capacity' => 2, 'status' => 'available']);

        $student = User::create(['name' => 'Student Alice', 'university_id' => 'ST-01', 'role' => User::ROLE_STUDENT, 'gender' => 'male', 'academic_year' => 2, 'registration_status' => 'completed']);

        // First allocation succeeds
        $this->service->allocate($student, $room1, $proctor);

        // Second active allocation attempt must throw ValidationException
        $this->expectException(ValidationException::class);
        $this->service->allocate($student, $room2, $proctor);
    }

    public function test_room_capacity_cannot_be_exceeded(): void
    {
        $block = DormitoryBlock::create(['name' => 'Block 1', 'block_code' => 'B-01', 'gender_category' => 'male']);
        $proctor = User::create(['name' => 'Proctor', 'university_id' => 'PR-01', 'role' => User::ROLE_PROCTOR, 'gender' => 'male', 'registration_status' => 'completed']);
        BlockProctorAssignment::create(['block_id' => $block->id, 'proctor_id' => $proctor->id, 'assigned_at' => now()]);

        $room = Room::create(['block_id' => $block->id, 'room_number' => '101', 'capacity' => 1, 'status' => 'available']);

        $student1 = User::create(['name' => 'Student 1', 'university_id' => 'ST-01', 'role' => User::ROLE_STUDENT, 'gender' => 'male', 'registration_status' => 'completed']);
        $student2 = User::create(['name' => 'Student 2', 'university_id' => 'ST-02', 'role' => User::ROLE_STUDENT, 'gender' => 'male', 'registration_status' => 'completed']);

        $this->service->allocate($student1, $room, $proctor);

        // Room now marked full, second student must fail
        $this->expectException(ValidationException::class);
        $this->service->allocate($student2, $room, $proctor);
    }

    public function test_proctor_cannot_allocate_room_in_unauthorized_block(): void
    {
        $block1 = DormitoryBlock::create(['name' => 'Block 1', 'block_code' => 'B-01', 'gender_category' => 'male']);
        $block2 = DormitoryBlock::create(['name' => 'Block 2', 'block_code' => 'B-02', 'gender_category' => 'male']);

        $proctor = User::create(['name' => 'Proctor', 'university_id' => 'PR-01', 'role' => User::ROLE_PROCTOR, 'gender' => 'male', 'registration_status' => 'completed']);
        BlockProctorAssignment::create(['block_id' => $block1->id, 'proctor_id' => $proctor->id, 'assigned_at' => now()]);

        $roomInBlock2 = Room::create(['block_id' => $block2->id, 'room_number' => '201', 'capacity' => 2, 'status' => 'available']);
        $student = User::create(['name' => 'Student', 'university_id' => 'ST-01', 'role' => User::ROLE_STUDENT, 'gender' => 'male', 'registration_status' => 'completed']);

        $this->expectException(ValidationException::class);
        $this->service->allocate($student, $roomInBlock2, $proctor);
    }

    public function test_cannot_change_block_gender_category_with_active_allocations(): void
    {
        $block = DormitoryBlock::create(['name' => 'Block 1', 'block_code' => 'B-01', 'gender_category' => 'male']);
        $manager = User::create(['name' => 'Manager', 'university_id' => 'M-01', 'role' => User::ROLE_PROCTOR_MANAGER, 'registration_status' => 'completed']);
        $proctor = User::create(['name' => 'Proctor', 'university_id' => 'PR-01', 'role' => User::ROLE_PROCTOR, 'gender' => 'male', 'registration_status' => 'completed']);
        BlockProctorAssignment::create(['block_id' => $block->id, 'proctor_id' => $proctor->id, 'assigned_at' => now()]);

        $room = Room::create(['block_id' => $block->id, 'room_number' => '101', 'capacity' => 2, 'status' => 'available']);
        $student = User::create(['name' => 'Student 1', 'university_id' => 'ST-01', 'role' => User::ROLE_STUDENT, 'gender' => 'male', 'registration_status' => 'completed']);

        $this->service->allocate($student, $room, $proctor);

        // Attempting to change block to 'female' while containing male student must throw ValidationException
        $this->expectException(ValidationException::class);
        $this->service->updateBlockCategory($block, 'female', $manager);
    }
}
