"""
engine/run_pso.py
==================
Jembatan (Adapter) antara Laravel (PHP) dan Engine PSO (Python).
File ini dijalankan via terminal oleh Laravel menggunakan shell_exec().
"""

import sys
import json
from types import SimpleNamespace

# Import fungsi inti yang sudah kamu buat
# Pastikan folder 'engine' bisa di-import (bisa ditambahkan sys.path jika perlu)
# Karena dijalankan dari root Laravel: python engine/run_pso.py
import os
sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))

from pso_engine import run_pso

def main():
    # 1. Tangkap JSON dari argumen PHP (sys.argv[1])
    if len(sys.argv) < 2:
        print(json.dumps({"error": "Tidak ada input JSON dari Laravel"}))
        sys.exit(1)

    input_data = json.loads(sys.argv[1])

    # Import fungsi klasifikasi dari file temanmu
from data_models import klasifikasi_dimensi 

# ... (di dalam fungsi main()) ...

    # 2. Parse Data Items mentah dari Database Laravel
    items = []
    for row in input_data.get('items', []):
        p = float(row['panjang'])
        l = float(row['lebar'])
        t = float(row['tinggi'])
        w = float(row['berat_fisik'])
        
        # Panggil fungsi Python temanmu untuk menghitung faktor, volume, berat vol, berat tagihan
        dim_result = klasifikasi_dimensi(p, l, t) 
        
        items.append({
            'id': row['id'],
            'nama': row['nama'],
            'panjang': p,
            'lebar': l,
            'tinggi': t,
            'volume': p * l * t,
            'berat_fisik': w,
            'berat_volumetrik': dim_result.get('berat_volumetrik', (p*l*t)/6000), # sesuaikan key returnan temanmu
            'berat_tagihan': dim_result.get('berat_tagihan', max(w, (p*l*t)/6000)),
            'faktor': dim_result.get('faktor', 1.0), 
            'kategori': dim_result.get('kategori', 'Reguler'),
            'kota_asal': row['kota_asal'],
            'kota_tujuan': row['kota_tujuan'],
            'truck_id': None, # Akan di-assign oleh item_assignment.py nanti
            'is_carryover': bool(row.get('is_carryover', False))
        })

    # 3. Parse Data Truk dari Laravel
    trucks = []
    for t in input_data.get('trucks', []):
        trucks.append({
            'id': t['id'],
            'max_weight_kg': float(t['max_weight_kg']),
            'box_p': float(t.get('box_p', 200)),
            'box_l': float(t.get('box_l', 130)),
            'box_t': float(t.get('box_t', 130)),
            'depot_asal': t['depot_asal'],
            'plate_number': t.get('plate_number', f"T{t['id']}")
        })

    # 4. Parse Data Graph (Adjacency Matrix, Coords, dll) dari Laravel
    graph_data = input_data.get('graph', {})
    cities = graph_data.get('cities', [])
    city_idx = {city: i for i, city in enumerate(cities)}
    adj = graph_data.get('adj', [])
    coords = graph_data.get('coords', {})
    depot_names = graph_data.get('depot_names', [])

    # 5. Parse Settings (PSO Params & Operasional Params) dari Laravel
    # Kita gunakan SimpleNamespace agar di kode lama tetap bisa dipanggil via titik (misal: pso_params.n_partikel)
    pso_raw = input_data.get('pso_params', {})
    pso_params = SimpleNamespace(
        n_partikel=int(pso_raw.get('n_partikel', 30)),
        n_iterasi=int(pso_raw.get('n_iterasi', 100)),
        early_stop=int(pso_raw.get('early_stop', 20)),
        w_max=float(pso_raw.get('w_max', 0.9)),
        w_min=float(pso_raw.get('w_min', 0.4)),
        c1=float(pso_raw.get('c1', 2.0)),
        c2=float(pso_raw.get('c2', 2.0)),
        base_seed=int(pso_raw.get('base_seed', 42))
    )

    op_raw = input_data.get('op_params', {})
    op_params = SimpleNamespace(
        harga_solar=float(op_raw.get('harga_solar', 6800)),
        tarif_dasar=float(op_raw.get('tarif_dasar', 20)),
        bbm_base=float(op_raw.get('bbm_base', 0.08)),
        bbm_faktor=float(op_raw.get('bbm_faktor', 0.02))
    )

    # 6. JALANKAN PSO!
    try:
        hasil_pso = run_pso(
            items=items,
            trucks=trucks,
            adj=adj,
            city_idx=city_idx,
            cities=cities,
            coords=coords,
            depot_names=depot_names,
            pso_params=pso_params,
            op_params=op_params,
            progress_callback=None # Tidak bisa dipakai di shell_exec batch mode
        )

        # 7. Sesuaikan format output SEBELUM dikirim ke Javascript Laravel
        # Karena JS butuh format tertentu (lihat results.blade.php)
        final_output = {
            "gbest_curve": hasil_pso["gbest_curve"],
            "velocity_breakdown": hasil_pso["velocity_breakdown"],
            "cities_coords": coords,
            "best_routes": hasil_pso["best_routes"], # Pastikan format ini cocok (sudah cocok)
            "carryover_items": hasil_pso.get("carryover_items", []),
            "relokasi": hasil_pso.get("relokasi", []),
            "total_tarif": sum(r['tarif'] for r in hasil_pso["best_routes"].values()),
            "total_bbm": sum(r['biaya_bbm'] for r in hasil_pso["best_routes"].values()),
            "biaya_relokasi": 0 # Hitung di sini jika ada logic relokasi
        }

        # Print output JSON (ini yang akan ditangkap oleh PHP)
        print(json.dumps(final_output))

    except Exception as e:
        print(json.dumps({"error": str(e)}))
        sys.exit(1)

if __name__ == "__main__":
    main()