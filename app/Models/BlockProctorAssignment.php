<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BlockProctorAssignment extends Model
{
    use HasFactory;

    public const MAX_DEPARTMENTS = 10;
    public const MAX_YEARS = 5;

    protected $fillable = [
        'block_id',
        'proctor_id',
        'assigned_by',
        'assigned_departments',
        'assigned_years',
        'assigned_at',
        'ended_at',
    ];

    protected function casts(): array
    {
        return [
            'assigned_departments' => 'array',
            'assigned_years' => 'array',
            'assigned_at' => 'datetime',
            'ended_at' => 'datetime',
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

    public function assigner()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function isActive(): bool
    {
        return is_null($this->ended_at);
    }

    public function getAssignedDepartmentsListAttribute(): array
    {
        return is_array($this->assigned_departments) ? array_values(array_filter($this->assigned_departments)) : [];
    }

    public function getAssignedYearsListAttribute(): array
    {
        if (! is_array($this->assigned_years)) {
            return [];
        }
        return array_values(array_map('intval', array_filter($this->assigned_years)));
    }

    public function getFormattedYearsAttribute(): string
    {
        $years = $this->assigned_years_list;
        if (empty($years)) {
            return 'All Batches';
        }

        $labels = [
            1 => '1st Year',
            2 => '2nd Year',
            3 => '3rd Year',
            4 => '4th Year',
            5 => '5th Year',
            6 => '6th Year',
            7 => '7th Year',
        ];

        return collect($years)->map(fn($y) => $labels[$y] ?? "Year {$y}")->join(', ');
    }
}
