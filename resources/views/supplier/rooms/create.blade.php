<x-layouts.dashboard>
    <div class="max-w-2xl mx-auto">

    <h1 class="text-2xl font-bold mb-6">
        Add Room — {{ $hotel->name }}
    </h1>

    <form method="POST" action="{{ route('supplier.hotels.rooms.store', $hotel) }}">
        @csrf

        <div class="mb-4">
        <label>Room Name</label>
        <input type="text" name="name" class="w-full border p-2 rounded">
        </div>

        <div class="mb-4">
        <label>Room Type</label>

        <select name="room_type_id" class="w-full border p-2 rounded">

        @foreach($roomTypes as $type)

        <option value="{{ $type->id }}">
            {{ $type->name }}
        </option>

        @endforeach

        </select>
        </div>

        <div class="mb-4">
        <label>Bed Type</label>

        <select name="bed_type_id" class="w-full border p-2 rounded">

        @foreach($bedTypes as $bed)

        <option value="{{ $bed->id }}">
            {{ $bed->name }}
        </option>

        @endforeach

        </select>
        </div>

        <div class="mb-6">

        <label class="block mb-2">Board Types</label>

        @foreach($boardTypes as $boardType)
            <div x-data="{ enabled: {{ isset($pivot[$boardType->id]) ? 'true' : 'false' }} }" class="flex items-center gap-4 mb-2">
                <input 
                    type="checkbox"
                    x-model="enabled"
                    name="board_types[{{ $boardType->id }}][enabled]"
                    value="1"
                    class="rounded"
                >

                <label>{{ $boardType->name }}</label>

                <input 
                    type="number"
                    step="0.01"
                    name="board_types[{{ $boardType->id }}][price]"
                    :disabled="!enabled"
                    value="{{ $pivot[$boardType->id]->pivot->price ?? '' }}"
                    class="border rounded px-2 py-1 board-price"
                >
            </div>
        @endforeach

        </div>

        <div class="mb-4">
            <label>Capacity</label>
            <input type="number" name="capacity" class="w-full border p-2 rounded">
        </div>

        <div class="mb-4">
            <label>Price per Night (€)</label>
            <input type="number" step="0.01" name="price_per_night" class="w-full border p-2 rounded">
        </div>

        <div class="mb-6">
            <label>Total Units</label>
            <input type="number" name="total_units" class="w-full border p-2 rounded">
        </div>

        <button class="bg-green-600 text-white px-4 py-2 rounded">
            Create Room
        </button>

    </form>

    </div>
</x-layouts.dashboard>