@props([
    'hotel',
    'room' => null,
    'step' => 'info'
])

@php
$steps = [
    'info' => [
        'label' => 'Info',
        'route' => $room
            ? route('supplier.hotels.rooms.edit', [$hotel, $room])
            : null
    ],
    'images' => [
        'label' => 'Images',
        'route' => $room
            ? route('supplier.rooms.images.index', $room)
            : null
    ],
    'facilities' => [
        'label' => 'Facilities',
        'route' => $room
            ? route('supplier.rooms.facilities', $room)
            : null
    ],
    'inventory' => [
        'label' => 'Inventory',
        'route' => $room
            ? route('supplier.rooms.inventory.index', $hotel)
            : null
    ],
];
@endphp

<div class="flex gap-6 mb-8 border-b pb-4">

@foreach($steps as $key => $s)

    <div>
        @if($s['route'])
            <a href="{{ $s['route'] }}"
               class="
               pb-2 border-b-2
               {{ $step === $key
                    ? 'border-blue-500 text-blue-500'
                    : 'border-transparent text-gray-400 hover:text-white' }}
               ">
               {{ $s['label'] }}
            </a>
        @else
            <span class="text-gray-500">
                {{ $s['label'] }}
            </span>
        @endif
    </div>

@endforeach

</div>