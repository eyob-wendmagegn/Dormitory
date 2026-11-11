<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentAllocation extends Model
{
    use HasFactory;

    public const STATUS_ACTIVE = 'active';
    public const STATUS_RELEASED = 'released';
    public const STATUS_TRANSFERRED = 'transferred';

    protected $fillable = [
        'student_id',
        'room_id',
        'allocated_by',
        'allocated_at',
        'released_at',
        'status',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'allocated_at' => 'datetime',
            'released_at' => 'datetime',
        ];
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function room()
    {
        return $this->belongsTo(Room::class, 'room_id');
    }

    public function allocator()
    {
        return $this->belongsTo(User::class, 'allocated_by');
    }

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeReleased($query)
    {
        return $query->whereIn('status', [self::STATUS_RELEASED, self::STATUS_TRANSFERRED]);
    }
}
