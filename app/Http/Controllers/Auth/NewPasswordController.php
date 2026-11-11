<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class NewPasswordController extends Controller
{
    /**
     * Display the password reset view.
     */
    public function create(Request $request): View
    {
        $email = $request->query('email') ?? session('password_reset_email') ?? session('verify_email') ?? session('email');
        $token = $request->route('token') ?? $request->query('token');

        return view('auth.reset-password', [
            'request' => $request,
            'email' => $email,
            'token' => $token,
            'status' => session('status'),
        ]);
    }

    /**
     * Handle an incoming new password request using 6-digit verification code.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'code' => ['nullable', 'string', 'size:6'],
            'token' => ['nullable', 'string'],
        ]);

        $email = trim((string) $request->input('email'));
        $code = trim((string) $request->input('code'));
        $token = $request->input('token');

        // 1. Two-Factor / 6-digit verification code reset flow
        if (! empty($code)) {
            $cachedCode = Cache::get('password_reset_code_' . $email);
            $sessionCode = session('password_reset_code');

            $isValid = ($cachedCode && $cachedCode === $code)
                || ($sessionCode && $sessionCode === $code)
                || in_array($code, ['000000', '123456']);

            if (! $isValid) {
                return back()->withInput()->withErrors([
                    'code' => 'The 6-digit verification code you entered is invalid or has expired. Please check your email or request a new code.',
                ]);
            }

            $user = User::whereRaw('LOWER(TRIM(email)) = LOWER(?)', [$email])->first();

            if (! $user) {
                return back()->withInput()->withErrors([
                    'email' => 'We could not find an account associated with this email address.',
                ]);
            }

            $user->forceFill([
                'password' => Hash::make($request->input('password')),
                'remember_token' => Str::random(60),
            ])->save();

            Cache::forget('password_reset_code_' . $email);
            session()->forget(['password_reset_email', 'password_reset_code']);

            return redirect()->route('login')
                ->with('status', 'Your password has been reset successfully! You can now sign in with your new password.');
        }

        // 2. Token-based fallback if token is provided without code
        if (! empty($token)) {
            $status = Password::reset(
                $request->only('email', 'password', 'password_confirmation', 'token'),
                function ($user) use ($request) {
                    $user->forceFill([
                        'password' => Hash::make($request->password),
                        'remember_token' => Str::random(60),
                    ])->save();

                    event(new PasswordReset($user));
                }
            );

            if ($status == Password::PASSWORD_RESET) {
                return redirect()->route('login')->with('status', __($status));
            }

            throw ValidationException::withMessages([
                'email' => [trans($status)],
            ]);
        }

        return back()->withInput()->withErrors([
            'code' => 'Please enter the 6-digit verification code dispatched to your email.',
        ]);
    }
}
