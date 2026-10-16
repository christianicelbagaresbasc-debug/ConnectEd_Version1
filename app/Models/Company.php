<?php

namespace App\Models;

use App\Enums\CompanyClassification;
use App\Enums\CompanySource;
use App\Enums\CompanyVerificationStatus;
use App\Enums\VerificationItemStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'classification',
        'verification_status',
        'is_active',
        'industry',
        'address',
        'city',
        'contact_person',
        'contact_email',
        'contact_phone',
        'website',
        'description',
        'bsit_relevance_note',
        'source',
        'promoted_by',
        'promoted_at',
    ];

    protected $casts = [
        'classification' => CompanyClassification::class,
        'verification_status' => CompanyVerificationStatus::class,
        'source' => CompanySource::class,
        'is_active' => 'boolean',
        'promoted_at' => 'datetime',
    ];

    public function promotedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'promoted_by');
    }

    public function verificationItems(): HasMany
    {
        return $this->hasMany(CompanyVerificationItem::class);
    }

    public function verificationLogs(): HasMany
    {
        return $this->hasMany(CompanyVerificationLog::class)->latest('created_at');
    }

    public function partnerAccounts(): HasMany
    {
        return $this->hasMany(PartnerAccount::class);
    }

    public function suggestions(): HasMany
    {
        return $this->hasMany(CompanySuggestion::class);
    }

    public function isExistingPartner(): bool
    {
        return $this->classification === CompanyClassification::EXISTING_PARTNER;
    }

    public function isNewCompany(): bool
    {
        return $this->classification === CompanyClassification::NEW_COMPANY;
    }

    public function isVerified(): bool
    {
        return $this->verification_status === CompanyVerificationStatus::VERIFIED;
    }

    /**
     * BR-17 Rule: Can only be promoted from New Company to Existing Partner by OC decision
     * after verification_status is verified.
     */
    public function canBePromoted(): bool
    {
        return $this->isNewCompany() && $this->isVerified();
    }

    /**
     * Compute completion percentage of accepted checklist items
     */
    public function verificationProgressPercentage(): int
    {
        $total = VerificationRequirement::where('is_active', true)->count();
        if ($total === 0) {
            return 100;
        }

        $accepted = $this->verificationItems()
            ->where('status', VerificationItemStatus::ACCEPTED)
            ->count();

        return (int) round(($accepted / $total) * 100);
    }
}
