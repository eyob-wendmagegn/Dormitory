<?php

namespace App\Http\Controllers;

use App\Models\AttendanceAlert;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AlertController extends Controller
{
    /**
     * Mark single alert as read
     */
    public function markRead(AttendanceAlert $alert): RedirectResponse
    {
        if ($alert->user_id !== Auth::id()) {
            abort(403);
        }

        $alert->markAsRead();

        if ($alert->action_url) {
            return redirect($alert->action_url);
        }

        return back()->with('success', 'Notification marked as read.');
    }

    /**
     * Mark all user alerts as read
     */
    public function markAllRead(): RedirectResponse
    {
        AttendanceAlert::where('user_id', Auth::id())
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        return back()->with('success', 'All notifications cleared.');
    }
}
