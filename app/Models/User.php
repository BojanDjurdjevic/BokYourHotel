<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

  
    protected $hidden = [
        'password',
        'remember_token',
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
            'password' => 'hashed',
        ];
    }

    const ROLE_SUPERADMIN = 'superadmin';
    const ROLE_ADMIN = 'admin';
    const ROLE_SUPPLIER = 'supplier';
    const ROLE_USER = 'user';

    public function scopeSuppliers($query)
    {
        return $query->where('role', 'supplier');
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_SUPERADMIN || $this->role === self::ROLE_ADMIN;
    }

    public function isSupplier(): bool
    {
        return $this->role === self::ROLE_SUPPLIER;
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === self::ROLE_SUPERADMIN;
    }

    public function hotels()
    {
        return $this->hasMany(Hotel::class, 'supplier_id');
    }
}
