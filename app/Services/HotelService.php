<?php

namespace App\Services;

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
}