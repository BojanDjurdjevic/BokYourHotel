@if (session('success'))
    <div
        x-data="{ show: true }"
        x-init="setTimeout(() => show = false, 3000)"
        x-show="show"
        x-transition
        class="mb-4 rounded-lg bg-green-100 text-green-800 px-4 py-3"
    >
        {{ session('success') }}
    </div>
@endif

@if (session('error'))
    <div
        x-data="{ show: true }"
        x-init="setTimeout(() => show = false, 4000)"
        x-show="show"
        x-transition
        class="mb-4 rounded-lg bg-red-100 text-red-800 px-4 py-3"
    >
        {{ session('error') }}
    </div>
@endif