@php
    $steps = $hotel->setupChecklist();
@endphp

<div class="flex gap-6 border-b pb-4 mb-6 text-sm">

    <a href="{{ route('supplier.hotels.setup.info',$hotel) }}">
        Hotel Info
        {!! $steps['info'] ? '✓' : '' !!}
    </a>

    <a href="{{ route('supplier.hotels.setup.rooms',$hotel) }}">
        Rooms
        {!! $steps['rooms'] ? '✓' : '' !!}
    </a>

    <a href="{{ route('supplier.hotels.setup.inventory',$hotel) }}">
        Inventory
        {!! $steps['inventory'] ? '✓' : '' !!}
    </a>

    <a href="{{ route('supplier.hotels.setup.images',$hotel) }}">
        Images
        {!! $steps['images'] ? '✓' : '' !!}
    </a>

    <a href="{{ route('supplier.hotels.setup.publish',$hotel) }}">
        Publish
    </a>

</div>