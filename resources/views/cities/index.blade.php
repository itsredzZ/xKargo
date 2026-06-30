@extends('layouts.app')
{{-- Mengatur judul halaman yang akan ditampilkan di tab browser --}}
@section('title', 'Manajemen Kota dan Jaringan')

@section('content')
    {{-- Memuat library Leaflet.js untuk menampilkan peta interaktif --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <style>
        {{-- Mengatur tinggi dan lebar container peta --}}
        #peta-kota {
            height: 420px;
            width: 100%;
            border-radius: 0 0 0 0;
            z-index: 0; /* Z-index rendah agar popup tetap terlihat */
        }

        {{-- Override font default Leaflet agar mengikuti font utama halaman --}}
        .leaflet-container {
            font-family: inherit;
        }

        {{-- Gaya teks popup untuk kota bertipe depot (ungu) --}}
        .popup-depot {
            font-weight: 700;
            color: #4338CA;
        }

        {{-- Gaya teks popup untuk kota bertipe reguler (teal) --}}
        .popup-reguler {
            font-weight: 600;
            color: #0f766e;
        }

        {{-- Sembunyikan modal hapus secara default --}}
        #modal-hapus {
            display: none;
        }

        {{-- Tampilkan modal saat diberi class 'aktif' (dipicu via JavaScript) --}}
        #modal-hapus.aktif {
            display: flex;
        }
    </style>

    {{-- Container utama halaman dengan lebar maksimal dan padding responsif --}}
    <div class="max-w-7xl mx-auto px-4 py-8 w-full font-sans">

        {{-- ==================== HEADER HALAMAN ==================== --}}
        <header class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
            <div class="flex items-center gap-4">
                {{-- Ikon globe sebagai identitas visual halaman --}}
                <div
                    class="w-12 h-12 rounded-2xl bg-blue-700 flex items-center justify-center shadow-md shadow-blue-100 flex-shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Manajemen Kota &amp; Jaringan</h1>
                    {{-- Deskripsi singkat fungsi halaman --}}
                    <p class="text-sm text-slate-500 mt-0.5">
                        Kelola status wilayah sebagai titik reguler atau pusat distribusi.
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-2 pl-10 md:pl-0">
                {{-- Badge yang menampilkan total jumlah kota yang terdaftar --}}
                <span
                    class="text-xs font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200 rounded-lg px-3 py-1.5">
                    {{ $cities->total() }} kota terdaftar
                </span>
            </div>
        </header>

        {{-- ==================== SECTION PETA INTERAKTIF ==================== --}}
        <section class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden mb-6">
            {{-- Bar header peta: judul dan legenda warna marker --}}
            <div class="flex items-center justify-between px-5 py-3.5 border-b border-slate-100 bg-slate-50">
                <div class="flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-slate-500" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                    </svg>
                    <span class="text-xs font-bold text-slate-600 uppercase tracking-wider">Peta Kota Aktif</span>
                </div>
                {{-- Legenda: menjelaskan makna warna marker di peta --}}
                <div class="flex items-center gap-4 text-xs text-slate-500">
                    <span class="flex items-center gap-1.5">
                        <span class="w-3 h-3 rounded-sm bg-indigo-600 inline-block"></span> Depot
                    </span>
                    <span class="flex items-center gap-1.5">
                        <span class="w-3 h-3 rounded-full bg-teal-400 inline-block"></span> Kota reguler
                    </span>
                </div>
            </div>
            {{-- Container ini akan diisi peta Leaflet oleh JavaScript --}}
            <div id="peta-kota"></div>
        </section>

        {{-- ==================== FORM TAMBAH KOTA BARU ==================== --}}
        <section aria-labelledby="form-heading"
            class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden mb-6 transition-all hover:shadow-md">
            <header class="bg-slate-50 px-6 py-4 border-b border-slate-100 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-slate-500" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                <h2 id="form-heading" class="font-semibold text-slate-700 text-sm uppercase tracking-wider">Tambah Kota Baru
                </h2>
            </header>

            <div class="p-6">
                {{-- Menampilkan pesan error validasi jika ada --}}
                @if ($errors->any())
                    <div class="mb-4 bg-red-50 border border-red-200 rounded-xl px-4 py-3 text-sm text-red-700">
                        <ul class="list-disc list-inside space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Form POST ke route 'cities.store' untuk menyimpan kota baru --}}
                <form method="POST" action="{{ route('cities.store') }}"
                    class="grid grid-cols-1 md:grid-cols-12 gap-5 items-end">
                    @csrf {{-- Token CSRF untuk keamanan form --}}

                    {{-- Input nama kota (6 kolom dari 12) --}}
                    <div class="md:col-span-6">
                        <label for="city_name" class="block text-xs font-bold text-slate-600 uppercase tracking-wide mb-2">
                            Nama Kota / Kabupaten
                        </label>
                        <input type="text" id="city_name" name="name" value="{{ old('name') }}"
                            class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-sm text-slate-900 focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all outline-none @error('name') border-red-400 bg-red-50 @enderror"
                            placeholder="Contoh: Lumajang" required autocomplete="off">
                    </div>

                    {{-- Checkbox untuk menandai kota sebagai depot (3 kolom) --}}
                    <div class="md:col-span-3 pb-3">
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <div class="relative flex items-center">
                                <input type="checkbox" name="is_depot" id="is_depot" value="1"
                                    {{ old('is_depot') ? 'checked' : '' }}
                                    class="peer w-5 h-5 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 transition-all cursor-pointer">
                            </div>
                            <div>
                                <span
                                    class="text-sm font-semibold text-slate-700 group-hover:text-slate-900 transition-colors block">
                                    Set sebagai Depot
                                </span>
                                <span class="text-xs text-slate-400">Pusat keberangkatan truk</span>
                            </div>
                        </label>
                    </div>

                    {{-- Tombol submit (3 kolom) --}}
                    <div class="md:col-span-3">
                        <button type="submit"
                            class="w-full bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white px-6 py-3 rounded-xl text-sm font-bold shadow-md shadow-indigo-100 transition-all flex items-center justify-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            Tambah Kota
                        </button>
                    </div>
                </form>
            </div>
        </section>

        {{-- ==================== TABEL DAFTAR KOTA ==================== --}}
        <section class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            {{-- Bar header tabel: judul dan input pencarian --}}
            <div
                class="px-6 py-4 border-b border-slate-100 bg-slate-50 flex flex-col sm:flex-row justify-between items-center gap-3">
                <h2 class="font-bold text-slate-700 text-xs uppercase tracking-wider">Daftar Kota</h2>
                {{-- Input pencarian real-time (tanpa reload halaman, difilter via JS) --}}
                <div class="relative w-full sm:w-64">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                            stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input id="search-kota" type="text" placeholder="Cari kota..."
                        class="w-full border border-slate-200 rounded-lg pl-9 pr-3 py-2 text-sm text-slate-700 focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400 outline-none transition-all">
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse min-w-[640px]" id="tabel-kota">
                    <thead>
                        <tr class="text-slate-400 uppercase text-[10px] tracking-widest border-b border-slate-200">
                            <th class="px-6 py-3.5 font-bold">Nama Kota</th>
                            <th class="px-6 py-3.5 font-bold">Koordinat</th>
                            <th class="px-6 py-3.5 font-bold text-center">Tipe</th>
                            <th class="px-6 py-3.5 font-bold text-center">Ubah Tipe</th>
                            <th class="px-6 py-3.5 font-bold text-center">Hapus</th>
                        </tr>
                    </thead>

                    {{-- Body tabel: loop seluruh kota dengan pagination --}}
                    <tbody class="divide-y divide-slate-100" id="tbody-kota">
                        @forelse($cities as $city)
                            {{-- data-nama digunakan untuk pencarian real-time oleh JavaScript --}}
                            <tr class="hover:bg-slate-50 transition-colors group"
                                data-nama="{{ strtolower($city->name) }}">

                                {{-- Kolom Nama: menampilkan ikon berbeda berdasarkan tipe kota --}}
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2.5">
                                        @if ($city->is_depot)
                                            {{-- Ikon gedung untuk depot --}}
                                            <span
                                                class="w-7 h-7 rounded-lg bg-indigo-100 flex items-center justify-center flex-shrink-0">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-indigo-600"
                                                    fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                                    stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                                </svg>
                                            </span>
                                        @else
                                            {{-- Ikon pin lokasi untuk kota reguler --}}
                                            <span
                                                class="w-7 h-7 rounded-lg bg-teal-50 flex items-center justify-center flex-shrink-0">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-teal-500"
                                                    fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                                    stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                                </svg>
                                            </span>
                                        @endif
                                        <div>
                                            <div class="font-bold text-slate-900 text-sm">{{ $city->name }}</div>
                                        </div>
                                    </div>
                                </td>

                                {{-- Kolom Koordinat: ditampilkan dalam format monospace --}}
                                <td class="px-6 py-4">
                                    <span class="font-mono text-xs text-slate-500 bg-slate-100 px-2 py-1 rounded-md">
                                        {{ number_format($city->latitude, 4) }}, {{ number_format($city->longitude, 4) }}
                                    </span>
                                </td>

                                {{-- Kolom Tipe: badge berwarna sesuai status depot/reguler --}}
                                <td class="px-6 py-4 text-center">
                                    @if ($city->is_depot)
                                        <span
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-[10px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                                            DEPOT
                                        </span>
                                    @else
                                        <span
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-[10px] font-bold bg-slate-100 text-slate-600 border border-slate-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-teal-400"></span>
                                            REGULER
                                        </span>
                                    @endif
                                </td>

                                {{-- Kolom Aksi: tombol toggle tipe (depot ↔ reguler) --}}
                                <td class="px-6 py-4 text-center">
                                    {{-- Menggunakan method PATCH untuk update parsial --}}
                                    <form method="POST" action="{{ route('cities.toggleDepot', $city->id) }}">
                                        @csrf
                                        @method('PATCH')
                                        {{-- Warna tombol berubah dinamis berdasarkan tipe saat ini --}}
                                        <button type="submit"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-[11px] font-semibold rounded-lg border transition-all
                                        {{ $city->is_depot
                                            ? 'border-teal-200 text-teal-700 bg-teal-50 hover:bg-teal-100'
                                            : 'border-indigo-200 text-indigo-700 bg-indigo-50 hover:bg-indigo-100' }}"
                                            title="{{ $city->is_depot ? 'Jadikan kota reguler' : 'Jadikan depot' }}">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                                            </svg>
                                            {{ $city->is_depot ? '→ Reguler' : '→ Depot' }}
                                        </button>
                                    </form>
                                </td>

                                {{-- Kolom Hapus: membuka modal konfirmasi, bukan langsung menghapus --}}
                                <td class="px-6 py-4 text-center">
                                    <button type="button"
                                        onclick="bukaModalHapus({{ $city->id }}, '{{ addslashes($city->name) }}')"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-[11px] font-semibold rounded-lg border border-red-200 text-red-600 bg-red-50 hover:bg-red-100 transition-all">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                        Hapus
                                    </button>
                                </td>
                            </tr>
                        @empty
                            {{-- Pesan kosong jika belum ada data kota --}}
                            <tr>
                                <td colspan="5" class="px-6 py-16 text-center">
                                    <div class="flex flex-col items-center gap-3 text-slate-400">
                                        <svg class="h-10 w-10 text-slate-300" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor" stroke-width="1.5">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                        <span class="text-sm font-medium">Belum ada kota terdaftar.</span>
                                        <span class="text-xs">Tambahkan kota pertama lewat form di atas.</span>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Komponen pagination Laravel --}}
            <div class="px-6 py-4 border-t border-slate-200 bg-white">
                {{ $cities->links('pagination::tailwind') }}
            </div>
        </section>

    </div>

    {{-- ==================== MODAL KONFIRMASI HAPUS ==================== --}}
    {{-- Modal ini tersembunyi secara default, ditampilkan via JS saat tombol Hapus diklik --}}
    <div id="modal-hapus" class="fixed inset-0 z-50 items-center justify-center bg-black/40 backdrop-blur-sm"
        role="dialog" aria-modal="true" aria-labelledby="modal-title">
        <div class="bg-white rounded-2xl shadow-xl border border-slate-200 w-full max-w-sm mx-4 p-6">
            <div class="flex items-start gap-4 mb-4">
                {{-- Ikon peringatan hapus --}}
                <div class="w-10 h-10 rounded-xl bg-red-100 flex items-center justify-center flex-shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-600" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </div>
                <div>
                    <h3 id="modal-title" class="font-bold text-slate-900 text-sm">Hapus kota ini?</h3>
                    {{-- Nama kota diisi secara dinamis oleh JavaScript --}}
                    <p class="text-sm text-slate-500 mt-1">
                        <span class="font-semibold text-slate-700" id="modal-nama-kota"></span>
                        dan semua data jarak terkait akan dihapus permanen. Tindakan ini tidak bisa dibatalkan.
                    </p>
                </div>
            </div>
            <div class="flex gap-3 justify-end mt-2">
                {{-- Tombol batal: menutup modal tanpa aksi apapun --}}
                <button type="button" onclick="tutupModalHapus()"
                    class="px-4 py-2 text-sm font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition-all">
                    Batal
                </button>
                {{-- Form hapus: action URL diisi dinamis oleh JS berdasarkan ID kota --}}
                <form id="form-hapus" method="POST" action="">
                    @csrf
                    @method('DELETE') {{-- Menggunakan HTTP method DELETE untuk penghapusan --}}
                    <button type="submit"
                        class="px-4 py-2 text-sm font-bold text-white bg-red-600 hover:bg-red-700 rounded-xl transition-all flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        Ya, Hapus
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- ==================== JAVASCRIPT ==================== --}}
    <script>
        @php
            {{-- Menyiapkan array data kota dari backend (PHP) ke format JavaScript --}}
            {{-- Menggunakan $allCities (bukan $cities) agar SEMUA kota tampil di peta, --}}
            {{-- bukan hanya yang ada di halaman pagination saat ini --}}
            $kotaMap = [];
            foreach ($allCities as $c) {
                $kotaMap[] = [
                    'id' => $c->id,
                    'name' => $c->name,
                    'lat' => (float) $c->latitude,   // Pastikan tipe data float
                    'lon' => (float) $c->longitude,   // Pastikan tipe data float
                    'is_depot' => (bool) $c->is_depot, // Pastikan tipe data boolean
                ];
            }
        @endphp
        {{-- @json() mengkonversi array PHP menjadi JSON yang valid untuk JavaScript --}}
        const datakota = @json($kotaMap);

        // ==================== INISIALISASI PETA LEAFLET ====================
        document.addEventListener('DOMContentLoaded', function() {
            // Membuat peta baru, centered di koordinat Jawa Timur (~-7.6, 112.3), zoom level 8
            const peta = L.map('peta-kota', {
                zoomControl: true
            }).setView([-7.6, 112.3], 8);

            // Menambahkan layer tile dari OpenStreetMap (peta dasar)
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
                maxZoom: 18,
            }).addTo(peta);

            // Membuat custom icon untuk marker depot (kotak ungu)
            const ikonDepot = L.divIcon({
                className: '',
                html: `<div style="width:20px;height:20px;background:#4F46E5;border-radius:4px;border:2px solid white;box-shadow:0 2px 6px rgba(79,70,229,0.45)"></div>`,
                iconSize: [20, 20],
                iconAnchor: [10, 10],    // Titik tengah ikon
                popupAnchor: [0, -14],   // Posisi popup relatif terhadap ikon
            });

            // Membuat custom icon untuk marker kota reguler (lingkaran teal)
            const ikonReguler = L.divIcon({
                className: '',
                html: `<div style="width:14px;height:14px;background:#2DD4BF;border-radius:50%;border:2px solid white;box-shadow:0 2px 5px rgba(45,212,191,0.45)"></div>`,
                iconSize: [14, 14],
                iconAnchor: [7, 7],
                popupAnchor: [0, -10],
            });

            // Array untuk menyimpan semua koordinat, digunakan untuk auto-fit peta
            const bounds = [];

            // Loop setiap kota untuk menambahkan marker ke peta
            datakota.forEach(function(kota) {
                // Pilih ikon berdasarkan tipe kota
                const ikon = kota.is_depot ? ikonDepot : ikonReguler;
                const kelas = kota.is_depot ? 'popup-depot' : 'popup-reguler';
                const label = kota.is_depot ? 'Depot' : 'Kota Reguler';

                // Buat marker dan tambahkan ke peta
                const marker = L.marker([kota.lat, kota.lon], {
                    icon: ikon
                }).addTo(peta);

                // Isi popup marker: nama, koordinat, dan badge tipe
                marker.bindPopup(`
            <div style="font-family:inherit;min-width:140px;">
                <div class="${kelas}" style="font-size:13px;margin-bottom:3px;">${kota.name}</div>
                <div style="font-size:11px;color:#64748b;font-family:monospace;">${kota.lat.toFixed(4)}, ${kota.lon.toFixed(4)}</div>
                <div style="margin-top:5px;">
                    <span style="font-size:10px;font-weight:700;padding:2px 7px;border-radius:4px;
                        background:${kota.is_depot ? '#EEF2FF' : '#F0FDF4'};
                        color:${kota.is_depot ? '#4338CA' : '#15803D'};
                        border:1px solid ${kota.is_depot ? '#C7D2FE' : '#BBF7D0'}">
                        ${label}
                    </span>
                </div>
            </div>
        `);

                // Tambahkan koordinat ke array bounds
                bounds.push([kota.lat, kota.lon]);
            });

            // Auto-fit peta agar semua marker terlihat, dengan padding 40px
            if (bounds.length > 0) {
                peta.fitBounds(bounds, {
                    padding: [40, 40]
                });
            }

            // ==================== GARIS PENGHUBUNG ANTAR DEPOT ====================
            // Filter hanya kota yang bertipe depot
            const depot = datakota.filter(k => k.is_depot);

            // Gambar garis putus-putus antar setiap pasangan depot
            for (let i = 0; i < depot.length; i++) {
                for (let j = i + 1; j < depot.length; j++) {
                    L.polyline([
                        [depot[i].lat, depot[i].lon],
                        [depot[j].lat, depot[j].lon]
                    ], {
                        color: '#818CF8',    // Warna ungu muda
                        weight: 1.2,         // Ketebalan garis
                        opacity: 0.35,       // Transparansi
                        dashArray: '4 5'     // Pola garis putus-putus
                    }).addTo(peta);
                }
            }
        });

        // ==================== FUNGSI MODAL HAPUS ====================

        /**
         * Membuka modal konfirmasi hapus
         * @param {int} id   - ID kota yang akan dihapus
         * @param {string} nama - Nama kota untuk ditampilkan di modal
         */
        function bukaModalHapus(id, nama) {
            document.getElementById('modal-nama-kota').textContent = nama;
            // Set action form ke URL DELETE kota yang bersangkutan
            document.getElementById('form-hapus').action = "{{ url('cities') }}/" + id;
            document.getElementById('modal-hapus').classList.add('aktif');
        }

        /** Menutup modal hapus dengan menghapus class 'aktif' */
        function tutupModalHapus() {
            document.getElementById('modal-hapus').classList.remove('aktif');
        }

        // Tutup modal jika klik di area overlay (luar konten modal)
        document.getElementById('modal-hapus').addEventListener('click', function(e) {
            if (e.target === this) tutupModalHapus();
        });

        // Tutup modal jika user menekan tombol Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') tutupModalHapus();
        });

        // ==================== FUNGSI PENCARIAN REAL-TIME ====================
        // Filter baris tabel berdasarkan input pencarian tanpa reload halaman
        document.getElementById('search-kota').addEventListener('input', function() {
            const q = this.value.toLowerCase(); // Konversi ke huruf kecil untuk pencarian case-insensitive
            document.querySelectorAll('#tbody-kota tr[data-nama]').forEach(function(tr) {
                // Sembunyikan baris jika nama kota tidak mengandung kata kunci
                tr.style.display = tr.dataset.nama.includes(q) ? '' : 'none';
            });
        });
    </script>
@endsection