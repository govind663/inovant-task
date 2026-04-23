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

        // Razorpay fields
        'razorpay_order_id',
        'razorpay_payment_id',
        'razorpay_signature',

        // fallback / legacy
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
        'amount' => 'decimal:2',
        'response' => 'array',
    ];

    /**
     * =========================
     * Relationships
     * =========================
     */

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * =========================
     * Business Logic
     * =========================
     */

    public function markAsSuccess(array $response = [])
    {
        $this->update([
            'status' => 'success',
            'response' => $response,
        ]);

        if ($this->order) {
            $this->order->update([
                'is_paid' => true,
                'status' => 'paid'
            ]);
        }
    }

    public function markAsFailed(array $response = [])
    {
        $this->update([
            'status' => 'failed',
            'response' => $response,
        ]);

        if ($this->order) {
            $this->order->update([
                'is_paid' => false,
                'status' => 'failed'
            ]);
        }
    }

    /**
     * =========================
     * Helpers
     * =========================
     */

    public function isSuccess(): bool
    {
        return $this->status === 'success';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }
}