<?php

namespace App\Models;

use App\Support\Levels;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable([
    'name', 'email', 'password', 'letter', 'role', 'tier', 'level', 'level_name',
    'points', 'rating', 'jobs_text', 'is_available', 'is_new', 'balance',
    'withdrawable', 'pending', 'jobs_done', 'jobs_clean', 'speed_badge',
    'streak', 'referral_code',
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    use Notifiable;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_available' => 'boolean',
            'is_new' => 'boolean',
            'speed_badge' => 'boolean',
            'rating' => 'decimal:1',
        ];
    }

    public function skills(): HasMany
    {
        return $this->hasMany(Skill::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function gigs(): HasMany
    {
        return $this->hasMany(Service::class, 'seller_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'worker_id');
    }

    public function progress(): array
    {
        return Levels::progress($this);
    }

    public function tierMeta(): array
    {
        return config('hadthiqa.tiers.'.$this->tier)
            ?? config('hadthiqa.tiers.pro');
    }
}
