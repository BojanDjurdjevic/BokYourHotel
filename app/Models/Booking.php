<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $table = "bookkings";

    protected $fillable = [
        'room_id',
        'user_id',
        'check_in',
        'check_out',
        'total_price',
        'status',
        'locked_until',
    ];

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
