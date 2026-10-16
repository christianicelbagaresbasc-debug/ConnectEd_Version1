<?php

namespace App\Models;

use App\Enums\CompanySuggestionStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CompanySuggestion extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'student_profile_id',
        'company_name',
        'contact_details',
        'notes',
        'status',
        'company_id',
        'handled_by',
        'handled_at',
    ];

    protected $casts = [
        'status' => CompanySuggestionStatus::class,
        'handled_at' => 'datetime',
    ];

    public function studentProfile(): BelongsTo
    {
        return $this->belongsTo(StudentProfile::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function handler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handled_by');
    }
}
