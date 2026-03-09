<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoomInventory extends Model
{
    protected $table = 'room_inventory';
    
    protected $fillable = [
        'room_id',
        'date',
        'available',
        'price'
    ];

    public function room()
    {
        return $this->belongsTo(Room::class);
    }
}
