<?php

namespace App\Models;

use App\Enums\MoaCustodianParty;
use App\Enums\MoaNoteType;
use App\Enums\MoaStatus;
use App\Enums\MoaType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Moa extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'student_profile_id',
        'moa_number',
        'type',
        'status',
        'current_custodian_party',
        'current_custodian_user_id',
        'effective_date',
        'expiry_date',
        'summary',
        'school_year',
        'created_by',
    ];

    protected $casts = [
        'type' => MoaType::class,
        'status' => MoaStatus::class,
        'current_custodian_party' => MoaCustodianParty::class,
        'effective_date' => 'date',
        'expiry_date' => 'date',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function studentProfile(): BelongsTo
    {
        return $this->belongsTo(StudentProfile::class);
    }

    public function currentCustodianUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'current_custodian_user_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(MoaVersion::class)->orderByDesc('version_no');
    }

    public function latestVersion(): HasOne
    {
        return $this->hasOne(MoaVersion::class)->latestOfMany('version_no');
    }

    public function transfers(): HasMany
    {
        return $this->hasMany(MoaTransfer::class)->latest('created_at');
    }

    public function notes(): HasMany
    {
        return $this->hasMany(MoaNote::class)->latest('created_at');
    }

    public function unresolvedNotes(): HasMany
    {
        return $this->hasMany(MoaNote::class)->whereNull('resolved_at')->latest('created_at');
    }

    public function legalRemarks(): HasMany
    {
        return $this->hasMany(MoaNote::class)
            ->whereIn('type', [MoaNoteType::LEGAL_REMARK->value, MoaNoteType::REVISION->value])
            ->latest('created_at');
    }

    public function isActive(): bool
    {
        return $this->status === MoaStatus::ACTIVE;
    }

    public function isExpired(): bool
    {
        if ($this->status === MoaStatus::EXPIRED) {
            return true;
        }

        return $this->expiry_date && $this->expiry_date->isPast();
    }

    public function isUnderLegalReview(): bool
    {
        return $this->status === MoaStatus::UNDER_LEGAL_REVIEW;
    }
}
