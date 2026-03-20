<x-layouts.dashboard>

@include('supplier.rooms.setup._steps', [
    'hotel' => $hotel,
    'room' => $room,
    'step' => 'inventory'
])

<div 
    x-data="inventoryGrid({
        updateUrl: '{{ route('supplier.rooms.inventory.update', $room) }}',
        dataUrl: '{{ route('supplier.rooms.inventory', $room) }}',
        csrf: '{{ csrf_token() }}'
    })"
    class="overflow-x-auto"
>
    <div class="flex items-center gap-4 mb-4">

        <x-button 
            @click="prevMonth"
            variant="secondary"
        >
        ←
        </x-button>

        <h2 class="text-lg font-semibold" x-text="monthLabel"></h2>

        <x-button 
            @click="nextMonth"
            variant="secondary"
        >
        →
        </x-button>

    </div>

    <table class="min-w-full text-sm">

        <thead>
        <tr class="text-gray-400 border-b border-gray-700">

            <th class="p-3 text-left">Date</th>

            @foreach($dates as $date)
            <th class="p-2 text-center">
                {{ $date->format('d') }}
            </th>
            @endforeach

        </tr>
        </thead>

        <tbody>

        <tr>

        <td class="p-3 font-semibold">
            {{ $room->name }}
        </td>

        @foreach($dates as $date)

        @php
            $key = $date->format('Y-m-d');
            $item = $inventory[$key] ?? null;
            $available = $item->available ?? $room->total_units;
        @endphp

        <td class="p-1">

        <div
            class="cursor-pointer rounded text-center p-2"
            :class="getColor(cells['{{ $key }}'])"
            @click="edit('{{ $key }}')"
        >

        <template x-if="editing !== '{{ $key }}'">
            <span x-text="cells['{{ $key }}']?.available"></span>
            <div class="text-xs">€<span x-text="cells['{{ $key }}']?.price"></span></div>
        </template>

        <template x-if="editing === '{{ $key }}'">
            <input
                type="number"
                class="w-16 text-black text-center"
                x-model="cells['{{ $key }}'].available"
                @blur="save('{{ $key }}')"
                @keydown.enter="save('{{ $key }}')"
                autofocus
            >
        </template>

        </div>

        </td>

        <script>
            window.initialCells = window.initialCells || {}
            window.initialCells['{{ $key }}'] = {
                available: {{ $available }},
                price: {{ $item->price ?? $room->price_per_night ?? 0 }}
            }
        </script>

        @endforeach

        </tr>

        </tbody>
    </table>

</div>

<script>
function inventoryGrid(config) {
    return {
        dataUrl: config.dataUrl,

        month: new Date(),

        get monthLabel() {
            return this.month.toLocaleDateString('en-US', {
                month: 'long',
                year: 'numeric'
            })
        },

        async load() {

            let res = await fetch(
                `${this.dataUrl}?month=${this.month.toISOString()}`
            )

            let data = await res.json()

            this.cells = {}

            data.dates.forEach(date => {

                let row = data.inventory[date]

                this.cells[date] = {
                    available: row?.available ?? 0,
                    price: row?.price ?? 0
                }

            })
        },

        prevMonth() {
            this.month = new Date(
                this.month.getFullYear(),
                this.month.getMonth() - 1,
                1
            )
            //this.load()
        },

        nextMonth() {
            this.month = new Date(
                this.month.getFullYear(),
                this.month.getMonth() + 1,
                1
            )
            //this.load()
        },

        cells: window.initialCells || {},
        editing: null,
        updateUrl: config.updateUrl,
        csrf: config.csrf,
        /*
        init() {
            this.cells = window.initialCells || {}
        } */

        edit(date) {
            this.editing = date
        },

        async save(date) {

            let value = this.cells[date] // value

            await fetch(this.updateUrl, {
                method: "PUT",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": this.csrf
                },
                body: JSON.stringify({
                    //room_id: this.roomId,
                    date: date,
                    available: this.cells[date].available,
                    price: this.cells[date].price
                })
            })

            this.editing = null
        },

        getColor(cell) {
            if (!cell) return 'bg-gray-700'

            if (cell.available == 0) return 'bg-red-600'
            if (cell.available < 3) return 'bg-yellow-500'
            return 'bg-green-600'
        }
    }
}
</script>

</x-layouts.dashboard>