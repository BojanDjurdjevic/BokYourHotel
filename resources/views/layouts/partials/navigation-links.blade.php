<a href="{{ route('hotels.index') }}" class="text-gray-300 hover:text-white">Hotels</a>
@auth
    <a href="{{ route('dashboard') }}" class="text-gray-300 hover:text-white">Dashboard</a>
    <a href="{{ route('bookings.index') }}" class="text-gray-300 hover:text-white">Bookings</a>
    @if(auth()->user()->isSupplier())
        <a href="{{ route('supplier.dashboard') }}" class="text-green-400">Supplier</a>
        <a href="{{ route('supplier.hotels.index') }}" class="text-gray-300 hover:text-white">My Hotels</a>
    @elseif(auth()->user()->isSuperAdmin())
        <a href="{{ route('admin.dashboard') }}" class="text-purple-400">Admin</a>
    @elseif(auth()->user()->isAdmin())
        <a href="{{ route('bookings.index') }}" class="text-purple-400">Booking management</a>
    @endif
    <a href="{{ route('profile.edit') }}" class="text-gray-300 hover:text-white">Profile</a>
    <form method="POST" action="{{ route('logout') }}">@csrf<button class="text-gray-300 hover:text-white">Logout</button></form>
@else
    <a href="{{ route('login') }}" class="text-gray-300 hover:text-white">Login</a>
    <a href="{{ route('register') }}" class="text-gray-300 hover:text-white">Register</a>
@endauth
