<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_stub',
        'is_deceased',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $attributes = [
        'role' => 'member',
        'is_stub' => false,
        'is_deceased' => false,
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'is_stub'           => 'boolean',
            'is_deceased'       => 'boolean',
        ];
    }

    public function profile(): HasOne
    {
        return $this->hasOne(Profile::class);
    }

    public function accessRequestsSent(): HasMany
    {
        return $this->hasMany(AccessRequest::class, 'requester_id');
    }

    public function accessRequestsReceived(): HasMany
    {
        return $this->hasMany(AccessRequest::class, 'target_id');
    }

    public function events(): HasMany
    {
        return $this->hasMany(Event::class, 'creator_id');
    }

    public function eventCommittees(): HasMany
    {
        return $this->hasMany(EventCommittee::class);
    }

    public function rsvps(): HasMany
    {
        return $this->hasMany(Rsvp::class);
    }

    public function financialContributions(): HasMany
    {
        return $this->hasMany(FinancialContribution::class, 'contributor_id');
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->isSuperAdmin();
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isEventCommitteeMember(): bool
    {
        return $this->eventCommittees()->exists();
    }
}
