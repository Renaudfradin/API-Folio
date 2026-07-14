<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements FilamentUser
{
    use HasFactory;
    use Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'linkedin_profile_id',
        'linkedin_url',
        'linkedin_headline',
        'linkedin_synced_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function canAccessPanel(Panel $panel): bool
    {
        return str_ends_with($this->email, '@gmail.com') && $this->hasVerifiedEmail();
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'linkedin_synced_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isDemo(): bool
    {
        return $this->role === 'demo';
    }

    public function isUser(): bool
    {
        return $this->role === 'user';
    }

    public function isPlatform(): bool
    {
        return $this->role === 'platform';
    }

    public function linkedinConnection(): HasOne
    {
        return $this->hasOne(LinkedinConnection::class);
    }

    public function linkedinProfileStats(): HasMany
    {
        return $this->hasMany(LinkedinProfileStat::class);
    }
}
