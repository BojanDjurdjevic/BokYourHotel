<div {{ $attributes->merge([
    'class' => 'bg-gray-900 border border-gray-800 rounded-2xl shadow-sm p-6'
]) }}>
    {{ $slot }}
</div>