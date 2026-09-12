<?php

namespace App\Models;

use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'booking_id', 'amount', 'currency', 'status', 'reference', 'attempt',
        'paid_at', 'failed_at', 'refunded_at', 'refund_reference',
    ];

    protected $casts = [
        'status' => PaymentStatus::class,
        'amount' => 'decimal:2',
        'attempt' => 'integer',
        'paid_at' => 'datetime',
        'failed_at' => 'datetime',
        'refunded_at' => 'datetime',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
}
