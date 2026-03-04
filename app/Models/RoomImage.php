<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoomImage extends Model
{
    protected $table = "room_images";

    protected $fillable = [
        'room_id',
        'path',
        'is_featured',
    ];

    public function room()
    {
        return $this->belongsTo(Room::class);
    }
}
