<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceSubmission extends Model
{
    use HasFactory;

    protected $fillable = [
        'block_id',
        'proctor_id',
        'date',
        'total_students',
        'present_count',
        'absent_count',
        'excused_count',
        'sick_count',
        'submitted_at',
        'notes',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'submitted_at' => 'datetime',
            'total_students' => 'integer',
            'present_count' => 'integer',
            'absent_count' => 'integer',
            'excused_count' => 'integer',
            'sick_count' => 'integer',
        ];
    }

    public function block()
    {
        return $this->belongsTo(DormitoryBlock::class, 'block_id');
    }

    public function proctor()
    {
        return $this->belongsTo(User::class, 'proctor_id');
    }

    public function getAttendanceRateAttribute(): float
    {
        if ($this->total_students === 0) {
            return 0.0;
        }

        return round(($this->present_count / $this->total_students) * 100, 1);
    }
}
