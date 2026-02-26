<nav x-data="{ open: false }"
     class="sticky top-0 z-50 bg-gray-900/80 backdrop-blur border-b border-gray-800">

    <div class="max-w-7xl mx-auto px-6">
        <div class="flex justify-between h-16 items-center">

            {{-- Logo --}}
            <div class="flex items-center space-x-8">
                <a href="{{ route('dashboard') }}"
                   class="text-xl font-semibold tracking-tight text-white">
                    StayCore
                </a>

                {{-- Desktop Nav --}}
                <div class="hidden md:flex items-center space-x-6 text-sm">

                    <a href="{{ route('dashboard') }}"
                       class="text-gray-300 hover:text-white transition">
                        Home
                    </a>

                    <a href="#"
                       class="text-gray-300 hover:text-white transition">
                        Search
                    </a>

                    @auth
                        @if(auth()->user()->role === 'user')
                            <a href="#"
                               class="text-gray-300 hover:text-white transition">
                                My Bookings
                            </a>
                        @endif

                        @if(auth()->user()->role === 'supplier')
                            <a href="#"
                               class="text-gray-300 hover:text-white transition">
                                My Hotels
                            </a>
                        @endif

                        @if(in_array(auth()->user()->role, ['admin','superadmin']))
                            <a href="#"
                               class="text-indigo-400 hover:text-indigo-300 font-medium transition">
                                Admin Dashboard
                            </a>
                        @endif
                    @endauth

                </div>
            </div>

            {{-- Right Side --}}
            <div class="hidden md:flex items-center space-x-6">

                @auth
                    <div class="text-sm text-gray-400">
                        {{ auth()->user()->name }}
                    </div>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="bg-gray-800 hover:bg-gray-700 text-gray-200 px-4 py-2 rounded-lg text-sm transition">
                            Logout
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}"
                       class="text-gray-300 hover:text-white transition text-sm">
                        Login
                    </a>
                @endauth

            </div>

            {{-- Mobile button --}}
            <div class="md:hidden">
                <button @click="open = !open"
                        class="text-gray-400 hover:text-white">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor"
                         viewBox="0 0 24 24">
                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              stroke-width="2"
                              d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
            </div>

        </div>
    </div>

    {{-- Mobile menu --}}
    <div x-show="open" class="md:hidden border-t border-gray-800 bg-gray-900">
        <div class="px-6 py-4 space-y-3 text-sm">

            <a href="{{ route('dashboard') }}" class="block text-gray-300 hover:text-white">Home</a>
            <a href="#" class="block text-gray-300 hover:text-white">Search</a>

            @auth
                @if(auth()->user()->role === 'user')
                    <a href="#" class="block text-gray-300 hover:text-white">My Bookings</a>
                @endif

                @if(auth()->user()->role === 'supplier')
                    <a href="#" class="block text-gray-300 hover:text-white">My Hotels</a>
                @endif

                @if(in_array(auth()->user()->role, ['admin','superadmin']))
                    <a href="#" class="block text-indigo-400">Admin Dashboard</a>
                @endif
            @endauth

        </div>
    </div>

</nav>