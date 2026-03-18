<div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">

    @foreach($facilities as $facility)

        <label
            class="cursor-pointer border rounded-xl p-4 flex items-center gap-3
            hover:border-blue-500 transition
            {{ in_array($facility->id, $selected ?? []) ? 'border-blue-500 bg-blue-500/10' : 'border-gray-700' }}"
        >

            <input
                type="checkbox"
                name="facilities[]"
                value="{{ $facility->id }}"
                class="hidden"
                @checked(in_array($facility->id, $selected ?? []))
            >

            <span class="text-xl">
                {{ config('facility_icons')[$facility->icon] ?? '❔' }}
            </span>

            <span class="text-sm">
                {{ $facility->name }}
            </span>

        </label>

    @endforeach

</div>