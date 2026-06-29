import os
import requests
import streamlit as st
from functools import lru_cache

# ──────────────────────────────────────────────────────────────────────────────
# KONFIGURASI
# ──────────────────────────────────────────────────────────────────────────────

def _get_config() -> tuple[str, str]:
    """ Ambil URL dan token dari st.secrets (priority) atau os.environ"""
    try:
        url   = st.secrets["LARAVEL_API_URL"]
        token = st.secrets["LARAVEL_API_TOKEN"]
    except (KeyError, AttributeError):
        url   = os.getenv("LARAVEL_API_URL", "http://localhost:8000")
        token = os.getenv("LARAVEL_API_TOKEN", "")

    return url.rstrip("/"), token


def _headers() -> dict:
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

def get_available_trucks() -> list[dict]:
    data = _get("trucks/available")
    return data["data"]


def get_active_trucks() -> list[dict]:
    data = _get("trucks")
    return data["data"]


def get_cities_and_adj() -> tuple:
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
    data = _get("cities/depots")
    return data["data"]


def get_all_cities_for_dropdown() -> list[str]:
    data = _get("cities")
    return [c["name"] for c in data["data"]]


def get_pso_settings() -> dict:
    return _get("simulations/settings")


def save_simulation_results(run_date: str, results: list[dict]) -> dict:
    return _post("simulations", {"run_date": run_date, "results": results})


def update_truck_status(truck_id: int, status: str, current_city_id: int = None) -> dict:
    payload = {"status": status}
    if current_city_id:
        payload["current_city_id"] = current_city_id
    return _patch(f"trucks/{truck_id}/status", payload)

def check_laravel_connection() -> dict:
    url, _ = _get_config()
    try:
        r = requests.get(f"{url}/api/health", timeout=5)
        r.raise_for_status()
        data = r.json()
        return {"ok": True, "url": url, "time": data.get("time")}
    except Exception as e:
        return {"ok": False, "url": url, "error": str(e)}
