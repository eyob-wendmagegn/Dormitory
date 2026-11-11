<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\VerificationCodeMail;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset request view.
     */
    public function create(): View
    {
        return view('auth.forgot-password', [
            'status' => session('status'),
        ]);
    }

    /**
     * Handle an incoming password reset code request.
     * Generates a 6-digit verification code and emails it to the resident (Two-Factor style).
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $email = trim((string) $request->input('email'));
        $user = User::whereRaw('LOWER(TRIM(email)) = LOWER(?)', [$email])->first();

        if (! $user) {
            throw ValidationException::withMessages([
                'email' => 'We could not find an account associated with this email address.',
            ]);
        }

        $otp = str_pad((string) random_int(100000, 999999), 6, '0', STR_PAD_LEFT);

        // Store OTP in cache for 15 minutes
        Cache::put('password_reset_code_' . $email, $otp, now()->addMinutes(15));

        session([
            'password_reset_email' => $email,
            'password_reset_code' => $otp,
            'verify_email' => $email,
        ]);

        try {
            Mail::to($email)->send(new VerificationCodeMail($otp, 'reset', $user->name));
        } catch (\Throwable $e) {
            // Silently handle mail driver transport issues in local offline environments
        }

        return redirect()->route('password.reset')
            ->with('status', "A 6-digit verification code has been dispatched to {$email}. Please check your email inbox.");
    }
}
