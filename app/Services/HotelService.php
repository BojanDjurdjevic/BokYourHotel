<?php

namespace App\Services;

use App\Actions\Hotels\SetHotelInventory;
use App\Actions\Hotels\UploadHotelImage;

class HotelService {
    public function uploadImages($hotel, $images)
    {
        $count = $hotel->images()->count();

        foreach ($images as $index => $image) {
            app(UploadHotelImage::class)->execute(
                $hotel,
                $image,
                $count + $index, 
                $count === 0 && $index === 0
            );
        }
    }

    public function createInventory($inventory, $room_id)
    {
        app(SetHotelInventory::class)->execute($inventory, $room_id);
    }
}