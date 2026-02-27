<x-app-layout>
    @php
        $bgColor = '';
        if(auth()->user()->role == 'superadmin') $bgColor = 'bg-purple-900';
        else $bgColor = 'bg-emerald-900';
    @endphp
    <aside class="w-64 {{ $bgColor }} border-r border-gray-800 p-6 hidden md:block rounded-lg">
        <h2 class="text-lg font-semibold mb-8">
            {{ ucfirst(auth()->user()->role) }} Panel
        </h2>

        @if(auth()->user()->role === 'supplier')
            @include('layouts.partials.sidebar-supplier', compact('bgColor'))
        @endif

        @if(in_array(auth()->user()->role, ['admin','superadmin']))
            @include('layouts.partials.sidebar-admin', compact('bgColor'))
        @endif
    </aside>
</x-app-layout>
