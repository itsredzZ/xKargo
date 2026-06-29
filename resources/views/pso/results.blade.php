@extends('layouts.app')
@section('title', 'Optimasi & Hasil PSO')
@section('content')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<style>
/* ── Spinner tombol loading ── */
.btn-loading { color: transparent !important; pointer-events: none; }
.btn-loading::after {
    content: ''; position: absolute;
    width: 20px; height: 20px;
    top: 50%; left: 50%;
    margin: -10px 0 0 -10px;
    border: 3px solid rgba(255,255,255,.3);
    border-radius: 50%; border-top-color: #fff;
    animation: spin 1s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

/* ── Peta ── */
#peta-rute { height: 520px; width: 100%; z-index: 0; }
</style>

<div class="max-w-7xl mx-auto px-4 py-8 w-full font-sans">

    {{-- ─── HEADER ─────────────────────────────────────────────── --}}
    <header class="flex items-center justify-between mb-8">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-violet-600 flex items-center justify-center shadow-md shadow-violet-100">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
            </div>
            <div>
                <h1 class="text-2xl font-extrabold text-slate-900">Optimasi & Hasil PSO</h1>
                <p class="text-sm text-slate-500">Jalankan algoritma PSO untuk alokasi truk terbaik.</p>
            </div>
        </div>
    </header>

    {{-- ─── TOMBOL RUN ──────────────────────────────────────────── --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 mb-6 flex flex-col md:flex-row items-center justify-between gap-4">
        <div id="status-text" class="text-sm text-slate-600">Siap menjalankan optimasi berdasarkan pesanan hari ini.</div>
        <button id="btn-run" onclick="runPSO()"
            class="relative bg-violet-600 hover:bg-violet-700 text-white px-8 py-3 rounded-xl text-sm font-bold shadow-md flex items-center gap-2 w-fit">
            🚀 Jalankan Optimasi PSO
        </button>
    </div>

    {{-- ─── AREA HASIL (hidden sampai PSO selesai) ─────────────── --}}
    <div id="area-hasil" class="hidden space-y-6">

        {{-- CHARTS ──────────────────────────────────────────────── --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            {{-- Konvergensi Gbest ─────────────── --}}
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                <div class="flex items-start justify-between mb-4">
                    <div>
                        <h3 class="text-sm font-bold text-slate-700">Grafik Konvergensi Gbest</h3>
                        <p class="text-xs text-slate-400 mt-0.5">Profit terbaik PSO per iterasi</p>
                    </div>
                    {{-- Stats awal/akhir di atas chart --}}
                    <div class="flex gap-4 text-right text-xs flex-shrink-0">
                        <div>
                            <p class="text-slate-400">Profit Awal</p>
                            <p class="font-bold text-slate-600 mt-0.5" id="conv-awal">—</p>
                        </div>
                        <div>
                            <p class="text-slate-400">Profit Akhir</p>
                            <p class="font-bold text-emerald-600 mt-0.5" id="conv-akhir">—</p>
                        </div>
                    </div>
                </div>
                {{-- Container tinggi eksplisit — ini yang bikin chart besar --}}
                <div style="position: relative; height: 360px;">
                    <canvas id="chartConv"></canvas>
                </div>
            </div>

            {{-- Velocity Breakdown ───────────── --}}
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 flex flex-col">
                <div class="mb-4">
                    <div class="flex items-start justify-between mb-3">
                        <div>
                            <h3 class="text-sm font-bold text-slate-700">Velocity Breakdown per Iterasi</h3>
                            <p class="text-xs text-slate-400 mt-0.5">v = w·v + C₁r₁(Pbest−x) + C₂r₂(Gbest−x)</p>
                        </div>
                        {{-- Label iterasi yang jelas: "X / N" --}}
                        <span id="iter-label"
                            class="flex-shrink-0 text-xs font-mono font-bold bg-violet-50 text-violet-700 border border-violet-200 px-3 py-1.5 rounded-xl whitespace-nowrap">
                            Iterasi 0 / 0
                        </span>
                    </div>
                    {{-- Slider dengan angka kiri/kanan --}}
                    <div class="flex items-center gap-2">
                        <span class="text-xs text-slate-400 w-4 text-center">0</span>
                        <input type="range" id="iterSlider" min="0" max="1" value="0"
                            class="flex-1 accent-violet-600 h-2 cursor-pointer">
                        <span id="iter-max" class="text-xs text-slate-400 w-6 text-right">0</span>
                    </div>
                </div>
                {{-- Container tinggi eksplisit --}}
                <div style="position: relative; height: 300px;">
                    <canvas id="chartVel"></canvas>
                </div>
            </div>
        </div>

        {{-- PETA RUTE ─────────────────────────────────────────────── --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <h3 class="text-sm font-bold text-slate-700">Peta Rute Pengiriman</h3>
                    <p class="text-xs text-slate-400 mt-0.5">
                        Klik marker untuk info. Angka bulat = urutan kunjungan. D = depot asal. ↩ = depot tujuan akhir.
                    </p>
                </div>
                <div class="flex items-center gap-4 text-xs text-slate-500 flex-wrap">
                    <span class="flex items-center gap-1.5">
                        <span class="w-4 h-4 rounded-[3px] bg-indigo-600 inline-block border border-white shadow-sm"></span>
                        Depot
                    </span>
                    <span class="flex items-center gap-1.5">
                        <span class="w-3 h-3 rounded-full bg-teal-400 inline-block border border-white shadow-sm"></span>
                        Kota Reguler
                    </span>
                    <span class="flex items-center gap-1.5">
                        <span style="display:inline-flex;align-items:center;justify-content:center;width:20px;height:20px;background:#64748b;border-radius:50%;color:white;font-size:9px;font-weight:800;border:2px solid white;">1</span>
                        Urutan Kunjungan
                    </span>
                </div>
            </div>
            <div class="relative">
                <div id="peta-rute"></div>
                {{-- Floating legend truk (diisi JS) --}}
                <div id="truck-legend"
                    class="absolute bottom-4 left-3 z-[999] bg-white/95 backdrop-blur-sm rounded-xl shadow-lg border border-slate-200 p-3 hidden">
                    <p class="font-bold text-slate-600 uppercase tracking-wide text-[10px] mb-2 pb-1.5 border-b border-slate-100">
                        Legenda Truk
                    </p>
                    <div id="truck-legend-items" class="space-y-1.5 text-xs"></div>
                </div>
            </div>
        </div>

        {{-- DETAIL TRUK ──────────────────────────────────────────── --}}
        <div id="truck-details" class="space-y-4"></div>

        {{-- RINGKASAN PROFIT + TOMBOL SIMPAN ────────────────────── --}}
        <section class="bg-gradient-to-r from-violet-600 to-indigo-600 rounded-2xl shadow-lg p-6 text-white">
            <div class="grid grid-cols-3 gap-6 mb-6">
                <div>
                    <p class="text-violet-200 text-xs font-bold uppercase">Total Tarif</p>
                    <p class="text-2xl font-extrabold mt-1" id="sum-tarif">Rp 0</p>
                </div>
                <div>
                    <p class="text-violet-200 text-xs font-bold uppercase">Total BBM</p>
                    <p class="text-2xl font-extrabold mt-1" id="sum-bbm">Rp 0</p>
                </div>
                <div>
                    <p class="text-violet-200 text-xs font-bold uppercase">💰 Profit Bersih</p>
                    <p class="text-2xl font-extrabold mt-1" id="sum-profit">Rp 0</p>
                </div>
            </div>
            <form action="{{ route('pso.save') }}" method="POST" class="flex gap-4">
                @csrf
                <button type="submit"
                    class="bg-white text-violet-700 hover:bg-violet-50 font-bold py-3 px-6 rounded-xl transition-all">
                    ✅ Selesai & Simpan Hari Ini
                </button>
                <button type="button" onclick="location.reload()"
                    class="border-2 border-white/30 px-6 py-3 rounded-xl font-bold hover:bg-white/10 transition-all">
                    🔁 Ulangi
                </button>
            </form>
        </section>
    </div>
</div>

<script>
// ─── Data kota dari Laravel (server-side) ─────────────────────────
// Dipakai untuk tahu mana depot dan mana kota reguler di peta.
// Diisi oleh controller via compact('allCities').
const ALL_CITIES = @json($allCities ?? []);

const TRUCK_COLORS = [
    '#ef4444', // merah
    '#3b82f6', // biru
    '#22c55e', // hijau
    '#f59e0b', // amber
    '#a855f7', // ungu
    '#06b6d4', // cyan
    '#f97316', // oranye
    '#ec4899', // pink
];

let psoData = null, chartConv = null, chartVel = null, petaRute = null;

// ─── Jalankan PSO ─────────────────────────────────────────────────
async function runPSO() {
    const btn = document.getElementById('btn-run');
    btn.classList.add('btn-loading');

    let dotCount = 0;
    const interval = setInterval(() => {
        dotCount = (dotCount + 1) % 4;
        document.getElementById('status-text').innerText =
            'Sedang memanggil Python PSO' + '.'.repeat(dotCount) + ' (estimasi 15–60 detik)';
    }, 400);

    try {
        const res = await fetch('{{ route("pso.run") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
        });
        const data = await res.json();

        clearInterval(interval);

        if (!res.ok || data.error) {
            document.getElementById('status-text').innerText = '❌ Error: ' + (data.error || 'Unknown error');
            if (data.python_output) console.error('Python raw output:', data.python_output);
            btn.classList.remove('btn-loading');
            return;
        }

        psoData = data;
        renderAll(data);

        const profit = (data.total_tarif || 0) - (data.total_bbm || 0);
        document.getElementById('status-text').innerText =
            '✅ PSO Selesai! Profit terbaik: Rp ' + Math.round(profit).toLocaleString('id-ID');

        btn.innerText  = '✅ Optimasi Selesai';
        btn.disabled   = true;
        btn.onclick    = null;
        btn.classList.remove('bg-violet-600', 'hover:bg-violet-700');
        btn.classList.add('bg-green-600', 'opacity-70', 'cursor-not-allowed');

    } catch (e) {
        clearInterval(interval);
        document.getElementById('status-text').innerText = '❌ Gagal: ' + e.message;
        console.error(e);
    } finally {
        btn.classList.remove('btn-loading');
    }
}

// ─── Render semua visualisasi ─────────────────────────────────────
function renderAll(d) {
    document.getElementById('area-hasil').classList.remove('hidden');
    renderConvergenceChart(d);
    renderVelocityChart(d);
    renderMap(d);
    renderTruckDetails(d);

    const fmt = n => 'Rp ' + Math.round(n || 0).toLocaleString('id-ID');
    document.getElementById('sum-tarif').innerText  = fmt(d.total_tarif);
    document.getElementById('sum-bbm').innerText    = fmt(d.total_bbm);
    document.getElementById('sum-profit').innerText = fmt((d.total_tarif || 0) - (d.total_bbm || 0));
}

// ─── 1. Grafik Konvergensi ────────────────────────────────────────
function renderConvergenceChart(d) {
    if (chartConv) chartConv.destroy();

    const curve = d.gbest_curve || [];
    const awal  = curve[0] || 0;
    const akhir = curve[curve.length - 1] || 0;

    // Tampilkan stats di atas chart
    document.getElementById('conv-awal').innerText  = 'Rp ' + Math.round(awal).toLocaleString('id-ID');
    document.getElementById('conv-akhir').innerText = 'Rp ' + Math.round(akhir).toLocaleString('id-ID');

    chartConv = new Chart(document.getElementById('chartConv'), {
        type: 'line',
        data: {
            labels: curve.map((_, i) => i),
            datasets: [{
                label: 'Gbest Profit (Rp)',
                data: curve,
                borderColor: '#7c3aed',
                backgroundColor: 'rgba(124,58,237,0.12)',
                borderWidth: 2.5,
                fill: true,
                tension: 0.35,
                pointRadius: 0,         // titik dihilangkan agar garis bersih
                pointHoverRadius: 6,
                pointHoverBackgroundColor: '#7c3aed',
            }]
        },
        options: {
            responsive: true,           // ← mengikuti ukuran container
            maintainAspectRatio: false, // ← wajib agar height container berlaku
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        title: ctx => 'Iterasi ' + ctx[0].label,
                        label: ctx => ' Profit: Rp ' + Math.round(ctx.parsed.y).toLocaleString('id-ID'),
                    }
                }
            },
            scales: {
                x: {
                    title: { display: true, text: 'Iterasi', font: { size: 11 } },
                    grid: { color: '#f1f5f9' },
                    ticks: { maxTicksLimit: 12, font: { size: 10 } }
                },
                y: {
                    beginAtZero: false,
                    grid: { color: '#f1f5f9' },
                    ticks: {
                        font: { size: 10 },
                        callback: val => 'Rp ' + (val / 1_000_000).toFixed(1) + ' Jt'
                    }
                }
            }
        }
    });
}

// ─── 2. Velocity Breakdown ────────────────────────────────────────
function renderVelocityChart(d) {
    const velData  = d.velocity_breakdown || [];
    const slider   = document.getElementById('iterSlider');
    const labelEl  = document.getElementById('iter-label');
    const maxLabel = document.getElementById('iter-max');
    const maxIter  = Math.max(0, velData.length - 1);

    slider.max   = maxIter;
    slider.value = 0;
    maxLabel.innerText   = maxIter;
    labelEl.innerText    = 'Iterasi 0 / ' + maxIter;

    function drawVel(i) {
        if (chartVel) chartVel.destroy();
        const v = velData[i] || {};

        chartVel = new Chart(document.getElementById('chartVel'), {
            type: 'bar',
            data: {
                labels: ['Inersia (w·v)', 'Kognitif (C₁r₁)', 'Sosial (C₂r₂)'],
                datasets: [{
                    data: [
                        parseFloat(v.inersia  || 0),
                        parseFloat(v.kognitif || 0),
                        parseFloat(v.sosial   || 0),
                    ],
                    backgroundColor: [
                        'rgba(59,130,246,0.85)',
                        'rgba(34,197,94,0.85)',
                        'rgba(245,158,11,0.85)',
                    ],
                    borderColor:  ['#3b82f6', '#22c55e', '#f59e0b'],
                    borderWidth:  1.5,
                    borderRadius: 6,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: ctx => ' ' + parseFloat(ctx.parsed.y).toFixed(5),
                        }
                    }
                },
                scales: {
                    x: { grid: { display: false }, ticks: { font: { size: 10 } } },
                    y: {
                        beginAtZero: true,
                        grid: { color: '#f1f5f9' },
                        ticks: { font: { size: 10 }, callback: v => parseFloat(v).toFixed(3) }
                    }
                }
            }
        });
    }

    // Update label "X / N" setiap kali slider digeser
    slider.oninput = function () {
        const i = parseInt(this.value);
        labelEl.innerText = 'Iterasi ' + i + ' / ' + maxIter;
        drawVel(i);
    };

    drawVel(0);
}

// ─── 3. Peta Rute ─────────────────────────────────────────────────
function renderMap(d) {
    if (petaRute) { petaRute.remove(); petaRute = null; }

    petaRute = L.map('peta-rute').setView([-7.6, 112.3], 8);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© <a href="https://osm.org/copyright">OpenStreetMap</a>',
        maxZoom: 18,
    }).addTo(petaRute);

    // ── Bangun map koordinat: gabungkan data server + Python output ──
    // Server-side (ALL_CITIES) → ada flag is_depot
    // Python output (d.cities_coords) → koordinat dari engine
    const cityInfo = {};
    ALL_CITIES.forEach(c => {
        cityInfo[c.name] = { lat: c.lat, lon: c.lon, is_depot: c.is_depot };
    });
    // Tambah kota dari Python jika belum ada di ALL_CITIES
    Object.entries(d.cities_coords || {}).forEach(([name, coords]) => {
        if (!cityInfo[name]) {
            cityInfo[name] = { lat: coords[0], lon: coords[1], is_depot: false };
        }
    });

    // ── Fungsi helper: icon depot ──
    function makeDepotIcon(color) {
        return L.divIcon({
            className: '',
            html: `<div style="
                width:20px; height:20px;
                background:${color};
                border-radius:4px;
                border:2.5px solid white;
                box-shadow:0 2px 8px rgba(0,0,0,0.3);
            "></div>`,
            iconSize: [20, 20], iconAnchor: [10, 10], popupAnchor: [0, -13],
        });
    }

    // ── Fungsi helper: icon kota reguler ──
    const cityIcon = L.divIcon({
        className: '',
        html: `<div style="
            width:10px; height:10px;
            background:#2DD4BF;
            border-radius:50%;
            border:2px solid white;
            box-shadow:0 1px 4px rgba(0,0,0,0.2);
        "></div>`,
        iconSize: [10, 10], iconAnchor: [5, 5], popupAnchor: [0, -8],
    });

    // ── Fungsi helper: icon nomor stop ──
    function makeStopIcon(num, color) {
        return L.divIcon({
            className: '',
            html: `<div style="
                width:26px; height:26px;
                background:${color};
                border-radius:50%;
                border:2.5px solid white;
                box-shadow:0 2px 8px rgba(0,0,0,0.28);
                color:white;
                font-size:11px;
                font-weight:800;
                display:flex;
                align-items:center;
                justify-content:center;
            ">${num}</div>`,
            iconSize: [26, 26], iconAnchor: [13, 13], popupAnchor: [0, -15],
        });
    }

    // ── Fungsi helper: icon depot KEMBALI (tanda ↩) ──
    function makeReturnIcon(color) {
        return L.divIcon({
            className: '',
            html: `<div style="
                width:26px; height:26px;
                background:${color};
                border-radius:50%;
                border:2.5px solid white;
                box-shadow:0 2px 8px rgba(0,0,0,0.28);
                color:white;
                font-size:13px;
                display:flex;
                align-items:center;
                justify-content:center;
            ">↩</div>`,
            iconSize: [26, 26], iconAnchor: [13, 13], popupAnchor: [0, -15],
        });
    }

    // ── Render semua kota sebagai background marker ──
    const bounds = [];
    Object.entries(cityInfo).forEach(([name, info]) => {
        const icon = info.is_depot
            ? makeDepotIcon('#4F46E5')  // depot: kotak indigo
            : cityIcon;                 // kota: lingkaran teal kecil

        const popup = info.is_depot
            ? `<div style="font-family:sans-serif"><b style="color:#4338CA">🏭 ${name}</b><br><span style="font-size:11px;color:#64748b">Depot</span></div>`
            : `<div style="font-family:sans-serif"><b style="color:#0f766e">${name}</b><br><span style="font-size:11px;color:#64748b">Kota Reguler</span></div>`;

        L.marker([info.lat, info.lon], { icon })
            .addTo(petaRute)
            .bindPopup(popup);

        bounds.push([info.lat, info.lon]);
    });

    // ── Render rute per truk ──
    const legendItems = document.getElementById('truck-legend-items');
    legendItems.innerHTML = '';

    Object.keys(d.best_routes).forEach(function (tid, idx) {
        const r     = d.best_routes[tid];
        const color = TRUCK_COLORS[idx % TRUCK_COLORS.length];
        const coords = cityInfo; // shortcut

        // Helper: ambil koordinat kota (return null jika tidak ada)
        const getLL = name => {
            if (!coords[name]) return null;
            return [coords[name].lat, coords[name].lon];
        };

        // Bangun path lengkap: depot → stop1 → stop2 → ... → depot_kembali
        const fullPath  = [r.depot, ...(r.rute || []), r.depot_kembali];
        const latLngs   = fullPath.map(getLL).filter(Boolean);

        if (latLngs.length >= 2) {
            // Garis rute truk dengan warna masing-masing
            L.polyline(latLngs, {
                color: color, weight: 4, opacity: 0.9, lineJoin: 'round',
            })
                .addTo(petaRute)
                .bindPopup(`<b style="color:${color}">🚛 Truk ${tid}</b><br>${fullPath.join(' → ')}`);
        }

        // Marker "D" di depot asal
        const llDepot = getLL(r.depot);
        if (llDepot) {
            L.marker(llDepot, { icon: makeDepotIcon(color) })
                .addTo(petaRute)
                .bindPopup(`<b style="color:${color}">🏭 Depot Asal — Truk ${tid}</b><br>${r.depot}`);
        }

        // Marker angka 1, 2, 3 ... di setiap kota kunjungan
        (r.rute || []).forEach(function (kota, i) {
            const ll = getLL(kota);
            if (!ll) return;
            L.marker(ll, { icon: makeStopIcon(i + 1, color) })
                .addTo(petaRute)
                .bindPopup(`<b style="color:${color}">🚛 Truk ${tid} — Stop ${i + 1}</b><br>${kota}`);
        });

        // Marker "↩" di depot kembali (hanya jika BEDA dari depot asal)
        if (r.depot_kembali && r.depot_kembali !== r.depot) {
            const llReturn = getLL(r.depot_kembali);
            if (llReturn) {
                L.marker(llReturn, { icon: makeReturnIcon(color) })
                    .addTo(petaRute)
                    .bindPopup(`<b style="color:${color}">🏁 Depot Kembali — Truk ${tid}</b><br>${r.depot_kembali}`);
            }
        }

        // Tambah entry ke floating legend
        const nStop = (r.rute || []).length;
        const li = document.createElement('div');
        li.className = 'flex items-center gap-2 whitespace-nowrap';
        li.innerHTML = `
            <div style="width:30px;height:6px;background:${color};border-radius:3px;flex-shrink:0;"></div>
            <span class="font-semibold text-slate-700">Truk ${tid}</span>
            <span class="text-slate-400">(${nStop} stop)</span>
        `;
        legendItems.appendChild(li);
    });

    // Tampilkan legend hanya jika ada rute
    if (Object.keys(d.best_routes).length > 0) {
        document.getElementById('truck-legend').classList.remove('hidden');
    }

    // Fit peta ke semua titik
    if (bounds.length) petaRute.fitBounds(bounds, { padding: [50, 50] });
}

// ─── 4. Detail Truk (accordion) ───────────────────────────────────
function renderTruckDetails(d) {
    const fmt = n => 'Rp ' + Math.round(n || 0).toLocaleString('id-ID');
    let html = '';

    Object.keys(d.best_routes).forEach(function (tid, idx) {
        const r     = d.best_routes[tid];
        const color = TRUCK_COLORS[idx % TRUCK_COLORS.length];
        const stops = [r.depot, ...(r.rute || []), r.depot_kembali];

        const totalBerat = (r.items || []).reduce((s, i) => s + (i.berat_fisik || 0), 0);
        const rows = (r.items || []).map(function (item) {
            const proporsi  = totalBerat > 0 ? (item.berat_fisik / totalBerat) : 0;
            const tarifItem = Math.round((r.tarif || 0) * proporsi);
            return `<tr class="border-t border-slate-100 hover:bg-slate-50 transition-colors">
                <td class="py-2.5 px-4 text-slate-800 font-medium text-sm">
                    ${item.is_carryover ? '<span class="text-[10px] font-bold bg-amber-100 text-amber-700 px-1.5 py-0.5 rounded mr-1.5">CO</span>' : ''}
                    ${item.nama || '-'}
                </td>
                <td class="py-2.5 px-4 text-slate-500 text-sm">${item.kota_tujuan || '-'}</td>
                <td class="py-2.5 px-4 text-slate-500 text-sm text-right">${(item.berat_fisik || 0).toFixed(1)} kg</td>
                <td class="py-2.5 px-4 text-emerald-600 font-semibold text-sm text-right">${fmt(tarifItem)}</td>
            </tr>`;
        }).join('');

        const routeDisplay = stops.map((s, i) => {
            const isDepotCity = (i === 0 || i === stops.length - 1);
            return `<span class="inline-flex items-center ${isDepotCity
                ? 'bg-indigo-50 text-indigo-700 border border-indigo-200 font-bold'
                : 'bg-slate-100 text-slate-700'
            } text-xs px-2 py-1 rounded-md">${isDepotCity ? '🏭 ' : ''}${s}</span>
            ${i < stops.length - 1 ? '<span class="text-slate-300 text-sm">→</span>' : ''}`;
        }).join(' ');

        html += `
        <details class="bg-white border border-slate-200 rounded-2xl overflow-hidden" open>
            <summary class="px-5 py-4 bg-slate-50 cursor-pointer hover:bg-slate-100 transition-colors select-none">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div style="width:14px;height:14px;background:${color};border-radius:3px;flex-shrink:0;"></div>
                        <span class="font-bold text-slate-800 text-sm">Truk ${tid}</span>
                        <span class="font-mono text-xs bg-slate-200 text-slate-600 px-2 py-0.5 rounded-md">${(r.items || []).length} item</span>
                    </div>
                    <div class="flex items-center gap-5 text-xs text-slate-500 font-normal">
                        <span>${(r.total_dist || 0).toFixed(1)} km</span>
                        <span class="font-bold text-emerald-600 text-sm">${fmt(r.tarif)}</span>
                    </div>
                </div>
            </summary>
            <div class="p-5">
                <div class="flex flex-wrap items-center gap-1.5 mb-3 text-xs">${routeDisplay}</div>
                <div class="flex gap-5 text-xs text-slate-500 mb-4">
                    <span>Berat muatan: <b class="text-slate-700">${(r.berat_muatan || 0).toFixed(1)} kg</b></span>
                    <span>BBM: <b class="text-red-500">${fmt(r.biaya_bbm)}</b></span>
                </div>
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="text-slate-400 text-[10px] uppercase tracking-wide border-b border-slate-100">
                            <th class="pb-2 px-4 font-bold">Nama Barang</th>
                            <th class="pb-2 px-4 font-bold">Kota Tujuan</th>
                            <th class="pb-2 px-4 font-bold text-right">Berat</th>
                            <th class="pb-2 px-4 font-bold text-right">Tarif (est.)</th>
                        </tr>
                    </thead>
                    <tbody>${rows || '<tr><td colspan="4" class="py-4 text-center text-slate-400 text-sm">Tidak ada item</td></tr>'}</tbody>
                </table>
            </div>
        </details>`;
    });

    document.getElementById('truck-details').innerHTML =
        html || '<p class="text-slate-400 text-sm text-center py-8">Tidak ada truk yang beroperasi.</p>';
}
</script>

@endsection