<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Gate;
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
        'images' => 'required|array|max:10',
        'images.*' => 'required|image|mimes:jpg,jpeg,png,webp|max:4096',
    ];

    public function removeTempImage($index)
    {
        Gate::authorize('update', $this->room->hotel);
        unset($this->images[$index]);
        $this->images = array_values($this->images);
    }

    public function upload()
    {
        Gate::authorize('update', $this->room->hotel);
        $this->validate();
        $key = 'room-images:'.auth()->id();
        abort_if(\Illuminate\Support\Facades\RateLimiter::tooManyAttempts($key, 10), 429);
        \Illuminate\Support\Facades\RateLimiter::hit($key, 60);

        foreach ($this->images as $index => $image) {

            app(\App\Actions\Rooms\UploadRoomImage::class)->execute($this->room, $image);
        }

        $this->reset('images');
    }

    public function setFeatured($imageId)
    {
        Gate::authorize('update', $this->room->hotel);
        $this->room->images()->findOrFail($imageId);
        $this->room->images()->update(['is_featured' => false]);

        $this->room->images()->where('id', $imageId)
            ->update(['is_featured' => true]);
    }

    public function deleteImage($imageId)
    {
        Gate::authorize('update', $this->room->hotel);
        $image = $this->room->images()->findOrFail($imageId);

        abort_unless(str_starts_with($image->path, "rooms/{$this->room->id}/") && ! str_contains($image->path, '..'), 403);

        if (Storage::disk('public')->exists($image->path) && ! Storage::disk('public')->delete($image->path)) {
            throw new \RuntimeException('Could not delete image file.');
        }

        $image->delete();
    }

    public function render()
    {
        Gate::authorize('update', $this->room->hotel);
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
