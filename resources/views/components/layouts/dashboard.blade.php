<x-app-layout>
    @php
        $bgColor = auth()->user()->role == 'superadmin' ? 'bg-purple-900' : 'bg-emerald-900';
    @endphp

    <div class="flex h-screen">
        {{-- Sidebar --}}
        <aside class="hidden md:block w-64 {{ $bgColor }} border-r border-gray-800 p-6 sticky top-0">
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

        {{-- Main content --}}
        <main class="flex-1 overflow-y-auto p-6">
            {{ $slot }}
        </main>
    </div>
</x-app-layout>