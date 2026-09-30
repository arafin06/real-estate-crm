<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Property extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id', 'title', 'description', 'type', 'listing_type',
        'price', 'address', 'city', 'state', 'zip',
        'bedrooms', 'bathrooms', 'area_sqft', 'status',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'bedrooms' => 'integer',
        'bathrooms' => 'integer',
        'area_sqft' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(PropertyImage::class);
    }

    public function primaryImage(): HasOne
    {
        return $this->hasOne(PropertyImage::class)->where('is_primary', true);
    }
}
