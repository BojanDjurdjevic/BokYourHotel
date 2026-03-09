<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Hotel extends Model
{
    protected $table = "hotels";

    protected $fillable = [
        'name', 
        'city', 
        'country',
        'address', 
        'description',
        'facilities',
    ];

    protected $casts = [
        'facilities' => 'array',
    ];

    public function rooms()
    {
        return $this->hasMany(Room::class);
    }

    public function images()
    {
        return $this->hasMany(HotelImage::class);
    }

    public function hasFacilities(): bool
    {
        return is_array($this->facilities) && count($this->facilities) > 0;
    }

    public function hasFacility(string $facility): bool
    {
        return in_array($facility, $this->facilities ?? []);
    }

    public function featuredImage()
    {
        return $this->hasOne(HotelImage::class)
            ->where('is_featured', true);
    }

    
    public function supplier()
    {
        return $this->belongsTo(User::class, 'supplier_id');
    }

    //helpers

    public function hasRooms(): bool
    {
        return $this->rooms()->exists();
    }

    public function hasImages(): bool
    {
        return $this->images()->exists();
    }

    public function setupChecklist(): array
    {
        return [

            'info' => !empty($this->name),

            'rooms' => $this->rooms()->exists(),

            'inventory' => $this->rooms()
                ->whereHas('inventories')
                ->exists(),

            'images' => $this->images()->exists(),

        ];
    }

    public function setupProgress(): int
    {
        $steps = $this->setupChecklist();

        $completed = collect($steps)
            ->filter()
            ->count();

        return intval(($completed / count($steps)) * 100);
    }

    public function canBePublished(): bool
    {
        return $this->setupProgress() === 100;
    }
}
