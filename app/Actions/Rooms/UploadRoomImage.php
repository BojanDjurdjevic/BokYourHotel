<?php

namespace App\Actions\Rooms;

use App\Models\Room;
use App\Traits\HandleImagesUpload;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class UploadRoomImage
{
    use HandleImagesUpload;

    public function execute(Room $room, UploadedFile $image)
    {
        $path = $this->uploadImage($image, "rooms/{$room->id}");
        try {
            return $room->images()->create([
                'path' => $path,
                'is_featured' => ! $room->images()->where('is_featured', true)->exists(),
            ]);
        } catch (\Throwable $e) {
            Storage::disk('public')->delete($path);
            throw $e;
        }
    }
}
