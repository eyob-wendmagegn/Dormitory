<?php

namespace Tests\Feature;

use App\Models\Announcement;
use App\Models\AssetInventory;
use App\Models\BlockProctorAssignment;
use App\Models\DormitoryBlock;
use App\Models\ProctorAssetAssignment;
use App\Models\Room;
use App\Models\StudentAllocation;
use App\Models\StudentAssetIssue;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssetAndNoticeWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected User $manager;
    protected User $proctor;
    protected User $student;
    protected DormitoryBlock $block;
    protected Room $room;

    protected function setUp(): void
    {
        parent::setUp();

        $this->manager = User::factory()->create([
            'role' => User::ROLE_PROCTOR_MANAGER,
            'is_active' => true,
        ]);

        $this->proctor = User::factory()->create([
            'role' => User::ROLE_PROCTOR,
            'gender' => 'male',
            'is_active' => true,
        ]);

        $this->student = User::factory()->create([
            'role' => User::ROLE_STUDENT,
            'gender' => 'male',
            'is_active' => true,
        ]);

        $this->block = DormitoryBlock::create([
            'name' => 'Block 50 Male Wing',
            'block_code' => 'BLK-50',
            'gender_category' => 'male',
            'description' => 'Test Male Block',
            'is_active' => true,
        ]);

        // Assign proctor to block
        BlockProctorAssignment::create([
            'proctor_id' => $this->proctor->id,
            'block_id' => $this->block->id,
            'assigned_by' => $this->manager->id,
            'assigned_at' => now(),
            'is_active' => true,
        ]);

        $this->room = Room::create([
            'block_id' => $this->block->id,
            'room_number' => '101',
            'floor_number' => 1,
            'capacity' => 6,
            'status' => 'available',
        ]);

        // Allocate student to room
        StudentAllocation::create([
            'student_id' => $this->student->id,
            'room_id' => $this->room->id,
            'block_id' => $this->block->id,
            'allocated_by' => $this->proctor->id,
            'status' => StudentAllocation::STATUS_ACTIVE,
            'allocated_at' => now(),
        ]);

        // Setup warehouse initial inventory
        AssetInventory::create([
            'item_name' => 'Mattress',
            'item_code' => 'MATTRESS',
            'total_quantity' => 500,
            'available_quantity' => 500,
            'unit_penalty_charge' => 1500.00,
        ]);

        AssetInventory::create([
            'item_name' => 'Pillow',
            'item_code' => 'PILLOW',
            'total_quantity' => 500,
            'available_quantity' => 500,
            'unit_penalty_charge' => 350.00,
        ]);
    }

    /**
     * Stage 1: Manager transfers warehouse assets to Proctor; Proctor accepts handover
     */
    public function test_stage_1_manager_transfers_asset_and_proctor_confirms_handover(): void
    {
        // 1. Manager initiates transfer of 100 mattresses to Proctor
        $response = $this->from(route('manager.assets.index'))
            ->actingAs($this->manager)
            ->post(route('manager.assets.transfer'), [
                'proctor_id' => $this->proctor->id,
                'block_id' => $this->block->id,
                'item_type' => 'mattress',
                'assigned_quantity' => 100,
                'notes' => 'Semester opening stock for Block 50',
            ]);

        $response->assertRedirect(route('manager.assets.index'));
        $response->assertSessionHas('success');

        // Verify warehouse inventory decremented
        $mattressWarehouse = AssetInventory::where('item_code', 'MATTRESS')->first();
        $this->assertEquals(400, $mattressWarehouse->available_quantity);

        // Verify assignment created in pending status
        $assignment = ProctorAssetAssignment::where('proctor_id', $this->proctor->id)
            ->where('item_type', 'mattress')
            ->first();

        $this->assertNotNull($assignment);
        $this->assertEquals(ProctorAssetAssignment::STATUS_PENDING, $assignment->status);
        $this->assertEquals(100, $assignment->assigned_quantity);

        // 2. Proctor accepts handover
        $confirmResponse = $this->from(route('proctor.assets.index'))
            ->actingAs($this->proctor)
            ->post(
                route('proctor.assets.confirm_transfer', $assignment),
                [
                    'action' => 'accept',
                    'confirmed_quantity' => 100,
                    'notes' => 'Received 100 intact mattresses',
                ]
            );

        $confirmResponse->assertRedirect(route('proctor.assets.index'));
        $confirmResponse->assertSessionHas('success');

        $assignment->refresh();
        $this->assertEquals(ProctorAssetAssignment::STATUS_CONFIRMED, $assignment->status);
        $this->assertEquals(100, $assignment->confirmed_quantity);
    }

    /**
     * Stage 2: Proctor issues asset to Student; Student digitally confirms
     */
    public function test_stage_2_proctor_issues_asset_and_student_digitally_confirms(): void
    {
        // Setup confirmed proctor stock
        ProctorAssetAssignment::create([
            'manager_id' => $this->manager->id,
            'proctor_id' => $this->proctor->id,
            'block_id' => $this->block->id,
            'item_type' => 'mattress',
            'assigned_quantity' => 50,
            'confirmed_quantity' => 50,
            'status' => ProctorAssetAssignment::STATUS_CONFIRMED,
            'transfer_date' => now()->toDateString(),
            'confirmed_at' => now(),
        ]);

        // 1. Proctor issues 1 mattress to student
        $response = $this->from(route('proctor.assets.index'))
            ->actingAs($this->proctor)
            ->post(route('proctor.assets.issue'), [
                'student_id' => $this->student->id,
                'items' => ['mattress'],
                'remarks' => 'Issued upon check-in to Room 101',
            ]);

        $response->assertRedirect(route('proctor.assets.index'));
        $response->assertSessionHas('success');

        $issue = StudentAssetIssue::where('student_id', $this->student->id)
            ->where('item_type', 'mattress')
            ->first();

        $this->assertNotNull($issue);
        $this->assertEquals(StudentAssetIssue::STATUS_PENDING, $issue->status);
        $this->assertEquals(1, $issue->quantity);

        // Before student confirms, clearance is NOT eligible
        $this->assertFalse(StudentAssetIssue::isStudentEligibleForClearance($this->student->id));

        // 2. Student confirms receipt
        $studentResponse = $this->from(route('student.assets.index'))
            ->actingAs($this->student)
            ->post(route('student.assets.confirm', $issue));

        $studentResponse->assertRedirect(route('student.assets.index'));
        $studentResponse->assertSessionHas('success');

        $issue->refresh();
        $this->assertEquals(StudentAssetIssue::STATUS_CONFIRMED, $issue->status);
        $this->assertNotNull($issue->student_confirmed_at);
    }

    /**
     * Stage 3: Student returns asset in good condition -> Eligible for clearance
     */
    public function test_stage_3_student_return_good_condition_grants_clearance(): void
    {
        // Issue active confirmed mattress to student
        $issue = StudentAssetIssue::create([
            'student_id' => $this->student->id,
            'proctor_id' => $this->proctor->id,
            'block_id' => $this->block->id,
            'room_id' => $this->room->id,
            'item_type' => 'mattress',
            'quantity' => 1,
            'issue_date' => now()->subMonths(3)->toDateString(),
            'status' => StudentAssetIssue::STATUS_CONFIRMED,
            'student_confirmed_at' => now()->subMonths(3),
        ]);

        $this->assertFalse(StudentAssetIssue::isStudentEligibleForClearance($this->student->id));

        // Proctor receives return in good condition
        $returnResponse = $this->from(route('proctor.assets.index'))
            ->actingAs($this->proctor)
            ->post(
                route('proctor.assets.return', $issue),
                [
                    'condition_status' => 'good',
                    'penalty_charge' => 0,
                    'remarks' => 'Inspected and verified intact',
                ]
            );

        $returnResponse->assertRedirect(route('proctor.assets.index'));
        $returnResponse->assertSessionHas('success');

        $issue->refresh();
        $this->assertEquals(StudentAssetIssue::STATUS_RETURNED, $issue->status);
        $this->assertEquals('good', $issue->condition_status);

        // Student now has no active issues and no penalties -> eligible for clearance!
        $this->assertTrue(StudentAssetIssue::isStudentEligibleForClearance($this->student->id));
    }

    /**
     * Stage 3: Damaged or missing asset logs penalty charge and blocks clearance until resolved
     */
    public function test_stage_3_damaged_asset_logs_penalty_charge_and_blocks_clearance(): void
    {
        $issue = StudentAssetIssue::create([
            'student_id' => $this->student->id,
            'proctor_id' => $this->proctor->id,
            'block_id' => $this->block->id,
            'room_id' => $this->room->id,
            'item_type' => 'pillow',
            'quantity' => 1,
            'issue_date' => now()->subMonths(2)->toDateString(),
            'status' => StudentAssetIssue::STATUS_CONFIRMED,
            'student_confirmed_at' => now()->subMonths(2),
        ]);

        // Proctor marks return as damaged with penalty
        $returnResponse = $this->from(route('proctor.assets.index'))
            ->actingAs($this->proctor)
            ->post(
                route('proctor.assets.return', $issue),
                [
                    'condition_status' => 'damaged',
                    'penalty_charge' => 150.00,
                    'remarks' => 'Pillow torn and stained beyond standard wear',
                ]
            );

        $returnResponse->assertRedirect(route('proctor.assets.index'));
        $issue->refresh();

        $this->assertEquals(StudentAssetIssue::STATUS_DAMAGED, $issue->status);
        $this->assertEquals('damaged', $issue->condition_status);
        $this->assertEquals(150.00, (float)$issue->penalty_charge);
        $this->assertFalse($issue->is_penalty_paid);

        // Clearance must be blocked due to unpaid penalty
        $this->assertFalse(StudentAssetIssue::isStudentEligibleForClearance($this->student->id));
    }

    /**
     * Notice Workflow: Manager to Proctors internal notice vs Proctor to Students block notice
     */
    public function test_notice_workflow_targeting_and_visibility(): void
    {
        // 1. Manager drafts internal announcement restricted to Proctors
        $managerNoticeResponse = $this->from(route('manager.notices.index'))
            ->actingAs($this->manager)
            ->post(route('manager.notices.store'), [
                'title' => 'Inventory Count Scheduled This Friday',
                'summary' => 'Physical audit for all blocks',
                'content' => 'Mattress and pillow inventory count is scheduled for this afternoon; please be prepared.',
                'priority' => 'urgent',
            ]);

        $managerNoticeResponse->assertRedirect(route('manager.notices.index'));
        $managerNoticeResponse->assertSessionHas('success');

        $managerNotice = Announcement::where('title', 'Inventory Count Scheduled This Friday')->first();
        $this->assertNotNull($managerNotice);
        $this->assertEquals('proctor', $managerNotice->target_role);

        // 2. Proctor views proctor notices feed -> manager notice is present
        $proctorFeed = $this->actingAs($this->proctor)->get(route('proctor.notices.index'));
        $proctorFeed->assertStatus(200);
        $proctorFeed->assertSee('Inventory Count Scheduled This Friday');

        // 3. Student views student notices feed -> manager internal notice is HIDDEN
        $studentFeed = $this->actingAs($this->student)->get(route('student.notices.index'));
        $studentFeed->assertStatus(200);
        $studentFeed->assertDontSee('Inventory Count Scheduled This Friday');

        // 4. Proctor posts notice for their block students
        $proctorNoticeResponse = $this->from(route('proctor.notices.index'))
            ->actingAs($this->proctor)
            ->post(route('proctor.notices.store_student'), [
                'title' => 'Mattress and Pillow Return Deadline',
                'summary' => 'Final semester clearance notice',
                'content' => 'The deadline to return mattresses and pillows is Friday at 5:00 PM for Block 50 residents.',
                'priority' => 'urgent',
            ]);

        $proctorNoticeResponse->assertRedirect(route('proctor.notices.index'));
        $proctorNoticeResponse->assertSessionHas('success');

        // 5. Student in Block 50 sees the notice
        $studentFeedAfter = $this->actingAs($this->student)->get(route('student.notices.index'));
        $studentFeedAfter->assertStatus(200);
        $studentFeedAfter->assertSee('Mattress and Pillow Return Deadline');
    }
}
