<?php

namespace App\Models;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'status',
        'must_change_password',
        'approved_by',
        'approved_at',
        'last_login_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'role' => UserRole::class,
        'status' => UserStatus::class,
        'must_change_password' => 'boolean',
        'approved_at' => 'datetime',
        'last_login_at' => 'datetime',
    ];

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function studentProfile(): HasOne
    {
        return $this->hasOne(StudentProfile::class);
    }

    public function partnerAccount(): HasOne
    {
        return $this->hasOne(PartnerAccount::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class, 'owner_user_id');
    }

    public function uploadedDocuments(): HasMany
    {
        return $this->hasMany(Document::class, 'uploaded_by');
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class, 'actor_id');
    }

    public function assignedStudents(): HasMany
    {
        return $this->hasMany(StudentProfile::class, 'assigned_coc_id');
    }

    public function isOc(): bool
    {
        return $this->role === UserRole::OC;
    }

    public function isCoc(): bool
    {
        return $this->role === UserRole::COC;
    }

    public function isUa(): bool
    {
        return $this->role === UserRole::UA;
    }

    public function isPc(): bool
    {
        return $this->role === UserRole::PC;
    }

    public function isStudent(): bool
    {
        return $this->role === UserRole::STUDENT;
    }

    public function isCoordinator(): bool
    {
        return in_array($this->role, [UserRole::OC, UserRole::COC]);
    }

    public function isCoCoordinator(): bool
    {
        return $this->role === UserRole::COC;
    }

    public function isApproved(): bool
    {
        return $this->isActive();
    }

    public function isActive(): bool
    {
        return $this->status === UserStatus::ACTIVE;
    }

    public function isPending(): bool
    {
        return $this->status === UserStatus::PENDING;
    }

    public function isSuspended(): bool
    {
        return $this->status === UserStatus::SUSPENDED;
    }
}
