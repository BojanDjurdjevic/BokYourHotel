<x-layouts.dashboard>

    @include('supplier.rooms.setup._steps', [
        'hotel' => $hotel,
        'room' => $room,
        'step' => 'inventory'
    ])

    <div class="max-w-6xl mx-auto">

        <div class="bg-gray-900 p-6 rounded-2xl">

            <h2 class="text-xl font-semibold mb-6">
                Inventory Calendar
            </h2>

            <select id="roomSelect"
                class="mb-4 bg-gray-800 p-2 rounded"
            >

                @foreach($hotel->rooms as $r)
                <option value="{{ $r->id }}">
                {{ $r->name }}
                </option>
                @endforeach

            </select>

            <div id="calendar">
                <!-- JS -->

                <h1 class="text-xl font-bold mb-6">
                    Inventory Calendar
                </h1>


                <div
                    x-data="inventoryCalendar({{ $rooms->first()->id }}, {{ $hotel->id }})"
                    x-init="load()"
                    class="space-y-6"
                >

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
            </div>

        </div>
    </div>

    <script>

        function inventoryCalendar(roomId,hotelId){

            return{

                roomId: roomId ?? {{ $rooms->first()->id }},

                month:new Date(),

                days:[],

                get monthLabel(){

                return this.month.toLocaleDateString(
                    'en-US',
                    {month:'long',year:'numeric'}
                )

                },

                load() {

                    fetch(`/supplier/hotels/${hotelId}/inventory-calendar/data?room_id=${this.roomId}&month=${this.month.toISOString()}`)

                    .then(r=>r.json())

                    .then(data=>{

                    this.days=data

                    })

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

                    let available=prompt(
                        'Available rooms',
                        day.available
                    )

                    let price=prompt(
                        'Price',
                    day.price
                    )

                    fetch(
                        '{{ route("supplier.inventory.update") }}',
                    {

                    method:'POST',

                    headers:{
                        'Content-Type':'application/json',
                        'X-CSRF-TOKEN':document
                        .querySelector('meta[name=csrf-token]')
                        .content
                    },

                    body:JSON.stringify({

                        room_id:this.roomId,
                        //room_id: roomId,
                        date:day.date,
                        available:available,
                        price:price

                })

            }

            )

            .then(()=>this.load())

            }

            }

        }

    </script>
</x-layouts.dashboard>