<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Room extends Model
{
    use HasFactory;

    protected $fillable = [
        'block_id',
        'room_number',
        'capacity',
        'image',
        'status',
        'notes',
    ];

    protected $appends = [
        'image_url',
        'floor',
    ];

    protected static function booted(): void
    {
        static::creating(function (Room $room) {
            if (empty($room->image) && !empty($room->room_number)) {
                $room->image = self::determineRoomImage($room->room_number);
            }
        });

        static::updating(function (Room $room) {
            if ($room->isDirty('room_number')) {
                $room->image = self::determineRoomImage($room->room_number);
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

    public static function determineRoomImage(?string $roomNumber): string
    {
        $num = self::extractNumber($roomNumber);
        if ($num === null) {
            return 'composit_only_room.jpg';
        }

        if (self::isPrime($num)) {
            return 'prime_only_room.jpg';
        }

        if ($num % 2 !== 0) {
            return 'odd_not_prime_room.jpg';
        }

        return 'composit_only_room.jpg';
    }

    public function getImageUrlAttribute(): string
    {
        $img = $this->image ?: self::determineRoomImage($this->room_number);
        return asset('images/' . $img);
    }

    public function getFloorAttribute(): int
    {
        $num = self::extractNumber($this->room_number);
        if ($num === null) {
            return 1;
        }
        if ($num >= 100) {
            return (int) floor($num / 100);
        }
        return 1;
    }

    public function scopeOrderByRoomNumber(Builder $query, string $direction = 'asc'): Builder
    {
        $dir = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';
        $driver = DB::connection()->getDriverName();

        if ($driver === 'pgsql') {
            return $query->orderByRaw("NULLIF(regexp_replace(room_number, '\D', '', 'g'), '')::bigint {$dir} NULLS LAST, room_number {$dir}");
        } elseif ($driver === 'mysql') {
            return $query->orderByRaw("CAST(NULLIF(regexp_replace(room_number, '[^0-9]', ''), '') AS UNSIGNED) {$dir}, room_number {$dir}");
        } else {
            return $query->orderByRaw("CAST(room_number AS INTEGER) {$dir}, room_number {$dir}");
        }
    }

    protected function casts(): array
    {
        return [
            'capacity' => 'integer',
        ];
    }

    public function block()
    {
        return $this->belongsTo(DormitoryBlock::class, 'block_id');
    }

    public function allocations()
    {
        return $this->hasMany(StudentAllocation::class, 'room_id')->orderByDesc('allocated_at');
    }

    public function activeAllocations()
    {
        return $this->hasMany(StudentAllocation::class, 'room_id')->where('status', 'active');
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class, 'room_id');
    }

    public function getOccupiedBedsAttribute(): int
    {
        return $this->activeAllocations()->count();
    }

    public function getAvailableBedsAttribute(): int
    {
        return max(0, $this->capacity - $this->occupied_beds);
    }

    public function getIsFullAttribute(): bool
    {
        return $this->occupied_beds >= $this->capacity;
    }

    public function getIsAvailableAttribute(): bool
    {
        return $this->status === 'available' && ! $this->is_full;
    }
}
