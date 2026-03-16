<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Hotel;
use App\Models\HotelImage;
use Illuminate\Support\Facades\Storage;
use App\Traits\HandleImagesUpload;
use App\Services\HotelService;

// HotelImagesManager
new class extends Component
{
    
    use WithFileUploads, HandleImagesUpload;

    public Hotel $hotel;
    protected HotelService $service;

    public array $images = [];

    public string $num = '';
    public string $name = 'Bojan';

    protected $rules = [
        'images.*' => 'image|mimes:jpg,jpeg,png,webp|max:4096'
    ];

    public function boot(HotelService $service)
    {
        $this->service = $service;
    }

    public function mount(Hotel $hotel)
    {
        $this->hotel = $hotel;
        
    }

    public function removeTempImage($index)
    {
        unset($this->images[$index]);
        $this->images = array_values($this->images);
    }

    public function uploadImages()
    {
        $this->validate();

        /*
        foreach ($this->images as $index => $image)
        {
            $id = $this->hotel->id;
            $path = $this->uploadImage($image, "hotels/$id");
            //$path = $image->store('hotels', 'public');

            $count = $this->hotel->images()->count();

            HotelImage::create([
                'hotel_id' => $id,
                'path' => $path,
                'position' => $count,
                'is_featured' => $this->hotel->images()->count() === 0 && $index === 0,
            ]);                      
        }
        */

        $this->service->uploadImages($this->hotel, $this->images);

        $this->reset('images');
    }

    public function showNum()
    {
        $this->num = $this->num == '' ? 'Ćaos' : '';
    }

    public function reorderImages($order)
    {
        foreach ($order as $index => $id) {
            HotelImage::where('id', $id)
                ->update(['position' => $index]);
        }
    }
    
    public function setFeatured($imageId)
    {
        $this->hotel->images()->update(['is_featured' => false]);
        
        HotelImage::where('id', $imageId)->update(['is_featured' => true]);
    }

    public function deleteImage($imageId)
    {
        $image = HotelImage::findOrFail($imageId);

        Storage::disk('public')->delete($image->path);

        $image->delete();
    }

    public function render()
    {
        //$hotelImages = $this->hotel->images()->latest()->get();
    
        $hotelImages = $this->hotel
        ? $this->hotel->images()->orderBy('position')->get()
        : collect();
    
        return $this->view([
            'hotelImages' => $hotelImages,
            'hotel' => $this->hotel,
            'ime' => $this->name
        ]); 

        //return view('hotel-images-manager', compact('hotelImages'));
    }
};
?>

<div class="space-y-6">
    
    <h1 class="text-red-500">Pozdrav iz livewire</h1>

    {{-- File input --}}
    <div>
        <input type="file" wire:model="images" multiple class="mb-4 p-2 bg-gray-700 rounded-lg">

        @error('images.*') 
            <span class="text-red-500 text-sm">{{ $message }}</span> 
        @enderror
    </div>

    {{-- PREVIEW BEFORE UPLOAD --}}
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

            <span wire:loading wire:target="uploadImages">
                Uploading...
            </span>

            <button wire:click="uploadImages"
                    wire:loading.attr="disabled"
                    class="mt-4 px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-lg"
            >
                Upload All
            </button>
        </div>
    @endif


    {{-- EXISTING IMAGES --}}
    <div>
        <h3 class="font-semibold mb-2">Uploaded Images</h3>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4"
            x-data
            x-init="
                new Sortable($el,{
                    animation:150,
                    onEnd: function(){

                        let order=[...$el.children]
                            .map(el=>el.dataset.id)

                        $wire.reorderImages(order)
                    }
                })
            "
            class="grid grid-cols-2 md:grid-cols-4 gap-4"
        >
            @foreach($hotelImages as $image)
                <div 
                    data-id="{{ $image->id }}"
                    class="relative border rounded p-2 cursor-move"
                >

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
    </div>

    <p class="text-white">{{ $ime }}</p>
    <p class="text-white">{{ $num }}</p>
    <x-button
        variant="danger"
        wire:click="showNum"
    >
        Kaži ćao
    </x-button>

    <button wire:click="$set('name', 'Matteo')">
        Change name
    </button>

</div>

<script
    
></script>