@props([
    'variant' => 'primary'
])

@php
$classes = match($variant) {
    'primary' => 'bg-indigo-600 hover:bg-indigo-500 text-white',
    'secondary' => 'bg-gray-800 hover:bg-gray-700 text-gray-200',
    'danger' => 'bg-red-600 hover:bg-red-500 text-white',
    default => 'bg-indigo-600 hover:bg-indigo-500 text-white',
};
@endphp

<button {{ $attributes->merge([
    'class' => "$classes rounded-xl px-4 py-2 transition text-sm font-medium"
]) }}>
    {{ $slot }}
</button>