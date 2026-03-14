<x-layouts.dashboard>

    @include('supplier.hotels.setup._steps')

    <h1 class="text-xl font-bold mb-6">
        Publish Hotel
    </h1>

    @if (!$hotel->published)

        @if($hotel->canBePublished())

        <div class="bg-emerald-700 p-4 rounded mb-4">
            Hotel is ready to publish.
        </div>

        <form method="POST" action="{{ route('supplier.hotels.setup.publishHotel', $hotel) }}">
            @csrf
            @method('PUT')
            <x-button variant="primary">
                Publish Hotel
            </x-button>

        </form>

        @else

        <div class="bg-yellow-400 text-red-700 p-4 rounded">

            <b>Hotel setup is not complete.

            Completion: {{ $hotel->setupProgress() }}%</b>

        </div>

        @endif

        @else
        <div class="bg-emerald-700 p-4 rounded mb-4">
            Hotel is already published.
        </div>
    @endif

</x-layouts.dashboard>