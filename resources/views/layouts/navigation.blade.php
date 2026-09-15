<nav x-data="{ open: false }" class="sticky top-0 z-50 bg-gray-900/95 border-b border-gray-800">
    <div class="max-w-7xl mx-auto px-6">
        <div class="flex justify-between h-16 items-center gap-6">
            <a href="{{ url('/') }}" class="text-xl font-semibold text-slate-800 dark:text-white">BookYourHotel</a>
            <button @click="open = !open" :aria-expanded="open" aria-controls="main-navigation" class="text-slate-700 hover:text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-400 md:hidden dark:text-gray-300 dark:hover:text-white">Menu</button>
            <div class="hidden md:flex items-center gap-5">@include('layouts.partials.navigation-links') <x-theme-toggle /></div>
        </div>
        <div id="main-navigation" x-show="open" x-cloak class="md:hidden py-4 flex flex-col gap-4">@include('layouts.partials.navigation-links') <x-theme-toggle /></div>
    </div>
</nav>
