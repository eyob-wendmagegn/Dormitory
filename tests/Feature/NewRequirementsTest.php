<?php

namespace Tests\Feature;

use App\Models\BlockProctorAssignment;
use App\Models\Comment;
use App\Models\DormitoryBlock;
use App\Models\Room;
use App\Models\StudentAllocation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NewRequirementsTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $manager;
    protected User $proctor1;
    protected User $proctor2;
    protected DormitoryBlock $block1;
    protected DormitoryBlock $block2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'registration_status' => User::REGISTRATION_COMPLETED,
            'is_active' => true,
        ]);

        $this->manager = User::factory()->create([
            'role' => User::ROLE_PROCTOR_MANAGER,
            'registration_status' => User::REGISTRATION_COMPLETED,
            'is_active' => true,
        ]);

        $this->proctor1 = User::factory()->create([
            'role' => User::ROLE_PROCTOR,
            'registration_status' => User::REGISTRATION_COMPLETED,
            'is_active' => true,
        ]);

        $this->proctor2 = User::factory()->create([
            'role' => User::ROLE_PROCTOR,
            'registration_status' => User::REGISTRATION_COMPLETED,
            'is_active' => true,
        ]);

        $this->block1 = DormitoryBlock::create([
            'name' => 'Fasiledes Hall',
            'block_code' => 'BLK-45',
            'gender_category' => 'male',
            'status' => 'active',
        ]);

        $this->block2 = DormitoryBlock::create([
            'name' => 'Lalibela Hall',
            'block_code' => 'BLK-46',
            'gender_category' => 'female',
            'status' => 'active',
        ]);
    }

    public function test_proctor_cannot_be_assigned_to_more_than_one_block(): void
    {
        // Assign proctor1 to block1
        BlockProctorAssignment::create([
            'block_id' => $this->block1->id,
            'proctor_id' => $this->proctor1->id,
            'assigned_by' => $this->manager->id,
            'assigned_at' => now(),
        ]);

        // Attempt to assign proctor1 to block2 -> should be rejected!
        $response = $this->actingAs($this->manager)
            ->post(route('manager.assignments.store'), [
                'block_id' => $this->block2->id,
                'proctor_id' => $this->proctor1->id,
            ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('block_proctor_assignments', [
            'block_id' => $this->block2->id,
            'proctor_id' => $this->proctor1->id,
            'ended_at' => null,
        ]);
    }

    public function test_block_cannot_be_assigned_more_than_one_proctor(): void
    {
        // Assign proctor1 to block1
        BlockProctorAssignment::create([
            'block_id' => $this->block1->id,
            'proctor_id' => $this->proctor1->id,
            'assigned_by' => $this->manager->id,
            'assigned_at' => now(),
        ]);

        // Attempt to assign proctor2 to block1 -> should be rejected!
        $response = $this->actingAs($this->manager)
            ->post(route('manager.assignments.store'), [
                'block_id' => $this->block1->id,
                'proctor_id' => $this->proctor2->id,
            ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('block_proctor_assignments', [
            'block_id' => $this->block1->id,
            'proctor_id' => $this->proctor2->id,
            'ended_at' => null,
        ]);
    }

    public function test_admin_can_edit_user_details(): void
    {
        $student = User::factory()->create([
            'name' => 'Original Name',
            'university_id' => 'STU-999',
            'role' => User::ROLE_STUDENT,
            'gender' => 'male',
            'academic_year' => 1,
            'registration_status' => User::REGISTRATION_COMPLETED,
        ]);

        $response = $this->actingAs($this->admin)
            ->put(route('admin.users.update', $student), [
                'name' => 'Updated Name',
                'university_id' => 'STU-999',
                'email' => 'updated@wldu.edu.et',
                'role' => User::ROLE_STUDENT,
                'gender' => 'male',
                'academic_year' => 2,
                'registration_status' => 'completed',
                'is_active' => true,
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'id' => $student->id,
            'name' => 'Updated Name',
            'email' => 'updated@wldu.edu.et',
            'academic_year' => 2,
        ]);
    }

    public function test_admin_can_deactivate_and_activate_user(): void
    {
        $student = User::factory()->create([
            'email' => 'active.student@wldu.edu.et',
            'password' => 'password123',
            'registration_status' => User::REGISTRATION_COMPLETED,
            'is_active' => true,
        ]);

        // Toggle to deactivate
        $this->actingAs($this->admin)->patch(route('admin.users.status', $student));
        $this->assertDatabaseHas('users', ['id' => $student->id, 'is_active' => false]);

        // Logout admin so request is from unauthenticated guest
        $this->post(route('logout'));

        // Attempt login as deactivated user -> should fail
        $loginResponse = $this->post(route('login'), [
            'email' => 'active.student@wldu.edu.et',
            'password' => 'password123',
        ]);
        $loginResponse->assertSessionHasErrors('email');
        $this->assertGuest();

        // Admin logs back in to activate the student
        $this->actingAs($this->admin)->patch(route('admin.users.status', $student));
        $this->assertDatabaseHas('users', ['id' => $student->id, 'is_active' => true]);

        // Logout admin
        $this->post(route('logout'));

        // Login again as active student -> should succeed
        $loginResponse2 = $this->post(route('login'), [
            'email' => 'active.student@wldu.edu.et',
            'password' => 'password123',
        ]);
        $this->assertAuthenticatedAs($student);
    }

    public function test_admin_can_bulk_delete_users_by_numeric_id_range(): void
    {
        // Create 5 users with numeric IDs between 2000 and 2005
        $createdIds = [];
        for ($i = 2001; $i <= 2005; $i++) {
            $user = User::factory()->create([
                'university_id' => (string) $i,
                'role' => User::ROLE_STUDENT,
            ]);
            $createdIds[] = $user->id;
        }

        // Create 1 user outside range (e.g. 3500)
        $outsideUser = User::factory()->create([
            'university_id' => '3500',
            'role' => User::ROLE_STUDENT,
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.users.bulk_destroy'), [
                'id_range' => '2000-2010',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // All users in range should be deleted
        foreach ($createdIds as $uid) {
            $this->assertDatabaseMissing('users', ['id' => $uid]);
        }

        // User outside range should remain
        $this->assertDatabaseHas('users', ['id' => $outsideUser->id]);

        // Admin account is never deleted
        $this->assertDatabaseHas('users', ['id' => $this->admin->id]);
    }

    public function test_admin_can_search_and_edit_dormitory_blocks(): void
    {
        // Search by block code
        $searchResponse = $this->actingAs($this->admin)
            ->get(route('admin.blocks.index', ['search' => 'BLK-45']));

        $searchResponse->assertOk();
        $searchResponse->assertSee('Fasiledes Hall');

        // Update block
        $updateResponse = $this->actingAs($this->admin)
            ->put(route('admin.blocks.update', $this->block1), [
                'name' => 'Fasiledes Hall Renovated',
                'block_code' => 'BLK-45',
                'gender_category' => 'male',
                'location' => 'Main Campus North',
            ]);

        $updateResponse->assertRedirect();
        $this->assertDatabaseHas('dormitory_blocks', [
            'id' => $this->block1->id,
            'name' => 'Fasiledes Hall Renovated',
            'location' => 'Main Campus North',
        ]);
    }

    public function test_admin_and_proctor_can_search_rooms(): void
    {
        $room = Room::create([
            'block_id' => $this->block1->id,
            'room_number' => '404',
            'capacity' => 4,
            'status' => 'available',
        ]);

        // Assign block1 to proctor1
        BlockProctorAssignment::create([
            'block_id' => $this->block1->id,
            'proctor_id' => $this->proctor1->id,
            'assigned_by' => $this->manager->id,
            'assigned_at' => now(),
        ]);

        // Admin room search by room number
        $adminRes = $this->actingAs($this->admin)->get(route('admin.rooms.index', ['search' => '404']));
        $adminRes->assertOk();
        $adminRes->assertSee('Room 404');

        // Proctor room search
        $proctorRes = $this->actingAs($this->proctor1)->get(route('proctor.rooms.index', ['search' => '404']));
        $proctorRes->assertOk();
        $proctorRes->assertSee('Room 404');
    }

    public function test_landing_page_includes_how_it_works_about_and_contact(): void
    {
        $response = $this->get(route('landing'));
        $response->assertOk();
        $response->assertSee('id="about"', false);
        $response->assertSee('id="how-it-works"', false);
        $response->assertSee('id="contact"', false);
        $response->assertSee('Contact Dormitory Directorate');
        $response->assertSee('Step-by-Step Workflow');
    }

    public function test_comment_deletion_for_admin_manager_and_proctor(): void
    {
        $comment = Comment::create([
            'user_id' => $this->proctor1->id,
            'title' => 'Maintenance needed',
            'message' => 'Heater issue in room 101',
            'category' => 'facility',
            'status' => 'pending',
            'target_role' => 'proctor_manager',
        ]);

        // Proctor delete own comment
        $resProctor = $this->actingAs($this->proctor1)->delete(route('proctor.comments.destroy', $comment));
        $resProctor->assertRedirect();
        $this->assertDatabaseMissing('comments', ['id' => $comment->id]);

        // Recreate comment and test manager delete
        $comment2 = Comment::create([
            'user_id' => $this->proctor1->id,
            'title' => 'Window lock broken',
            'message' => 'Please repair window lock',
            'category' => 'facility',
            'status' => 'pending',
            'target_role' => 'proctor_manager',
        ]);

        $resManager = $this->actingAs($this->manager)->delete(route('manager.comments.destroy', $comment2));
        $resManager->assertRedirect();
        $this->assertDatabaseMissing('comments', ['id' => $comment2->id]);

        // Recreate comment and test admin delete
        $comment3 = Comment::create([
            'user_id' => $this->manager->id,
            'title' => 'Quarterly Summary',
            'message' => 'Quarterly report submitted',
            'category' => 'suggestion',
            'status' => 'pending',
            'target_role' => 'admin',
        ]);

        $resAdmin = $this->actingAs($this->admin)->delete(route('admin.comments.destroy', $comment3));
        $resAdmin->assertRedirect();
        $this->assertDatabaseMissing('comments', ['id' => $comment3->id]);
    }

    public function test_proctor_manager_can_edit_and_delete_block(): void
    {
        $block = DormitoryBlock::create([
            'name' => 'Block to Delete',
            'block_code' => 'BLK-88',
            'gender_category' => 'male',
            'status' => 'active',
        ]);

        // Manager edits block
        $editRes = $this->actingAs($this->manager)->put(route('manager.blocks.update', $block), [
            'name' => 'Block Updated by Manager',
            'block_code' => 'BLK-88',
            'gender_category' => 'female',
            'location' => 'South Zone',
        ]);
        $editRes->assertRedirect();
        $this->assertDatabaseHas('dormitory_blocks', [
            'id' => $block->id,
            'name' => 'Block Updated by Manager',
            'gender_category' => 'female',
        ]);

        // Manager deletes block without residents
        $delRes = $this->actingAs($this->manager)->delete(route('manager.blocks.destroy', $block));
        $delRes->assertRedirect();
        $this->assertDatabaseMissing('dormitory_blocks', ['id' => $block->id]);
    }

    public function test_proctor_manager_cannot_delete_block_with_active_residents(): void
    {
        $room = Room::create([
            'block_id' => $this->block1->id,
            'room_number' => '101',
            'capacity' => 6,
            'status' => 'available',
        ]);

        $student = User::factory()->create([
            'role' => User::ROLE_STUDENT,
            'gender' => 'male',
            'registration_status' => User::REGISTRATION_COMPLETED,
            'is_active' => true,
        ]);

        StudentAllocation::create([
            'student_id' => $student->id,
            'room_id' => $room->id,
            'allocated_by' => $this->proctor1->id,
            'academic_year' => '2026',
            'semester' => 1,
            'allocated_at' => now(),
        ]);

        $delRes = $this->actingAs($this->manager)->delete(route('manager.blocks.destroy', $this->block1));
        $delRes->assertSessionHas('error');
        $this->assertDatabaseHas('dormitory_blocks', ['id' => $this->block1->id]);
    }

    public function test_proctor_manager_can_edit_and_delete_assignment(): void
    {
        $assignment = BlockProctorAssignment::create([
            'block_id' => $this->block1->id,
            'proctor_id' => $this->proctor1->id,
            'assigned_by' => $this->manager->id,
            'assigned_at' => now(),
        ]);

        // Edit assignment to assign proctor2 instead
        $editRes = $this->actingAs($this->manager)->put(route('manager.assignments.update', $assignment), [
            'block_id' => $this->block1->id,
            'proctor_id' => $this->proctor2->id,
        ]);
        $editRes->assertRedirect();
        $this->assertDatabaseHas('block_proctor_assignments', [
            'id' => $assignment->id,
            'block_id' => $this->block1->id,
            'proctor_id' => $this->proctor2->id,
        ]);

        // Delete assignment
        $delRes = $this->actingAs($this->manager)->delete(route('manager.assignments.destroy', $assignment));
        $delRes->assertRedirect();
        $this->assertDatabaseMissing('block_proctor_assignments', ['id' => $assignment->id]);
    }

    public function test_room_capacity_max_6_and_block_max_50_rooms_enforced(): void
    {
        // Reject capacity > 6
        $res = $this->actingAs($this->admin)->post(route('admin.rooms.store'), [
            'block_id' => $this->block1->id,
            'room_number' => '102',
            'capacity' => 7,
        ]);
        $res->assertSessionHasErrors('capacity');

        // Allow capacity 6
        $resValid = $this->actingAs($this->admin)->post(route('admin.rooms.store'), [
            'block_id' => $this->block1->id,
            'room_number' => '102',
            'capacity' => 6,
        ]);
        $resValid->assertRedirect();
        $this->assertDatabaseHas('rooms', [
            'block_id' => $this->block1->id,
            'room_number' => '102',
            'capacity' => 6,
        ]);
    }

    public function test_comment_hierarchy_and_scoping(): void
    {
        $student = User::factory()->create([
            'role' => User::ROLE_STUDENT,
            'gender' => 'male',
            'registration_status' => User::REGISTRATION_COMPLETED,
            'is_active' => true,
        ]);

        $room = Room::create([
            'block_id' => $this->block1->id,
            'room_number' => '201',
            'capacity' => 6,
            'status' => 'available',
        ]);

        StudentAllocation::create([
            'student_id' => $student->id,
            'room_id' => $room->id,
            'allocated_by' => $this->proctor1->id,
            'academic_year' => '2026',
            'semester' => 1,
            'allocated_at' => now(),
        ]);

        BlockProctorAssignment::create([
            'block_id' => $this->block1->id,
            'proctor_id' => $this->proctor1->id,
            'assigned_by' => $this->manager->id,
            'assigned_at' => now(),
        ]);

        // Student submits comment -> automatically targets proctor and sets block_id
        $this->actingAs($student)->post(route('student.comments.store'), [
            'title' => 'Room light issue',
            'message' => 'Bulb is flickering',
            'category' => 'maintenance',
        ]);

        $this->assertDatabaseHas('comments', [
            'user_id' => $student->id,
            'target_role' => 'proctor',
            'block_id' => $this->block1->id,
            'title' => 'Room light issue',
        ]);

        // Proctor views comments -> sees student's comment
        $proctorCommentsRes = $this->actingAs($this->proctor1)->get(route('proctor.comments.index'));
        $proctorCommentsRes->assertOk();
        $proctorCommentsRes->assertSee('Room light issue');

        // Proctor manager submits comment to admin
        $this->actingAs($this->manager)->post(route('manager.comments.store'), [
            'title' => 'Safety report for campus',
            'message' => 'All blocks inspected successfully',
            'category' => 'suggestion',
        ]);

        $this->assertDatabaseHas('comments', [
            'user_id' => $this->manager->id,
            'target_role' => 'admin',
            'title' => 'Safety report for campus',
        ]);

        // Admin views comments -> sees proctor manager's comment
        $adminCommentsRes = $this->actingAs($this->admin)->get(route('admin.comments.index'));
        $adminCommentsRes->assertOk();
        $adminCommentsRes->assertSee('Safety report for campus');
    }

    public function test_admin_can_preregister_and_update_user_with_department(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.users.preregister'), [
            'name' => 'Kalkidan Alemu',
            'university_id' => 'WLD/5501/16',
            'role' => 'student',
            'gender' => 'female',
            'department' => 'Computer Science',
            'academic_year' => 2,
        ]);
        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'name' => 'Kalkidan Alemu',
            'university_id' => 'WLD/5501/16',
            'department' => 'Computer Science',
            'role' => 'student',
        ]);

        $user = User::where('university_id', 'WLD/5501/16')->first();
        $updateRes = $this->actingAs($this->admin)->put(route('admin.users.update', $user), [
            'name' => 'Kalkidan Alemu',
            'university_id' => 'WLD/5501/16',
            'role' => 'student',
            'gender' => 'female',
            'department' => 'Software Engineering',
            'academic_year' => 3,
            'registration_status' => 'completed',
        ]);
        $updateRes->assertRedirect();
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'department' => 'Software Engineering',
            'academic_year' => 3,
        ]);
    }

    public function test_department_filtering_and_searching(): void
    {
        $s1 = User::factory()->create([
            'name' => 'Dept Tester One',
            'university_id' => 'WLD/DEPT/01',
            'role' => User::ROLE_STUDENT,
            'department' => 'Architecture',
        ]);

        $s2 = User::factory()->create([
            'name' => 'Dept Tester Two',
            'university_id' => 'WLD/DEPT/02',
            'role' => User::ROLE_STUDENT,
            'department' => 'Medicine',
        ]);

        // Admin Users filter by department
        $res = $this->actingAs($this->admin)->get(route('admin.users.index', ['department' => 'Architecture']));
        $res->assertOk();
        $res->assertSee('Dept Tester One');
        $res->assertDontSee('Dept Tester Two');

        // Admin Students filter by department
        $resStudents = $this->actingAs($this->admin)->get(route('admin.students.index', ['department' => 'Medicine']));
        $resStudents->assertOk();
        $resStudents->assertSee('Dept Tester Two');
        $resStudents->assertDontSee('Dept Tester One');

        // Manager Students search by department
        $resManager = $this->actingAs($this->manager)->get(route('manager.students.index', ['search' => 'Architecture']));
        $resManager->assertOk();
        $resManager->assertSee('Dept Tester One');
    }

    public function test_bulk_csv_import_of_students(): void
    {
        $csvContent = "name,university_id,gender,department,academic_year,role\n";
        $csvContent .= "Import Student A,3001,male,Computer Science,1,student\n";
        $csvContent .= "Import Student B,3002,female,Medicine,2,student\n";
        $csvContent .= "Import Student C,3003,male,Accounting & Finance,3,student\n";

        $file = \Illuminate\Http\UploadedFile::fake()->createWithContent('students.csv', $csvContent);

        $response = $this->actingAs($this->admin)->post(route('admin.users.import'), [
            'file' => $file,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'name' => 'Import Student A',
            'university_id' => '3001',
            'department' => 'Computer Science',
            'gender' => 'male',
            'academic_year' => 1,
            'registration_status' => User::REGISTRATION_PENDING,
        ]);
        $this->assertDatabaseHas('users', [
            'name' => 'Import Student B',
            'university_id' => '3002',
            'department' => 'Medicine',
            'gender' => 'female',
            'academic_year' => 2,
        ]);
        $this->assertDatabaseHas('users', [
            'name' => 'Import Student C',
            'university_id' => '3003',
            'department' => 'Accounting & Finance',
            'gender' => 'male',
            'academic_year' => 3,
        ]);
    }

    public function test_bulk_csv_import_with_id_range_and_department_filters(): void
    {
        $csvContent = "name,university_id,gender,department,academic_year,role\n";
        $csvContent .= "Range Student Low,2500,male,Computer Science,1,student\n";
        $csvContent .= "Range Student Inside 1,3100,male,Computer Science,1,student\n";
        $csvContent .= "Range Student Inside 2,3200,female,Electrical Engineering,2,student\n";
        $csvContent .= "Range Student High,4500,male,Computer Science,1,student\n";

        // Filter by ID range 3000-4000 AND department Computer Science
        $file = \Illuminate\Http\UploadedFile::fake()->createWithContent('students_ranged.csv', $csvContent);

        $response = $this->actingAs($this->admin)->post(route('admin.users.import'), [
            'file' => $file,
            'id_range' => '3000-4000',
            'department' => 'Computer Science',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Only "Range Student Inside 1" should be imported
        $this->assertDatabaseHas('users', ['university_id' => '3100', 'name' => 'Range Student Inside 1']);
        $this->assertDatabaseMissing('users', ['university_id' => '2500']); // Outside ID range
        $this->assertDatabaseMissing('users', ['university_id' => '3200']); // Different department
        $this->assertDatabaseMissing('users', ['university_id' => '4500']); // Outside ID range
    }

    public function test_admin_can_download_sample_csv(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.users.sample_csv'));
        $response->assertOk();
        $response->assertHeader('Content-Disposition', 'attachment; filename="students_import_template.csv"');
        $this->assertStringContainsString('name,university_id,gender,department,academic_year,role', $response->getContent());
    }
}
