<x-layouts.dashboard>

@include('supplier.hotels.setup._steps')

<h1 class="text-xl font-bold mb-6">
    Inventory Manager
</h1>

<form
    method="POST"
    action="{{ route('supplier.hotels.inventory.store',$hotel) }}"
    x-data="inventoryManager()"
    class="max-w-3xl space-y-6"
>

    @csrf

    @if ($errors)
        <p class="text-red-600">{{ $errors->first() }}</p>
    @endif

    {{-- ROOM SELECT --}}

    <div>

        <label class="block text-sm mb-1">
            Room
        </label>

        <select
            name="room_id"
            x-model="room_id"
            class="border rounded w-full p-2"
        >

            <option value="" class="bg-gray-800">Select room</option>

            @foreach($rooms as $room)

            <option value="{{ $room->id }}"
                class="bg-gray-800"
            >
                {{ $room->name }}
            </option>

            @endforeach

        </select>

    </div>


    {{-- BULK RANGE --}}

    <div class="border p-4 rounded">

        <h2 class="font-semibold mb-3">
            Bulk update
        </h2>

        <div class="grid grid-cols-2 gap-4">

            <input type="date"
                x-model="from"
                class="border rounded-lg p-2 bg-gray-800"
            />

            <input type="date"
                x-model="to"
                class="border rounded-lg p-2 bg-gray-800"
            />

            </div>

            <div class="grid grid-cols-2 gap-4 mt-4">

            <input type="number"
                x-model="available"
                placeholder="Available rooms"
                class="border rounded-lg p-2 bg-gray-800"
            />

            <input type="number"
                x-model="price"
                placeholder="Price"
                class="border rounded-lg p-2 bg-gray-800"
            />

        </div>

        <button
            type="button"
            @click="generate"
            class="mt-4 bg-blue-600 text-white px-4 py-2 rounded"
        >
            Generate days
        </button>

    </div>


    {{-- PREVIEW --}}

    <div
        x-show="days.length"
        class="border rounded p-4"
    >

        <h2 class="font-semibold mb-4">
        Preview
        </h2>

        <div class="grid grid-cols-7 gap-2 text-center text-sm">

        <template x-for="(day,index) in days" :key="index">

        <div class="bg-gray-800 p-2 rounded">

        <div x-text="day.date"></div>

        <div class="text-xs">
            rooms: <span x-text="day.available"></span>
        </div>

        <div class="text-xs">
            €<span x-text="day.price"></span>
        </div>

        <input type="hidden"
            :name="'inventory['+index+'][date]'"
            :x-value="day.date"
        >

        <input type="hidden"
            :name="'inventory['+index+'][available]'"
            :x-value="day.available"
        >

        <input type="hidden"
            :name="'inventory['+index+'][price]'"
            :x-value="day.price"
        >

    </div>

    </template>

    </div>

    </div>


    <x-button
        class="primary"
        type="submit"
        class="bg-green-600 text-white px-6 py-2 rounded"
    >

        Save inventory

    </x-button>

</form>


<script>

function inventoryManager(){

return {

room_id:null,

from:null,
to:null,

available:0,
price:0,

days:[],

generate(){

this.days = []

let start = new Date(this.from)
let end = new Date(this.to)

while(start <= end){

this.days.push({

date:start.toISOString().slice(0,10),
available:this.available,
price:this.price

})

start.setDate(start.getDate()+1)

}

}

}

}

</script>

</x-layouts.dashboard>