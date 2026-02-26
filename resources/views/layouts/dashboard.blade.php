<!DOCTYPE html x-data="{ dark: true }" :class="dark ? 'dark' : ''">
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-gray-950 text-gray-100">

<div class="flex min-h-screen">

    {{-- Sidebar --}}
    <aside class="w-64 bg-gray-900 border-r border-gray-800 p-6 hidden md:block">
        <h2 class="text-lg font-semibold mb-8">Dashboard</h2>

        <nav class="space-y-3 text-sm">
            <a href="#" class="block text-gray-400 hover:text-white">Overview</a>
            <a href="#" class="block text-gray-400 hover:text-white">Hotels</a>
            <a href="#" class="block text-gray-400 hover:text-white">Bookings</a>
        </nav>
    </aside>

    {{-- Content --}}
    <div class="flex-1">

        <header class="border-b border-gray-800 p-6 bg-gray-900/60 backdrop-blur">
            <h1 class="text-xl font-semibold">
                @yield('title')
            </h1>
        </header>

        <main class="p-8">
            {{ $slot }}
        </main>

    </div>

</div>

@livewireScripts
</body>
</html>