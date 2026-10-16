<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class MoaVersion extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'moa_id',
        'version_no',
        'document_id',
        'uploaded_by',
        'note',
    ];

    public function moa(): BelongsTo
    {
        return $this->belongsTo(Moa::class);
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
