<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskOccurrence extends Model
{
    protected $fillable = ['task_id', 'user_id', 'due_date', 'note', 'completed_at'];

    protected $casts = [
        'due_date' => 'date:Y-m-d',
        'completed_at' => 'datetime',
    ];

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
