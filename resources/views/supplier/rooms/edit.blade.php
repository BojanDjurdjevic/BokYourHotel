<x-layouts.dashboard>
    <div class="max-w-2xl mx-auto">

    <h1 class="text-2xl font-bold mb-6">
    Edit Room
    </h1>

    <form method="POST" action="{{ route('supplier.rooms.update', $room) }}">
    @csrf
    @method('PUT')

    <div class="mb-4">
    <label>Room Name</label>
    <input
    type="text"
    name="name"
    value="{{ old('name', $room->name) }}"
    class="w-full border p-2 rounded"
    >
    </div>

    <div>
        <select name="room_type_id" class="w-full border p-2 rounded">

        @foreach($roomTypes as $type)

        <option
        value="{{ $type->id }}"
        @selected($room->room_type_id == $type->id)
        >

        {{ $type->name }}

        </option>

        @endforeach

        </select>
    </div>

    <div>
        <select name="bed_type_id" class="w-full border p-2 rounded">

        @foreach($bedTypes as $bed)

        <option
        value="{{ $bed->id }}"
        @selected($room->bed_type_id == $bed->id)
        >

        {{ $bed->name }}

        </option>

        @endforeach

        </select>
    </div>

    <div>
        @foreach($boardTypes as $board)

        <label class="block">

        <input
        type="checkbox"
        name="board_types[]"
        value="{{ $board->id }}"
        @checked($room->boardTypes->contains($board->id))
        >

        {{ $board->name }}

        </label>

        @endforeach
    </div>

    <div class="mb-4">
    <label>Capacity</label>
    <input
    type="number"
    name="capacity"
    value="{{ old('capacity', $room->capacity) }}"
    class="w-full border p-2 rounded"
    >
    </div>

    <div class="mb-4">
    <label>Price per Night (€)</label>
    <input
    type="number"
    step="0.01"
    name="price_per_night"
    value="{{ old('price_per_night', $room->price_per_night) }}"
    class="w-full border p-2 rounded"
    >
    </div>

    <div class="mb-6">
    <label>Total Units</label>
    <input
    type="number"
    name="total_units"
    value="{{ old('total_units', $room->total_units) }}"
    class="w-full border p-2 rounded"
    >
    </div>

    <button class="bg-blue-600 text-white px-4 py-2 rounded">
    Update Room
    </button>

    </form>

    </div>
</x-layouts.dashboard>