<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\AuditLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = Auth::user();

        AuditLog::log('user_login', "User {$user->name} logged in.", null, $user);

        // Redirect based on user role
        return match ($user->role) {
            'admin' => redirect()->intended(route('admin.dashboard')),
            'proctor_manager' => redirect()->intended(route('manager.dashboard')),
            'proctor' => redirect()->intended(route('proctor.dashboard')),
            'student' => redirect()->intended(route('student.dashboard')),
            default => redirect()->route('login'),
        };
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $user = Auth::user();
        if ($user) {
            AuditLog::log('user_logout', "User {$user->name} logged out.", null, $user);
        }

        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('success', 'You have been logged out securely.');
    }
}
