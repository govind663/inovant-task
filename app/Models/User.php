<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\AuditTrail;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes, AuditTrail, HasApiTokens;

    /**
     * Role Constants
     */
    public const ROLE_USER = 'user';
    public const ROLE_ADMIN = 'admin';

    /**
     * Mass Assignable Fields
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_admin', // ✅ IMPORTANT
    ];

    /**
     * Default values
     */
    protected $attributes = [
        'role' => self::ROLE_USER,
        'is_admin' => false, // ✅ default
    ];

    /**
     * Hidden Fields
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Casts
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_admin' => 'boolean', // ✅ IMPORTANT
    ];

    /**
     * =========================
     * 🔐 Role Helpers
     * =========================
     */

    public function isAdmin(): bool
    {
        return $this->is_admin || $this->role === self::ROLE_ADMIN;
    }

    public function isUser(): bool
    {
        return !$this->isAdmin();
    }

    /**
     * Scope: only admins
     */
    public function scopeAdmins($query)
    {
        return $query->where(function ($q) {
            $q->where('role', self::ROLE_ADMIN)
              ->orWhere('is_admin', true);
        });
    }

    /**
     * Scope: only users
     */
    public function scopeUsers($query)
    {
        return $query->where(function ($q) {
            $q->where('role', self::ROLE_USER)
              ->where('is_admin', false);
        });
    }

    /**
     * =========================
     * Relationships
     * =========================
     */

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function carts()
    {
        return $this->hasMany(Cart::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}