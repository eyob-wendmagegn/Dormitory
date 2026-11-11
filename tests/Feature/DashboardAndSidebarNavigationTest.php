<?php

namespace Tests\Feature;

use App\Models\BlockProctorAssignment;
use App\Models\DormitoryBlock;
use App\Models\Room;
use App\Models\StudentAllocation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardAndSidebarNavigationTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $manager;
    protected User $proctor;
    protected User $student;
    protected DormitoryBlock $block;
    protected Room $room;

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

        $this->proctor = User::factory()->create([
            'role' => User::ROLE_PROCTOR,
            'registration_status' => User::REGISTRATION_COMPLETED,
        ]);

        $this->student = User::factory()->create([
            'role' => User::ROLE_STUDENT,
            'gender' => 'male',
            'academic_year' => 2,
            'registration_status' => User::REGISTRATION_COMPLETED,
        ]);

        $this->block = DormitoryBlock::create([
            'name' => 'Fasiledes Block',
            'block_code' => 'B-01',
            'gender_category' => 'male',
            'capacity_rooms' => 10,
            'status' => 'active',
        ]);

        $this->room = Room::create([
            'block_id' => $this->block->id,
            'room_number' => '101',
            'capacity' => 4,
            'floor' => 1,
            'status' => 'available',
        ]);

        BlockProctorAssignment::create([
            'block_id' => $this->block->id,
            'proctor_id' => $this->proctor->id,
            'assigned_by' => $this->manager->id,
            'is_active' => true,
            'assigned_at' => now(),
        ]);
    }

    public function test_admin_sidebar_and_views_render_successfully(): void
    {
        $this->actingAs($this->admin);

        $routes = [
            'admin.dashboard',
            'admin.users.index',
            'admin.preregister.index',
            'admin.students.index',
            'admin.blocks.index',
            'admin.rooms.index',
            'admin.allocations.index',
            'admin.comments.index',
            'admin.reports.index',
            'admin.backups.index',
        ];

        foreach ($routes as $route) {
            $response = $this->get(route($route));
            $response->assertStatus(200);
        }
    }

    public function test_proctor_manager_sidebar_and_views_render_successfully(): void
    {
        $this->actingAs($this->manager);

        $routes = [
            'manager.dashboard',
            'manager.blocks.index',
            'manager.assignments.index',
            'manager.students.index',
            'manager.comments.index',
            'manager.reports.index',
        ];

        foreach ($routes as $route) {
            $response = $this->get(route($route));
            $response->assertStatus(200);
        }
    }

    public function test_proctor_sidebar_and_views_render_successfully(): void
    {
        $this->actingAs($this->proctor);

        $routes = [
            'proctor.dashboard',
            'proctor.blocks.index',
            'proctor.rooms.index',
            'proctor.allocate.index',
            'proctor.allocated.index',
            'proctor.students.index',
            'proctor.comments.index',
            'proctor.reports.index',
        ];

        foreach ($routes as $route) {
            $response = $this->get(route($route));
            $response->assertStatus(200);
        }
    }

    public function test_student_sidebar_and_views_render_successfully(): void
    {
        $this->actingAs($this->student);

        $routes = [
            'student.dashboard',
            'student.dorm.index',
            'student.room.index',
            'student.comments.create',
            'student.comments.index',
        ];

        foreach ($routes as $route) {
            $response = $this->get(route($route));
            $response->assertStatus(200);
        }
    }

    public function test_student_can_submit_feedback_via_form(): void
    {
        $this->actingAs($this->student);

        $response = $this->post(route('student.comments.store'), [
            'category' => 'maintenance',
            'title' => 'Broken window handle in Room 101',
            'message' => 'The window handle fell off and the latch cannot close properly.',
        ]);

        $response->assertRedirect(route('student.comments.index'));
        $this->assertDatabaseHas('comments', [
            'user_id' => $this->student->id,
            'title' => 'Broken window handle in Room 101',
            'category' => 'maintenance',
        ]);
    }

    public function test_proctor_can_release_allocated_student(): void
    {
        $this->actingAs($this->proctor);

        $allocation = StudentAllocation::create([
            'student_id' => $this->student->id,
            'room_id' => $this->room->id,
            'allocated_by' => $this->proctor->id,
            'status' => StudentAllocation::STATUS_ACTIVE,
            'allocated_at' => now(),
        ]);

        $response = $this->post(route('proctor.allocated.release', $allocation));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('student_allocations', [
            'id' => $allocation->id,
            'status' => StudentAllocation::STATUS_RELEASED,
        ]);
    }

    public function test_proctor_can_submit_comment(): void
    {
        $this->actingAs($this->proctor);

        $response = $this->post(route('proctor.comments.store'), [
            'category' => 'maintenance',
            'title' => 'Hallway light replacement on Block 1',
            'message' => 'Multiple bulbs need urgent replacement in north corridor.',
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('comments', [
            'user_id' => $this->proctor->id,
            'title' => 'Hallway light replacement on Block 1',
            'category' => 'maintenance',
        ]);
    }

    public function test_proctor_can_reply_to_student_comment(): void
    {
        $this->actingAs($this->student);
        $comment = \App\Models\Comment::create([
            'user_id' => $this->student->id,
            'category' => 'maintenance',
            'title' => 'Leaking faucet in bathroom',
            'message' => 'Water is dripping constantly.',
            'status' => \App\Models\Comment::STATUS_PENDING,
        ]);

        $this->actingAs($this->proctor);
        $response = $this->post(route('proctor.comments.reply', $comment), [
            'status' => 'in_progress',
            'response' => 'Maintenance team scheduled for today at 2 PM.',
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
            'status' => 'in_progress',
            'response' => 'Maintenance team scheduled for today at 2 PM.',
            'responded_by' => $this->proctor->id,
        ]);
    }

    public function test_header_includes_theme_toggle_and_dark_default_script(): void
    {
        foreach ([$this->admin, $this->student] as $user) {
            $route = $user->role === User::ROLE_ADMIN ? 'admin.dashboard' : 'student.dashboard';
            $response = $this->actingAs($user)->get(route($route));

            $response->assertStatus(200);
            $response->assertSee('class="dark"', false);
            $response->assertSee("localStorage.getItem('theme') || 'dark'", false);
            $response->assertSee('window.toggleTheme = function ()', false);
            $response->assertSee('onclick="toggleTheme()"', false);
            $response->assertSee('theme-icon-sun', false);
            $response->assertSee('theme-icon-moon', false);
        }
    }

    public function test_sidebar_pages_and_landing_use_compact_layout_and_animation_shell(): void
    {
        $landing = $this->get(route('landing'));
        $landing->assertStatus(200);
        $landing->assertSee('id="about"', false);
        $landing->assertSee('id="features"', false);
        $landing->assertSee('id="how-it-works"', false);
        $landing->assertSee('id="contact"', false);

        $roleSamples = [
            [$this->admin, 'admin.dashboard'],
            [$this->admin, 'admin.blocks.index'],
            [$this->admin, 'admin.backups.index'],
            [$this->admin, 'profile.edit'],
            [$this->manager, 'manager.dashboard'],
            [$this->manager, 'manager.assets.index'],
            [$this->proctor, 'proctor.dashboard'],
            [$this->proctor, 'proctor.attendance.index'],
            [$this->proctor, 'proctor.blocks.index'],
            [$this->student, 'student.dashboard'],
            [$this->student, 'student.dorm.index'],
            [$this->student, 'student.comments.create'],
        ];

        foreach ($roleSamples as [$user, $route]) {
            $response = $this->actingAs($user)->get(route($route));

            $response->assertStatus(200);
            $response->assertSee('id="main-content"', false);
            $response->assertSee('class="app-main flex-1 px-3 sm:px-5 pt-2.5 sm:pt-3 pb-4 w-full overflow-x-clip"', false);
            $response->assertDontSee('class="flex-1 p-4 sm:p-6 lg:p-8 max-w-7xl w-full mx-auto"', false);
        }

        $adminDash = $this->actingAs($this->admin)->get(route('admin.dashboard'));
        $adminDash->assertDontSee('space-y-8 animate-slow-fade', false);
    }

    public function test_three_dot_sort_dropdowns_teleport_to_body_above_cards_and_tables(): void
    {
        $pagesWithElevatedBars = [
            [$this->admin, 'admin.blocks.index'],
            [$this->manager, 'manager.blocks.index'],
            [$this->proctor, 'proctor.blocks.index'],
            [$this->proctor, 'proctor.rooms.index'],
        ];

        foreach ($pagesWithElevatedBars as [$user, $route]) {
            $response = $this->actingAs($user)->get(route($route));
            $response->assertStatus(200);
            $response->assertSee('relative z-20', false);
            $response->assertSee('x-teleport="body"', false);
            $response->assertSee('zIndex: 45', false);
            $response->assertSee('x-ref="trigger"', false);
        }

        $tableSortPages = [
            [$this->admin, 'admin.rooms.index'],
            [$this->admin, 'admin.users.index'],
        ];

        foreach ($tableSortPages as [$user, $route]) {
            $response = $this->actingAs($user)->get(route($route));
            $response->assertStatus(200);
            $response->assertSee('x-teleport="body"', false);
            $response->assertSee('zIndex: 45', false);
        }
    }
}
