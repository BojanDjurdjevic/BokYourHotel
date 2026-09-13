<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    protected $casts = ['archived_at' => 'datetime'];
    protected $table = "rooms";
    protected $fillable = [
        'hotel_id',
        'room_type_id',
        'bed_type_id',
        'name',
        'capacity',
        'price_per_night',
        'total_units',
    ];

    /*
    protected $casts = [
        'facilities' => 'array'
    ]; */

    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }

    public function bookings()
    {
        return $this->hasManyThrough(Booking::class, BookingItem::class, 'room_id', 'id', 'id', 'booking_id');
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
            ->whereNull('board_types.archived_at')
            ->withPivot('price')
            ->withTimestamps();
    }

    public function inventories()
    {
        return $this->hasMany(RoomInventory::class);
    }

    // From room & facilities table:

    public function facilities()
    {
        return $this->belongsToMany(Facility::class);
    }
}
