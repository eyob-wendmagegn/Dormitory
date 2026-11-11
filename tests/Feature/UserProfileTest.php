<?php

namespace Tests\Feature;

use App\Models\DormitoryBlock;
use App\Models\Room;
use App\Models\StudentAllocation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UserProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_profile_page(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_PROCTOR_MANAGER,
            'gender' => 'male',
        ]);

        $response = $this->actingAs($user)->get('/profile');

        $response->assertOk();
        $response->assertSee($user->name);
        $response->assertSee($user->university_id);
        $response->assertSee('Proctor Manager');
        $response->assertSee('Male');
    }

    public function test_user_can_update_profile_information(): void
    {
        $user = User::factory()->create(['name' => 'Old Name', 'email' => 'old@woldia.edu.et']);

        $response = $this->actingAs($user)->patch('/profile', [
            'name' => 'New Name',
            'email' => 'new@woldia.edu.et',
            'gender' => 'female',
        ]);

        $response->assertRedirect('/profile');
        $response->assertSessionHas('success');

        $user->refresh();
        $this->assertEquals('New Name', $user->name);
        $this->assertEquals('new@woldia.edu.et', $user->email);
        $this->assertEquals('female', $user->gender);
    }

    public function test_user_can_change_password_with_valid_current_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('current-password-123'),
        ]);

        $response = $this->actingAs($user)->put('/profile/password', [
            'current_password' => 'current-password-123',
            'password' => 'new-secret-password-456',
            'password_confirmation' => 'new-secret-password-456',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $user->refresh();
        $this->assertTrue(Hash::check('new-secret-password-456', $user->password));
    }

    public function test_user_cannot_change_password_with_invalid_current_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('current-password-123'),
        ]);

        $response = $this->actingAs($user)->put('/profile/password', [
            'current_password' => 'wrong-current-password',
            'password' => 'new-secret-password-456',
            'password_confirmation' => 'new-secret-password-456',
        ]);

        $response->assertSessionHasErrors('current_password');
    }

    public function test_user_can_upload_and_remove_profile_avatar(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();

        // Create image payload without requiring GD library
        $file = UploadedFile::fake()->create('avatar.jpg', 120, 'image/jpeg');

        $response = $this->actingAs($user)->post('/profile/avatar', [
            'avatar' => $file,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $user->refresh();
        $this->assertNotNull($user->profile_photo_path);
        Storage::disk('public')->assertExists($user->profile_photo_path);

        // Test removing avatar
        $removeResponse = $this->actingAs($user)->delete('/profile/avatar');
        $removeResponse->assertRedirect();
        $removeResponse->assertSessionHas('success');

        $user->refresh();
        $this->assertNull($user->profile_photo_path);
    }

    public function test_user_can_upload_avatar_up_to_3mb_and_rejects_over_3mb(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();

        // 1. File <= 3MB (e.g. 2900 KB) should succeed
        $validFile = UploadedFile::fake()->create('valid-3mb.jpg', 2900, 'image/jpeg');
        $responseValid = $this->actingAs($user)->post('/profile/avatar', [
            'avatar' => $validFile,
        ]);
        $responseValid->assertRedirect();
        $responseValid->assertSessionHas('success');

        // 2. File > 3MB (e.g. 3500 KB) should fail validation
        $tooLargeFile = UploadedFile::fake()->create('large.jpg', 3500, 'image/jpeg');
        $responseTooLarge = $this->actingAs($user)->post('/profile/avatar', [
            'avatar' => $tooLargeFile,
        ]);
        $responseTooLarge->assertSessionHasErrors('avatar');
    }

    public function test_header_has_no_duplicate_logo_and_only_sidebar_renders_logo(): void
    {
        $manager = User::factory()->create([
            'role' => User::ROLE_PROCTOR_MANAGER,
            'gender' => 'male',
            'registration_status' => User::REGISTRATION_COMPLETED,
        ]);

        $response = $this->actingAs($manager)->get(route('manager.dashboard'));
        $response->assertOk();

        $html = $response->getContent();

        // Header must NOT contain images/logo.jpg
        preg_match('/<header[\s\S]*?<\/header>/', $html, $headerMatches);
        $this->assertNotEmpty($headerMatches);
        $this->assertStringNotContainsString('images/logo.jpg', $headerMatches[0], 'Header must not contain duplicate logo image.');

        // Sidebar must contain the official logo
        preg_match('/<aside[\s\S]*?<\/aside>/', $html, $sidebarMatches);
        $this->assertNotEmpty($sidebarMatches);
        $this->assertStringContainsString('images/logo.jpg', $sidebarMatches[0], 'Sidebar must contain the official logo.');
    }

    public function test_gender_and_role_are_displayed_in_profile_dropdown_and_profile_page(): void
    {
        $manager = User::factory()->create([
            'name' => 'Supervisor Pro',
            'role' => User::ROLE_PROCTOR_MANAGER,
            'gender' => 'male',
            'registration_status' => User::REGISTRATION_COMPLETED,
        ]);

        $response = $this->actingAs($manager)->get(route('manager.dashboard'));
        $response->assertOk();
        $html = $response->getContent();

        preg_match('/<header[\s\S]*?<\/header>/', $html, $headerMatches);
        $headerHtml = $headerMatches[0] ?? '';

        // Dropdown inside header contains role and gender
        $this->assertStringContainsString('Proctor Manager', $headerHtml);
        $this->assertStringContainsString('male', $headerHtml);

        // Profile edit page displays role and gender clearly
        $profileResponse = $this->actingAs($manager)->get(route('profile.edit'));
        $profileResponse->assertOk();
        $profileResponse->assertSee('Proctor Manager');
        $profileResponse->assertSee('Male');
    }

    public function test_student_with_active_allocation_cannot_alter_gender_mismatch(): void
    {
        $block = DormitoryBlock::create([
            'name' => 'Block Alpha',
            'block_code' => 'B-A',
            'gender_category' => 'male',
        ]);
        $room = Room::create([
            'block_id' => $block->id,
            'room_number' => '101',
            'capacity' => 4,
            'status' => 'available',
        ]);
        $student = User::factory()->create([
            'role' => User::ROLE_STUDENT,
            'gender' => 'male',
            'registration_status' => User::REGISTRATION_COMPLETED,
        ]);
        StudentAllocation::create([
            'student_id' => $student->id,
            'room_id' => $room->id,
            'status' => 'active',
            'allocated_at' => now(),
        ]);

        $response = $this->actingAs($student)->patch(route('profile.update'), [
            'name' => 'Updated Student',
            'email' => $student->email,
            'gender' => 'female',
        ]);

        $response->assertSessionHasErrors('gender');
    }
}
