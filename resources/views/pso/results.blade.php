@extends('layouts.app')
@section('title', 'Optimasi & Hasil PSO')
@section('content')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<style>.btn-loading{color:transparent!important;pointer-events:none}.btn-loading::after{content:'';position:absolute;width:20px;height:20px;top:50%;left:50%;margin:-10px 0 0 -10px;border:3px solid rgba(255,255,255,.3);border-radius:50%;border-top-color:#fff;animation:spin 1s linear infinite}@keyframes spin{to{transform:rotate(360deg)}}#peta-rute{height:400px;width:100%;z-index:0;}</style>

<div class="max-w-7xl mx-auto px-4 py-8 w-full font-sans">
    <header class="flex items-center justify-between mb-8">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-violet-600 flex items-center justify-center shadow-md shadow-violet-100">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
            </div>
            <div>
                <h1 class="text-2xl font-extrabold text-slate-900">Optimasi & Hasil PSO</h1>
                <p class="text-sm text-slate-500">Jalankan algoritma PSO untuk alokasi truk terbaik.</p>
            </div>
        </div>
    </header>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 mb-6 flex flex-col md:flex-row items-center justify-between gap-4">
        <div id="status-text" class="text-sm text-slate-600">Siap menjalankan optimasi berdasarkan pesanan hari ini.</div>
        <button id="btn-run" onclick="runPSO()" class="relative bg-violet-600 hover:bg-violet-700 text-white px-8 py-3 rounded-xl text-sm font-bold shadow-md flex items-center gap-2 w-fit">🚀 Jalankan Optimasi PSO</button>
    </div>

    <div id="area-hasil" class="hidden space-y-6">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white rounded-2xl shadow-sm border p-6"><h3 class="text-sm font-bold text-slate-600 mb-3">Grafik Konvergensi</h3><canvas id="chartConv"></canvas></div>
            <div class="bg-white rounded-2xl shadow-sm border p-6">
                <div class="flex justify-between items-center mb-3"><h3 class="text-sm font-bold text-slate-600">Velocity Breakdown</h3><input type="range" id="iterSlider" min="0" max="1" value="0" class="w-32 accent-violet-600"></div>
                <canvas id="chartVel"></canvas>
            </div>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border overflow-hidden"><div id="peta-rute"></div></div>
        
        <div id="truck-details" class="space-y-4"></div>

        <section class="bg-gradient-to-r from-violet-600 to-indigo-600 rounded-2xl shadow-lg p-6 text-white">
            <div class="grid grid-cols-3 gap-6 mb-6">
                <div><p class="text-violet-200 text-xs font-bold uppercase">Total Tarif</p><p class="text-2xl font-extrabold mt-1" id="sum-tarif">Rp 0</p></div>
                <div><p class="text-violet-200 text-xs font-bold uppercase">Total BBM</p><p class="text-2xl font-extrabold mt-1" id="sum-bbm">Rp 0</p></div>
                <div><p class="text-violet-200 text-xs font-bold uppercase">Profit Bersih</p><p class="text-2xl font-extrabold mt-1" id="sum-profit">Rp 0</p></div>
            </div>
            <form action="{{ route('pso.save') }}" method="POST" class="flex gap-4">
                @csrf
                <button type="submit" class="bg-white text-violet-700 hover:bg-violet-50 font-bold py-3 px-6 rounded-xl transition-all">✅ Selesai & Simpan Hari Ini</button>
                <button type="button" onclick="location.reload()" class="border-2 border-white/30 px-6 py-3 rounded-xl font-bold hover:bg-white/10 transition-all">Ulangi</button>
            </form>
        </section>
    </div>
</div>

<script>
let psoData = null, chartConv = null, chartVel = null, petaRute = null;

async function runPSO() {
    const btn = document.getElementById('btn-run');
    btn.classList.add('btn-loading');
    document.getElementById('status-text').innerText = "Sedang memanggil Python PSO...";
    
    // [FIX] Mengambil token langsung dari Blade, JANGAN pakai document.querySelector
    const csrfToken = '{{ csrf_token() }}';

    try {
        const res = await fetch('{{ route("pso.run") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            }
        });
        
        const data = await res.json();
        
        if(res.ok) { 
            psoData = data; 
            renderAll(data); 
        } else { 
            alert('Error: ' + (data.error || 'Unknown')); 
            document.getElementById('status-text').innerText = "Gagal menjalankan PSO."; 
            btn.classList.remove('btn-loading'); 
        }
    } catch(e) { 
        alert('Gagal menghubungi server: ' + e.message); 
        btn.classList.remove('btn-loading'); 
    }
}

function renderAll(d) {
    document.getElementById('area-hasil').classList.remove('hidden');
    const btn = document.getElementById('btn-run'); 
    btn.innerText = "✅ Optimasi Selesai"; 
    btn.classList.add('bg-green-600','hover:bg-green-700'); 
    btn.classList.remove('bg-violet-600');
    
    // 1. Charts Konvergensi
    if(chartConv) chartConv.destroy();
    chartConv = new Chart(document.getElementById('chartConv'), {
        type: 'line',
        data: {
            labels: d.gbest_curve.map((_, i) => i),
            datasets: [{
                label: 'Gbest Profit',
                data: d.gbest_curve,
                borderColor: '#7c3aed',
                fill: true,
                backgroundColor: 'rgba(124,58,237,0.1)',
                tension: 0.3
            }]
        },
        options: { responsive: true, maintainAspectRatio: false }
    });
    
    // 2. Chart Velocity
    const velData = d.velocity_breakdown;
    document.getElementById('iterSlider').max = velData.length - 1;
    
    function drawVel(i) { 
        if(chartVel) chartVel.destroy(); 
        const v = velData[i]; 
        chartVel = new Chart(document.getElementById('chartVel'), {
            type: 'bar',
            data: {
                labels: ['Inersia', 'Kognitif', 'Sosial'],
                datasets: [{ data: [v.inersia, v.kognitif, v.sosial], backgroundColor: ['#3b82f6','#22c55e','#f59e0b'] }]
            },
            options: { responsive: true, maintainAspectRatio: false }
        }); 
    }
    
    document.getElementById('iterSlider').oninput = e => drawVel(e.target.value); 
    drawVel(0);

    // 3. Map
    if(petaRute) petaRute.remove();
    petaRute = L.map('peta-rute').setView([-7.6, 112.3], 8);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(petaRute);
    
    const colors = ['#ef4444','#3b82f6','#22c55e','#f59e0b','#a855f7','#06b6d4'];
    let bounds = [];
    
    Object.keys(d.best_routes).forEach((tid, idx) => {
        const r = d.best_routes[tid]; 
        const path = [r.depot, ...r.rute, r.depot_kembali];
        const latLngs = path.map(c => d.cities_coords[c] ? d.cities_coords[c] : null).filter(Boolean);
        if(latLngs.length > 1) L.polyline(latLngs, {color: colors[idx%6], weight: 4}).addTo(petaRute);
        bounds.push(...latLngs);
    });
    
    if(bounds.length) petaRute.fitBounds(bounds, {padding: [40, 40]});

    // 4. Detail Truk
    let html = '';
    Object.keys(d.best_routes).forEach(tid => {
        const r = d.best_routes[tid];
        html += `
        <details class="bg-white border rounded-xl overflow-hidden">
            <summary class="p-4 bg-slate-50 cursor-pointer font-bold text-slate-800">
                🚛 Truk ${tid} | ${r.rute.length} Kota | Rp ${r.tarif.toLocaleString('id-ID')}
            </summary>
            <div class="p-4 text-sm text-slate-600">
                <p class="mb-2"><b>Rute:</b> ${r.depot} → ${r.rute.join(' → ')} → ${r.depot_kembali} (${r.total_dist.toFixed(1)} km)</p>
                <table class="w-full text-left border-collapse">
                    <thead class="text-xs text-slate-400 uppercase"><tr><th class="pb-2">Nama</th><th class="pb-2">Tujuan</th><th class="pb-2">Berat</th></tr></thead>
                    <tbody>
                        ${r.items.map(i=>`<tr class="border-t border-slate-100"><td class="py-1">${i.nama}</td><td class="py-1">${i.kota_tujuan}</td><td class="py-1">${i.berat_fisik} kg</td></tr>`).join('')}
                    </tbody>
                </table>
            </div>
        </details>`;
    });
    document.getElementById('truck-details').innerHTML = html;

    // 5. Summary
    const fmt = n => `Rp ${n.toLocaleString('id-ID')}`;
    document.getElementById('sum-tarif').innerText = fmt(d.total_tarif);
    document.getElementById('sum-bbm').innerText = fmt(d.total_bbm);
    document.getElementById('sum-profit').innerText = fmt(d.total_tarif - d.total_bbm);
}
</script>

@endsection