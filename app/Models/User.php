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
     * Mass Assignable Fields
     */
    protected $fillable = [
        'name',
        'email',
        'password',
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
    ];

    /**
     * Relationships
     */

    // One user has many products
    public function products()
    {
        return $this->hasMany(Product::class);
    }

    // One user has many carts
    public function carts()
    {
        return $this->hasMany(Cart::class);
    }

    // One user has many orders
    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}