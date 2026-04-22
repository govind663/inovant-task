<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\AuditTrail;

class Order extends Model
{
    use SoftDeletes, AuditTrail;

    /**
     * Mass Assignable Fields
     */
    protected $fillable = [
        'user_id',
        'total_amount',
        'is_paid',
        'is_processed',
        'status',
    ];

    /**
     * Casts
     */
    protected $casts = [
        'total_amount' => 'decimal:2',
        'is_paid' => 'boolean',
        'is_processed' => 'boolean',
    ];

    /**
     * Append attributes
     */
    protected $appends = ['items_count'];

    /**
     * Relationships
     */

    // Order belongs to a user
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // One order has many items
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    // One order has one payment
    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    /**
     * Scopes (clean querying)
     */

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Helper Methods (Business Logic)
     */

    // Mark order as paid
    public function markAsPaid()
    {
        $this->update([
            'is_paid' => true,
            'status' => 'paid',
        ]);
    }

    // Mark order as processed
    public function markAsProcessed()
    {
        $this->update([
            'is_processed' => true,
            'status' => 'completed',
        ]);
    }

    // Mark order as failed
    public function markAsFailed()
    {
        $this->update([
            'status' => 'failed',
        ]);
    }

    /**
     * Accessors
     */

    // Total items count
    public function getItemsCountAttribute()
    {
        return $this->items()->count();
    }
}