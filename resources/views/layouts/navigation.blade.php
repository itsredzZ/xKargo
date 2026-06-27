<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>XKargo</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    @stack('styles')
</head>
<body class="bg-gray-100 font-sans antialiased">

<div class="flex h-screen overflow-hidden">

    <aside class="w-64 bg-gray-800 text-white flex flex-col flex-shrink-0">

        <div class="h-16 flex items-center px-6 border-b border-gray-700">
            <a href="{{ route('dashboard') }}" class="text-xl font-bold text-white">
                🚚 XKargo
            </a>
        </div>

        <nav class="flex-1 px-4 py-4 space-y-1 overflow-y-auto">
            
            <a href="{{ route('dashboard') }}"
               class="flex items-center gap-3 px-3 py-2 rounded-md text-sm font-medium transition {{ request()->routeIs('dashboard') ? 'bg-gray-900 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                Dashboard
            </a>

            <a href="{{ route('cities.index') }}"
               class="flex items-center gap-3 px-3 py-2 rounded-md text-sm font-medium transition {{ request()->routeIs('cities.*') ? 'bg-gray-900 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                Master Data Kota
            </a>

            <a href="{{ route('trucks.index') }}"
               class="flex items-center gap-3 px-3 py-2 rounded-md text-sm font-medium transition {{ request()->routeIs('trucks.*') ? 'bg-gray-900 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                Master Data Truk
            </a>

            <a href="{{ route('pso.items') }}"
               class="flex items-center gap-3 px-3 py-2 rounded-md text-sm font-medium transition {{ request()->routeIs('pso.items') ? 'bg-gray-900 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                Database Barang
            </a>

            <a href="{{ route('depot.index') }}"
               class="flex items-center gap-3 px-3 py-2 rounded-md text-sm font-medium transition {{ request()->routeIs('depot.*') ? 'bg-gray-900 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                Depot
            </a>

            <a href="{{ route('pso.orders') }}"
               class="flex items-center gap-3 px-3 py-2 rounded-md text-sm font-medium transition {{ request()->routeIs('pso.orders') ? 'bg-gray-900 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                Input Pengiriman
            </a>

            <a href="{{ route('pso.results') }}"
               class="flex items-center gap-3 px-3 py-2 rounded-md text-sm font-medium transition {{ request()->routeIs('pso.results') ? 'bg-gray-900 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                Optimasi Hasil
            </a>

            <a href="{{ route('settings.index') }}"
               class="flex items-center gap-3 px-3 py-2 rounded-md text-sm font-medium transition {{ request()->routeIs('settings.*') ? 'bg-gray-900 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                Settings
            </a>
        </nav>

        <div class="px-4 py-4 border-t border-gray-700">
            @if(Auth::check())
            <div class="flex items-center gap-2 mb-3">
                <div class="w-8 h-8 rounded-full bg-gray-600 flex items-center justify-center text-sm font-bold">
                    {{ strtoupper(substr(Auth::user()->username ?? 'U', 0, 1)) }}
                </div>
                <div>
                    <div class="text-sm font-medium text-white">{{ Auth::user()->username ?? 'User' }}</div>
                    <div class="text-xs text-gray-400">{{ Auth::user()->role ?? 'Admin' }}</div>
                </div>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                        class="w-full text-left px-3 py-2 rounded-md text-sm text-gray-300 hover:bg-gray-700 hover:text-white transition">
                    Log Out
                </button>
            </form>
            @endif
        </div>

    </aside>

    <div class="flex-1 flex flex-col overflow-hidden">

        <header class="h-16 bg-white border-b border-gray-200 flex items-center justify-between px-6">
            <div class="flex items-center gap-4">
                @if(!request()->routeIs('dashboard'))
                <a href="{{ route('dashboard') }}" class="text-sm bg-gray-100 hover:bg-gray-200 text-gray-600 px-3 py-1.5 rounded-md flex items-center gap-1 transition font-medium">
                    ← Dashboard
                </a>
                @endif

                <h1 class="text-lg font-semibold text-gray-700">
                    @yield('title', 'Dashboard')
                </h1>
            </div>
        </header>

        <main class="flex-1 overflow-y-auto @yield('fullscreen', 'p-6')">
            {{ $slot }}
            @yield('content')
        </main>

    </div>
</div>

@livewireScripts
@stack('scripts')
</body>
</html>