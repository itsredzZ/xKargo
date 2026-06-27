@extends('layouts.app')
@section('title', 'Optimasi & Hasil PSO')

@section('content')
    {{-- Library Eksternal --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <style>
        /* Styling Button Loading */
        .btn-loading { position: relative; color: transparent !important; pointer-events: none; }
        .btn-loading::after {
            content: ''; position: absolute; width: 20px; height: 20px; top: 50%; left: 50%;
            margin-top: -10px; margin-left: -10px;
            border: 3px solid rgba(255,255,255,0.3); border-radius: 50%;
            border-top-color: #fff; animation: spin 1s ease-in-out infinite;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
        
        #peta-rute { height: 450px; width: 100%; border-radius: 0 0 16px 16px; z-index: 0; }
    </style>

    <div class="max-w-7xl mx-auto px-4 py-8 w-full font-sans">

        {{-- ─── PAGE HEADER ─────────────────────────────── --}}
        <header class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-violet-600 flex items-center justify-center shadow-md shadow-violet-100 flex-shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Optimasi & Hasil Pengiriman</h1>
                    <p class="text-sm text-slate-500 mt-0.5">Jalankan algoritma PSO + A* untuk menentukan alokasi truk terbaik hari ini.</p>
                </div>
            </div>
        </header>

        {{-- ─── METRICS RINGKASAN ─────────────────────────── --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm">
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Item Hari Ini</p>
                <p class="text-3xl font-extrabold text-slate-800 mt-1" id="metric-total">{{ $itemsHariIni->count() }}</p>
            </div>
            <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm">
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Carry-over (Wajib Masuk)</p>
                <p class="text-3xl font-extrabold text-amber-600 mt-1" id="metric-co">{{ $itemsHariIni->where('is_carryover', true)->count() }}</p>
            </div>
            <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm">
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Siap Dioptimasi</p>
                <p class="text-3xl font-extrabold text-indigo-600 mt-1" id="metric-ready">{{ $itemsHariIni->count() }}</p>
            </div>
            <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm flex flex-col justify-center">
                <button id="btn-optimize" onclick="runPSO()" class="w-full bg-violet-600 hover:bg-violet-700 active:bg-violet-800 text-white px-6 py-3 rounded-xl text-sm font-bold shadow-md shadow-violet-100 transition-all flex items-center justify-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" /><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    Optimalkan Pengiriman Sekarang
                </button>
                <p class="text-[10px] text-slate-400 text-center mt-2" id="estimasi-waktu">Estimasi: 5 - 30 detik</p>
            </div>
        </div>

        {{-- ─── AREA HASIL (Hidden sebelum dijalankan) ───── --}}
        <div id="area-hasil" class="hidden space-y-6">
            
            {{-- E.1 VISUALISASI ALGORITMA --}}
            <section class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                <header class="bg-slate-50 px-6 py-4 border-b border-slate-100 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
                    <h2 class="font-semibold text-slate-700 text-sm uppercase tracking-wider">E.1 Visualisasi Algoritma</h2>
                </header>
                <div class="p-6 grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Chart Konvergensi -->
                    <div>
                        <h3 class="text-sm font-bold text-slate-600 mb-3">Grafik Konvergensi PSO</h3>
                        <div class="relative bg-slate-50 rounded-xl p-4 border border-slate-100" style="height: 300px;">
                            <canvas id="chartConvergence"></canvas>
                        </div>
                    </div>
                    <!-- Chart Velocity Breakdown -->
                    <div>
                        <div class="flex justify-between items-center mb-3">
                            <h3 class="text-sm font-bold text-slate-600">PSO Velocity Breakdown</h3>
                            <input type="range" id="iterSlider" min="0" max="10" value="0" class="w-32 accent-violet-600">
                            <span id="iterLabel" class="text-xs font-mono text-slate-500 w-16 text-right">Iter: 0</span>
                        </div>
                        <div class="relative bg-slate-50 rounded-xl p-4 border border-slate-100" style="height: 300px;">
                            <canvas id="chartVelocity"></canvas>
                        </div>
                    </div>
                </div>
            </section>

            {{-- PETA RUTE AKTIF --}}
            <section class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                <header class="bg-slate-50 px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" /></svg>
                        <h2 class="font-semibold text-slate-700 text-sm uppercase tracking-wider">Peta Rute Aktif</h2>
                    </div>
                    <div id="map-legend" class="flex gap-4 text-xs text-slate-500"></div>
                </header>
                <div id="peta-rute"></div>
            </section>

            {{-- E.2 HASIL OPERASIONAL --}}
            <section class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                <header class="bg-slate-50 px-6 py-4 border-b border-slate-100 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                    <h2 class="font-semibold text-slate-700 text-sm uppercase tracking-wider">E.2 Detail Alokasi Per Truk</h2>
                </header>
                <div class="p-6 space-y-4" id="truck-details">
                    <!-- Akan diisi oleh JavaScript -->
                </div>
            </section>

            {{-- CARRY OVER & RELOKASI --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <section class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                    <header class="bg-red-50 px-6 py-3 border-b border-red-100 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-red-500"></span>
                        <h2 class="font-semibold text-red-700 text-sm uppercase tracking-wider">Carry-Over</h2>
                    </header>
                    <div class="p-4 max-h-64 overflow-y-auto" id="carryover-table">
                        <p class="text-sm text-slate-400 p-4 text-center">Tidak ada carry over.</p>
                    </div>
                </section>

                <section class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                    <header class="bg-blue-50 px-6 py-3 border-b border-blue-100 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                        <h2 class="font-semibold text-blue-700 text-sm uppercase tracking-wider">Keputusan Relokasi Truk</h2>
                    </header>
                    <div class="p-4 max-h-64 overflow-y-auto" id="relokasi-table">
                        <p class="text-sm text-slate-400 p-4 text-center">Tidak ada relokasi.</p>
                    </div>
                </section>
            </div>

            {{-- RINGKASAN PROFIT & SIMPAN --}}
            <section class="bg-gradient-to-r from-violet-600 to-indigo-600 rounded-2xl shadow-lg p-6 text-white">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mb-6">
                    <div>
                        <p class="text-violet-200 text-xs font-bold uppercase">Total Tarif</p>
                        <p class="text-2xl font-extrabold mt-1" id="sum-tarif">Rp 0</p>
                    </div>
                    <div>
                        <p class="text-violet-200 text-xs font-bold uppercase">Biaya BBM</p>
                        <p class="text-2xl font-extrabold mt-1" id="sum-bbm">Rp 0</p>
                    </div>
                    <div>
                        <p class="text-violet-200 text-xs font-bold uppercase">Biaya Relokasi</p>
                        <p class="text-2xl font-extrabold mt-1" id="sum-relokasi">Rp 0</p>
                    </div>
                    <div>
                        <p class="text-violet-200 text-xs font-bold uppercase">Profit Bersih</p>
                        <p class="text-3xl font-extrabold mt-1" id="sum-profit">Rp 0</p>
                    </div>
                </div>
                <div class="flex gap-4">
                    <form action="{{ route('pso.save') }}" method="POST" class="flex-1">
                        @csrf
                        <button type="submit" class="w-full bg-white text-violet-700 hover:bg-violet-50 font-bold py-3 rounded-xl shadow-md transition-all flex items-center justify-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            Selesai & Simpan Hari Ini
                        </button>
                    </form>
                    <button onclick="location.reload()" class="px-6 py-3 border-2 border-white/30 text-white hover:bg-white/10 font-bold rounded-xl transition-all">
                        Ulangi Optimasi
                    </button>
                </div>
            </section>

        </div>
    </div>

    {{-- ─── JAVASCRIPT UNTUK INTERAKSI & CHART ──────────────────────── --}}
    <script>
        // Variabel Global untuk menampung hasil PSO dari Python
        let psoData = null;
        let chartConv = null;
        let chartVel = null;
        let petaRute = null;

        // Kumpulkan ID item yang ada di halaman ini
        const itemIds = [];
        @foreach($itemsHariIni as $item)
            itemIds.push({{ $item->id }});
        @endforeach

        // Fungsi Utama: Jalankan PSO via AJAX ke Laravel
        async function runPSO() {
            const btn = document.getElementById('btn-optimize');
            btn.classList.add('btn-loading');
            document.getElementById('estimasi-waktu').innerText = "Memproses PSO + A*...";

            try {
                const response = await fetch('{{ route("pso.run") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ item_ids: itemIds })
                });

                const result = await response.json();
                
                if (response.ok) {
                    psoData = result;
                    renderAll(result);
                } else {
                    alert('Error: ' + (result.error || 'Terjadi kesalahan pada script Python'));
                    btn.classList.remove('btn-loading');
                    document.getElementById('estimasi-waktu').innerText = "Gagal. Coba lagi.";
                }
            } catch (error) {
                alert('Gagal menghubungi server: ' + error.message);
                btn.classList.remove('btn-loading');
            }
        }

        // Master Renderer
        function renderAll(data) {
            document.getElementById('area-hasil').classList.remove('hidden');
            document.getElementById('btn-optimize').innerText = "Optimasi Selesai ✓";
            document.getElementById('btn-optimize').classList.add('bg-green-600', 'hover:bg-green-700');
            document.getElementById('btn-optimize').classList.remove('bg-violet-600', 'hover:bg-violet-700', 'btn-loading');
            
            renderConvergenceChart(data.gbest_curve);
            renderVelocityChart(data.velocity_breakdown);
            renderMap(data.best_routes, data.cities_coords);
            renderTruckDetails(data.best_routes);
            renderCarryOver(data.carryover_items);
            renderRelokasi(data.relokasi);
            renderSummary(data);
        }

        // --- 1. CHART KONVERGENSI ---
        function renderConvergenceChart(curve) {
            const ctx = document.getElementById('chartConvergence').getContext('2d');
            if(chartConv) chartConv.destroy();
            chartConv = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: curve.map((_, i) => i),
                    datasets: [{
                        label: 'Gbest Profit (Rp)',
                        data: curve,
                        borderColor: '#7c3aed',
                        backgroundColor: 'rgba(124, 58, 237, 0.1)',
                        fill: true,
                        tension: 0.3,
                        pointRadius: 2
                    }]
                },
                options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: false } } }
            });
        }

        // --- 2. CHART VELOCITY BREAKDOWN + SLIDER ---
        function renderVelocityChart(velData) {
            const ctx = document.getElementById('chartVelocity').getContext('2d');
            const slider = document.getElementById('iterSlider');
            slider.max = velData.length - 1;
            
            function updateVelChart(iter) {
                document.getElementById('iterLabel').innerText = `Iter: ${iter}`;
                const d = velData[iter];
                if(chartVel) chartVel.destroy();
                chartVel = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: ['Inersia (w·v)', 'Kognitif (C1)', 'Sosial (C2)'],
                        datasets: [{
                            data: [d.inersia, d.kognitif, d.sosial],
                            backgroundColor: ['#3b82f6', '#22c55e', '#f59e0b']
                        }]
                    },
                    options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true } } }
                });
            }
            slider.oninput = (e) => updateVelChart(e.target.value);
            updateVelChart(0);
        }

        // --- 3. PETA RUTE LEAFLET ---
        function renderMap(routes, coords) {
            if(petaRute) petaRute.remove();
            petaRute = L.map('peta-rute').setView([-7.6, 112.3], 8);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '© OSM' }).addTo(petaRute);
            
            const colors = ['#ef4444', '#3b82f6', '#22c55e', '#f59e0b', '#a855f7', '#06b6d4'];
            let legendHtml = '';

            Object.keys(routes).forEach((truckId, idx) => {
                const r = routes[truckId];
                const color = colors[idx % colors.length];
                const fullPath = [r.depot, ...r.rute, r.depot_kembali];
                const latLngs = fullPath.map(city => coords[city] ? [coords[city][0], coords[city][1]] : null).filter(Boolean);
                
                if(latLngs.length > 1) {
                    L.polyline(latLngs, { color: color, weight: 4, opacity: 0.8 }).addTo(petaRute);
                }
                // Tambah Marker Depot
                if(coords[r.depot]) L.marker(coords[r.depot]).addTo(petaRute).bindPopup(`<b>Depot: ${r.depot}</b>`);

                legendHtml += `<span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-sm inline-block" style="background:${color}"></span>Truk ${truckId}</span>`;
            });

            document.getElementById('map-legend').innerHTML = legendHtml;
            
            // Auto fit bounds
            const allPoints = Object.values(routes).flatMap(r => {
                const path = [r.depot, ...r.rute, r.depot_kembali];
                return path.map(c => coords[c] ? [coords[c][0], coords[c][1]] : null).filter(Boolean);
            });
            if(allPoints.length) petaRute.fitBounds(allPoints, { padding: [40, 40] });
        }

        // --- 4. DETAIL TRUK (Expander) ---
        function renderTruckDetails(routes) {
            const container = document.getElementById('truck-details');
            container.innerHTML = '';
            
            Object.keys(routes).forEach(truckId => {
                const r = routes[truckId];
                const html = `
                <details class="group border border-slate-200 rounded-xl overflow-hidden">
                    <summary class="flex items-center justify-between p-4 bg-slate-50 cursor-pointer hover:bg-slate-100 transition-colors">
                        <div class="flex items-center gap-3">
                            <span class="font-bold text-slate-800">🚛 Truk ${truckId}</span>
                            <span class="text-xs bg-indigo-50 text-indigo-700 px-2 py-0.5 rounded-md font-semibold">${r.rute.length} Kota</span>
                        </div>
                        <span class="text-sm font-bold text-slate-600">${r.depot} → ${r.depot_kembali}</span>
                    </summary>
                    <div class="p-4 border-t border-slate-100">
                        <div class="grid grid-cols-3 gap-4 mb-4 text-center">
                            <div class="bg-slate-50 p-2 rounded-lg"><p class="text-xs text-slate-400">Jarak</p><p class="font-bold text-slate-800">${r.total_dist.toFixed(1)} km</p></div>
                            <div class="bg-slate-50 p-2 rounded-lg"><p class="text-xs text-slate-400">Tarif</p><p class="font-bold text-green-600">Rp ${r.tarif.toLocaleString('id-ID')}</p></div>
                            <div class="bg-slate-50 p-2 rounded-lg"><p class="text-xs text-slate-400">BBM</p><p class="font-bold text-red-500">Rp ${r.biaya_bbm.toLocaleString('id-ID')}</p></div>
                        </div>
                        <table class="w-full text-sm text-left">
                            <thead class="text-xs text-slate-400 uppercase"><tr><th class="pb-2">Nama Barang</th><th class="pb-2">Tujuan</th><th class="pb-2">Berat</th></tr></thead>
                            <tbody class="divide-y divide-slate-100">
                                ${r.items.map(it => `<tr><td class="py-2 text-slate-700">${it.nama}</td><td class="py-2 text-slate-500">${it.kota_tujuan}</td><td class="py-2 font-mono text-xs">${it.berat_fisik} kg</td></tr>`).join('')}
                            </tbody>
                        </table>
                    </div>
                </details>`;
                container.innerHTML += html;
            });
        }

        // --- 5. CARRY OVER & RELOKASI TABLES ---
        function renderCarryOver(items) {
            const el = document.getElementById('carryover-table');
            if(!items || items.length === 0) { el.innerHTML = '<p class="text-sm text-green-600 font-semibold p-4 text-center">✅ Semua barang berhasil dikirim!</p>'; return; }
            el.innerHTML = `<table class="w-full text-sm text-left"><thead class="text-xs text-slate-400 uppercase"><tr><th>Nama</th><th>Alasan</th></tr></thead><tbody class="divide-y divide-slate-100">${items.map(i=>`<tr><td class="py-2 text-slate-700">${i.nama}</td><td class="py-2 text-xs text-red-500">${i.alasan}</td></tr>`).join('')}</tbody></table>`;
        }

        function renderRelokasi(data) {
            const el = document.getElementById('relokasi-table');
            if(!data || data.length === 0) { el.innerHTML = '<p class="text-sm text-slate-400 p-4 text-center">Tidak perlu relokasi truk.</p>'; return; }
            el.innerHTML = `<table class="w-full text-sm text-left"><thead class="text-xs text-slate-400 uppercase"><tr><th>Truk</th><th>Dari</th><th>Ke</th><th>Keputusan</th></tr></thead><tbody class="divide-y divide-slate-100">${data.map(d=>`<tr><td class="py-2 font-bold">${d.truk}</td><td class="py-2 text-slate-500">${d.dari}</td><td class="py-2 text-slate-500">${d.ke}</td><td class="py-2 text-xs font-bold ${d.relokasi ? 'text-green-600' : 'text-red-500'}">${d.relokasi ? 'RELOKASI' : 'CARRY-OVER'}</td></tr>`).join('')}</tbody></table>`;
        }

        // --- 6. RINGKASAN PROFIT ---
        function renderSummary(data) {
            const format = (num) => `Rp ${num.toLocaleString('id-ID')}`;
            document.getElementById('sum-tarif').innerText = format(data.total_tarif);
            document.getElementById('sum-bbm').innerText = format(data.total_bbm);
            document.getElementById('sum-relokasi').innerText = format(data.biaya_relokasi || 0);
            document.getElementById('sum-profit').innerText = format(data.total_tarif - data.total_bbm - (data.biaya_relokasi || 0));
        }
    </script>
@endsection