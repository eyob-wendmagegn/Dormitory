<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProctorAssetAssignment extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending_confirmation';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'manager_id',
        'proctor_id',
        'block_id',
        'item_type',
        'assigned_quantity',
        'confirmed_quantity',
        'status',
        'transfer_date',
        'confirmed_at',
        'notes',
        'rejection_reason',
    ];

    protected function casts(): array
    {
        return [
            'assigned_quantity' => 'integer',
            'confirmed_quantity' => 'integer',
            'transfer_date' => 'date',
            'confirmed_at' => 'datetime',
        ];
    }

    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function proctor()
    {
        return $this->belongsTo(User::class, 'proctor_id');
    }

    public function block()
    {
        return $this->belongsTo(DormitoryBlock::class, 'block_id');
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeConfirmed($query)
    {
        return $query->where('status', self::STATUS_CONFIRMED);
    }
}
