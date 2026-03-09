<x-layouts.dashboard>

    @include('supplier.hotels.setup._steps')

    <h1 class="text-xl font-bold mb-6">
        Hotel Information
    </h1>

    <form method="POST" action="{{ route('supplier.hotels.update', $hotel) }}">
        @csrf
        @method('PUT')

        <div class="mb-4">
        <label class="block mb-1">Hotel Name</label>

        <input
            type="text"
            name="name"
            value="{{ old('name',$hotel->name) }}"
            class="border p-2 w-full rounded"
        >
        </div>

        <div class="mb-4">
        <label>Description</label>

        <textarea
            name="description"
            class="border p-2 w-full rounded"
        >
        {{ old('description',$hotel->description) }}
        </textarea>
        </div>

        <button class="bg-blue-600 text-white px-4 py-2 rounded">
            Save
        </button>

    </form>

</x-layouts.dashboard>