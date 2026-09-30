<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

#[Fillable(['name', 'email', 'password', 'role', 'phone', 'avatar', 'subscription_status', 'stripe_customer_id', 'stripe_subscription_id'])]
#[Hidden(['password', 'remember_token', 'stripe_customer_id', 'stripe_subscription_id'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $appends = ['avatar_url'];

    public function getAvatarUrlAttribute(): ?string
    {
        return $this->avatar ? asset('storage/'.$this->avatar) : null;
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function isAgent(): bool
    {
        return $this->role === 'agent';
    }

    public function isClient(): bool
    {
        return $this->role === 'client';
    }
}
