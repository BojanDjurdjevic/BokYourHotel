<x-layouts.dashboard>

    @include('supplier.hotels.setup._steps')

    <h1 class="text-xl font-bold mb-6">
        Hotel Images
    </h1>
    {{--  
    <form
        method="POST"
        action="#"
        enctype="multipart/form-data"
    >
    @csrf

    <input type="file" name="images[]" multiple>

    <button class="bg-blue-600 text-white px-4 py-2 rounded mt-4">
        Upload
    </button>

    </form>
    --}}

    <livewire:hotel-images-manager/>

</x-layouts.dashboard>