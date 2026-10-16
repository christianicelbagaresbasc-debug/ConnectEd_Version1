<?php

namespace App\Models;

use App\Enums\MoaNoteType;
use App\Enums\MoaRiskLevel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class MoaNote extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'moa_id',
        'author_id',
        'type',
        'risk_level',
        'body',
        'resolved_at',
        'resolved_by',
    ];

    protected $casts = [
        'type' => MoaNoteType::class,
        'risk_level' => MoaRiskLevel::class,
        'resolved_at' => 'datetime',
    ];

    public function moa(): BelongsTo
    {
        return $this->belongsTo(Moa::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function isResolved(): bool
    {
        return !is_null($this->resolved_at);
    }

    public function isLegal(): bool
    {
        return in_array($this->type, [MoaNoteType::LEGAL_REMARK, MoaNoteType::REVISION]);
    }
}
