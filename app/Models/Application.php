<?php

namespace App\Models;

use App\Enums\ApplicationStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Application extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'student_profile_id',
        'opportunity_id',
        'status',
        'cover_note',
        'submitted_at',
        'decided_by',
        'decided_at',
        'decision_remark',
        'student_confirmed_at',
        'oc_confirmed_at',
        'withdrawn_reason',
        'school_year',
    ];

    protected $casts = [
        'status' => ApplicationStatus::class,
        'submitted_at' => 'datetime',
        'decided_at' => 'datetime',
        'student_confirmed_at' => 'datetime',
        'oc_confirmed_at' => 'datetime',
    ];

    public function studentProfile(): BelongsTo
    {
        return $this->belongsTo(StudentProfile::class);
    }

    public function opportunity(): BelongsTo
    {
        return $this->belongsTo(Opportunity::class);
    }

    public function decidedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'decided_by');
    }

    public function statusLogs(): HasMany
    {
        return $this->hasMany(ApplicationStatusLog::class)->orderBy('created_at', 'asc');
    }

    public function placement(): HasOne
    {
        return $this->hasOne(Placement::class);
    }

    public function isPending(): bool
    {
        return $this->status === ApplicationStatus::PENDING;
    }

    public function isUnderReview(): bool
    {
        return $this->status === ApplicationStatus::UNDER_REVIEW;
    }

    public function isAccepted(): bool
    {
        return $this->status === ApplicationStatus::ACCEPTED;
    }

    public function isPlaced(): bool
    {
        return $this->status === ApplicationStatus::PLACED;
    }

    public function isWithdrawn(): bool
    {
        return $this->status === ApplicationStatus::WITHDRAWN;
    }

    public function isRejected(): bool
    {
        return $this->status === ApplicationStatus::REJECTED;
    }

    public function isDeclined(): bool
    {
        return $this->status === ApplicationStatus::DECLINED;
    }

    public function isDualConfirmed(): bool
    {
        return ! is_null($this->student_confirmed_at) && ! is_null($this->oc_confirmed_at);
    }
}
