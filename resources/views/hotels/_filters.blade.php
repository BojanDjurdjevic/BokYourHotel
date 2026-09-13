<details open class="rounded-xl border border-gray-800 bg-gray-900 p-4">
    <summary class="cursor-pointer font-semibold">Filter your stay</summary>
    @php($selectedHotelFacilities = collect(request('hotel_facilities', []))->map(fn ($value) => \App\Support\FacilityLabel::key($value)))
    <div class="mt-5 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
        <fieldset class="space-y-2">
            <legend class="mb-2 font-medium">Hotel stars</legend>
            @foreach(range(1,5) as $star)
                <label class="flex min-h-11 items-center gap-2"><input type="checkbox" name="stars[]" value="{{ $star }}" @checked(in_array($star, request('stars', [])))> <span>{{ $star }} stars</span></label>
            @endforeach
        </fieldset>
        <fieldset class="space-y-2">
            <legend class="mb-2 font-medium">Hotel facilities</legend>
            @foreach($hotelFacilities as $facility)
                <label class="flex min-h-11 items-center gap-2"><input type="checkbox" name="hotel_facilities[]" value="{{ $facility['value'] }}" @checked($selectedHotelFacilities->contains($facility['key']))> <span>{{ $facility['label'] }}</span></label>
            @endforeach
        </fieldset>
        <fieldset class="space-y-2">
            <legend class="mb-2 font-medium">Room facilities</legend>
            @foreach($roomFacilities as $facility)
                <label class="flex min-h-11 items-center gap-2"><input type="checkbox" name="room_facilities[]" value="{{ $facility->id }}" @checked(in_array($facility->id, request('room_facilities', [])))> <span>{{ $facility->label }}</span></label>
            @endforeach
        </fieldset>
        <div class="space-y-4">
            <div>
                <label for="board_type" class="mb-2 block font-medium">Board type</label>
                <select id="board_type" name="board_type" class="min-h-12 w-full rounded-xl border border-gray-700 bg-gray-950 px-3 py-3 text-base text-white focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-400/50">
                    <option value="">Any board type</option>
                    @foreach($boards as $board)<option value="{{ $board->id }}" @selected(request('board_type') == $board->id)>{{ $board->label }}</option>@endforeach
                </select>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label for="min_price" class="mb-2 block font-medium">Min price (EUR)</label>
                    <input id="min_price" type="number" name="min_price" min="0" step="0.01" value="{{ request('min_price') }}" placeholder="e.g. 100" inputmode="decimal" class="min-h-12 w-full rounded-xl border border-gray-700 bg-gray-950 px-3 py-3 text-base text-white placeholder:text-gray-500 focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-400/50">
                </div>
                <div>
                    <label for="max_price" class="mb-2 block font-medium">Max price (EUR)</label>
                    <input id="max_price" type="number" name="max_price" min="0" step="0.01" value="{{ request('max_price') }}" placeholder="e.g. 500" inputmode="decimal" class="min-h-12 w-full rounded-xl border border-gray-700 bg-gray-950 px-3 py-3 text-base text-white placeholder:text-gray-500 focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-400/50">
                </div>
            </div>
            <div>
                <label for="sort" class="mb-2 block font-medium">Sort by</label>
                <select id="sort" name="sort" class="min-h-12 w-full rounded-xl border border-gray-700 bg-gray-950 px-3 py-3 text-base text-white focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-400/50">
                    @foreach(['recommended'=>'Hotel name A–Z','price_asc'=>'Price: low to high','price_desc'=>'Price: high to low','stars'=>'Stars: high to low'] as $value=>$label)<option value="{{ $value }}" @selected(request('sort','recommended') === $value)>{{ $label }}</option>@endforeach
                </select>
            </div>
        </div>
    </div>
    <p class="mt-5 text-sm text-gray-400">Matches one room for all guests. With dates: available for every night, price for the whole stay including board. Without dates: indicative room + board price per night; availability is not guaranteed.</p>
</details>
