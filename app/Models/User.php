<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    public const ROLE_ADMIN = 'admin';
    public const ROLE_PROCTOR_MANAGER = 'proctor_manager';
    public const ROLE_PROCTOR = 'proctor';
    public const ROLE_STUDENT = 'student';

    public const REGISTRATION_PENDING = 'pending';
    public const REGISTRATION_COMPLETED = 'completed';

    protected $fillable = [
        'name',
        'university_id',
        'email',
        'password',
        'role',
        'gender',
        'department',
        'academic_year',
        'registration_status',
        'is_active',
        'profile_photo_path',
        'email_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'academic_year' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    // Role check helpers
    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isProctorManager(): bool
    {
        return $this->role === self::ROLE_PROCTOR_MANAGER;
    }

    public function isProctor(): bool
    {
        return $this->role === self::ROLE_PROCTOR;
    }

    public function isStudent(): bool
    {
        return $this->role === self::ROLE_STUDENT;
    }

    public function isRegistrationCompleted(): bool
    {
        return $this->registration_status === self::REGISTRATION_COMPLETED;
    }

    // Academic year label accessor (1 -> 1st Year, etc.)
    public function getAcademicYearLabelAttribute(): ?string
    {
        if (! $this->academic_year) {
            return null;
        }

        $suffixes = [1 => '1st Year', 2 => '2nd Year', 3 => '3rd Year', 4 => '4th Year', 5 => '5th Year', 6 => '6th Year', 7 => '7th Year'];
        return $suffixes[$this->academic_year] ?? ($this->academic_year . 'th Year');
    }

    public function getHasCustomPhotoAttribute(): bool
    {
        return ! empty($this->profile_photo_path) && Storage::disk('public')->exists($this->profile_photo_path);
    }

    // Avatar URL accessor
    public function getAvatarUrlAttribute(): string
    {
        if ($this->profile_photo_path && Storage::disk('public')->exists($this->profile_photo_path)) {
            return '/storage/' . ltrim($this->profile_photo_path, '/');
        }

        // Return a clean modern inline SVG data URI placeholder
        $initials = collect(explode(' ', trim($this->name)))
            ->filter()
            ->map(fn($part) => strtoupper(substr($part, 0, 1)))
            ->take(2)
            ->join('');

        if (empty($initials)) {
            $initials = 'U';
        }

        $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 128 128" width="128" height="128">'
            . '<rect width="128" height="128" rx="28" fill="#121927"/>'
            . '<text x="50%" y="54%" dominant-baseline="middle" text-anchor="middle" fill="#10B981" font-family="system-ui, -apple-system, sans-serif" font-size="48" font-weight="bold">'
            . htmlspecialchars($initials, ENT_QUOTES, 'UTF-8')
            . '</text></svg>';

        return 'data:image/svg+xml;utf8,' . rawurlencode($svg);
    }

    // Relationships
    public function proctorAssignments()
    {
        return $this->hasMany(BlockProctorAssignment::class, 'proctor_id');
    }

    public function blockProctorAssignments()
    {
        return $this->hasMany(BlockProctorAssignment::class, 'proctor_id');
    }

    public function activeProctorAssignments()
    {
        return $this->hasMany(BlockProctorAssignment::class, 'proctor_id')->whereNull('ended_at');
    }

    public function managedBlocks()
    {
        return $this->hasManyThrough(
            DormitoryBlock::class,
            BlockProctorAssignment::class,
            'proctor_id', // Foreign key on block_proctor_assignments table...
            'id',         // Foreign key on dormitory_blocks table...
            'id',         // Local key on users table...
            'block_id'    // Local key on block_proctor_assignments table...
        )->whereNull('block_proctor_assignments.ended_at');
    }

    public function allocations()
    {
        return $this->hasMany(StudentAllocation::class, 'student_id')->orderByDesc('allocated_at');
    }

    public function activeAllocation()
    {
        return $this->hasOne(StudentAllocation::class, 'student_id')->where('status', 'active');
    }

    public function comments()
    {
        return $this->hasMany(Comment::class, 'user_id')->orderByDesc('created_at');
    }

    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class, 'user_id');
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class, 'student_id')->orderByDesc('date');
    }

    public function recordedAttendances()
    {
        return $this->hasMany(Attendance::class, 'proctor_id')->orderByDesc('date');
    }

    public function leaveRequests()
    {
        return $this->hasMany(LeaveRequest::class, 'student_id')->orderByDesc('created_at');
    }

    public function attendanceAlerts()
    {
        return $this->hasMany(AttendanceAlert::class, 'user_id')->latest();
    }

    public function unreadAttendanceAlerts()
    {
        return $this->hasMany(AttendanceAlert::class, 'user_id')->where('is_read', false);
    }
}
