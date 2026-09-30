<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id', 'name', 'email', 'type', 'source',
        'budget_min', 'budget_max', 'preferred_contact', 'timeline', 'pre_approval_amount',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function notes(): MorphMany
    {
        return $this->morphMany(Note::class, 'notable')->latest();
    }

    public function phones(): HasMany
    {
        return $this->hasMany(ClientPhone::class);
    }

    public function deals(): HasMany
    {
        return $this->hasMany(Deal::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class)->orderBy('due_date');
    }
}
