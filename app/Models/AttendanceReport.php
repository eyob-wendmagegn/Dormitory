<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceReport extends Model
{
    use HasFactory;

    public const TYPE_WEEKLY = 'weekly';
    public const TYPE_MONTHLY = 'monthly';
    public const TYPE_CUSTOM = 'custom';

    public const STATUS_SUBMITTED = 'submitted';
    public const STATUS_REVIEWED = 'reviewed';

    protected $fillable = [
        'block_id',
        'proctor_id',
        'report_type',
        'start_date',
        'end_date',
        'tracked_days_count',
        'total_residents',
        'total_present',
        'total_absent',
        'total_excused',
        'total_sick',
        'frequent_absentees_count',
        'notes',
        'status',
        'submitted_at',
        'reviewed_by',
        'reviewed_at',
        'manager_remarks',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'tracked_days_count' => 'integer',
        'total_residents' => 'integer',
        'total_present' => 'integer',
        'total_absent' => 'integer',
        'total_excused' => 'integer',
        'total_sick' => 'integer',
        'frequent_absentees_count' => 'integer',
    ];

    public function block(): BelongsTo
    {
        return $this->belongsTo(DormitoryBlock::class, 'block_id');
    }

    public function proctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'proctor_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function getAttendanceRateAttribute(): float
    {
        $total = $this->total_present + $this->total_absent + $this->total_excused + $this->total_sick;
        if ($total === 0) {
            return 100.0;
        }

        return round(($this->total_present / $total) * 100, 1);
    }
}
