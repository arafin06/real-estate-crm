<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'parent_id', 'deal_id', 'client_id',
        'title', 'description', 'due_date', 'priority', 'status',
        'is_recurring', 'recurrence_type', 'recurrence_interval',
        'recurrence_days', 'recurrence_end_date', 'recurring_parent_id',
    ];

    protected $casts = [
        'due_date' => 'date:Y-m-d',
        'recurrence_end_date' => 'date:Y-m-d',
        'recurrence_days' => 'array',
        'is_recurring' => 'boolean',
    ];

    protected $appends = ['is_overdue'];

    public function getIsOverdueAttribute(): bool
    {
        return in_array($this->status, ['incomplete', 'in_progress'], true)
            && $this->due_date->lt(now()->startOfDay());
    }

    // ─── Relationships ────────────────────────────────
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function deal(): BelongsTo
    {
        return $this->belongsTo(Deal::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'parent_id');
    }

    public function subtasks(): HasMany
    {
        return $this->hasMany(Task::class, 'parent_id')
            ->orderByRaw("FIELD(status, 'in_progress', 'incomplete', 'complete', 'closed')")
            ->orderByRaw("FIELD(priority, 'urgent', 'high', 'medium', 'low')");
    }

    public function occurrences(): HasMany
    {
        return $this->hasMany(TaskOccurrence::class)->orderByDesc('completed_at');
    }

    public function notes(): MorphMany
    {
        return $this->morphMany(Note::class, 'notable')->latest();
    }

    public function recurringInstances(): HasMany
    {
        return $this->hasMany(Task::class, 'recurring_parent_id');
    }

    // ─── Recurrence helpers ───────────────────────────
    public function nextDueDate(): ?Carbon
    {
        if (! $this->is_recurring || ! $this->recurrence_type) {
            return null;
        }

        $base = $this->due_date->copy();
        $interval = max(1, (int) $this->recurrence_interval);

        return match ($this->recurrence_type) {
            'daily' => $base->addDays($interval),
            'weekly' => $base->addWeeks($interval),
            'biweekly' => $base->addWeeks(2 * $interval),
            'monthly' => $base->addMonths($interval),
            'yearly' => $base->addYears($interval),
            default => null,
        };
    }

    public function shouldGenerateNext(): bool
    {
        if (! $this->is_recurring) {
            return false;
        }
        $next = $this->nextDueDate();
        if (! $next) {
            return false;
        }
        if ($this->recurrence_end_date && $next->gt($this->recurrence_end_date)) {
            return false;
        }

        return true;
    }
}
