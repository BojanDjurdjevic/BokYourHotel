<x-layouts.dashboard>
    <div class="max-w-3xl mx-auto">

    <h1 class="text-2xl font-bold mb-6">
    Edit Hotel
    </h1>

    <form method="POST" action="{{ route('supplier.hotels.update', $hotel) }}" class="space-y-6">
    @csrf
    @method('PUT')

    <div class="mb-4">
    <label class="block mb-1">Hotel name</label>

    <input
        type="text"
        name="name"
        value="{{ old('name', $hotel->name) }}"
        class="min-h-12 w-full rounded-xl border border-gray-700 bg-gray-800 px-3 py-3 text-base text-white focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-400/50"
    >
    </div>

    <div class="mb-4">
    <label class="block mb-1">Hotel city</label>

    <input
        type="text"
        name="city"
        value="{{ old('city', $hotel->city) }}"
        class="min-h-12 w-full rounded-xl border border-gray-700 bg-gray-800 px-3 py-3 text-base text-white focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-400/50"
    >
    </div>

    <div class="mb-4">
    <label class="block mb-1">Hotel country</label>

    <input
        type="text"
        name="country"
        value="{{ old('country', $hotel->country) }}"
        class="min-h-12 w-full rounded-xl border border-gray-700 bg-gray-800 px-3 py-3 text-base text-white focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-400/50"
    >
    </div>

    <div class="mb-4">
    <label class="block mb-1">Hotel address</label>
    <input
        type="text"
        name="address"
        value="{{ old('address', $hotel->address) }}"
        class="min-h-12 w-full rounded-xl border border-gray-700 bg-gray-800 px-3 py-3 text-base text-white focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-400/50"
    >
    </div>

    <div class="mb-4">
    <label class="block mb-1">Description</label>

    <textarea
        name="description"
        class="min-h-32 w-full rounded-xl border border-gray-700 bg-gray-800 px-3 py-3 text-base text-white focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-400/50"
        rows="4"
    >{{ old('description', $hotel->description) }}</textarea>
    </div>

    <div class="mb-6">
        <label class="block mb-2">Facilities</label>

        @foreach(config('hotel_facilities') as $facility)

        <label class="block">

        <input
            type="checkbox"
            name="facilities[]"
            value="{{ $facility }}"
            @checked(collect($hotel->facilities ?? [])->map(fn ($item) => \App\Support\FacilityLabel::key($item))->contains(\App\Support\FacilityLabel::key($facility)))
        >

        {{ \App\Support\FacilityLabel::label($facility) }}

        </label>

        @endforeach

    </div>

    <a
        href="{{ route('supplier.hotels.index') }}"
        class="px-4 py-2 m-2 bg-gray-700 rounded-lg"
        >
        Cancel
    </a>

    <x-button class="primary">
        Save Changes
    </x-button>

    </form>

    </div>
</x-layouts.dashboard>
