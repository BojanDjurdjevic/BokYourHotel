<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Hotel extends Model
{
    protected $table = "hotels";

    protected $filable = [
        'name', 'city',
        'address', 'description',
    ];
}
