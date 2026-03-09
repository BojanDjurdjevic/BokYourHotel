<x-layouts.dashboard>

    @include('supplier.hotels.setup._steps')

    <h1 class="text-xl font-bold mb-6">
        Publish Hotel
    </h1>

    @if($hotel->canBePublished())

    <div class="bg-green-100 p-4 rounded mb-4">
        Hotel is ready to publish.
    </div>

    <form method="POST" action="{{ route('supplier.hotels.publish',$hotel) }}">
    @csrf

    <button class="bg-green-600 text-white px-6 py-3 rounded">
        Publish Hotel
    </button>

    </form>

    @else

    <div class="bg-yellow-100 p-4 rounded">

        Hotel setup is not complete.

        Completion: {{ $hotel->setupProgress() }}%

    </div>

    @endif

</x-layouts.dashboard>