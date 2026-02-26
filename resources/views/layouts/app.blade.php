<!DOCTYPE html x-data="{ dark: true }" :class="dark ? 'dark' : ''">
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'StayCore') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="font-sans antialiased bg-gray-950 text-gray-100">

<div class="min-h-screen flex flex-col">

    @include('layouts.navigation')

    {{-- Page Heading --}}
    @isset($header)
        <header class="border-b border-gray-800 bg-gray-900/60 backdrop-blur">
            <div class="max-w-7xl mx-auto px-6 py-6">
                {{ $header }}
            </div>
        </header>
    @endisset

    {{-- Main Content --}}
    <main class="flex-1">
        <div class="max-w-7xl mx-auto px-6 py-10">
            {{ $slot }}
        </div>
    </main>

    @include('layouts.footer')

</div>

@livewireScripts
</body>
</html>