<x-layouts.dashboard>
    <div class="max-w-2xl mx-auto">

    <h1 class="text-2xl font-bold mb-6">
    Add Room — {{ $hotel->name }}
    </h1>

    <form method="POST" action="{{ route('supplier.rooms.store', $hotel) }}">
    @csrf

    <div class="mb-4">
    <label>Room Name</label>
    <input type="text" name="name" class="w-full border p-2 rounded">
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