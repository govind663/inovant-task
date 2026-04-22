<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\AuditTrail;

class Payment extends Model
{
    use SoftDeletes, AuditTrail;

    /**
     * Mass Assignable Fields
     */
    protected $fillable = [
        'order_id',
        'transaction_id',
        'gateway',
        'amount',
        'status',
        'response',
    ];

    /**
     * Casts
     */
    protected $casts = [
        'response' => 'array',
    ];

    /**
     * Relationships
     */

    // Payment belongs to an order
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Helper Methods (Business Logic)
     */

    // Mark payment as success
    public function markAsSuccess(array $response = [])
    {
        $this->update([
            'status' => 'success',
            'response' => $response,
        ]);
    }

    // Mark payment as failed
    public function markAsFailed(array $response = [])
    {
        $this->update([
            'status' => 'failed',
            'response' => $response,
        ]);
    }
}