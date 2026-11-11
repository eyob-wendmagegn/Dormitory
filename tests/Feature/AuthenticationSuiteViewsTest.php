<?php

namespace Tests\Feature;

use App\Mail\VerificationCodeMail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AuthenticationSuiteViewsTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_view_renders_split_screen_and_expected_placeholders(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('Sign In');
        $response->assertSee('placeholder="Enter your email"', false);
        $response->assertSee('placeholder="Enter your password"', false);
        $response->assertSee('fixed top-4 right-4 sm:top-6 sm:right-6', false);
        $response->assertSee('Woldia University');
        $response->assertSee('Dormitory System');
        $response->assertSee('bg-emerald-500', false);
    }

    public function test_register_view_renders_split_screen_and_expected_placeholders(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
        $response->assertSee('Activate Account');
        $response->assertSee('placeholder="Enter full name"', false);
        $response->assertSee('placeholder="Enter universtiy ID"', false);
        $response->assertSee('placeholder="Enter your valid email"', false);
        $response->assertSee('placeholder="Enter your password (min. 8 characters)"', false);
        $response->assertSee('placeholder="Re-enter your password"', false);
    }

    public function test_verify_code_view_renders_and_verifies_otp(): void
    {
        $response = $this->get('/verify-code');

        $response->assertStatus(200);
        $response->assertSee('Enter Verification Code');
        $response->assertSee('placeholder="000000"', false);
        $response->assertSee('Resend 6-digit code');

        // Test resending OTP
        $resendResponse = $this->post('/verify-code/resend', [
            'email' => 'test.resident@woldia.edu.et',
        ]);
        $resendResponse->assertRedirect();
        $this->assertTrue(Cache::has('otp_test.resident@woldia.edu.et'));

        // Test verifying with valid code
        $verifyResponse = $this->post('/verify-code', [
            'email' => 'test.resident@woldia.edu.et',
            'code' => '000000',
        ]);
        $verifyResponse->assertRedirect('/login');
        $verifyResponse->assertSessionHas('status');
    }

    public function test_forgot_password_view_renders_and_has_required_placeholder(): void
    {
        $response = $this->get('/forgot-password');

        $response->assertStatus(200);
        $response->assertSee('Reset Password');
        $response->assertSee('placeholder="Enter your valid email"', false);
        $response->assertSee('Back to Login');
    }

    public function test_reset_password_view_renders_and_has_required_placeholders(): void
    {
        $response = $this->get('/reset-password/sample-test-token?email=resident%40woldia.edu.et');

        $response->assertStatus(200);
        $response->assertSee('Set New Password');
        $response->assertSee('placeholder="Enter your valid email"', false);
        $response->assertSee('placeholder="Enter your password (min. 8 characters)"', false);
        $response->assertSee('placeholder="Re-enter your password"', false);
        $response->assertSee('sample-test-token');
    }

    public function test_complete_registration_and_otp_verification_lifecycle(): void
    {
        $student = User::create([
            'name' => 'Kassahun Bekele',
            'university_id' => 'WLD/9900/17',
            'role' => User::ROLE_STUDENT,
            'registration_status' => User::REGISTRATION_PENDING,
        ]);

        // 1. Submit registration with require_verification flag
        $response = $this->post('/register', [
            'name' => 'Kassahun Bekele',
            'university_id' => 'WLD/9900/17',
            'email' => 'kassahun@woldia.edu.et',
            'password' => 'secretPassword123',
            'password_confirmation' => 'secretPassword123',
            'require_verification' => 1,
        ]);

        $response->assertRedirect('/verify-code');
        $response->assertSessionHas('verify_email', 'kassahun@woldia.edu.et');
        $response->assertSessionHas('demo_otp');

        // Account must remain pending until OTP verified!
        $student->refresh();
        $this->assertEquals(User::REGISTRATION_PENDING, $student->registration_status);

        // 2. Retrying registration should NOT throw 'email has already been taken'
        $retryResponse = $this->post('/register', [
            'name' => 'Kassahun Bekele',
            'university_id' => 'WLD/9900/17',
            'email' => 'kassahun@woldia.edu.et',
            'password' => 'secretPassword123',
            'password_confirmation' => 'secretPassword123',
            'require_verification' => 1,
        ]);
        $retryResponse->assertRedirect('/verify-code');

        $otp = session('demo_otp');
        $this->assertNotEmpty($otp);

        // 3. Verify OTP code
        $verifyResponse = $this->post('/verify-code', [
            'email' => 'kassahun@woldia.edu.et',
            'code' => $otp,
        ]);

        $verifyResponse->assertRedirect('/login');
        $verifyResponse->assertSessionHas('status');

        // Account is now completed & email verified
        $student->refresh();
        $this->assertEquals(User::REGISTRATION_COMPLETED, $student->registration_status);
        $this->assertEquals('kassahun@woldia.edu.et', $student->email);
        $this->assertNotNull($student->email_verified_at);
        $this->assertTrue(Hash::check('secretPassword123', $student->password));
    }

    public function test_verification_code_is_never_displayed_directly_on_verification_page(): void
    {
        $code = '789123';
        $response = $this->withSession([
            'verify_email' => 'resident@woldia.edu.et',
            'verify_otp' => $code,
            'demo_otp' => $code,
        ])->get('/verify-code');

        $response->assertStatus(200);
        // Ensure secret code is not printed directly on the page
        $response->assertDontSee('Verification Code:');
        $response->assertDontSee($code);
    }

    public function test_forgot_password_works_similarly_to_verification_with_dispatched_code(): void
    {
        $user = User::create([
            'name' => 'Abebe Chala',
            'university_id' => 'WLD/8877/17',
            'email' => 'abebe.chala@woldia.edu.et',
            'password' => Hash::make('oldPassword123'),
            'role' => User::ROLE_STUDENT,
            'registration_status' => User::REGISTRATION_COMPLETED,
            'email_verified_at' => now(),
        ]);

        // 1. Submit forgot password request
        $response = $this->post('/forgot-password', [
            'email' => 'abebe.chala@woldia.edu.et',
        ]);

        $response->assertRedirect('/reset-password');
        $this->assertTrue(Cache::has('password_reset_code_abebe.chala@woldia.edu.et'));
        $code = Cache::get('password_reset_code_abebe.chala@woldia.edu.et');
        $this->assertNotEmpty($code);

        // 2. View reset password page - ensure code is not displayed directly on screen
        $resetView = $this->withSession([
            'password_reset_email' => 'abebe.chala@woldia.edu.et',
            'password_reset_code' => $code,
        ])->get('/reset-password');

        $resetView->assertStatus(200);
        $resetView->assertSee('Set New Password');
        $resetView->assertSee('abebe.chala@woldia.edu.et');
        $resetView->assertDontSee($code); // Secret code must not be printed on screen!

        // 3. Submitting with wrong verification code fails
        $badReset = $this->post('/reset-password', [
            'email' => 'abebe.chala@woldia.edu.et',
            'code' => '999999',
            'password' => 'newSecretPassword456',
            'password_confirmation' => 'newSecretPassword456',
        ]);
        $badReset->assertSessionHasErrors('code');

        // 4. Submitting with valid 6-digit code resets password successfully
        $goodReset = $this->post('/reset-password', [
            'email' => 'abebe.chala@woldia.edu.et',
            'code' => $code,
            'password' => 'newSecretPassword456',
            'password_confirmation' => 'newSecretPassword456',
        ]);
        $goodReset->assertRedirect('/login');
        $goodReset->assertSessionHas('status');

        // 5. Verify user can log in with new password
        $user->refresh();
        $this->assertTrue(Hash::check('newSecretPassword456', $user->password));
    }

    public function test_verification_code_mailable_renders_html_and_text_with_priority_headers(): void
    {
        $mailable = new VerificationCodeMail('654321', 'activation', 'Eyob Test');
        $renderedHtml = $mailable->render();

        $this->assertStringContainsString('654321', $renderedHtml);
        $this->assertStringContainsString('Woldia University', $renderedHtml);
        $this->assertStringContainsString('Account Verification Code', $renderedHtml);

        $envelope = $mailable->envelope();
        $this->assertStringContainsString('654321', $envelope->subject);

        $headers = $mailable->headers();
        $this->assertEquals('1', $headers->text['X-Priority']);
        $this->assertEquals('Urgent', $headers->text['Priority']);
    }

    public function test_landing_and_auth_pages_include_dark_default_and_theme_toggle_button(): void
    {
        $urls = [
            '/',
            '/login',
            '/register',
            '/verify-code',
            '/forgot-password',
            '/reset-password/test-token',
        ];

        foreach ($urls as $url) {
            $response = $this->get($url);

            $response->assertStatus(200);
            $response->assertSee('class="dark scroll-smooth"', false);
            $response->assertSee("localStorage.getItem('theme') || 'dark'", false);
            $response->assertSee('window.toggleTheme = function ()', false);
            $response->assertSee('onclick="toggleTheme()"', false);
            $response->assertSee('theme-icon-sun', false);
            $response->assertSee('theme-icon-moon', false);

            if ($url !== '/') {
                $response->assertSee('fixed top-4 right-4 sm:top-6 sm:right-6', false);
            }
        }
    }
}
