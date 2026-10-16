<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Document extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'owner_user_id',
        'documentable_type',
        'documentable_id',
        'category',
        'original_name',
        'storage_key',
        'stored_path',
        'disk',
        'mime_type',
        'size_bytes',
        'checksum_sha256',
        'validation_status',
        'validation_remark',
        'uploaded_by',
        'upload_batch_id',
        'version_group_id',
        'version_no',
        'is_current',
        'review_status',
        'review_remark',
        'reviewed_by',
        'reviewed_at',
        'school_year',
        'expires_at',
    ];

    protected $casts = [
        'size_bytes' => 'integer',
        'version_no' => 'integer',
        'is_current' => 'boolean',
        'reviewed_at' => 'datetime',
        'expires_at' => 'date',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(DocumentBatch::class, 'upload_batch_id');
    }

    public function documentable(): MorphTo
    {
        return $this->morphTo();
    }

    public function scopeCurrent(Builder $query): Builder
    {
        return $query->where('is_current', true);
    }

    public function scopeValidated(Builder $query): Builder
    {
        return $query->where('validation_status', 'ok');
    }

    public function scopeCategory(Builder $query, string $category): Builder
    {
        return $query->where('category', $category);
    }

    public function isValid(): bool
    {
        return $this->validation_status === 'ok';
    }
}
