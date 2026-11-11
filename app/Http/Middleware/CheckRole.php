<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if (! in_array($user->role, $roles, true)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthorized action for this role.'], 403);
            }

            // Redirect user to their own valid dashboard
            $target = match ($user->role) {
                'admin' => route('admin.dashboard'),
                'proctor_manager' => route('manager.dashboard'),
                'proctor' => route('proctor.dashboard'),
                'student' => route('student.dashboard'),
                default => route('login'),
            };

            return redirect($target)->with('error', 'Access denied. You do not have permission to access that area.');
        }

        return $next($request);
    }
}
