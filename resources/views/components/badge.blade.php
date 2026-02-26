@props(['variant' => 'success'])

@php
$classes = match($variant) {
    'success' => 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20',
    'danger' => 'bg-red-500/10 text-red-400 border border-red-500/20',
    'warning' => 'bg-yellow-500/10 text-yellow-400 border border-yellow-500/20',
    default => 'bg-gray-500/10 text-gray-400 border border-gray-500/20',
};
@endphp

<span {{ $attributes->merge([
    'class' => "$classes px-3 py-1 rounded-full text-xs font-medium"
]) }}>
    {{ $slot }}
</span>