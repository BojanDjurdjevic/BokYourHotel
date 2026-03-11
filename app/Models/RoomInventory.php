<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoomInventory extends Model
{
    protected $table = 'room_inventories';
    
    protected $fillable = [
        'room_id',
        'date',
        'available',
        'price'
    ];

    protected $casts = [
        'date' => 'date',
        'price' => 'decimal:2'
    ];

    public function room()
    {
        return $this->belongsTo(Room::class);
    }
}
