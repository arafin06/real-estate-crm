<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Deal extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id', 'property_id', 'client_id', 'title', 'stage',
        'deal_value', 'commission_rate', 'commission_amount',
        'expected_close_date', 'closed_at', 'notes', 'lost_reason',
    ];

    protected $casts = [
        'deal_value' => 'decimal:2',
        'commission_rate' => 'decimal:2',
        'commission_amount' => 'decimal:2',
        'expected_close_date' => 'date:Y-m-d',
        'closed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Timeline notes. Deliberately NOT called notes(): deals already have a
     * 'notes' text column, and a relation of the same name shadows it in
     * toArray(), silently dropping the written notes from every response.
     */
    public function noteEntries(): MorphMany
    {
        return $this->morphMany(Note::class, 'notable')->latest();
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(DealActivity::class)->latest();
    }
}
