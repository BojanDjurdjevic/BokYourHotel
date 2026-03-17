<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Facility extends Model
{
    protected $table = "facilities";

    protected $fillable = [
        "name",
        "icon",
        "category",
    ] ;

    public function rooms()
    {
        return $this->belongsToMany(Room::class);
    }
}
