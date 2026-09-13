<x-layouts.dashboard>
    <div class="mx-auto max-w-3xl">
        <h1 class="mb-6 text-2xl font-bold">Add room — {{ $hotel->name }}</h1>

        <form method="POST" action="{{ route('supplier.hotels.rooms.store', $hotel) }}" class="space-y-6">
            @csrf
            <x-input-error :messages="$errors->all()" />

            <div>
                <label for="room-name" class="mb-2 block text-sm font-medium">Room name</label>
                <input id="room-name" type="text" name="name" value="{{ old('name') }}" required
                       class="min-h-12 w-full rounded-xl border border-gray-700 bg-gray-800 px-3 py-3 text-base text-white focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-400/50">
            </div>

            <div class="grid gap-6 md:grid-cols-2">
                <div>
                    <label for="room-type" class="mb-2 block text-sm font-medium">Room type</label>
                    <select id="room-type" name="room_type_id" class="min-h-12 w-full rounded-xl border border-gray-700 bg-gray-800 px-3 py-3 text-base text-white focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-400/50">
                        @foreach($roomTypes as $type)<option value="{{ $type->id }}">{{ $type->name }}</option>@endforeach
                    </select>
                </div>
                <div>
                    <label for="bed-type" class="mb-2 block text-sm font-medium">Bed type</label>
                    <select id="bed-type" name="bed_type_id" class="min-h-12 w-full rounded-xl border border-gray-700 bg-gray-800 px-3 py-3 text-base text-white focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-400/50">
                        @foreach($bedTypes as $bed)<option value="{{ $bed->id }}">{{ $bed->name }}</option>@endforeach
                    </select>
                </div>
            </div>

            <fieldset class="space-y-3">
                <legend class="mb-2 text-sm font-medium">Board types</legend>
                @foreach($boardTypes as $board)
                    <div x-data="{ enabled: false }" class="grid gap-3 rounded-xl border border-gray-700 bg-gray-900 p-3 sm:grid-cols-[auto_1fr_10rem] sm:items-center">
                        <input type="checkbox" x-model="enabled" name="board_types[{{ $board->id }}][enabled]" value="1" class="h-5 w-5 rounded border-gray-600 bg-gray-800 text-blue-600">
                        <label for="board-price-{{ $board->id }}" class="font-medium">{{ $board->label }}</label>
                        <input id="board-price-{{ $board->id }}" type="number" step="0.01" min="0" name="board_types[{{ $board->id }}][price]" :disabled="!enabled" placeholder="Supplement (€)"
                               class="min-h-11 w-full rounded-lg border border-gray-700 bg-gray-800 px-3 py-2 text-white disabled:cursor-not-allowed disabled:opacity-50 focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-400/50">
                    </div>
                @endforeach
            </fieldset>

            <div class="grid gap-6 sm:grid-cols-3">
                <div><label for="capacity" class="mb-2 block text-sm font-medium">Capacity</label><input id="capacity" type="number" name="capacity" min="1" required class="min-h-12 w-full rounded-xl border border-gray-700 bg-gray-800 px-3 py-3 text-base text-white focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-400/50"></div>
                <div><label for="price" class="mb-2 block text-sm font-medium">Price per night (€)</label><input id="price" type="number" step="0.01" min="0" name="price_per_night" required class="min-h-12 w-full rounded-xl border border-gray-700 bg-gray-800 px-3 py-3 text-base text-white focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-400/50"></div>
                <div><label for="units" class="mb-2 block text-sm font-medium">Total units</label><input id="units" type="number" min="1" name="total_units" required class="min-h-12 w-full rounded-xl border border-gray-700 bg-gray-800 px-3 py-3 text-base text-white focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-400/50"></div>
            </div>

            <fieldset>
                <legend class="mb-3 text-sm font-medium">Room facilities</legend>
                <div class="grid gap-3 sm:grid-cols-2">
                    @foreach($facilities as $facility)
                        <label class="flex min-h-11 items-center gap-3 rounded-lg border border-gray-700 bg-gray-900 px-3 py-2">
                            <input type="checkbox" name="facilities[]" value="{{ $facility->id }}" class="h-5 w-5 rounded border-gray-600 bg-gray-800 text-blue-600">
                            <span>{{ $facility->label }}</span>
                        </label>
                    @endforeach
                </div>
            </fieldset>

            <div class="flex flex-wrap justify-end gap-3">
                <a href="{{ route('supplier.hotels.rooms.index', $hotel) }}" class="inline-flex min-h-11 items-center rounded-xl border border-gray-700 px-5 py-3 text-gray-200 hover:border-gray-500">Cancel</a>
                <button type="submit" class="min-h-11 rounded-xl bg-emerald-600 px-5 py-3 font-semibold text-white hover:bg-emerald-500">Create room</button>
            </div>
        </form>
    </div>
</x-layouts.dashboard>
