<?php

namespace App\Models;

use App\Enums\VerificationItemStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CompanyVerificationItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'verification_requirement_id',
        'status',
        'document_id',
        'remark',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'status' => VerificationItemStatus::class,
        'reviewed_at' => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function requirement(): BelongsTo
    {
        return $this->belongsTo(VerificationRequirement::class, 'verification_requirement_id');
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function isAccepted(): bool
    {
        return $this->status === VerificationItemStatus::ACCEPTED;
    }
}
