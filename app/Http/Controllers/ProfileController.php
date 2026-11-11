<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile view.
     */
    public function edit(Request $request): View
    {
        $user = $request->user();

        if ($user->isStudent()) {
            $user->loadMissing(['activeAllocation.room.block']);
        } elseif ($user->isProctor()) {
            $user->loadMissing(['activeProctorAssignments.block']);
        }

        $recentActivity = $user->auditLogs()->latest()->take(6)->get();

        return view('profile.edit', [
            'user' => $user,
            'recentActivity' => $recentActivity,
        ]);
    }

    /**
     * Update the user's profile information (name, email, gender, department).
     */
    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id),
            ],
            'gender' => ['nullable', 'string', 'in:male,female'],
            'department' => ['nullable', 'string', 'max:100'],
        ];

        $validated = $request->validate($rules);

        // Protect student gender consistency with room allocations
        if ($user->isStudent() && $user->activeAllocation()->exists()) {
            if (!empty($validated['gender']) && $validated['gender'] !== $user->gender) {
                return back()->withErrors([
                    'gender' => 'Gender cannot be changed while you have an active dormitory room allocation. Please contact your proctor manager if an institutional record correction is needed.',
                ])->withInput();
            }
        }

        $user->fill($validated);
        $user->save();

        AuditLog::log('profile_updated', "User {$user->name} updated basic profile details.", null, $user);

        return redirect()->route('profile.edit')->with('success', 'Profile information updated successfully.');
    }

    /**
     * Update the user's password.
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        $user = $request->user();
        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        AuditLog::log('password_changed', "User {$user->name} changed their account password.", null, $user);

        return back()->with('success', 'Password updated successfully.');
    }

    /**
     * Upload or replace profile avatar.
     */
    public function updateAvatar(Request $request): RedirectResponse
    {
        $request->validate([
            'avatar' => ['required', 'image', 'mimes:jpeg,png,jpg,webp', 'max:3072'],
        ]);

        $user = $request->user();

        // Delete old profile picture if exists
        if ($user->profile_photo_path && Storage::disk('public')->exists($user->profile_photo_path)) {
            Storage::disk('public')->delete($user->profile_photo_path);
        }

        $file = $request->file('avatar');
        $extension = $file->getClientOriginalExtension() ?: 'jpg';
        $filename = 'avatars/' . Str::uuid() . '.' . $extension;
        $file->storeAs('avatars', basename($filename), 'public');

        $user->update(['profile_photo_path' => $filename]);

        AuditLog::log('avatar_updated', "User {$user->name} updated profile photo.", null, $user);

        return back()->with('success', 'Profile photo updated successfully.');
    }

    /**
     * Remove profile avatar and restore default avatar.
     */
    public function destroyAvatar(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->profile_photo_path && Storage::disk('public')->exists($user->profile_photo_path)) {
            Storage::disk('public')->delete($user->profile_photo_path);
        }

        $user->update(['profile_photo_path' => null]);

        AuditLog::log('avatar_removed', "User {$user->name} removed custom profile photo.", null, $user);

        return back()->with('success', 'Profile photo removed. Default avatar restored.');
    }
}
