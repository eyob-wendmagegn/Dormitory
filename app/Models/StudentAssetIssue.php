<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentAssetIssue extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending_confirmation';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_RETURNED = 'returned';
    public const STATUS_DAMAGED = 'damaged';
    public const STATUS_MISSING = 'missing';

    public const CONDITION_GOOD = 'good';
    public const CONDITION_DAMAGED = 'damaged';
    public const CONDITION_MISSING = 'missing';

    protected $fillable = [
        'student_id',
        'proctor_id',
        'block_id',
        'room_id',
        'item_type',
        'quantity',
        'issue_date',
        'status',
        'student_confirmed_at',
        'return_date',
        'inspected_by',
        'condition_status',
        'penalty_charge',
        'is_penalty_paid',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'issue_date' => 'date',
            'return_date' => 'date',
            'student_confirmed_at' => 'datetime',
            'penalty_charge' => 'decimal:2',
            'is_penalty_paid' => 'boolean',
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

    public function inspector()
    {
        return $this->belongsTo(User::class, 'inspected_by');
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', [self::STATUS_PENDING, self::STATUS_CONFIRMED]);
    }

    public function scopePendingStudent($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeReturned($query)
    {
        return $query->where('status', self::STATUS_RETURNED);
    }

    /**
     * Check if a student is eligible for end-of-semester clearance
     */
    public static function isStudentEligibleForClearance(int $studentId): bool
    {
        $hasActiveAssets = static::where('student_id', $studentId)
            ->whereIn('status', [self::STATUS_PENDING, self::STATUS_CONFIRMED])
            ->exists();

        if ($hasActiveAssets) {
            return false;
        }

        $hasUnpaidPenalties = static::where('student_id', $studentId)
            ->whereIn('status', [self::STATUS_DAMAGED, self::STATUS_MISSING])
            ->where('is_penalty_paid', false)
            ->where('penalty_charge', '>', 0)
            ->exists();

        return !$hasUnpaidPenalties;
    }
}
