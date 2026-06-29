{{-- resources/views/layouts/app.blade.php --}}
{{-- POLA 3: Layout utama — Laravel sebagai portal, Streamlit embed di dalam --}}
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'XKargo') — Dashboard</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400&display=swap"
        rel="stylesheet">

    {{-- Tailwind CSS via CDN (ganti dengan Vite di production) --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#eff6ff',
                            500: '#3b82f6',
                            700: '#1d4ed8',
                            900: '#1e3a8a'
                        }
                    }
                }
            }
        }
    </script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            text-rendering: optimizeLegibility;
        }

        h1,
        h2,
        h3,
        .font-bold {
            letter-spacing: -0.025em;
        }

        .nav-item.active {
            background-color: #1d4ed8;
            color: white;
        }

        .nav-item:not(.active):hover {
            background-color: #1e40af;
            color: white;
        }

        #streamlit-frame {
            border: none;
            width: 100%;
            height: calc(100vh - 64px);
        }

        #frame-loader {
            position: absolute;
            inset: 0;
            background: #f8fafc;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10;
        }
    </style>
    @stack('styles')
</head>

<body class="bg-gray-50 text-gray-800 font-sans antialiased">

    {{-- ── TOP NAVBAR ────────────────────────────────────────────────────────── --}}
    <nav class="bg-blue-800 text-white h-16 flex items-center px-6 shadow-md fixed top-0 left-0 right-0 z-50">
        
        {{-- PERBAIKAN 1: Logo sekarang dibungkus tag <a> agar bisa diklik balik ke Dashboard --}}
        <a href="{{ route('dashboard') }}" class="flex items-center gap-3 w-64 cursor-pointer group text-white no-underline">
            <div class="bg-white rounded-lg p-1.5 group-hover:scale-105 transition transform">
                <svg class="w-7 h-7 text-blue-800" fill="currentColor" viewBox="0 0 24 24">
                    <path
                        d="M20 8h-3V4H3c-1.1 0-2 .9-2 2v11h2c0 1.66 1.34 3 3 3s3-1.34 3-3h6c0 1.66 1.34 3 3 3s3-1.34 3-3h2v-5l-3-4zm-1.5 1.5l1.96 2.5H17V9.5h1.5zM6 18c-.55 0-1-.45-1-1s.45-1 1-1 1 .45 1 1-.45 1-1 1zm11 0c-.55 0-1-.45-1-1s.45-1 1-1 1 .45 1 1-.45 1-1 1z" />
                </svg>
            </div>
            <span class="font-bold text-xl tracking-tight group-hover:text-blue-200 transition">XKargo</span>
        </a>

        <div class="flex-1"></div>

        {{-- User info --}}
        <div class="flex items-center gap-3">
            <span class="text-blue-200 text-sm">{{ auth()->user()->username ?? 'Admin' }}</span>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                    class="bg-blue-700 hover:bg-blue-600 text-white text-sm px-3 py-1.5 rounded-lg transition">
                    Keluar
                </button>
            </form>
        </div>
    </nav>

    {{-- ── SIDEBAR + CONTENT WRAPPER ───────────────────────────────────────── --}}
    <div class="flex pt-16">

        {{-- Sidebar --}}
        <aside class="w-64 bg-blue-900 text-white min-h-screen fixed top-16 left-0 bottom-0 overflow-y-auto">
            <nav class="p-4 space-y-1">
                
                {{-- PERBAIKAN 2: Memasukkan menu Utama / Dashboard (Pesan titipan Valen) --}}
                <a href="{{ route('dashboard') }}"
                    class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition font-medium {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                     <span>Dashboard</span>
                </a>

                {{-- Grup: Data Master --}}
                <p class="text-blue-400 text-xs font-semibold uppercase tracking-wider px-3 pt-4 pb-1">
                    Data Master
                </p>
                <a href="{{ route('cities.index') }}"
                    class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition {{ request()->routeIs('cities.*') ? 'active' : '' }}">
                    <span>Kota & Jaringan</span>
                </a>
                <a href="{{ route('trucks.index') }}"
                    class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition {{ request()->routeIs('trucks.*') ? 'active' : '' }}">
                    <span>Armada Truk</span>
                </a>
                <a href="{{ route('depot.index') }}"
                    class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition {{ request()->routeIs('depot.*') ? 'active' : '' }}">
                    <span>Depot</span>
                </a>

                {{-- PERBAIKAN 3: Memasukkan menu Database Barang (Tugas Chelsea) --}}
                <a href="{{ route('items.index') }}"
                    class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition {{ request()->routeIs('pso.items') ? 'active' : '' }}">
                    <span>Database Barang</span>
                </a>

                {{-- Grup: PSO Engine (Streamlit embed) --}}
                <p class="text-blue-400 text-xs font-semibold uppercase tracking-wider px-3 pt-5 pb-1">
                    PSO Engine
                </p>
                <a href="{{ route('pso.orders') }}"
                    class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition {{ request()->routeIs('pso.orders') ? 'active' : '' }}">
                    <span>Input Pesanan</span>
                </a>
                <a href="{{ route('pso.results') }}"
                    class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition {{ request()->routeIs('pso.results') ? 'active' : '' }}">
                    <span>Hasil & Peta</span>
                </a>

                {{-- Grup: Administrasi --}}
                <p class="text-blue-400 text-xs font-semibold uppercase tracking-wider px-3 pt-5 pb-1">
                    Administrasi
                </p>
                <a href="{{ route('riwayat.index') }}"
                    class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition {{ request()->routeIs('riwayat.*') ? 'active' : '' }}">
                    <span>Riwayat & Laporan</span>
                </a>
                <a href="{{ route('settings.index') }}"
                    class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition {{ request()->routeIs('settings.*') ? 'active' : '' }}">
                    <span>Parameter PSO</span>
                </a>
            </nav>

            {{-- Info versi --}}
            <div class="absolute bottom-0 left-0 right-0 p-4">
                <div class="bg-blue-800 rounded-lg p-3 text-xs text-blue-300 text-center">
                    XKargo v2.0<br>Laravel + Streamlit
                </div>
            </div>
        </aside>

        {{-- Main Content --}}
        <main class="ml-64 flex-1 @yield('fullscreen', '') min-h-screen">
            @hasSection('fullscreen')
                @yield('content')
            @else
                <div class="p-6">
                    @if (session('success'))
                        <div class="mb-6 flex items-center gap-3 bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-medium px-5 py-3.5 rounded-xl">
                            <svg class="h-4 w-4 flex-shrink-0 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                            {{ session('success') }}
                        </div>
                    @endif
                    @if (session('error'))
                        <div class="mb-6 flex items-center gap-3 bg-red-50 border border-red-200 text-red-800 text-sm font-medium px-5 py-3.5 rounded-xl">
                            <svg class="h-4 w-4 flex-shrink-0 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            {{ session('error') }}
                        </div>
                    @endif
                    @yield('content')
                </div>
            @endif
        </main>
    </div>

    @stack('scripts')
    @push('scripts')
        <script>
            document.getElementById('streamlit-frame')?.addEventListener('load', function() {
                const loader = document.getElementById('frame-loader');
                if (loader) loader.style.display = 'none';
            });
        </script>
    @endpush
</body>

</html>