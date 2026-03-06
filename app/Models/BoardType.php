<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BoardType extends Model
{
    protected $table = "board_types";

    protected $fillable = [
        'code',
        'name',
    ];

    public function rooms()
    {
        return $this->belongsToMany(Room::class, 'room_board_types')
            ->withPivot('price')
            ->withTimestamps();
    }
}
