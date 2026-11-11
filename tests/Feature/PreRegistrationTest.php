<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PreRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_pre_registered_user_can_complete_registration_and_retains_assigned_role(): void
    {
        // Admin pre-registers a proctor
        $user = User::create([
            'name' => 'Sara Daniel',
            'university_id' => 'EMP/4040',
            'role' => User::ROLE_PROCTOR,
            'registration_status' => User::REGISTRATION_PENDING,
        ]);

        $response = $this->post('/register', [
            'name' => 'Sara Daniel',
            'university_id' => 'EMP/4040',
            'email' => 'sara.daniel@woldia.edu.et',
            'password' => 'secret1234',
            'password_confirmation' => 'secret1234',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHas('success');

        $user->refresh();
        $this->assertEquals(User::REGISTRATION_COMPLETED, $user->registration_status);
        $this->assertEquals('sara.daniel@woldia.edu.et', $user->email);
        $this->assertTrue(Hash::check('secret1234', $user->password));
        // Verify that the server preserved the role assigned by administrator
        $this->assertEquals(User::ROLE_PROCTOR, $user->role);
    }

    public function test_unregistered_user_cannot_complete_registration(): void
    {
        $response = $this->post('/register', [
            'name' => 'Stranger Person',
            'university_id' => 'UNKNOWN/9999',
            'email' => 'stranger@woldia.edu.et',
            'password' => 'secret1234',
            'password_confirmation' => 'secret1234',
        ]);

        $response->assertSessionHasErrors(['university_id']);
        $this->assertDatabaseMissing('users', ['email' => 'stranger@woldia.edu.et']);
    }

    public function test_name_mismatch_with_university_id_is_rejected(): void
    {
        User::create([
            'name' => 'Real Student',
            'university_id' => 'UGR/5555/15',
            'role' => User::ROLE_STUDENT,
            'academic_year' => 2,
            'registration_status' => User::REGISTRATION_PENDING,
        ]);

        $response = $this->post('/register', [
            'name' => 'Wrong Name',
            'university_id' => 'UGR/5555/15',
            'email' => 'student@woldia.edu.et',
            'password' => 'secret1234',
            'password_confirmation' => 'secret1234',
        ]);

        $response->assertSessionHasErrors(['university_id']);
    }

    public function test_user_cannot_register_twice(): void
    {
        User::create([
            'name' => 'Almaz Bekele',
            'university_id' => 'UGR/8888/15',
            'email' => 'almaz@woldia.edu.et',
            'password' => Hash::make('alreadyactive'),
            'role' => User::ROLE_STUDENT,
            'registration_status' => User::REGISTRATION_COMPLETED,
        ]);

        $response = $this->post('/register', [
            'name' => 'Almaz Bekele',
            'university_id' => 'UGR/8888/15',
            'email' => 'newemail@woldia.edu.et',
            'password' => 'newsecret123',
            'password_confirmation' => 'newsecret123',
        ]);

        $response->assertSessionHasErrors(['university_id']);
    }

    public function test_duplicate_email_is_rejected(): void
    {
        User::create([
            'name' => 'Existing User',
            'university_id' => 'UGR/1111/15',
            'email' => 'duplicate@woldia.edu.et',
            'password' => Hash::make('password'),
            'role' => User::ROLE_STUDENT,
            'registration_status' => User::REGISTRATION_COMPLETED,
        ]);

        User::create([
            'name' => 'New User',
            'university_id' => 'UGR/2222/15',
            'role' => User::ROLE_STUDENT,
            'registration_status' => User::REGISTRATION_PENDING,
        ]);

        $response = $this->post('/register', [
            'name' => 'New User',
            'university_id' => 'UGR/2222/15',
            'email' => 'duplicate@woldia.edu.et',
            'password' => 'secret1234',
            'password_confirmation' => 'secret1234',
        ]);

        $response->assertSessionHasErrors(['email']);
    }
}
