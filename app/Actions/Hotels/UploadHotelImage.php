<?php

namespace App\Actions\Hotels;

use App\Models\HotelImage;
use App\Traits\HandleImagesUpload;
use Livewire\WithFileUploads;

class UploadHotelImage {
    use HandleImagesUpload, WithFileUploads ;

    public function execute($hotel, $image, $position, $isFeatured = false)
    {
        $id = $hotel->id;
        $path = $this->uploadImage($image, "hotels/$id");


        return HotelImage::create([
                    'hotel_id' => $id,
                    'path' => $path,
                    'position' => $position,
                    'is_featured' => $isFeatured,
                ]);
    }
}