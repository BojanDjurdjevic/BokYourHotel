<x-layouts.dashboard>
    <div class="mx-auto max-w-3xl">
        @include('supplier.rooms.setup._steps', ['hotel' => $hotel, 'room' => $room, 'step' => 'info'])
        <h1 class="mb-6 text-2xl font-bold">Edit room</h1>

        <form method="POST" action="{{ route('supplier.hotels.rooms.update', [$hotel, $room]) }}" class="space-y-6">
            @csrf @method('PUT')
            <x-input-error :messages="$errors->all()" />

            <div>
                <label for="room-name" class="mb-2 block text-sm font-medium">Room name</label>
                <input id="room-name" type="text" name="name" value="{{ old('name', $room->name) }}" required
                       class="min-h-12 w-full rounded-xl border border-gray-700 bg-gray-800 px-3 py-3 text-base text-white focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-400/50">
            </div>

            <div class="grid gap-6 md:grid-cols-2">
                <div>
                    <label for="room-type" class="mb-2 block text-sm font-medium">Room type</label>
                    <select id="room-type" name="room_type_id" class="min-h-12 w-full rounded-xl border border-gray-700 bg-gray-800 px-3 py-3 text-base text-white focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-400/50">
                        @foreach($roomTypes as $type)<option value="{{ $type->id }}" @selected($room->room_type_id == $type->id)>{{ $type->name }}</option>@endforeach
                    </select>
                </div>
                <div>
                    <label for="bed-type" class="mb-2 block text-sm font-medium">Bed type</label>
                    <select id="bed-type" name="bed_type_id" class="min-h-12 w-full rounded-xl border border-gray-700 bg-gray-800 px-3 py-3 text-base text-white focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-400/50">
                        @foreach($bedTypes as $bed)<option value="{{ $bed->id }}" @selected($room->bed_type_id == $bed->id)>{{ $bed->name }}</option>@endforeach
                    </select>
                </div>
            </div>

            <fieldset class="space-y-3">
                <legend class="mb-2 text-sm font-medium">Board types</legend>
                @foreach($boardTypes as $board)
                    <div class="grid gap-3 rounded-xl border border-gray-700 bg-gray-900 p-3 sm:grid-cols-[auto_1fr_10rem] sm:items-center">
                        <input id="board-{{ $board->id }}" type="checkbox" name="board_types[{{ $board->id }}][enabled]" value="1"
                               @checked(old('board_types.'.$board->id.'.enabled', $room->boardTypes->contains($board->id))) class="h-5 w-5 rounded border-gray-600 bg-gray-800 text-blue-600">
                        <label for="board-{{ $board->id }}" class="font-medium">{{ $board->label }}</label>
                        <input aria-label="{{ $board->label }} supplement per night" type="number" step="0.01" min="0" name="board_types[{{ $board->id }}][price]"
                               value="{{ old('board_types.'.$board->id.'.price', $room->boardTypes->find($board->id)?->pivot->price ?? 0) }}"
                               class="min-h-11 w-full rounded-lg border border-gray-700 bg-gray-800 px-3 py-2 text-white focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-400/50">
                    </div>
                @endforeach
            </fieldset>

            <div class="grid gap-6 sm:grid-cols-3">
                <div><label for="capacity" class="mb-2 block text-sm font-medium">Capacity</label><input id="capacity" type="number" name="capacity" min="1" value="{{ old('capacity', $room->capacity) }}" required class="min-h-12 w-full rounded-xl border border-gray-700 bg-gray-800 px-3 py-3 text-base text-white focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-400/50"></div>
                <div><label for="price" class="mb-2 block text-sm font-medium">Price per night (€)</label><input id="price" type="number" step="0.01" min="0" name="price_per_night" value="{{ old('price_per_night', $room->price_per_night) }}" required class="min-h-12 w-full rounded-xl border border-gray-700 bg-gray-800 px-3 py-3 text-base text-white focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-400/50"></div>
                <div><label for="units" class="mb-2 block text-sm font-medium">Total units</label><input id="units" type="number" min="1" name="total_units" value="{{ old('total_units', $room->total_units) }}" required class="min-h-12 w-full rounded-xl border border-gray-700 bg-gray-800 px-3 py-3 text-base text-white focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-400/50"></div>
            </div>

            <div>
                <p class="mb-2 text-sm font-medium">Selected facilities</p>
                <div class="flex flex-wrap gap-2">
                    @forelse($room->facilities as $facility)
                        <span class="rounded-lg bg-gray-800 px-3 py-2 text-sm">{{ $facility->label ?? \App\Support\FacilityLabel::label($facility->name) }}</span>
                    @empty
                        <span class="text-sm text-gray-400">No facilities selected. Use the Facilities step to update them.</span>
                    @endforelse
                </div>
            </div>

            <div class="flex flex-wrap justify-between gap-3">
                <a href="{{ route('supplier.rooms.facilities', $room) }}" class="inline-flex min-h-11 items-center rounded-xl border border-gray-700 px-5 py-3 text-blue-300 hover:border-gray-500">Manage facilities</a>
                <div class="flex gap-3">
                    <a href="{{ route('supplier.hotels.rooms.index', $hotel) }}" class="inline-flex min-h-11 items-center rounded-xl border border-gray-700 px-5 py-3 text-gray-200 hover:border-gray-500">Cancel</a>
                    <button type="submit" class="min-h-11 rounded-xl bg-emerald-600 px-5 py-3 font-semibold text-white hover:bg-emerald-500">Save room</button>
                </div>
            </div>
        </form>
    </div>
</x-layouts.dashboard>
