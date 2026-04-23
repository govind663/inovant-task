<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\AuditTrail;
use Illuminate\Support\Carbon;

class Cart extends Model
{
    use SoftDeletes, AuditTrail;

    /**
     * Mass Assignable Fields
     */
    protected $fillable = [
        'user_id',
        'status',
        'total_amount',
        'total_items',
        'last_activity_at',
    ];

    /**
     * Casts
     */
    protected $casts = [
        'total_amount' => 'decimal:2',
        'total_items' => 'integer',
        'last_activity_at' => 'datetime',
    ];

    /**
     * =========================
     * Relationships
     * =========================
     */

    // Cart belongs to a user
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    // ✅ FIXED: explicit keys (important for stability)
    public function items()
    {
        return $this->hasMany(CartItem::class, 'cart_id', 'id');
    }

    /**
     * =========================
     * Scopes
     * =========================
     */

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeCheckedOut($query)
    {
        return $query->where('status', 'checked_out');
    }

    /**
     * =========================
     * Business Logic
     * =========================
     */

    public function recalculate()
    {
        $items = $this->items()->get();

        $this->total_items = $items->sum('quantity');

        $this->total_amount = $items->sum(function ($item) {
            return $item->price * $item->quantity;
        });

        $this->status = $this->total_items > 0 ? 'active' : 'empty';

        $this->last_activity_at = Carbon::now();

        $this->save();
    }

    /**
     * =========================
     * Helpers (🔥 Useful)
     * =========================
     */

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isCheckedOut(): bool
    {
        return $this->status === 'checked_out';
    }

    public function isEmpty(): bool
    {
        return $this->total_items === 0;
    }
}