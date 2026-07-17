<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingItem extends Model
{
    protected $fillable = [
        'booking_id', 'room_id', 'board_type_id',
        'room_name', 'board_name', 'quantity',
        'adults', 'children', 'price_per_night',
        'subtotal', 'nights', 'check_in', 
        'check_out', 'currency'
    ];

    public function booking() : BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function boardType(): BelongsTo 
    {
        return $this->belongsTo(BoardType::class);
    }

    //helpers:
    public function getGuestsAttribute(): int
    {
        return $this->adults + $this->children;
    }

    public function getPeriodAttribute(): string
    {
        return "{$this->check_in->format('d.m.Y')} - {$this->check_out->format('d.m.Y')}";
    }

    public function getAveragePriceAttribute(): float
    {
        return $this->subtotal / max($this->nights, 1);
    }

    protected $casts = [
        'check_in' => 'date',
        'check_out' => 'date',
        'price_per_night' => 'decimal:2',
        'subtotal' => 'decimal:2'
    ];
}
