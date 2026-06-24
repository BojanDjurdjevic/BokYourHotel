<div
    x-data="{
        facilities: @js($selected)
    }"
>
    @foreach($facilities as $facility)

        <label
            class="cursor-pointer border rounded-xl p-4 flex items-center gap-3 hover:border-blue-500 transition"
            :class="facilities.includes('{{ $facility->id }}')
                ? 'border-blue-500 bg-blue-500/10'
                : 'border-gray-700'"
        >

            <input
                type="checkbox"
                name="facilities[]"
                value="{{ $facility->id }}"
                class="hidden"
                x-model="facilities"
            >

            <span>
                {{ config('facility_icons')[$facility->name] ?? '❔' }}
            </span>

            <span>
                {{ $facility->name }}
            </span>

        </label>

    @endforeach
</div>