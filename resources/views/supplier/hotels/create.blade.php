<x-layouts.dashboard>

    <div class="max-w-3xl mx-auto">

        <h1 class="text-2xl font-bold mb-6">
        Add New Hotel
        </h1>

        <form
        method="POST"
        action="{{ route('supplier.hotels.store') }}"
        class="space-y-6"
        >
            @if ($errors)
                <p class="text-red-600">{{ $errors->first() }}</p>
            @endif

            @csrf

            <div>
                <label class="block text-sm mb-2">Hotel Name</label>
                <input
                    type="text"
                    name="name"
                    class="min-h-12 w-full rounded-xl border border-gray-700 bg-gray-800 px-3 py-3 text-base text-white focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-400/50"
                    required
                    value="{{ old('name') }}"
                />
            </div>

            <div class="grid md:grid-cols-2 gap-6">

                <div>
                    <label class="block text-sm mb-2">City</label>
                    <input
                        type="text"
                        name="city"
                        class="min-h-12 w-full rounded-xl border border-gray-700 bg-gray-800 px-3 py-3 text-base text-white focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-400/50"
                        required
                        value="{{ old('city') }}"
                    />
                </div>

                <div>
                    <label class="block text-sm mb-2">Country</label>
                    <input
                        type="text"
                        name="country"
                        class="min-h-12 w-full rounded-xl border border-gray-700 bg-gray-800 px-3 py-3 text-base text-white focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-400/50"
                        required
                        value="{{ old('country') }}"
                    >
                </div>

            </div>

            <div>
                <label class="block text-sm mb-2">Address</label>
                <input
                    type="text"
                    name="address"
                    class="min-h-12 w-full rounded-xl border border-gray-700 bg-gray-800 px-3 py-3 text-base text-white focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-400/50"
                    value="{{ old('address') }}"
                >
            </div>

            <div>
                <label class="block text-sm mb-2">Description</label>
                <textarea
                    name="description"
                    rows="4"
                    class="min-h-32 w-full rounded-xl border border-gray-700 bg-gray-800 px-3 py-3 text-base text-white focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-400/50"
                    value="{{ old('description') }}"
                ></textarea>
            </div>

            <div class="mb-6">
                <label class="block mb-2">Facilities</label>

                @foreach(config('hotel_facilities') as $facility)

                <label class="block">

                <input
                    type="checkbox"
                    name="facilities[]"
                    value="{{ $facility }}"
                    @checked(collect(old('facilities', []))->map(fn ($item) => \App\Support\FacilityLabel::key($item))->contains(\App\Support\FacilityLabel::key($facility)))
                >

                {{ \App\Support\FacilityLabel::label($facility) }}

                </label>

                @endforeach

            </div>

            <div class="flex justify-end gap-4">

                <a
                    href="{{ route('supplier.hotels.index') }}"
                    class="px-4 py-2 bg-gray-700 rounded-lg"
                >
                    Cancel
                </a>

                <button
                type="submit"
                class="px-5 py-2 bg-emerald-600 rounded-lg hover:bg-emerald-700"
                >
                Create Hotel
                </button>

            </div>
        </form>

    </div>

</x-layouts.dashboard>
