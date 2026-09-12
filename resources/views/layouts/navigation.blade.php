<nav x-data="{ open: false }" class="sticky top-0 z-50 bg-gray-900/95 border-b border-gray-800">
    <div class="max-w-7xl mx-auto px-6">
        <div class="flex justify-between h-16 items-center gap-6">
            <a href="{{ url('/') }}" class="text-xl font-semibold text-white">BookYourHotel</a>
            <button @click="open = !open" :aria-expanded="open" aria-controls="main-navigation" class="md:hidden text-gray-300">Menu</button>
            <div class="hidden md:flex items-center gap-5">@include('layouts.partials.navigation-links')</div>
        </div>
        <div id="main-navigation" x-show="open" x-cloak class="md:hidden py-4 flex flex-col gap-4">@include('layouts.partials.navigation-links')</div>
    </div>
</nav>
