<details open class="bg-gray-900 border border-gray-800 rounded-xl p-4">
    <summary class="font-semibold cursor-pointer">Filter your stay</summary>
    <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6 mt-4">
        <fieldset><legend class="mb-2">Hotel stars (property rating)</legend>
            @foreach(range(1,5) as $star)<label class="block"><input type="checkbox" name="stars[]" value="{{ $star }}" @checked(in_array($star, request('stars', [])))> {{ $star }} stars</label>@endforeach
        </fieldset>
        <fieldset><legend class="mb-2">Hotel facilities — all selected</legend>
            @foreach($hotelFacilities as $facility)<label class="block"><input type="checkbox" name="hotel_facilities[]" value="{{ $facility }}" @checked(in_array($facility, request('hotel_facilities', [])))> {{ $facility }}</label>@endforeach
        </fieldset>
        <fieldset><legend class="mb-2">Room facilities — in the same room</legend>
            @foreach($roomFacilities as $facility)<label class="block"><input type="checkbox" name="room_facilities[]" value="{{ $facility->id }}" @checked(in_array($facility->id, request('room_facilities', [])))> {{ $facility->name }}</label>@endforeach
        </fieldset>
        <div class="space-y-3">
            <label class="block">Board type<select name="board_type" class="block w-full rounded-lg bg-gray-800"><option value="">Any (cheapest matching board)</option>@foreach($boards as $board)<option value="{{ $board->id }}" @selected(request('board_type') == $board->id)>{{ $board->name }}</option>@endforeach</select></label>
            <label class="block">Minimum EUR<input type="number" name="min_price" min="0" step="0.01" value="{{ request('min_price') }}" class="block w-full rounded-lg bg-gray-800"></label>
            <label class="block">Maximum EUR<input type="number" name="max_price" min="0" step="0.01" value="{{ request('max_price') }}" class="block w-full rounded-lg bg-gray-800"></label>
            <label class="block">Sort<select name="sort" class="block w-full rounded-lg bg-gray-800">@foreach(['recommended'=>'Recommended (hotel name)','price_asc'=>'Price: low to high','price_desc'=>'Price: high to low','stars'=>'Stars: high to low'] as $value=>$label)<option value="{{ $value }}" @selected(request('sort','recommended') === $value)>{{ $label }}</option>@endforeach</select></label>
        </div>
    </div>
    <p class="text-sm text-gray-400 mt-4">Matches one room for all guests. With dates: available for every night, price for the whole stay including board. Without dates: indicative room + board price per night; availability is not guaranteed.</p>
</details>
