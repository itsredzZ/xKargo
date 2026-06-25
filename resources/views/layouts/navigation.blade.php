<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>XKargo</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-gray-100 font-sans antialiased">

<div class="flex h-screen overflow-hidden">

    <!-- Sidebar -->
    <aside class="w-64 bg-gray-800 text-white flex flex-col flex-shrink-0">

        <!-- Logo -->
        <div class="h-16 flex items-center px-6 border-b border-gray-700">
            <a href="{{ route('dashboard') }}" class="text-xl font-bold text-white">
                🚚 XKargo
            </a>
        </div>

        <!-- Nav Links -->
        <nav class="flex-1 px-4 py-4 space-y-1 overflow-y-auto">
            <a href="{{ route('dashboard') }}"
               class="flex items-center gap-3 px-3 py-2 rounded-md text-sm font-medium transition
               {{ request()->routeIs('dashboard') ? 'bg-gray-900 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                Dashboard
            </a>
            <a href="#"
               class="flex items-center gap-3 px-3 py-2 rounded-md text-sm font-medium text-gray-300 hover:bg-gray-700 hover:text-white transition">
                Master Data Kota
            </a>
            <a href="#"
               class="flex items-center gap-3 px-3 py-2 rounded-md text-sm font-medium text-gray-300 hover:bg-gray-700 hover:text-white transition">
                Master Data Truk
            </a>
            <a href="#"
               class="flex items-center gap-3 px-3 py-2 rounded-md text-sm font-medium text-gray-300 hover:bg-gray-700 hover:text-white transition">
                Database Barang
            </a>
            <a href="#"
               class="flex items-center gap-3 px-3 py-2 rounded-md text-sm font-medium text-gray-300 hover:bg-gray-700 hover:text-white transition">
                Depot
            </a>
            <a href="#"
               class="flex items-center gap-3 px-3 py-2 rounded-md text-sm font-medium text-gray-300 hover:bg-gray-700 hover:text-white transition">
                Input Pengiriman
            </a>
            <a href="#"
               class="flex items-center gap-3 px-3 py-2 rounded-md text-sm font-medium text-gray-300 hover:bg-gray-700 hover:text-white transition">
                Optimasi Hasil
            </a>
            <a href="{{ route('riwayat') }}"
               class="flex items-center gap-3 px-3 py-2 rounded-md text-sm font-medium transition
               {{ request()->routeIs('riwayat') ? 'bg-gray-900 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                Riwayat & Laporan
            </a>
            <a href="{{ route('settings') }}"
               class="flex items-center gap-3 px-3 py-2 rounded-md text-sm font-medium transition
               {{ request()->routeIs('settings') ? 'bg-gray-900 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                Settings
            </a>
        </nav>

        <!-- User Info & Logout -->
        <div class="px-4 py-4 border-t border-gray-700">
            <div class="flex items-center gap-2 mb-3">
                <div class="w-8 h-8 rounded-full bg-gray-600 flex items-center justify-center text-sm font-bold">
                    {{ strtoupper(substr(Auth::user()->username, 0, 1)) }}
                </div>
                <div>
                    <div class="text-sm font-medium text-white">{{ Auth::user()->username }}</div>
                    <div class="text-xs text-gray-400">{{ Auth::user()->role }}</div>
                </div>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                        class="w-full text-left px-3 py-2 rounded-md text-sm text-gray-300 hover:bg-gray-700 hover:text-white transition">
                    Log Out
                </button>
            </form>
        </div>

    </aside>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col overflow-hidden">

        <!-- Top bar -->
        <header class="h-16 bg-white border-b border-gray-200 flex items-center px-6">
            <h1 class="text-lg font-semibold text-gray-700">
                @yield('page-title', 'Dashboard')
            </h1>
        </header>

        <!-- Page Content -->
        <main class="flex-1 overflow-y-auto p-6">
            {{ $slot }}
        </main>

    </div>
</div>

@livewireScripts
</body>
</html>