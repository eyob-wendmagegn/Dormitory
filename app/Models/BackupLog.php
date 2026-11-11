<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BackupLog extends Model
{
    use HasFactory;

    public const TRIGGER_AUTOMATIC = 'Automatic';
    public const TRIGGER_MANUAL = 'Manual';
    public const TRIGGER_PRE_RESTORE = 'Pre-Restore Safety';

    public const STATUS_SUCCESS = 'Success';
    public const STATUS_FAILED = 'Failed';

    protected $fillable = [
        'file_name',
        'file_size',
        'google_drive_file_id',
        'trigger_type',
        'status',
        'error_message',
        'created_by',
        'restored_at',
    ];

    protected function casts(): array
    {
        return [
            'file_size' => 'integer',
            'restored_at' => 'datetime',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isSuccessful(): bool
    {
        return $this->status === self::STATUS_SUCCESS;
    }

    public function isAvailableOnDrive(): bool
    {
        return $this->isSuccessful() && filled($this->google_drive_file_id);
    }

    protected function formattedSize(): Attribute
    {
        return Attribute::get(function (): string {
            $bytes = $this->file_size;

            if ($bytes === null || $bytes <= 0) {
                return '—';
            }

            $units = ['B', 'KB', 'MB', 'GB', 'TB'];
            $power = (int) floor(log($bytes, 1024));
            $power = min($power, count($units) - 1);

            return number_format($bytes / (1024 ** $power), $power === 0 ? 0 : 2).' '.$units[$power];
        });
    }
}
