from collections import defaultdict
from engine.data_models import TruckState


def build_depot_truck_map(trucks: list[TruckState]) -> dict[str, list[int]]:
    """
    Bangun peta: nama_depot -> [truck_id, ...] yang sedang parkir di sana.
    Satu depot bisa punya lebih dari satu truk.
    """
    depot_map = defaultdict(list)
    for truck in trucks:
        depot_map[truck.current_city].append(truck.id)
    return dict(depot_map)


def assign_trucks_to_items(
    items: list[dict],
    trucks: list[TruckState],
) -> tuple[list[dict], list[dict]]:

    depot_truck_map = build_depot_truck_map(trucks)

    # Pisah carry-over dan baru per depot untuk jaga urutan prioritas
    carryover_per_depot: dict[str, list[dict]] = defaultdict(list)
    baru_per_depot:      dict[str, list[dict]] = defaultdict(list)
    items_notruk: list[dict] = []

    for item in items:
        depot = item.get("kota_asal", "")
        if depot not in depot_truck_map:
            # Depot tidak ada truknya → langsung carry-over
            item = dict(item)
            item["is_carryover"] = True
            items_notruk.append(item)
            continue

        if item.get("is_carryover", False):
            carryover_per_depot[depot].append(item)
        else:
            baru_per_depot[depot].append(item)

    # Gabung carry-over dulu, baru item baru — assign truck_id dari truk pertama di depot
    items_siap: list[dict] = []
    for depot, truck_ids in depot_truck_map.items():
        assigned_truck_id = truck_ids[0]
        for item in carryover_per_depot.get(depot, []):
            item = dict(item)
            item["truck_id"] = assigned_truck_id
            items_siap.append(item)
        for item in baru_per_depot.get(depot, []):
            item = dict(item)
            item["truck_id"] = assigned_truck_id
            items_siap.append(item)

    return items_siap, items_notruk