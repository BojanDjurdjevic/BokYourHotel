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

        <h2 class="text-lg font-semibold" x-text="label"></h2>

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
            {{--
            @foreach($dates as $date)
            <th class="p-2 text-center">
                {{ $date->format('d') }}
            </th>
            @endforeach
            --}}
 
            <template x-for="date in dates" :key="date">

                <th class="p-2 text-center">

                    <span
                        x-text="new Date(date).getDate()"
                    ></span>

                </th>

            </template>

        </tr>
        </thead>

        <tbody>

        <tr>

        <td class="p-3 font-semibold">
            {{ $room->name }}
        </td>

        <template x-for="date in dates" :key="date">

            <td class="p-1">

                <div
                    class="cursor-pointer rounded text-center p-2"
                    :class="getColor(cells[date])"
                    @click="edit(date)"
                >

                    <template x-if="editing !== date">

                        <div>

                            <span x-text="cells[date]?.available"></span>

                            <div class="text-xs">
                                €
                                <span x-text="cells[date]?.price"></span>
                            </div>

                        </div>

                    </template>

                    <template x-if="editing === date">

                        <input
                            type="number"
                            class="w-16 text-black text-center"
                            x-model="cells[date].available"
                            @blur="save(date)"
                            @keydown.enter="save(date)"
                        >

                    </template>

                </div>

            </td>

        </template>

        </tr>

        </tbody>
    </table>

</div>

<script>
function inventoryGrid(config) {
    return {
        init() {
            this.load()
        },

        dataUrl: config.dataUrl,

        month: new Date(),

        label: '',

        async load() {
            let res = await fetch(
                `${this.dataUrl}?month=${this.month.toISOString()}`,
                {
                    headers: {
                        Accept: 'application/json'
                    }
                }
            )

            //console.log(await res.text())

            let data = await res.json()

            this.label = data.label

            this.dates = data.dates

            this.cells = {}

            for (const date of data.dates) {

                const row = data.inventory[date]

                this.cells[date] = {
                    available: row
                        ? row.available
                        : data.defaults.available,

                    price: row
                        ? row.price
                        : data.defaults.price
                }

            }
        },

        prevMonth() {
            this.month = new Date(
                this.month.getFullYear(),
                this.month.getMonth() - 1,
                1
            )
            this.load()
        },

        nextMonth() {
            this.month = new Date(
                this.month.getFullYear(),
                this.month.getMonth() + 1,
                1
            )
            this.load()
        },

        cells: {},
        dates: [],
        editing: null,
        updateUrl: config.updateUrl,
        csrf: config.csrf,


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