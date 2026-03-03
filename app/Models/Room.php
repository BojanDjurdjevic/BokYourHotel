<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    protected $table = "rooms";
    protected $fillable = [
        'hotel_id',
        'name',
        'capacity',
        'price_per_night',
        'total_units',
    ];

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }
}
