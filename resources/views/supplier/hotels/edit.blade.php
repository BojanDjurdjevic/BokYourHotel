<x-layouts.dashboard>
    <div class="max-w-3xl mx-auto">

    <h1 class="text-2xl font-bold mb-6">
    Edit Hotel
    </h1>

    <form method="POST" action="{{ route('supplier.hotels.update', $hotel) }}">
    @csrf
    @method('PUT')

    <div class="mb-4">
    <label class="block mb-1">Hotel name</label>

    <input
        type="text"
        name="name"
        value="{{ old('name', $hotel->name) }}"
        class="w-full border rounded p-2"
    >
    </div>

    <div class="mb-4">
    <label class="block mb-1">Hotel city</label>

    <input
        type="text"
        name="city"
        value="{{ old('city', $hotel->city) }}"
        class="w-full border rounded p-2"
    >
    </div>

    <div class="mb-4">
    <label class="block mb-1">Hotel country</label>

    <input
        type="text"
        name="country"
        value="{{ old('country', $hotel->country) }}"
        class="w-full border rounded p-2"
    >
    </div>

    <div class="mb-4">
    <label class="block mb-1">Hotel address</label>
    <input
        type="text"
        name="address"
        value="{{ old('address', $hotel->address) }}"
        class="w-full border rounded p-2"
    >
    </div>

    <div class="mb-4">
    <label class="block mb-1">Description</label>

    <textarea
        name="description"
        class="w-full border rounded p-2"
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
            @checked(in_array($facility, $hotel->facilities ?? []))
        >

        {{ ucfirst(str_replace('_',' ',$facility)) }}

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