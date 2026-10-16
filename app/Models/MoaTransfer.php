<?php

namespace App\Models;

use App\Enums\MoaCustodianParty;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MoaTransfer extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'moa_id',
        'from_party',
        'to_party',
        'from_user_id',
        'to_user_id',
        'actor_id',
        'note',
        'created_at',
    ];

    protected $casts = [
        'from_party' => MoaCustodianParty::class,
        'to_party' => MoaCustodianParty::class,
        'created_at' => 'datetime',
    ];

    /**
     * Enforce append-only integrity: block updates and deletes at runtime
     */
    protected static function booted(): void
    {
        static::updating(function () {
            throw new \RuntimeException('MOA custody transfer logs are append-only and cannot be modified.');
        });

        static::deleting(function () {
            throw new \RuntimeException('MOA custody transfer logs are permanent audit records and cannot be deleted.');
        });
    }

    public function moa(): BelongsTo
    {
        return $this->belongsTo(Moa::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    public function fromUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'from_user_id');
    }

    public function toUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'to_user_id');
    }
}
