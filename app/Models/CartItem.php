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
     * Append custom attributes
     */
    protected $appends = ['total_price'];

    /**
     * Relationships
     */

    // Item belongs to cart
    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }

    // Item belongs to product
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Accessors
     */

    // Total price for this item
    public function getTotalPriceAttribute()
    {
        return $this->price * $this->quantity;
    }
}