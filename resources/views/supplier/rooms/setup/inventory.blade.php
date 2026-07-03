<x-layouts.dashboard>

@include('supplier.rooms.setup._steps', [
    'hotel' => $hotel,
    'room' => $room,
    'step' => 'inventory'
])

<div 
    x-data="inventoryGrid({
        updateUrl: '{{ route('supplier.rooms.inventory.update', $room) }}',
        bulkUrl: '{{ route('supplier.rooms.inventory.bulk', $room) }}',
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

    <div >
        <x-button
            variant="primary"
            @click="openBulk"
        >
            Bulk
        </x-button>

        <div x-show="bulk.open">
            <div>
                <x-button
                    variant="primary"
                    @click="openBulk"
                >
                    Bulk
                </x-button>

                <div
                    x-show="bulk.open"
                    x-transition.opacity
                    class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm"
                >

                    <div
                        @click.outside="bulk.open = false"
                        class="w-full max-w-lg rounded-2xl bg-zinc-900 border border-zinc-700 shadow-2xl"
                    >

                        <!-- Header -->
                        <div class="flex items-center justify-between px-6 py-4 border-b border-zinc-700">

                            <h2 class="text-xl font-semibold text-white">
                                Bulk Inventory Update
                            </h2>

                            <button
                                @click="bulk.open = false"
                                class="text-zinc-400 hover:text-white text-xl"
                            >
                                ✕
                            </button>

                        </div>

                        <!-- Body -->
                        <div class="p-6 space-y-5">

                            <div class="grid grid-cols-2 gap-4">

                                <div>
                                    <label class="block mb-2 text-sm text-zinc-300">
                                        From
                                    </label>

                                    <input
                                        type="date"
                                        x-model="bulk.from"
                                        class="w-full rounded-lg border border-zinc-700 bg-zinc-800 px-3 py-2 text-white focus:border-blue-500 focus:ring-blue-500"
                                    >
                                </div>

                                <div>
                                    <label class="block mb-2 text-sm text-zinc-300">
                                        To
                                    </label>

                                    <input
                                        type="date"
                                        x-model="bulk.to"
                                        class="w-full rounded-lg border border-zinc-700 bg-zinc-800 px-3 py-2 text-white focus:border-blue-500 focus:ring-blue-500"
                                    >
                                </div>

                            </div>

                            <div class="grid grid-cols-2 gap-4">

                                <div>
                                    <label class="block mb-2 text-sm text-zinc-300">
                                        Availability
                                    </label>

                                    <input
                                        type="number"
                                        min="0"
                                        x-model="bulk.available"
                                        class="w-full rounded-lg border border-zinc-700 bg-zinc-800 px-3 py-2 text-white focus:border-blue-500 focus:ring-blue-500"
                                    >
                                </div>

                                <div>
                                    <label class="block mb-2 text-sm text-zinc-300">
                                        Price (€)
                                    </label>

                                    <input
                                        type="number"
                                        min="0"
                                        step="0.01"
                                        x-model="bulk.price"
                                        class="w-full rounded-lg border border-zinc-700 bg-zinc-800 px-3 py-2 text-white focus:border-blue-500 focus:ring-blue-500"
                                    >
                                </div>

                            </div>

                        </div>

                        <!-- Footer -->
                        <div class="flex justify-end gap-3 px-6 py-4 border-t border-zinc-700">

                            <x-button
                                variant="secondary"
                                @click="bulk.open = false"
                            >
                                Cancel
                            </x-button>

                            <x-button
                                variant="primary"
                                @click="saveBulk"
                            >
                                Confirm
                            </x-button>

                        </div>

                    </div>

                </div>
            </div>
        </div>
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

                        <div class="flex flex-col gap-1" 
                            @click.stop
                        >

                            <input
                                type="number"
                                class="w-16 text-black text-center"
                                x-model="form.available"
                            >

                            <input
                                type="number"
                                class="w-16 text-black text-center"
                                x-model="form.price"
                            >

                            <button @click.stop="save(date)">OK</button>
                            <button @click.stop="editing = null; form = {}">Cancel</button>

                        </div>

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

        data: '',

        async load() {
            let res = await fetch(
                `${this.dataUrl}?month=${this.month.toISOString()}`,
                {
                    headers: {
                        Accept: 'application/json'
                    }
                }
            )

            let data = await res.json()

            this.data = data

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
        form: {},
        updateUrl: config.updateUrl,
        bulkUrl: config.bulkUrl,
        csrf: config.csrf,

        bulk: {
            open: false,
            from: '',
            to: '',
            available: '',
            price: ''
        },

        openBulk() {
            this.bulk.open = true
            this.bulk.from = ''
            this.bulk.to = ''
            this.bulk.available = this.data.defaults.available
            this.bulk.price = this.data.defaults.price
        },

        edit(date) {
            if (this.editing === date) {
                return;
            }

            this.editing = date

            this.form = {
                available: this.cells[date].available,
                price: this.cells[date].price
            }
        },

        async save(date) {

            this.cells[date] = {
                available: this.form.available,
                price: this.form.price
            }

            const response = await fetch(this.updateUrl, {
                method: "PUT",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": this.csrf
                },
                body: JSON.stringify({
                    date: date,
                    available: this.form.available,
                    price: this.form.price
                })
            })

            this.editing = null
            this.form = {}
        },

        async saveBulk() {

            try {

                const response = await fetch(this.bulkUrl, {
                    method: "PUT",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": this.csrf
                    },
                    body: JSON.stringify({
                        from: this.bulk.from,
                        to: this.bulk.to,
                        available: this.bulk.available,
                        price: this.bulk.price
                    })
                });

                if (!response.ok) {
                    return;
                }

                await this.load();

                this.bulk = {
                    open: false,
                    from: '',
                    to: '',
                    available: '',
                    price: ''
                };

            } catch (e) {
                console.error(e);
            }
        }

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