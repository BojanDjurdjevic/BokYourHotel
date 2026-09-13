<x-layouts.dashboard>

    @include('supplier.hotels.setup._steps')

    <h1 class="text-xl font-bold mb-6">
        Hotel Information
    </h1>

    <form method="POST" action="{{ route('supplier.hotels.update', $hotel) }}">
        @csrf
        @method('PUT')
        <label class="block mb-4">Property star rating (not guest reviews)
            <select name="star_rating" class="block rounded-lg bg-gray-900"><option value="">Not rated</option>@foreach(range(1,5) as $star)<option value="{{ $star }}" @selected(old('star_rating',$hotel->star_rating) == $star)>{{ $star }} stars</option>@endforeach</select>
        </label>
        {{-- NAME --}}
        <div class="mb-4">
            <label class="block mb-1">Hotel Name</label>

            <input
                type="text"
                name="name"
                value="{{ old('name',$hotel->name) }}"
                class="border p-2 w-full rounded-lg bg-gray-900"
            >
        </div>

        {{-- CITY, COUNTRY --}}
        <div class="grid md:grid-cols-2 gap-6">

            <div>
                <label class="block text-sm mb-2">City</label>
                <input
                    type="text"
                    name="city"
                    class="w-full rounded-lg bg-gray-900 border-gray-700 p-2"
                    required
                    value="{{ old('city', $hotel->city) }}"
                />
            </div>

            <div>
                <label class="block text-sm mb-2">Country</label>
                <input
                    type="text"
                    name="country"
                    class="w-full rounded-lg bg-gray-900 border-gray-700 p-2"
                    required
                    value="{{ old('country', $hotel->country) }}"
                >
            </div>

        </div>

        {{-- ADDRESS --}}
        <div>
            <label class="block text-sm mb-2">Address</label>
            <input
                type="text"
                name="address"
                class="w-full rounded-lg bg-gray-900 border-gray-700 p-2"
                value="{{ old('address', $hotel->address) }}"
            >
        </div>

        {{-- DESCRIPTION --}}

        <div class="mb-4">
            <label>Description</label>

            <textarea
                name="description"
                class="p-2 w-full rounded-lg bg-gray-900"
            >
                {{ old('description',$hotel->description) }}
            </textarea>
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

        <button class="bg-blue-600 text-white px-4 py-2 rounded">
            Save
        </button>

    </form>

</x-layouts.dashboard>
