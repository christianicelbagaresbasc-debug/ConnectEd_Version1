<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentProfile extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'student_number',
        'program',
        'year_level',
        'section',
        'contact_number',
        'address',
        'bio',
        'resume_document_id',
        'portfolio_url',
        'assigned_coc_id',
        'school_year',
        'has_active_placement',
        'consented_at',
        'privacy_notice_version',
    ];

    protected $casts = [
        'has_active_placement' => 'boolean',
        'consented_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function assignedCoc(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_coc_id');
    }

    public function resume(): BelongsTo
    {
        return $this->belongsTo(Document::class, 'resume_document_id');
    }

    public function companySuggestions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(CompanySuggestion::class);
    }

    public function moas(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Moa::class);
    }
}
