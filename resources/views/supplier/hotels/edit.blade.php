<x-layouts.dashboard>
    <div class="max-w-3xl mx-auto">

    <h1 class="text-2xl font-bold mb-6">
    Edit Hotel
    </h1>

    <form method="POST" action="{{ route('supplier.hotels.update', $hotel) }}">
    @csrf
    @method('PUT')

    <div class="mb-4">
    <label class="block mb-1">Hotel Name</label>

    <input
    type="text"
    name="name"
    value="{{ old('name', $hotel->name) }}"
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

    <button class="bg-blue-600 text-white px-4 py-2 rounded">
    Save Changes
    </button>

    </form>

    </div>
</x-layouts.dashboard>