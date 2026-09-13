<div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-6">
    <div class="relative sm:col-span-2" x-data="destinationSearch(@js(route('destinations.index')), @js(request('city', '')), @js(request('country', '')))" @click.outside="close()">
        <label for="destination" class="mb-2 block text-sm font-medium text-gray-200">Destination</label>
        <input id="destination" type="text" x-model="term" @input="changed()" @input.debounce.300ms="search()" @keydown="key($event)" role="combobox" aria-autocomplete="list" aria-controls="destination-options" :aria-expanded="open" :aria-activedescendant="active >= 0 && open ? 'destination-' + active : null" autocomplete="off" maxlength="64" placeholder="City" class="min-h-12 w-full rounded-xl border border-gray-700 bg-gray-950 px-4 py-3 text-base text-white placeholder:text-gray-500 focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-400/50">
        <input type="hidden" name="city" :value="city">
        <input type="hidden" name="country" :value="country">
        <p class="mt-1 text-sm text-gray-400" aria-live="polite" x-text="loading ? 'Searching destinations…' : error"></p>
        <ul id="destination-options" role="listbox" x-show="open" x-cloak class="absolute z-30 mt-1 w-full overflow-hidden rounded-xl border border-gray-600 bg-gray-900 shadow-lg">
            <template x-for="(item, index) in results" :key="item.city + item.country">
                <li :id="'destination-' + index" role="option" :aria-selected="index === active" @mousedown.prevent="choose(index)" @click="choose(index)" class="cursor-pointer px-4 py-3" :class="index === active ? 'bg-blue-700' : 'hover:bg-gray-800'" x-text="item.city + ', ' + item.country"></li>
            </template>
            <li x-show="!loading && !results.length" class="p-4 text-gray-400" role="presentation">No published destinations found.</li>
        </ul>
    </div>
    @foreach(['check_in' => 'Check-in', 'check_out' => 'Check-out'] as $field => $label)
        <div>
            <label for="{{ $field }}" class="mb-2 block text-sm font-medium text-gray-200">{{ $label }}</label>
            <input id="{{ $field }}" name="{{ $field }}" type="date" value="{{ request($field) }}" min="{{ now()->toDateString() }}" class="min-h-12 w-full rounded-xl border border-gray-700 bg-gray-950 px-3 py-3 text-base text-white focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-400/50">
        </div>
    @endforeach
    @foreach(['adults' => ['Adults',1,1], 'children' => ['Children',0,0]] as $field => [$label,$default,$min])
        <div>
            <label for="{{ $field }}" class="mb-2 block text-sm font-medium text-gray-200">{{ $label }}</label>
            <input id="{{ $field }}" name="{{ $field }}" type="number" min="{{ $min }}" max="255" value="{{ request($field, $default) }}" class="min-h-12 w-full rounded-xl border border-gray-700 bg-gray-950 px-3 py-3 text-base text-white focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-400/50">
        </div>
    @endforeach
</div>
@once
<script>
function destinationSearch(url, city = '', country = '') {
    return {
        city: city || '', country: country || '', term: city ? city + (country ? ', ' + country : '') : '',
        results: [], active: -1, open: false, loading: false, error: '', serial: 0,
        changed() { this.serial++; this.city = this.term.trim(); this.country = ''; this.results = []; this.active = -1; this.open = false; this.loading = false; this.error = ''; },
        close() { this.serial++; this.open = false; this.loading = false; },
        async search() {
            if (this.term.trim().length < 2 || this.country) return;
            const serial = ++this.serial;
            this.loading = true;
            this.error = '';
            try {
                const response = await fetch(url + '?q=' + encodeURIComponent(this.term.trim()), { headers: { Accept: 'application/json' } });
                const data = await response.json();
                if (serial !== this.serial) return;
                if (!response.ok) throw new Error(data.message || 'Destination search unavailable.');
                this.results = data; this.active = -1; this.open = true;
            } catch (error) { if (serial === this.serial) { this.error = error.message; this.open = false; } }
            finally { if (serial === this.serial) this.loading = false; }
        },
        choose(index) {
            const item = this.results[index];
            if (!item) return;
            this.city = item.city; this.country = item.country; this.term = item.city + ', ' + item.country;
            this.close();
        },
        key(event) {
            if (event.key === 'Escape') { event.preventDefault(); this.close(); }
            if (!this.open || !this.results.length) return;
            if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
                event.preventDefault();
                this.active = this.active < 0 ? (event.key === 'ArrowDown' ? 0 : this.results.length - 1) : (this.active + (event.key === 'ArrowDown' ? 1 : this.results.length - 1)) % this.results.length;
            }
            if (event.key === 'Enter' && this.active >= 0) { event.preventDefault(); this.choose(this.active); }
        }
    };
}
</script>
@endonce
