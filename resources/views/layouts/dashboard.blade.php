<aside class="w-64 bg-gray-900 border-r border-gray-800 p-6 hidden md:block">
    <h2 class="text-lg font-semibold mb-8">
        {{ ucfirst(auth()->user()->role) }} Panel
    </h2>

    @if(auth()->user()->role === 'supplier')
        @include('layouts.partials.sidebar-supplier')
    @endif

    @if(in_array(auth()->user()->role, ['admin','superadmin']))
        @include('layouts.partials.sidebar-admin')
    @endif
</aside>