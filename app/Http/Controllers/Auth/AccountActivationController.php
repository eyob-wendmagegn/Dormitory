<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class AccountActivationController extends Controller
{
    // Render Step 1: Verify Account Credentials
    public function showVerifyForm()
    {
        return Inertia::render('Auth/ActivateAccount');
    }

    // Step 1 Submission: Check User Existence
    public function verify(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'name' => 'required|string',
            'user_id_number' => 'required|string',
        ]);

        $user = User::where('email', $request->email)
            ->where('name', $request->name)
            ->where('user_id_number', $request->user_id_number)
            ->first();

        if (! $user) {
            return back()->withErrors(['email' => 'User not found or credentials do not match our records.']);
        }

        if ($user->is_active) {
            return back()->withErrors(['email' => 'This account has already been activated. Please login.']);
        }

        // Pass user ID to next step
        return redirect()->route('activate.password.form', ['user_id' => $user->id]);
    }

    // Render Step 2: Set New Password Form
    public function showPasswordForm(Request $request)
    {
        $user = User::findOrFail($request->user_id);

        return Inertia::render('Auth/ChangePassword', [
            'userId' => $user->id,
            'email' => $user->email,
        ]);
    }

    // Step 2 Submission: Save Password & Activate Account
    public function setPassword(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::findOrFail($request->user_id);
        $user->password = Hash::make($request->password);
        $user->is_active = true;
        $user->save();

        return redirect()->route('login')->with('status', 'Account activated successfully! Please login with your new password.');
    }
}
