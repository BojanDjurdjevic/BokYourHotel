<nav class="space-y-3 text-md p-3 {{ $bgColor }} rounded-lg">

    <a href="{{ route('supplier.dashboard') }}" 
        class="block rounded-lg px-2 py-1 text-gray-300 hover:bg-emerald-800/60 hover:text-white transition focus:outline-none focus:ring-2 focus:ring-emerald-300"
        wire:navigate
    >
        Overview
    </a>

    <a href="{{ route('supplier.hotels.index') }}" 
        class="block rounded-lg px-2 py-1 text-gray-300 hover:bg-emerald-800/60 hover:text-white transition focus:outline-none focus:ring-2 focus:ring-emerald-300"
        wire:navigate    
    >
        My Hotels
    </a>

    <a href="{{ route('supplier.bookings') }}" 
        class="block rounded-lg px-2 py-1 text-gray-300 hover:bg-emerald-800/60 hover:text-white transition focus:outline-none focus:ring-2 focus:ring-emerald-300"
        wire:navigate
    >
        Bookings
    </a>

    <a href="{{ route('supplier.pending') }}" 
        class="block rounded-lg px-2 py-1 text-gray-300 hover:bg-emerald-800/60 hover:text-white transition focus:outline-none focus:ring-2 focus:ring-emerald-300"
        wire:navigate
    >
        Pending
    </a>

    <a href="{{ route('supplier.revenue') }}" 
        class="block rounded-lg px-2 py-1 text-gray-300 hover:bg-emerald-800/60 hover:text-white transition focus:outline-none focus:ring-2 focus:ring-emerald-300"
        wire:navigate
    >
        Revenue
    </a>

</nav>
