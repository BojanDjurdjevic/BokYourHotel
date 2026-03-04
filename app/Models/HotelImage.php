<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HotelImage extends Model
{
    protected $table = "hotel_images";

    protected $fillable = [
        'hotel_id',
        'path',
        'is_featured',
    ];

    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }
}
