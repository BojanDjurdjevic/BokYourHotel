<nav class="space-y-3 text-md p-3 {{ $bgColor }} rounded-lg">

    <a href="{{ route('supplier.dashboard') }}" 
        class="block text-gray-300 hover:text-white transition"
        wire:navigate
    >
        Overview
    </a>

    <a href="{{ route('supplier.hotels') }}" 
        class="block text-gray-300 hover:text-white transition"
        wire:navigate    
    >
        My Hotels
    </a>

    <a href="{{ route('supplier.bookings') }}" 
        class="block text-gray-300 hover:text-white transition"
        wire:navigate
    >
        Bookings
    </a>

    <a href="{{ route('supplier.pending') }}" 
        class="block text-gray-300 hover:text-white transition"
        wire:navigate
    >
        Pending
    </a>

    <a href="{{ route('supplier.revenue') }}" 
        class="block text-gray-300 hover:text-white transition"
        wire:navigate
    >
        Revenue
    </a>

</nav>