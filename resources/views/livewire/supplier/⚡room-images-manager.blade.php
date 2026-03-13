<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Room;
use App\Models\RoomImage;
use Illuminate\Support\Facades\Storage;

// RoomImagesManager
new class extends Component
{
    use WithFileUploads;

    public Room $room;

    public array $images = [];

    protected $rules = [
        'images.*' => 'image|max:2048',
    ];

    public function removeTempImage($index)
    {
        unset($this->images[$index]);
        $this->images = array_values($this->images);
    }

    public function upload()
    {
        $this->validate();

        foreach ($this->images as $index => $image) {

            $path = $image->store('rooms', 'public');

            RoomImage::create([
                'room_id' => $this->room->id,
                'path' => $path,
                'is_featured' => $this->room->images()->count() === 0 && $index === 0
            ]);
        }

        $this->reset('images');
    }

    public function setFeatured($imageId)
    {
        $this->room->images()->update(['is_featured' => false]);

        RoomImage::where('id', $imageId)
            ->update(['is_featured' => true]);
    }

    public function deleteImage($imageId)
    {
        $image = RoomImage::findOrFail($imageId);

        Storage::disk('public')->delete($image->path);

        $image->delete();
    }

    public function render()
    {
        //$roomImages = $this->room->images()->latest()->get();

        return $this->view([
            'ime' => 'Boris'
        ]);

        //return view('livewire.supplier.room-images-manager', compact('roomImages'));
    }
};
?>

<div class="space-y-6">

    <h2 class="text-white">Pozdrav {{ $ime }}</h2>

    <div>
        <input type="file" wire:model="images" multiple class="mb-4">
        @error('images.*') 
            <span class="text-red-500 text-sm">{{ $message }}</span> 
        @enderror
    </div>
    
    {{-- PREVIEW --}}{{--  
    @if($images)
        <div>
            <h3 class="font-semibold mb-2">Preview</h3>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                @foreach($images as $index => $image)
                    <div class="relative border rounded p-2">

                        <img src="{{ $image->temporaryUrl() }}"
                             class="w-full h-32 object-cover rounded">

                        <button wire:click="removeTempImage({{ $index }})"
                                class="absolute top-1 right-1 bg-red-600 text-white text-xs px-2 py-1 rounded">
                            X
                        </button>
                    </div>
                @endforeach
            </div>

            <button wire:click="upload"
                    class="mt-4 px-4 py-2 bg-blue-600 text-white rounded">
                Upload All
            </button>
        </div>
    @endif
--}}
    {{-- EXISTING IMAGES 
    <div>
        <h3 class="font-semibold mb-2">Uploaded Images</h3>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            @foreach($roomImages as $image)
                <div class="relative border rounded p-2">

                    <img src="{{ asset('storage/'.$image->path) }}"
                         class="w-full h-32 object-cover rounded">

                    @if($image->is_featured)
                        <span class="absolute top-1 left-1 bg-green-600 text-white text-xs px-2 py-1 rounded">
                            Featured
                        </span>
                    @endif

                    <div class="flex justify-between mt-2 text-sm">
                        <button wire:click="setFeatured({{ $image->id }})"
                                class="text-blue-600">
                            Set Featured
                        </button>

                        <button wire:click="deleteImage({{ $image->id }})"
                                class="text-red-600">
                            Delete
                        </button>
                    </div>

                </div>
            @endforeach
        </div>
    </div>--}}

</div>