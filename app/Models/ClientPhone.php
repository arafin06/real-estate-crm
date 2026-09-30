<?php

namespace App\Models;

use App\Support\PhoneNumber;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientPhone extends Model
{
    protected $fillable = ['client_id', 'type', 'phone'];

    protected $appends = ['formatted', 'tel_link'];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Always store digits-only, regardless of how the value arrives.
     */
    protected function setPhoneAttribute(?string $value): void
    {
        $this->attributes['phone'] = PhoneNumber::normalize($value);
    }

    public function getFormattedAttribute(): ?string
    {
        return PhoneNumber::format($this->attributes['phone'] ?? null);
    }

    public function getTelLinkAttribute(): ?string
    {
        return PhoneNumber::telLink($this->attributes['phone'] ?? null);
    }
}
