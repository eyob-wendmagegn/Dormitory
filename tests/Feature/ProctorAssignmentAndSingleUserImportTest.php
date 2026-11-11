<?php

namespace Tests\Feature;

use App\Models\BlockProctorAssignment;
use App\Models\DormitoryBlock;
use App\Models\Room;
use App\Models\StudentAllocation;
use App\Models\User;
use App\Services\RoomAllocationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class ProctorAssignmentAndSingleUserImportTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $manager;
    protected User $maleProctor;
    protected User $femaleProctor;
    protected DormitoryBlock $maleBlock;
    protected DormitoryBlock $femaleBlock;
    protected Room $maleRoom;
    protected Room $femaleRoom;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'registration_status' => User::REGISTRATION_COMPLETED,
        ]);

        $this->manager = User::factory()->create([
            'role' => User::ROLE_PROCTOR_MANAGER,
            'registration_status' => User::REGISTRATION_COMPLETED,
        ]);

        $this->maleProctor = User::factory()->create([
            'name' => 'Abebe Proctor',
            'role' => User::ROLE_PROCTOR,
            'gender' => 'male',
            'registration_status' => User::REGISTRATION_COMPLETED,
        ]);

        $this->femaleProctor = User::factory()->create([
            'name' => 'Tigist Proctor',
            'role' => User::ROLE_PROCTOR,
            'gender' => 'female',
            'registration_status' => User::REGISTRATION_COMPLETED,
        ]);

        $this->maleBlock = DormitoryBlock::create([
            'name' => 'Block 49',
            'block_code' => 'BLK-49',
            'gender_category' => 'male',
            'status' => 'active',
        ]);

        $this->femaleBlock = DormitoryBlock::create([
            'name' => 'Block 50',
            'block_code' => 'BLK-50',
            'gender_category' => 'female',
            'status' => 'active',
        ]);

        $this->maleRoom = Room::create([
            'block_id' => $this->maleBlock->id,
            'room_number' => '101',
            'capacity' => 4,
            'status' => 'available',
        ]);

        $this->femaleRoom = Room::create([
            'block_id' => $this->femaleBlock->id,
            'room_number' => '201',
            'capacity' => 4,
            'status' => 'available',
        ]);
    }

    public function test_admin_can_import_single_user_by_id_from_csv(): void
    {
        $this->actingAs($this->admin);

        $csvContent = "name,university_id,gender,department,academic_year,role\n" .
                      "Yared Tesfaye,3001,male,Computer Science,2,student\n" .
                      "Sara Daniel,3002,female,Medicine,3,student\n" .
                      "Nahom Kifle,3003,male,Mechanical Engineering,1,student\n";

        $file = UploadedFile::fake()->createWithContent('students.csv', $csvContent);

        // Import only single user 3002
        $response = $this->post(route('admin.users.import'), [
            'file' => $file,
            'single_id' => '3002',
        ]);

        $response->assertSessionHas('success');

        // Only Sara Daniel (3002) should be imported
        $this->assertDatabaseHas('users', [
            'university_id' => '3002',
            'name' => 'Sara Daniel',
            'gender' => 'female',
            'department' => 'Medicine',
            'academic_year' => 3,
        ]);

        $this->assertDatabaseMissing('users', ['university_id' => '3001']);
        $this->assertDatabaseMissing('users', ['university_id' => '3003']);
    }

    public function test_admin_can_import_single_user_by_id_entered_in_id_range_field(): void
    {
        $this->actingAs($this->admin);

        $csvContent = "name,university_id,gender,department,academic_year,role\n" .
                      "Kebede Haile,WLD/5001,male,Information Technology,1,student\n" .
                      "Aster Bekele,WLD/5002,female,Economics,2,student\n";

        $file = UploadedFile::fake()->createWithContent('students.csv', $csvContent);

        // Enter single ID in id_range without hyphen
        $response = $this->post(route('admin.users.import'), [
            'file' => $file,
            'id_range' => 'WLD/5001',
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('users', ['university_id' => 'WLD/5001', 'name' => 'Kebede Haile']);
        $this->assertDatabaseMissing('users', ['university_id' => 'WLD/5002']);
    }

    public function test_import_single_id_not_found_returns_clear_error(): void
    {
        $this->actingAs($this->admin);

        $csvContent = "name,university_id,gender,department,academic_year,role\n" .
                      "Kebede Haile,WLD/5001,male,Information Technology,1,student\n";

        $file = UploadedFile::fake()->createWithContent('students.csv', $csvContent);

        $response = $this->post(route('admin.users.import'), [
            'file' => $file,
            'single_id' => 'NON_EXISTENT_999',
        ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('users', ['university_id' => 'NON_EXISTENT_999']);
    }

    public function test_proctor_manager_can_assign_proctor_with_departments_and_years(): void
    {
        $this->actingAs($this->manager);

        $response = $this->post(route('manager.assignments.store'), [
            'block_id' => $this->maleBlock->id,
            'proctor_id' => $this->maleProctor->id,
            'assigned_departments' => ['Computer Science', 'Software Engineering', 'Information Technology'],
            'assigned_years' => [1, 2, 3],
        ]);

        $response->assertSessionHas('success');

        $assignment = BlockProctorAssignment::where('block_id', $this->maleBlock->id)
            ->where('proctor_id', $this->maleProctor->id)
            ->first();

        $this->assertNotNull($assignment);
        $this->assertEquals(['Computer Science', 'Software Engineering', 'Information Technology'], $assignment->assigned_departments_list);
        $this->assertEquals([1, 2, 3], $assignment->assigned_years_list);
    }

    public function test_proctor_manager_cannot_exceed_10_departments_or_5_batches(): void
    {
        $this->actingAs($this->manager);

        // Try assigning 11 departments
        $tooManyDepts = [
            'Dept 1', 'Dept 2', 'Dept 3', 'Dept 4', 'Dept 5',
            'Dept 6', 'Dept 7', 'Dept 8', 'Dept 9', 'Dept 10', 'Dept 11'
        ];

        $response = $this->post(route('manager.assignments.store'), [
            'block_id' => $this->maleBlock->id,
            'proctor_id' => $this->maleProctor->id,
            'assigned_departments' => $tooManyDepts,
            'assigned_years' => [1, 2],
        ]);

        $response->assertSessionHasErrors('assigned_departments');

        // Try assigning 6 batches
        $response2 = $this->post(route('manager.assignments.store'), [
            'block_id' => $this->maleBlock->id,
            'proctor_id' => $this->maleProctor->id,
            'assigned_departments' => ['Computer Science'],
            'assigned_years' => [1, 2, 3, 4, 5, 6],
        ]);

        $response2->assertSessionHasErrors('assigned_years');
    }

    public function test_duplicate_departments_and_batches_can_be_assigned_to_male_and_female_proctors(): void
    {
        $this->actingAs($this->manager);

        // Assign Male Proctor to Male Block with Computer Science Year 2
        $resp1 = $this->post(route('manager.assignments.store'), [
            'block_id' => $this->maleBlock->id,
            'proctor_id' => $this->maleProctor->id,
            'assigned_departments' => ['Computer Science'],
            'assigned_years' => [2],
        ]);
        $resp1->assertSessionHas('success');

        // Assign Female Proctor to Female Block with the SAME department & year (Computer Science Year 2)
        $resp2 = $this->post(route('manager.assignments.store'), [
            'block_id' => $this->femaleBlock->id,
            'proctor_id' => $this->femaleProctor->id,
            'assigned_departments' => ['Computer Science'],
            'assigned_years' => [2],
        ]);
        $resp2->assertSessionHas('success');

        $this->assertEquals(2, BlockProctorAssignment::whereNull('ended_at')->count());
    }

    public function test_proctor_only_views_and_allocates_students_matching_their_gender_departments_and_batches(): void
    {
        // Setup assignments
        BlockProctorAssignment::create([
            'block_id' => $this->maleBlock->id,
            'proctor_id' => $this->maleProctor->id,
            'assigned_departments' => ['Computer Science'],
            'assigned_years' => [2],
            'assigned_by' => $this->manager->id,
            'assigned_at' => now(),
        ]);

        // Create eligible students:
        // 1. Male, Computer Science, Year 2 -> Matches male proctor
        $maleCsStudent = User::factory()->create([
            'name' => 'Henok Male CS',
            'role' => User::ROLE_STUDENT,
            'gender' => 'male',
            'department' => 'Computer Science',
            'academic_year' => 2,
            'registration_status' => User::REGISTRATION_COMPLETED,
        ]);

        // 2. Female, Computer Science, Year 2 -> Should NOT be visible/allocatable to male proctor
        $femaleCsStudent = User::factory()->create([
            'name' => 'Hanna Female CS',
            'role' => User::ROLE_STUDENT,
            'gender' => 'female',
            'department' => 'Computer Science',
            'academic_year' => 2,
            'registration_status' => User::REGISTRATION_COMPLETED,
        ]);

        // 3. Male, Medicine, Year 2 -> Outside male proctor's assigned department
        $maleMedStudent = User::factory()->create([
            'name' => 'Dawit Male Med',
            'role' => User::ROLE_STUDENT,
            'gender' => 'male',
            'department' => 'Medicine',
            'academic_year' => 2,
            'registration_status' => User::REGISTRATION_COMPLETED,
        ]);

        // 4. Male, Computer Science, Year 4 -> Outside male proctor's assigned batch (Year 2)
        $maleCsYear4Student = User::factory()->create([
            'name' => 'Biniyam Male CS Year 4',
            'role' => User::ROLE_STUDENT,
            'gender' => 'male',
            'department' => 'Computer Science',
            'academic_year' => 4,
            'registration_status' => User::REGISTRATION_COMPLETED,
        ]);

        // Act as Male Proctor
        $this->actingAs($this->maleProctor);

        // 1. View allocate form
        $response = $this->get(route('proctor.allocate.index'));
        $response->assertStatus(200);
        $response->assertSee('Henok Male CS');
        $response->assertDontSee('Hanna Female CS');
        $response->assertDontSee('Dawit Male Med');
        $response->assertDontSee('Biniyam Male CS Year 4');

        // 2. View student directory (/proctor/students)
        $dirResponse = $this->get(route('proctor.students.index'));
        $dirResponse->assertStatus(200);
        $dirResponse->assertSee('Henok Male CS');
        $dirResponse->assertDontSee('Hanna Female CS');
        $dirResponse->assertDontSee('Dawit Male Med');
        $dirResponse->assertDontSee('Biniyam Male CS Year 4');

        // 3. Allocate the matching male student -> SUCCESS
        $allocResponse = $this->post(route('proctor.allocate.store'), [
            'student_id' => $maleCsStudent->id,
            'room_id' => $this->maleRoom->id,
        ]);
        $allocResponse->assertSessionHas('success');
        $this->assertDatabaseHas('student_allocations', [
            'student_id' => $maleCsStudent->id,
            'room_id' => $this->maleRoom->id,
            'status' => 'active',
        ]);

        // 4. Try allocating student outside department/batch/gender scope -> FAILS
        $service = app(RoomAllocationService::class);

        $this->expectException(\Illuminate\Validation\ValidationException::class);
        $service->allocate($maleMedStudent, $this->maleRoom, $this->maleProctor);
    }

    public function test_department_catalog_has_no_duplicates_and_normalizes_loose_entries(): void
    {
        $all = \App\Services\DepartmentCatalog::all();
        $this->assertEquals(count($all), count(array_unique($all)));

        // Test normalizations
        $this->assertEquals('Mathematics', \App\Services\DepartmentCatalog::normalize('mathematics'));
        $this->assertEquals('Accounting & Finance', \App\Services\DepartmentCatalog::normalize('accounting'));
        $this->assertEquals('Computer Science', \App\Services\DepartmentCatalog::normalize('computer science'));
        $this->assertEquals('Journalism & Communication', \App\Services\DepartmentCatalog::normalize('jornal'));
        $this->assertEquals('Electrical & Computer Engineering', \App\Services\DepartmentCatalog::normalize('electrical engineering'));
    }

    public function test_pending_status_students_are_fetched_and_allocatable_case_insensitively(): void
    {
        // Proctor assigned "Mathematics"
        BlockProctorAssignment::create([
            'block_id' => $this->maleBlock->id,
            'proctor_id' => $this->maleProctor->id,
            'assigned_departments' => ['Mathematics'],
            'assigned_years' => [1],
            'assigned_by' => $this->manager->id,
            'assigned_at' => now(),
        ]);

        // Student with lowercase 'mathematics' and 'pending' registration status
        $ahmed = User::factory()->create([
            'name' => 'Ahmed Test',
            'university_id' => '3099',
            'role' => User::ROLE_STUDENT,
            'gender' => 'male',
            'department' => 'mathematics',
            'academic_year' => 1,
            'registration_status' => User::REGISTRATION_PENDING,
            'is_active' => true,
        ]);

        $this->actingAs($this->maleProctor);

        // 1. Visible in allocateForm
        $allocFormResp = $this->get(route('proctor.allocate.index'));
        $allocFormResp->assertStatus(200);
        $allocFormResp->assertSee('Ahmed Test');

        // 2. Visible in students directory
        $dirResp = $this->get(route('proctor.students.index'));
        $dirResp->assertStatus(200);
        $dirResp->assertSee('Ahmed Test');

        // 3. Allocatable by proctor
        $postResp = $this->post(route('proctor.allocate.store'), [
            'student_id' => $ahmed->id,
            'room_id' => $this->maleRoom->id,
        ]);
        $postResp->assertSessionHas('success');
    }
}
