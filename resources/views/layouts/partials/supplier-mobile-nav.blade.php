<nav aria-label="Supplier sections" class="order-first block w-full max-w-full min-w-0 overflow-hidden border-b border-emerald-800 bg-emerald-950 px-4 py-3 md:hidden dark:border-emerald-800 dark:bg-emerald-950">
    <div class="supplier-mobile-scrollbar w-full min-w-0 overflow-x-auto overscroll-x-contain pb-1">
        <div class="flex min-w-max gap-2 whitespace-nowrap">
        @php
            $supplierNav = [
                ['label' => 'Overview', 'route' => 'supplier.dashboard', 'active' => request()->routeIs('supplier.dashboard')],
                ['label' => 'My Hotels', 'route' => 'supplier.hotels.index', 'active' => request()->routeIs('supplier.hotels.*', 'supplier.myhotels', 'supplier.rooms.*', 'supplier.inventory.*')],
                ['label' => 'Bookings', 'route' => 'supplier.bookings', 'active' => request()->routeIs('supplier.bookings', 'bookings.show', 'bookings.confirm', 'bookings.complete', 'bookings.cancel')],
                ['label' => 'Pending', 'route' => 'supplier.pending', 'active' => request()->routeIs('supplier.pending')],
                ['label' => 'Revenue', 'route' => 'supplier.revenue', 'active' => request()->routeIs('supplier.revenue')],
            ];
        @endphp
        @foreach($supplierNav as $item)
            <a href="{{ route($item['route']) }}"
               @class([
                   'inline-flex min-h-11 shrink-0 items-center rounded-xl border px-4 py-2 text-sm font-medium transition focus:outline-none focus:ring-2 focus:ring-emerald-300',
                   'border-emerald-300 bg-emerald-700 text-white' => $item['active'],
                   'border-emerald-800 text-emerald-100 hover:border-emerald-500 hover:bg-emerald-900' => ! $item['active'],
               ])
               @if($item['active']) aria-current="page" @endif>{{ $item['label'] }}</a>
        @endforeach
        </div>
    </div>
</nav>
