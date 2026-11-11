<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DormitoryBlock extends Model
{
    use HasFactory;

    public const MAX_CAMPUS_BLOCKS = 70;
    public const ROOMS_PER_BLOCK = 50;
    public const MAX_ROOM_CAPACITY = 6;
    public const STANDARD_BLOCK_CAPACITY = 300; // 50 rooms * 6 students per room = 300 beds
    public const TOTAL_CAMPUS_ROOMS = 3500; // 70 blocks * 50 rooms = 3,500 rooms
    public const TOTAL_CAMPUS_BEDS = 21000; // 70 blocks * 50 rooms * 6 beds = 21,000 beds

    protected $fillable = [
        'name',
        'block_code',
        'gender_category',
        'location',
        'description',
        'image',
        'status',
    ];

    protected $appends = [
        'image_url',
    ];

    protected static function booted(): void
    {
        static::creating(function (DormitoryBlock $block) {
            if (empty($block->image) && !empty($block->block_code)) {
                $block->image = self::determineBlockImage($block->block_code);
            }
        });

        static::updating(function (DormitoryBlock $block) {
            if ($block->isDirty('block_code')) {
                $block->image = self::determineBlockImage($block->block_code);
            }
        });
    }

    public static function isPrime(int $n): bool
    {
        if ($n < 2) {
            return false;
        }
        if ($n === 2 || $n === 3) {
            return true;
        }
        if ($n % 2 === 0 || $n % 3 === 0) {
            return false;
        }
        for ($i = 5; $i * $i <= $n; $i += 6) {
            if ($n % $i === 0 || $n % ($i + 2) === 0) {
                return false;
            }
        }
        return true;
    }

    public static function extractNumber(?string $value): ?int
    {
        if ($value === null) {
            return null;
        }
        if (preg_match('/\d+/', $value, $matches)) {
            return (int) $matches[0];
        }
        return null;
    }

    public static function determineBlockImage(?string $code): string
    {
        $num = self::extractNumber($code);
        if ($num === null) {
            return 'composit_only_block.png';
        }

        if (self::isPrime($num)) {
            return 'prime_only_block.png';
        }

        if ($num % 2 !== 0) {
            return 'odd_not_prime_block.jpg';
        }

        return 'composit_only_block.png';
    }

    public function getImageUrlAttribute(): string
    {
        $img = $this->image ?: self::determineBlockImage($this->block_code);
        return asset('images/' . $img);
    }

    public function rooms()
    {
        return $this->hasMany(Room::class, 'block_id');
    }

    public function proctorAssignments()
    {
        return $this->hasMany(BlockProctorAssignment::class, 'block_id');
    }

    public function activeProctorAssignments()
    {
        return $this->hasMany(BlockProctorAssignment::class, 'block_id')->whereNull('ended_at');
    }

    public function activeProctorAssignment()
    {
        return $this->hasOne(BlockProctorAssignment::class, 'block_id')->whereNull('ended_at');
    }

    public function allocations()
    {
        return $this->hasManyThrough(
            StudentAllocation::class,
            Room::class,
            'block_id', // Foreign key on rooms table
            'room_id',  // Foreign key on student_allocations table
            'id',       // Local key on dormitory_blocks table
            'id'        // Local key on rooms table
        );
    }

    public function activeAllocations()
    {
        return $this->allocations()->where('student_allocations.status', 'active');
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class, 'block_id');
    }

    public function attendanceSubmissions()
    {
        return $this->hasMany(AttendanceSubmission::class, 'block_id');
    }

    public function getTotalCapacityAttribute(): int
    {
        $sum = (int) $this->rooms()->sum('capacity');
        return $sum > 0 ? $sum : self::STANDARD_BLOCK_CAPACITY;
    }

    public function getActiveAllocationsCountAttribute(): int
    {
        return (int) $this->activeAllocations()->count();
    }

    public function getAvailableBedsAttribute(): int
    {
        return max(0, $this->total_capacity - $this->active_allocations_count);
    }

    public function getStandardCapacityAttribute(): int
    {
        return self::STANDARD_BLOCK_CAPACITY; // 300
    }

    public function getStandardAvailableBedsAttribute(): int
    {
        return max(0, self::STANDARD_BLOCK_CAPACITY - $this->active_allocations_count);
    }

    public function getOccupancyRateAttribute(): float
    {
        $cap = $this->total_capacity ?: self::STANDARD_BLOCK_CAPACITY;
        if ($cap <= 0) {
            return 0.0;
        }
        return round(($this->active_allocations_count / $cap) * 100, 1);
    }

    public function getStandardOccupancyRateAttribute(): float
    {
        return round(($this->active_allocations_count / self::STANDARD_BLOCK_CAPACITY) * 100, 1);
    }
}
