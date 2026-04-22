<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\AuditTrail;
use Illuminate\Support\Facades\DB;

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

    // One cart has many items
    public function items()
    {
        return $this->hasMany(CartItem::class);
    }

    /**
     * Business Logic
     */

    // Recalculate cart totals
    public function recalculate()
    {
        $this->total_items = $this->items()->sum('quantity');

        $this->total_amount = $this->items()->sum(
            DB::raw('price * quantity')
        );

        $this->last_activity_at = now();

        $this->save();
    }
}