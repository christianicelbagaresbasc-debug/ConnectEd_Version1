<?php

namespace App\Models;

use App\Enums\OpportunityRequirementLevel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class OpportunitySkill extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'opportunity_id',
        'skill_id',
        'requirement_level',
    ];

    protected $casts = [
        'requirement_level' => OpportunityRequirementLevel::class,
    ];

    public function opportunity(): BelongsTo
    {
        return $this->belongsTo(Opportunity::class);
    }

    public function skill(): BelongsTo
    {
        return $this->belongsTo(Skill::class);
    }

    public function isRequired(): bool
    {
        return $this->requirement_level === OpportunityRequirementLevel::REQUIRED;
    }

    public function isPreferred(): bool
    {
        return $this->requirement_level === OpportunityRequirementLevel::PREFERRED;
    }
}
