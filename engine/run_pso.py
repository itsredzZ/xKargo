import sys
import json
import os
from types import SimpleNamespace

sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))
from pso_engine import run_pso
from data_models import klasifikasi_dimensi 

def main():
    if len(sys.argv) < 2:
        print(json.dumps({"error": "No JSON input"}))
        sys.exit(1)

    input_data = json.loads(sys.argv[1])

    # 1. Parse Items (Data fisik + relasi dari Laravel)
    items = []
    for row in input_data.get('items', []):
        p, l, t, w = float(row['panjang']), float(row['lebar']), float(row['tinggi']), float(row['berat_fisik'])
        
        dim_result = klasifikasi_dimensi(p, l, t) 
        
        items.append({
            'id': row['id'],
            'nama': row['nama'],
            'panjang': p, 'lebar': l, 'tinggi': t,
            'volume': p * l * t,
            'berat_fisik': w,
            'berat_volumetrik': dim_result.get('berat_volumetrik', (p*l*t)/6000),
            'berat_tagihan': dim_result.get('berat_tagihan', max(w, (p*l*t)/6000)),
            'faktor': dim_result.get('faktor', 1.0), 
            'kategori': dim_result.get('kategori', 'Reguler'),
            'kota_asal': row['kota_asal'],
            'kota_tujuan': row['kota_tujuan'],
            'truck_id': None, 
            'is_carryover': bool(row.get('is_carryover', False))
        })

    # 2. Parse Trucks
    trucks = [{'id': t['id'], 'max_weight_kg': float(t['max_weight_kg']), 'box_p': float(t.get('box_p', 200)), 'box_l': float(t.get('box_l', 130)), 'box_t': float(t.get('box_t', 130)), 'depot_asal': t['depot_asal'], 'plate_number': t.get('plate_number', f"T{t['id']}")} for t in input_data.get('trucks', [])]

    # 3. Parse Graph & Params
    graph = input_data.get('graph', {})
    cities, coords, depot_names = graph.get('cities', []), graph.get('coords', {}), graph.get('depot_names', [])
    city_idx = {city: i for i, city in enumerate(cities)}
    adj = graph.get('adj', [])

    pso_raw, op_raw = input_data.get('pso_params', {}), input_data.get('op_params', {})
    pso_params = SimpleNamespace(n_partikel=int(pso_raw.get('n_partikel', 30)), n_iterasi=int(pso_raw.get('n_iterasi', 100)), early_stop=int(pso_raw.get('early_stop', 20)), w_max=float(pso_raw.get('w_max', 0.9)), w_min=float(pso_raw.get('w_min', 0.4)), c1=float(pso_raw.get('c1', 2.0)), c2=float(pso_raw.get('c2', 2.0)), base_seed=int(pso_raw.get('base_seed', 42)))
    op_params = SimpleNamespace(harga_solar=float(op_raw.get('harga_solar', 6800)), tarif_dasar=float(op_raw.get('tarif_dasar', 20)), bbm_base=float(op_raw.get('bbm_base', 0.08)), bbm_faktor=float(op_raw.get('bbm_faktor', 0.02)))

    try:
        hasil = run_pso(items=items, trucks=trucks, adj=adj, city_idx=city_idx, cities=cities, coords=coords, depot_names=depot_names, pso_params=pso_params, op_params=op_params, progress_callback=None)
        
        final_output = {
            "gbest_curve": hasil["gbest_curve"],
            "velocity_breakdown": hasil["velocity_breakdown"],
            "cities_coords": coords,
            "best_routes": hasil["best_routes"],
            "total_tarif": sum(r['tarif'] for r in hasil["best_routes"].values()),
            "total_bbm": sum(r['biaya_bbm'] for r in hasil["best_routes"].values())
        }
        print(json.dumps(final_output))
    except Exception as e:
        print(json.dumps({"error": str(e)}))
        sys.exit(1)

if __name__ == "__main__":
    main()