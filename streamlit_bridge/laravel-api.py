"""
streamlit-bridge/laravel_api.py
=================================
POLA 2 — Streamlit → Laravel REST API

Adapter ini menggantikan db/queries.py jika kamu ingin Streamlit
berkomunikasi via REST API Laravel, bukan langsung ke database.

Cara pakai:
  Ganti import di halaman Streamlit:

  # Sebelum (Pola 1 — Shared DB):
  from db.queries import get_active_trucks, get_cities_and_adj

  # Sesudah (Pola 2 — REST API):
  from streamlit-bridge.laravel_api import get_active_trucks, get_cities_and_adj

Konfigurasi (tambahkan ke .streamlit/secrets.toml atau .env):
  LARAVEL_API_URL = "http://localhost:8000"
  LARAVEL_API_TOKEN = "your-secret-token"

Semua fungsi mengembalikan format IDENTIK dengan queries.py
agar PSO engine tidak perlu diubah.
"""

import os
import requests
import streamlit as st
from functools import lru_cache

# ──────────────────────────────────────────────────────────────────────────────
# KONFIGURASI
# ──────────────────────────────────────────────────────────────────────────────

def _get_config() -> tuple[str, str]:
    """
    Ambil URL dan token dari st.secrets (priority) atau os.environ.
    st.secrets diset di .streamlit/secrets.toml — lebih aman dari .env.
    """
    try:
        url   = st.secrets["LARAVEL_API_URL"]
        token = st.secrets["LARAVEL_API_TOKEN"]
    except (KeyError, AttributeError):
        url   = os.getenv("LARAVEL_API_URL", "http://localhost:8000")
        token = os.getenv("LARAVEL_API_TOKEN", "")

    return url.rstrip("/"), token


def _headers() -> dict:
    """Build Authorization header untuk semua request."""
    _, token = _get_config()
    return {
        "Authorization": f"Bearer {token}",
        "Accept":        "application/json",
        "Content-Type":  "application/json",
    }


def _base() -> str:
    url, _ = _get_config()
    return f"{url}/api"


class LaravelApiError(Exception):
    """Error saat memanggil API Laravel."""
    pass


def _get(endpoint: str, params: dict = None) -> dict:
    """GET request ke Laravel API dengan error handling."""
    try:
        r = requests.get(
            f"{_base()}/{endpoint}",
            headers=_headers(),
            params=params,
            timeout=10,
        )
        r.raise_for_status()
        return r.json()
    except requests.ConnectionError:
        raise LaravelApiError(
            f"Tidak bisa terhubung ke Laravel API di {_base()}. "
            "Pastikan server Laravel berjalan: php artisan serve"
        )
    except requests.HTTPError as e:
        if e.response.status_code == 401:
            raise LaravelApiError(
                "Token API tidak valid. "
                "Cek LARAVEL_API_TOKEN di .streamlit/secrets.toml"
            )
        raise LaravelApiError(f"API error {e.response.status_code}: {e.response.text[:200]}")


def _post(endpoint: str, data: dict) -> dict:
    """POST request ke Laravel API."""
    try:
        r = requests.post(
            f"{_base()}/{endpoint}",
            headers=_headers(),
            json=data,
            timeout=15,
        )
        r.raise_for_status()
        return r.json()
    except requests.HTTPError as e:
        raise LaravelApiError(f"API error {e.response.status_code}: {e.response.text[:300]}")


def _patch(endpoint: str, data: dict) -> dict:
    """PATCH request ke Laravel API."""
    r = requests.patch(
        f"{_base()}/{endpoint}",
        headers=_headers(),
        json=data,
        timeout=10,
    )
    r.raise_for_status()
    return r.json()


# ──────────────────────────────────────────────────────────────────────────────
# FUNGSI PUBLIK — format identik dengan queries.py
# ──────────────────────────────────────────────────────────────────────────────

def get_available_trucks() -> list[dict]:
    """
    Setara get_available_trucks() di queries.py.
    Panggil GET /api/trucks/available.
    """
    data = _get("trucks/available")
    return data["data"]


def get_active_trucks() -> list[dict]:
    """
    Setara get_active_trucks() di queries.py.
    Panggil GET /api/trucks.
    """
    data = _get("trucks")
    return data["data"]


def get_cities_and_adj() -> tuple:
    """
    Setara get_cities_and_adj() di queries.py.
    Panggil GET /api/cities/matrix.

    Return (cities, city_idx, adj, coords) — format SAMA.
    adj: None → dikonversi ke float('inf') seperti queries.py.
    """
    data = _get("cities/matrix")

    cities   = data["cities"]
    city_idx = data["city_idx"]
    coords   = {k: tuple(v) for k, v in data["coords"].items()}

    # Konversi null → float('inf')
    raw_adj = data["adj"]
    adj = [
        [float("inf") if v is None else float(v) for v in row]
        for row in raw_adj
    ]

    return cities, city_idx, adj, coords


def get_depots() -> list[dict]:
    """
    Setara get_depots() di queries.py.
    Panggil GET /api/cities/depots.
    """
    data = _get("cities/depots")
    return data["data"]


def get_all_cities_for_dropdown() -> list[str]:
    """
    Setara get_all_cities_for_dropdown() di queries.py.
    Panggil GET /api/cities.
    """
    data = _get("cities")
    return [c["name"] for c in data["data"]]


def get_pso_settings() -> dict:
    """
    Ambil parameter PSO dari Laravel (GET /api/simulations/settings).
    Return {"pso": {...}, "operasional": {...}}.
    """
    return _get("simulations/settings")


def save_simulation_results(run_date: str, results: list[dict]) -> dict:
    """
    Kirim hasil PSO ke Laravel (POST /api/simulations).
    Dipanggil setelah PSO engine selesai run.

    Args:
        run_date: "2025-01-15"
        results:  list hasil per truk (sesuai format TruckResult di PSO engine)
                  [{truck_id, route_json, total_weight_kg, total_volume_m3,
                    tariff_total, fuel_cost, net_profit, gbest_curve_json}, ...]

    Returns:
        Response JSON dari Laravel {"message": ..., "run_date": ...}
    """
    return _post("simulations", {"run_date": run_date, "results": results})


def update_truck_status(truck_id: int, status: str, current_city_id: int = None) -> dict:
    """
    Update status operasional truk setelah PSO dispatch.
    PATCH /api/trucks/{id}/status
    """
    payload = {"status": status}
    if current_city_id:
        payload["current_city_id"] = current_city_id
    return _patch(f"trucks/{truck_id}/status", payload)


# ──────────────────────────────────────────────────────────────────────────────
# HEALTH CHECK — tampilkan di halaman Streamlit
# ──────────────────────────────────────────────────────────────────────────────

def check_laravel_connection() -> dict:
    """
    Cek apakah Laravel API bisa dijangkau.
    Tampilkan hasilnya di Streamlit dengan st.status() atau st.sidebar.

    Contoh penggunaan:
        health = check_laravel_connection()
        if health['ok']:
            st.sidebar.success(f"✅ Laravel API: {health['url']}")
        else:
            st.sidebar.error(f"❌ {health['error']}")
    """
    url, _ = _get_config()
    try:
        r = requests.get(f"{url}/api/health", timeout=5)
        r.raise_for_status()
        data = r.json()
        return {"ok": True, "url": url, "time": data.get("time")}
    except Exception as e:
        return {"ok": False, "url": url, "error": str(e)}
