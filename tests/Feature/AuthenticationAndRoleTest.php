<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationAndRoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_administrator_seeder_creates_expected_initial_admin(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertDatabaseHas('users', [
            'name' => 'Eyob',
            'university_id' => '1000',
            'email' => 'eyobnigatu18@gmail.com',
            'role' => User::ROLE_ADMIN,
            'registration_status' => User::REGISTRATION_COMPLETED,
        ]);

        $admin = User::where('email', 'eyobnigatu18@gmail.com')->first();
        $this->assertTrue(Hash::check('eyoba123', $admin->password));
    }

    public function test_unauthenticated_visitors_cannot_access_dashboards(): void
    {
        $this->get('/admin/dashboard')->assertRedirect('/login');
        $this->get('/proctor-manager/dashboard')->assertRedirect('/login');
        $this->get('/proctor/dashboard')->assertRedirect('/login');
        $this->get('/student/dashboard')->assertRedirect('/login');
    }

    public function test_users_can_log_in_with_valid_credentials(): void
    {
        $user = User::create([
            'name' => 'Valid User',
            'university_id' => 'V-100',
            'email' => 'valid@woldia.edu.et',
            'password' => Hash::make('password123'),
            'role' => User::ROLE_STUDENT,
            'registration_status' => User::REGISTRATION_COMPLETED,
        ]);

        $response = $this->post('/login', [
            'email' => 'valid@woldia.edu.et',
            'password' => 'password123',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect('/student/dashboard');
    }

    public function test_invalid_credentials_are_rejected(): void
    {
        User::create([
            'name' => 'Valid User',
            'university_id' => 'V-100',
            'email' => 'valid@woldia.edu.et',
            'password' => Hash::make('password123'),
            'role' => User::ROLE_STUDENT,
            'registration_status' => User::REGISTRATION_COMPLETED,
        ]);

        $response = $this->post('/login', [
            'email' => 'valid@woldia.edu.et',
            'password' => 'wrongpassword',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }

    public function test_pending_unregistered_users_cannot_log_in(): void
    {
        User::create([
            'name' => 'Pending User',
            'university_id' => 'P-100',
            'email' => 'pending@woldia.edu.et',
            'password' => Hash::make('password123'),
            'role' => User::ROLE_STUDENT,
            'registration_status' => User::REGISTRATION_PENDING,
        ]);

        $response = $this->post('/login', [
            'email' => 'pending@woldia.edu.et',
            'password' => 'password123',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }

    public function test_users_are_redirected_to_correct_role_dashboard(): void
    {
        $admin = User::create([
            'name' => 'Admin User',
            'university_id' => 'A-1',
            'email' => 'admin@woldia.edu.et',
            'password' => Hash::make('password'),
            'role' => User::ROLE_ADMIN,
            'registration_status' => User::REGISTRATION_COMPLETED,
        ]);

        $response = $this->post('/login', [
            'email' => 'admin@woldia.edu.et',
            'password' => 'password',
        ]);

        $response->assertRedirect('/admin/dashboard');
    }

    public function test_unauthorized_cross_role_access_is_denied(): void
    {
        $student = User::create([
            'name' => 'Student User',
            'university_id' => 'S-1',
            'email' => 'student@woldia.edu.et',
            'password' => Hash::make('password'),
            'role' => User::ROLE_STUDENT,
            'registration_status' => User::REGISTRATION_COMPLETED,
        ]);

        // Student attempting to access Admin dashboard should be denied and redirected to their student dashboard
        $response = $this->actingAs($student)->get('/admin/dashboard');
        $response->assertRedirect('/student/dashboard');
        $response->assertSessionHas('error');
    }

    public function test_logout_invalidates_authenticated_session(): void
    {
        $student = User::create([
            'name' => 'Student User',
            'university_id' => 'S-1',
            'email' => 'student@woldia.edu.et',
            'password' => Hash::make('password'),
            'role' => User::ROLE_STUDENT,
            'registration_status' => User::REGISTRATION_COMPLETED,
        ]);

        $response = $this->actingAs($student)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
    }
}
