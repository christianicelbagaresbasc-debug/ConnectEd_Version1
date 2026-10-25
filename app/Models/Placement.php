<?php

namespace App\Models;

use App\Enums\PlacementStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Placement extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'student_profile_id',
        'company_id',
        'opportunity_id',
        'application_id',
        'status',
        'is_active',
        'start_authorized_at',
        'start_authorization_note',
        'start_authorization_document_id',
        'start_date',
        'end_date',
        'termination_reason',
        'confirmed_by',
        'school_year',
    ];

    protected $casts = [
        'status' => PlacementStatus::class,
        'is_active' => 'boolean',
        'start_authorized_at' => 'datetime',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function studentProfile(): BelongsTo
    {
        return $this->belongsTo(StudentProfile::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function opportunity(): BelongsTo
    {
        return $this->belongsTo(Opportunity::class);
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function startAuthorizationDocument(): BelongsTo
    {
        return $this->belongsTo(Document::class, 'start_authorization_document_id');
    }

    public function confirmedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    public function weeklyReports(): HasMany
    {
        return $this->hasMany(WeeklyReport::class)->orderBy('week_start', 'desc');
    }

    public function isPlaced(): bool
    {
        return $this->status === PlacementStatus::PLACED;
    }

    public function isAuthorized(): bool
    {
        return $this->status === PlacementStatus::AUTHORIZED;
    }

    public function isOngoing(): bool
    {
        return $this->status === PlacementStatus::ONGOING;
    }

    public function isCompleted(): bool
    {
        return $this->status === PlacementStatus::COMPLETED;
    }

    public function isTerminated(): bool
    {
        return $this->status === PlacementStatus::TERMINATED;
    }
}
