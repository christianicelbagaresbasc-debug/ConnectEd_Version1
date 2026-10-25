<?php

namespace App\Models;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'created_by',
        'assigned_to',
        'due_date',
        'priority',
        'status',
        'related_student_profile_id',
        'related_company_id',
        'completed_at',
    ];

    protected $casts = [
        'due_date' => 'date',
        'completed_at' => 'datetime',
        'priority' => TaskPriority::class,
        'status' => TaskStatus::class,
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function studentProfile(): BelongsTo
    {
        return $this->belongsTo(StudentProfile::class, 'related_student_profile_id');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'related_company_id');
    }

    public function isDone(): bool
    {
        return $this->status === TaskStatus::DONE;
    }

    public function isTodo(): bool
    {
        return $this->status === TaskStatus::TODO;
    }

    public function isInProgress(): bool
    {
        return $this->status === TaskStatus::IN_PROGRESS;
    }

    public function markDone(): void
    {
        $this->update([
            'status' => TaskStatus::DONE,
            'completed_at' => now(),
        ]);
    }

    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where(function ($q) use ($userId) {
            $q->where('assigned_to', $userId)
                ->orWhere('created_by', $userId);
        });
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', '!=', TaskStatus::DONE->value);
    }

    public function scopeDueSoon(Builder $query, int $days = 3): Builder
    {
        return $query->pending()
            ->whereNotNull('due_date')
            ->where('due_date', '<=', now()->addDays($days)->toDateString());
    }
}
