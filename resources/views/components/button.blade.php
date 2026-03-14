@props([
    'variant' => 'primary',
    'href' => null
])

@php
$classes = match($variant) {
    'primary' => 'bg-emerald-700 hover:bg-emerald-500 text-white',
    'secondary' => 'bg-gray-800 hover:bg-gray-700 text-gray-200',
    'action' => 'bg-indigo-700 hover:bg-indigo-500 text-gray-100',
    'danger' => 'bg-red-600 hover:bg-red-500 text-white',
    default => 'bg-indigo-600 hover:bg-indigo-500 text-white',
};

$base = "$classes rounded-xl px-6 py-3 transition text-sm font-medium inline-flex items-center gap-2";
@endphp

@if ($href)
<a href="{{ $href }}" {{ $attributes->merge(['class' => $base]) }}>
    {{ $slot }}
</a>
@else
<button {{ $attributes->merge(['class' => $base]) }}>
    {{ $slot }}
</button>
@endif