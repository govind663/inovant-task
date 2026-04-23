<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\AuditTrail;

class CartItem extends Model
{
    use SoftDeletes, AuditTrail;

    /**
     * Mass Assignable Fields
     */
    protected $fillable = [
        'cart_id',
        'product_id',
        'quantity',
        'price',
    ];

    /**
     * Casts
     */
    protected $casts = [
        'price' => 'decimal:2',
        'quantity' => 'integer',
    ];

    /**
     * Appended attributes
     */
    protected $appends = ['total_price'];

    /**
     * =========================
     * Relationships
     * =========================
     */

    // ✅ FIXED: explicit FK mapping
    public function cart()
    {
        return $this->belongsTo(Cart::class, 'cart_id', 'id');
    }

    // ✅ FIXED: explicit FK mapping
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    /**
     * =========================
     * Accessors
     * =========================
     */

    public function getTotalPriceAttribute(): float
    {
        return (float) $this->price * $this->quantity;
    }

    /**
     * =========================
     * Helpers
     * =========================
     */

    public function increaseQuantity(int $qty = 1): void
    {
        $this->increment('quantity', $qty);
    }

    public function decreaseQuantity(int $qty = 1): void
    {
        $this->decrement('quantity', $qty);
    }

    /**
     * Optional: set quantity safely
     */
    public function setQuantity(int $qty): void
    {
        $this->update(['quantity' => max(1, $qty)]);
    }
}