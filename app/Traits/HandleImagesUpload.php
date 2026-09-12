<?php

namespace App\Traits;

use Illuminate\Container\Attributes\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

trait HandleImagesUpload {
    public function uploadImage(UploadedFile $request, string $path)
    {
        abort_unless(preg_match('#^(hotels|rooms)/[0-9]+$#', $path), 422);
        validator(['image' => $request], ['image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096', 'dimensions:max_width=6000,max_height=6000']])->validate();
        $dimensions = getimagesize($request->getRealPath());
        if ($dimensions[0] * $dimensions[1] > 12000000) {
            throw \Illuminate\Validation\ValidationException::withMessages(['image' => 'Images may contain at most 12 megapixels.']);
        }
        /*
        $avatar = Auth::user()->avatar;
        if($avatar !== null) {
            File::delete("storage/images/avatars/$avatar"); // ovde ide puna putanja
        } */

        // kompresija:

        $name = (string) \Illuminate\Support\Str::uuid().'.webp';
        $file = $request; // uzimamo naš fajl

        $gd = new Driver(); // kupimo novi GD driver
        $manager = new ImageManager($gd); // uzimamo iz bibl intervention/image Manager

        $image = $manager->read($file)->scaleDown(width: 1200)->toWebp(85); // prepakujemo u Webp

        if (! Storage::disk('public')->put("$path/$name", (string) $image)) {
            throw new \RuntimeException('Image storage write failed.');
        }

        return "$path/$name"; 
    }
}
