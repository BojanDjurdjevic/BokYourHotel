<x-layouts.dashboard>

<x-button
    variant="action"
    href="{{ route('supplier.hotels.setup.inventory', $hotel) }}"
>
    <<- Back
</x-button>

<h1 class="text-xl font-bold mb-6">
    Inventory Calendar
</h1>


<div
    x-data="inventoryCalendar({{ $rooms->first()->id }}, {{ $hotel->id }})"
    x-init="load()"
    class="space-y-6"
>

<p x-show="error" x-text="error" class="text-red-400" role="alert"></p>
<select x-model="roomId" @change="load()" class="border rounded p-2 bg-gray-800">

@foreach($rooms as $room)

<option value="{{ $room->id }}">
{{ $room->name }}
</option>

@endforeach

</select>

<div class="flex gap-4 items-center">

<button
@click="prevMonth"
class="px-3 py-1 bg-gray-700 rounded"
>
←
</button>

<h2
x-text="monthLabel"
class="text-lg font-semibold"
></h2>

<button
@click="nextMonth"
class="px-3 py-1 bg-gray-700 rounded"
>
→
</button>

</div>


<div class="grid grid-cols-7 gap-2 text-center text-sm">

<template x-for="day in days" :key="day.date">

<div
class="border rounded p-2 cursor-pointer hover:bg-gray-800"
@click="edit(day)"
>

<div
class="font-semibold"
x-text="day.date"
></div>

<div class="text-xs">

<span x-text="day.available"></span>
rooms

</div>

<div class="text-xs">

€<span x-text="day.price"></span>

</div>

</div>

</template>

</div>

</div>

<script>

    function inventoryCalendar(roomId,hotelId){

        return{

            roomId: roomId ?? {{ $rooms->first()->id }},

            month:new Date(),

            days:[],
            error: null,
            loadId: 0,
            busy: false,

            get monthLabel(){

            return this.month.toLocaleDateString(
                'en-US',
                {month:'long',year:'numeric'}
            )

            },

            load() {
                const loadId = ++this.loadId
                this.error = null
                this.days = []

                fetch(`/supplier/hotels/${hotelId}/inventory-calendar/data?room_id=${this.roomId}&month=${`${this.month.getFullYear()}-${String(this.month.getMonth() + 1).padStart(2, "0")}`}`)

                .then(async r => { if (!r.ok) throw new Error('Could not load inventory.'); return r.json() })

                .then(data=>{

                if (loadId === this.loadId) this.days=data

                }).catch(e => { if (loadId === this.loadId) this.error = e.message })

            },

            prevMonth() {

                this.month = new Date(
                    this.month.getFullYear(),
                    this.month.getMonth()-1,
                    1
                )

                this.load()

            },

            nextMonth(){

                this.month = new Date(
                    this.month.getFullYear(),
                    this.month.getMonth()+1,
                    1
                )

                this.load()

            },

            edit(day) {
                if (this.busy) return

                let available=prompt(
                    'Available rooms',
                    day.available
                )

                if (available === null) return

                let price=prompt(
                    'Price',
                day.price
                )

                if (price === null) return
                this.busy = true

                fetch(
                    '{{ route("supplier.inventory.update") }}',
                {

                method:'POST',

                headers:{
                    'Content-Type':'application/json',
                    'Accept':'application/json',
                    'X-CSRF-TOKEN':document
                    .querySelector('meta[name=csrf-token]')
                    .content
                },

                body:JSON.stringify({

                    room_id:this.roomId,
                    //room_id: roomId,
                    date:day.date,
                    version:day.version,
                    available:available,
                    price:price

            })

        }

        )

        .then(async response => { if (!response.ok) throw new Error((await response.json()).message || 'Could not save inventory.'); this.load() }).catch(e => { this.error = e.message }).finally(() => { this.busy = false })

        }

        }

    }

</script>

</x-layouts.dashboard>
