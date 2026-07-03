<button {{ $attributes->merge(['type' => 'submit', 
    'class' => 'inline-flex items-center px-4 py-2 mb-6
    bg-emerald-600 hover:bg-emerald-500
    border border-transparent 
    rounded-md font-semibold text-xs text-white 
    transition ease-in-out duration-150']) }}
>
    {{ $slot }}
</button>
