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

    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function images()
    {
        return $this->hasMany(RoomImage::class);
    }

    public function featuredImage()
    {
        return $this->hasOne(RoomImage::class)
            ->where('is_featured', true);
    }

    public function roomType()
    {
        return $this->belongsTo(RoomType::class);
    }

    public function bedType()
    {
        return $this->belongsTo(BedType::class);
    }
    public function boardTypes()
    {
        return $this->belongsToMany(BoardType::class, 'room_board_types')
            ->withPivot('price')
            ->withTimestamps();
    }
}
