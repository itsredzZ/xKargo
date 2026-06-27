"""
engine/graph_builder.py
=========================
Versi Laravel: membangun graph dari data JSON yang dikirim PHP,
bukan dari database langsung.
"""

def build_graph_from_db(session=None):
    """
    Versi lama — tidak dipakai di Laravel.
    Dibiarkan agar tidak error saat diimport.
    """
    raise NotImplementedError("Gunakan build_graph_from_json() untuk Laravel.")


def build_graph_from_json(graph_data: dict):
    """
    Membangun graph dari dict yang dikirim PsoController Laravel.
    
    Parameter:
        graph_data: {
            'cities': [...],
            'adj': [[...]],
            'coords': {...},
            'depot_names': [...]
        }
    
    Mengembalikan:
        cities, city_idx, adj, coords, depot_names
    """
    cities      = graph_data.get('cities', [])
    adj         = graph_data.get('adj', [])
    coords      = graph_data.get('coords', {})
    depot_names = graph_data.get('depot_names', [])

    city_idx = {name: i for i, name in enumerate(cities)}

    # Ganti None/null dengan float('inf')
    n = len(cities)
    clean_adj = [[float('inf')] * n for _ in range(n)]
    for i in range(n):
        for j in range(n):
            val = adj[i][j] if adj and i < len(adj) and j < len(adj[i]) else float('inf')
            if val is None or val != val:  # None atau NaN
                clean_adj[i][j] = float('inf')
            else:
                try:
                    clean_adj[i][j] = float(val)
                except (TypeError, ValueError):
                    clean_adj[i][j] = float('inf')

    return cities, city_idx, clean_adj, coords, depot_names