<x-layouts.dashboard>

    @include('supplier.hotels.setup._steps')

    <h1 class="text-xl font-bold mb-6">
    Inventory Setup
    </h1>

    <p class="text-gray-500 mb-6">
    Select a date range to generate availability for all rooms.
    </p>

    <form method="POST"
        action="{{ route('supplier.hotels.inventory.store',$hotel) }}"
        class="space-y-6 max-w-lg">

    @csrf

        <div>
            <label class="block text-sm font-medium mb-1">
                From date
            </label>

            <input type="date"
                name="from"
                class="border rounded w-full p-2 text-white"
                required>
            </div>

            <div>
            <label class="block text-sm font-medium mb-1">
                To date
            </label>

            <input type="date"
                name="to"
                class="border rounded w-full p-2"
                required
            >
        </div>

        <button class="bg-blue-600 text-white px-4 py-2 rounded">
            Generate Inventory
        </button>

    </form>

</x-layouts.dashboard>