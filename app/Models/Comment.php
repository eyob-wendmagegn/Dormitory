<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_RESOLVED = 'resolved';
    public const STATUS_CLOSED = 'closed';

    protected $fillable = [
        'user_id',
        'category',
        'target_role',
        'block_id',
        'title',
        'message',
        'status',
        'response',
        'responded_by',
        'responded_at',
    ];

    public function block()
    {
        return $this->belongsTo(DormitoryBlock::class, 'block_id');
    }

    protected function casts(): array
    {
        return [
            'responded_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function responder()
    {
        return $this->belongsTo(User::class, 'responded_by');
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeResolved($query)
    {
        return $query->where('status', self::STATUS_RESOLVED);
    }
}
