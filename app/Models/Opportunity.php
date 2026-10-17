<?php

namespace App\Models;

use App\Enums\CompanyClassification;
use App\Enums\CompanyVerificationStatus;
use App\Enums\OpportunityRequirementLevel;
use App\Enums\OpportunityStatus;
use App\Enums\OpportunityWorkArrangement;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Opportunity extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'title',
        'description',
        'qualifications',
        'slots_total',
        'slots_filled',
        'location',
        'work_arrangement',
        'deadline',
        'status',
        'created_by',
        'approved_by',
        'approved_at',
        'school_year',
    ];

    protected $casts = [
        'work_arrangement' => OpportunityWorkArrangement::class,
        'status' => OpportunityStatus::class,
        'deadline' => 'date',
        'approved_at' => 'datetime',
        'slots_total' => 'integer',
        'slots_filled' => 'integer',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function opportunitySkills(): HasMany
    {
        return $this->hasMany(OpportunitySkill::class);
    }

    public function skills(): BelongsToMany
    {
        return $this->belongsToMany(Skill::class, 'opportunity_skills')
            ->withPivot('requirement_level')
            ->withTimestamps();
    }

    public function requiredSkills(): BelongsToMany
    {
        return $this->belongsToMany(Skill::class, 'opportunity_skills')
            ->wherePivot('requirement_level', OpportunityRequirementLevel::REQUIRED->value)
            ->withTimestamps();
    }

    public function preferredSkills(): BelongsToMany
    {
        return $this->belongsToMany(Skill::class, 'opportunity_skills')
            ->wherePivot('requirement_level', OpportunityRequirementLevel::PREFERRED->value)
            ->withTimestamps();
    }

    public function slotsAvailable(): int
    {
        return max(0, $this->slots_total - $this->slots_filled);
    }

    public function hasAvailableSlots(): bool
    {
        return $this->slots_total > $this->slots_filled;
    }

    public function isDeadlinePassed(): bool
    {
        return $this->deadline && $this->deadline->isPast() && !$this->deadline->isToday();
    }

    public function isOpen(): bool
    {
        return $this->status === OpportunityStatus::OPEN && !$this->isDeadlinePassed() && $this->hasAvailableSlots();
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->where('status', OpportunityStatus::OPEN)
            ->whereColumn('slots_total', '>', 'slots_filled')
            ->where(function ($q) {
                $q->whereNull('deadline')
                  ->orWhere('deadline', '>=', now()->toDateString());
            });
    }

    public function scopeForVerifiedPartner(Builder $query): Builder
    {
        return $query->whereHas('company', function ($q) {
            $q->where('classification', CompanyClassification::EXISTING_PARTNER)
              ->where('verification_status', CompanyVerificationStatus::VERIFIED)
              ->where('is_active', true);
        });
    }
}
