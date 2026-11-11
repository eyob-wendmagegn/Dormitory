<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    public const STATUS_PRESENT = 'present';
    public const STATUS_ABSENT = 'absent';
    public const STATUS_EXCUSED = 'excused';
    public const STATUS_SICK = 'sick';

    protected $fillable = [
        'student_id',
        'proctor_id',
        'block_id',
        'room_id',
        'leave_request_id',
        'date',
        'recorded_at',
        'status',
        'remarks',
        'is_finalized',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'recorded_at' => 'datetime',
            'is_finalized' => 'boolean',
        ];
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function proctor()
    {
        return $this->belongsTo(User::class, 'proctor_id');
    }

    public function block()
    {
        return $this->belongsTo(DormitoryBlock::class, 'block_id');
    }

    public function room()
    {
        return $this->belongsTo(Room::class, 'room_id');
    }

    public function leaveRequest()
    {
        return $this->belongsTo(LeaveRequest::class, 'leave_request_id');
    }

    public function scopeForDate($query, $date)
    {
        return $query->whereDate('date', $date);
    }

    public function scopePresent($query)
    {
        return $query->where('status', self::STATUS_PRESENT);
    }

    public function scopeAbsent($query)
    {
        return $query->where('status', self::STATUS_ABSENT);
    }

    public function scopeExcused($query)
    {
        return $query->where('status', self::STATUS_EXCUSED);
    }

    public function scopeSick($query)
    {
        return $query->where('status', self::STATUS_SICK);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PRESENT => 'Present',
            self::STATUS_ABSENT => 'Absent',
            self::STATUS_EXCUSED => 'On Leave / Excused',
            self::STATUS_SICK => 'Sick / Clinic',
            default => ucfirst($this->status),
        };
    }
}
