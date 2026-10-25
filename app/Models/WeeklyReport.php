<?php

namespace App\Models;

use App\Enums\WeeklyReportStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class WeeklyReport extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'placement_id',
        'week_start',
        'document_id',
        'status',
        'reviewed_by',
        'remark',
    ];

    protected $casts = [
        'week_start' => 'date',
        'status' => WeeklyReportStatus::class,
    ];

    public function placement(): BelongsTo
    {
        return $this->belongsTo(Placement::class);
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function isSubmitted(): bool
    {
        return $this->status === WeeklyReportStatus::SUBMITTED;
    }

    public function isReviewed(): bool
    {
        return $this->status === WeeklyReportStatus::REVIEWED;
    }

    public function isFlagged(): bool
    {
        return $this->status === WeeklyReportStatus::FLAGGED;
    }
}
