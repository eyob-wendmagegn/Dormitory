<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class UserController extends Controller
{
    /**
     * Display a listing of registered users and the registration form.
     */
    public function index()
    {
        $users = User::select('id', 'name', 'user_id_number', 'email', 'role', 'is_active', 'created_at')
            ->latest()
            ->get();

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
        ]);
    }

    /**
     * Store a newly created user registered by Admin.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'user_id_number' => 'required|string|max:50|unique:users,user_id_number',
            'email' => 'required|string|email|max:255|unique:users,email',
            'role' => ['required', Rule::in(['admin', 'proctor_manager', 'proctor', 'student'])],
        ]);

        // Default password set to user_id_number. Account is inactive until user completes activation.
        User::create([
            'name' => $validated['name'],
            'user_id_number' => $validated['user_id_number'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'password' => Hash::make($validated['user_id_number']),
            'is_active' => false,
        ]);

        return redirect()->back()->with('success', 'User registered successfully! Account is pending activation.');
    }

    /**
     * Remove a user from the database.
     */
    public function destroy(Request $request, User $user)
    {
        // Prevent self-deletion
        if ($request->user()->id === $user->id) {
            return redirect()->back()->withErrors(['message' => 'You cannot delete your own admin account.']);
        }

        $user->delete();

        return redirect()->back()->with('success', 'User account deleted successfully.');
    }
}
