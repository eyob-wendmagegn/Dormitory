<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveRequest extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_CANCELLED = 'cancelled';

    public const TYPE_GATE_PASS = 'gate_pass';
    public const TYPE_OVERNIGHT = 'overnight';
    public const TYPE_WEEKEND = 'weekend';
    public const TYPE_MEDICAL = 'medical';
    public const TYPE_EMERGENCY = 'emergency';

    protected $fillable = [
        'student_id',
        'request_type',
        'start_date',
        'end_date',
        'destination',
        'emergency_phone',
        'reason',
        'status',
        'approved_by',
        'approved_at',
        'rejection_reason',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'approved_at' => 'datetime',
        ];
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class, 'leave_request_id');
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function scopeActiveOnDate($query, $date)
    {
        return $query->where('status', self::STATUS_APPROVED)
            ->whereDate('start_date', '<=', $date)
            ->whereDate('end_date', '>=', $date);
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->request_type) {
            self::TYPE_GATE_PASS => 'Gate Pass (Day)',
            self::TYPE_OVERNIGHT => 'Overnight Pass',
            self::TYPE_WEEKEND => 'Weekend Leave',
            self::TYPE_MEDICAL => 'Medical Leave',
            self::TYPE_EMERGENCY => 'Emergency Leave',
            default => ucfirst(str_replace('_', ' ', $this->request_type)),
        };
    }
}
