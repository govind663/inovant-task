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
        'last_activity_at' => 'datetime',
    ];

    /**
     * Relationships
     */

    // Cart belongs to a user
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // One cart has many items (with product eager load)
    public function items()
    {
        return $this->hasMany(CartItem::class)->with('product');
    }

    /**
     * Scopes
     */

    // Get active cart
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Business Logic
     */

    // Recalculate cart totals (SAFE + optimized)
    public function recalculate()
    {
        // Always fetch fresh data (avoid stale relation issue)
        $items = $this->items()->get();

        $this->total_items = $items->sum('quantity');

        $this->total_amount = $items->sum(function ($item) {
            return $item->price * $item->quantity;
        });

        $this->last_activity_at = Carbon::now();

        $this->save();
    }
}