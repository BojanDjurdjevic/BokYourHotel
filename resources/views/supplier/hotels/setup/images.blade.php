<x-layouts.dashboard>

    @include('supplier.hotels.setup._steps')

    <h1 class="text-xl font-bold mb-6">
        Hotel Images
    </h1>
    

    <livewire:supplier.hotel-images-manager :hotel="$hotel" />

</x-layouts.dashboard>