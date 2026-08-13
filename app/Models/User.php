<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
        'active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'active' => 'boolean',
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        // return $this->active && in_array($this->role, ['admin', 'barber', 'cashier']);
        return true;
    }

    public function barber()
    {
        return $this->hasOne(Barber::class);
    }

    public function sales()
    {
        return $this->hasMany(Sale::class, 'createdBy');
    }

    public function cashRegisters()
    {
        return $this->hasMany(CashRegister::class, 'openedBy');
    }

    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class, 'userId');
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isBarber(): bool
    {
        return $this->role === 'barber';
    }

    public function isCashier(): bool
    {
        return $this->role === 'cashier';
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        $initials = Str::initials($this->name, true);

        return Str::length($initials) > 1
            ? Str::substr($initials, 0, 1).Str::substr($initials, -1)
            : $initials;
    }
}
