<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Announcement extends Model
{
    use HasFactory;

    public const TARGET_ALL = 'all';
    public const TARGET_PROCTOR = 'proctor';
    public const TARGET_STUDENT = 'student';

    public const PRIORITY_NORMAL = 'normal';
    public const PRIORITY_URGENT = 'urgent';
    public const PRIORITY_CRITICAL = 'critical';

    protected $fillable = [
        'author_id',
        'title',
        'slug',
        'summary',
        'content',
        'category',
        'target_role',
        'target_audience',
        'block_id',
        'priority',
        'is_published',
        'published_at',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'published_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    protected static function booted()
    {
        static::creating(function ($announcement) {
            if (empty($announcement->slug)) {
                $announcement->slug = Str::slug($announcement->title) . '-' . Str::random(6);
            }
        });
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function block()
    {
        return $this->belongsTo(DormitoryBlock::class, 'block_id');
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true)->whereNotNull('published_at');
    }

    /**
     * Scope notices visible to proctors (internal manager notices or general)
     */
    public function scopeForProctors($query, int|array|null $blockIds = null)
    {
        return $query->where(function ($q) use ($blockIds) {
            $q->whereIn('target_role', [self::TARGET_ALL, self::TARGET_PROCTOR]);
            if (!empty($blockIds)) {
                $ids = is_array($blockIds) ? $blockIds : [$blockIds];
                $q->where(function ($bq) use ($ids) {
                    $bq->whereNull('block_id')->orWhereIn('block_id', $ids);
                });
            }
        });
    }

    /**
     * Scope notices visible to students (block-specific proctor notice or general)
     */
    public function scopeForStudents($query, ?int $blockId = null)
    {
        return $query->where(function ($q) use ($blockId) {
            $q->whereIn('target_role', [self::TARGET_ALL, self::TARGET_STUDENT]);
            if ($blockId) {
                $q->where(function ($bq) use ($blockId) {
                    $bq->whereNull('block_id')->orWhere('block_id', $blockId);
                });
            } else {
                $q->whereNull('block_id');
            }
        });
    }
}
