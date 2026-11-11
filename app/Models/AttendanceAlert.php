<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceAlert extends Model
{
    use HasFactory;

    public const TYPE_ABSENTEE_WARNING = 'absentee_warning';
    public const TYPE_ATTENDANCE_SUBMITTED = 'attendance_submitted';
    public const TYPE_LEAVE_APPROVED = 'leave_approved';
    public const TYPE_LEAVE_REJECTED = 'leave_rejected';
    public const TYPE_GENERAL = 'general';

    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'date',
        'is_read',
        'read_at',
        'action_url',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'is_read' => 'boolean',
            'read_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function markAsRead(): void
    {
        $this->update([
            'is_read' => true,
            'read_at' => now(),
        ]);
    }

    public static function sendAlert(User|int $user, string $type, string $title, string $message, ?string $date = null, ?string $actionUrl = null): self
    {
        $userId = $user instanceof User ? $user->id : $user;

        return self::create([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'date' => $date ?? now()->toDateString(),
            'action_url' => $actionUrl,
            'is_read' => false,
        ]);
    }
}
