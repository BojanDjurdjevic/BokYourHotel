<?php

namespace App\Models;

use App\Enums\BookingStatus;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $table = "bookings";

    protected $fillable = [
        'hotel_id',
        'user_id',

        'booking_number',

        'guest_name',
        'guest_email',
        'guest_phone',

        'check_in',
        'check_out',

        'subtotal',
        'discount',
        'tax',
        'total',

        'currency',

        'status',

        'locked_until',

        'notes',
    ];

    protected $casts = [
        'status' => BookingStatus::class,

        'check_in' => 'date',
        'check_out' => 'date',

        'locked_until' => 'datetime',
    ];

    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(BookingItem::class);
    }

    public function getNumberOfRoomsAttribute()
    {
        return $this->items->sum('quantity');
    }

    public function getNumberOfGuestsAttribute()
    {
        return $this->items->sum(function ($item) {
            return $item->adults + $item->children;
        });
    }

    public function isPending(): bool
    {
        return $this->status === BookingStatus::Pending;
    }

    public function isConfirmed(): bool
    {
        return $this->status === BookingStatus::Confirmed;
    }

    public function isCancelled(): bool
    {
        return $this->status === BookingStatus::Cancelled;
    }
}
