import sys
import json
import os
import math
import traceback
from collections import defaultdict
from types import SimpleNamespace

import numpy as np

sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))
from pso_engine import run_pso
from data_models import klasifikasi_dimensi, TruckState


def clean_json(obj):
    if isinstance(obj, float):
        if math.isnan(obj) or math.isinf(obj):
            return None
        return obj
    elif isinstance(obj, dict):
        return {k: clean_json(v) for k, v in obj.items()}
    elif isinstance(obj, list):
        return [clean_json(v) for v in obj]
    return obj


def _json_fallback(o):
    if isinstance(o, np.integer):
        return int(o)
    if isinstance(o, np.floating):
        v = float(o)
        return None if (math.isnan(v) or math.isinf(v)) else v
    if isinstance(o, np.ndarray):
        return o.tolist()
    raise TypeError(f"Object of type {type(o)} is not JSON serializable")


def _cast_num(v):
    if isinstance(v, (int, float)):
        return v
    try:
        return int(v)
    except (TypeError, ValueError):
        pass
    try:
        return float(v)
    except (TypeError, ValueError):
        return v


def _build_trucks(raw_trucks):
    trucks = []
    for t in raw_trucks:
        trucks.append(TruckState(
            id=t['id'],
            plate_number=t['plate_number'],
            max_weight_kg=float(t['max_weight_kg']),
            box_p=float(t['box_p']),
            box_l=float(t['box_l']),
            box_t=float(t['box_t']),
            home_depot=t['depot_asal'],
            current_city=t['depot_asal'],
            tarif_per_km=float(t.get('tarif_per_km', 10000)),  # ← tambah ini
        ))
    return trucks


def _assign_truck_id(items_raw, trucks):
    """
    [ASUMSI - belum ada di kode asli] Assign tiap item ke truk yang
    SEDANG berada di kota asal item (current_city == kota_asal).
    Kalau >1 truk di kota yang sama -> round-robin. Item tanpa truk
    di kota asalnya -> dikeluarkan (dikembalikan sebagai 'unassigned').
    """
    trucks_by_city = defaultdict(list)
    for t in trucks:
        trucks_by_city[t.current_city].append(t.id)

    rr_counter = defaultdict(int)
    assigned, unassigned = [], []

    for raw in items_raw:
        candidates = trucks_by_city.get(raw['kota_asal'], [])
        if not candidates:
            unassigned.append(raw)
            continue
        idx = rr_counter[raw['kota_asal']] % len(candidates)
        rr_counter[raw['kota_asal']] += 1
        assigned.append({**raw, 'truck_id': candidates[idx]})

    return assigned, unassigned


def _build_items(raw_items):
    items = []
    for raw in raw_items:
        p = float(raw['panjang'])
        l = float(raw['lebar'])
        t = float(raw['tinggi'])
        berat_fisik = float(raw['berat_fisik'])

        berat_volumetrik = (p * l * t) / 6000.0
        berat_tagihan = max(berat_fisik, berat_volumetrik)
        kategori, faktor = klasifikasi_dimensi(p, l, t)

        items.append({
            "id":               str(raw['id']),
            "nama":             raw['nama'],
            "berat_fisik":      berat_fisik,
            "berat_volumetrik": berat_volumetrik,
            "berat_tagihan":    berat_tagihan,
            "faktor":           faktor,
            "kategori":         kategori,
            "panjang":          p,
            "lebar":            l,
            "tinggi":           t,
            "volume":           p * l * t,
            "kota_asal":        raw['kota_asal'],
            "kota_tujuan":      raw['kota_tujuan'],
            "truck_id":         raw['truck_id'],
            "is_carryover":     bool(raw.get('is_carryover', False)),
        })
    return items


def main():
    if len(sys.argv) < 2:
        print(json.dumps({"error": "No JSON input"}))
        sys.exit(1)

    arg = sys.argv[1]

    try:
        if os.path.isfile(arg):
            with open(arg, 'r', encoding='utf-8') as f:
                input_data = json.load(f)
        else:
            input_data = json.loads(arg)

        trucks = _build_trucks(input_data['trucks'])
        assigned_raw, unassigned_raw = _assign_truck_id(input_data['items'], trucks)

        if not assigned_raw:
            print(json.dumps({
                "error": "Tidak ada barang yang bisa diproses: tidak ada truk yang berada di kota asal barang manapun.",
                "unassigned_count": len(unassigned_raw),
            }))
            sys.exit(1)

        items = _build_items(assigned_raw)

        graph = input_data['graph']
        cities = graph['cities']
        city_idx = {name: idx for idx, name in enumerate(cities)}
        adj = graph['adj']
        coords = graph['coords']
        depot_names = graph['depot_names']

        DEFAULT_PSO = {
            'n_partikel': 30, 'n_iterasi': 100, 'early_stop': 20,
            'w_max': 0.9, 'w_min': 0.4, 'c1': 2.0, 'c2': 2.0, 'base_seed': 42
        }
        DEFAULT_OP = {
            'harga_solar': 6800, 'tarif_dasar': 20,
            'bbm_base': 0.08, 'bbm_faktor': 0.02
        }
        
        pso_raw = input_data.get('pso_params', {})
        op_raw = input_data.get('op_params', {})
        
        pso_params = SimpleNamespace(**{k: _cast_num(v) for k, v in {**DEFAULT_PSO, **pso_raw}.items()})
        op_params = SimpleNamespace(**{k: _cast_num(v) for k, v in {**DEFAULT_OP, **op_raw}.items()})
       
        pso_result = run_pso(
            items, trucks, adj, city_idx, cities, coords, depot_names,
            pso_params, op_params,
        )

        best_routes = pso_result['best_routes']
        total_tarif = sum(r['tarif'] for r in best_routes.values())
        total_bbm = sum(r['biaya_bbm'] for r in best_routes.values())
        n_terkirim = sum(len(r['items']) for r in best_routes.values())

        result = {
            **pso_result,
            "total_tarif":      total_tarif,
            "total_bbm":        total_bbm,
            "n_terkirim":       n_terkirim,
            "cities_coords":    coords,
            "unassigned_count": len(unassigned_raw),
        }
        result['gbest_pos'] = result['gbest_pos'].tolist()

        print(json.dumps(clean_json(result), default=_json_fallback))

    except Exception as e:
        print(json.dumps({
            "error": str(e),
            "trace": traceback.format_exc(),
        }))
        sys.exit(1)


if __name__ == "__main__":
    main()