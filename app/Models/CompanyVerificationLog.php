<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompanyVerificationLog extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'company_id',
        'from_status',
        'to_status',
        'remark',
        'actor_id',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public static function boot(): void
    {
        parent::boot();

        static::updating(function () {
            throw new \RuntimeException('Company verification logs are append-only and cannot be updated.');
        });

        static::deleting(function () {
            throw new \RuntimeException('Company verification logs are append-only and cannot be deleted.');
        });
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
