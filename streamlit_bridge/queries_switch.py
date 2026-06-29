import streamlit as st

def _mode() -> str:
    try:
        return st.secrets.get("INTEGRATION_MODE", "db")
    except Exception:
        return "db"

if _mode() == "api":
    from streamlit_bridge.laravel_api import (
        get_available_trucks,
        get_active_trucks,
        get_cities_and_adj,
        get_depots,
        get_all_cities_for_dropdown,
        get_pso_settings,
        save_simulation_results,
        update_truck_status,
        check_laravel_connection,
        LaravelApiError,
    )

    def show_connection_status():
        health = check_laravel_connection()
        if health["ok"]:
            st.sidebar.success(f"✅ Laravel API terhubung")
        else:
            st.sidebar.error(f"❌ Laravel API: {health['error']}")
            st.sidebar.info("Jalankan: `php artisan serve`")

else:
    from db.queries import (
        get_available_trucks,
        get_active_trucks,
        get_cities_and_adj,
        get_depots,
        get_all_cities_for_dropdown,
    )

    def get_pso_settings() -> dict:
        from db.database import SessionLocal
        from db.models import Setting
        db = SessionLocal()
        try:
            pso  = {s.param_key: s.param_value for s in db.query(Setting).filter(Setting.param_group == 'pso').all()}
            ops  = {s.param_key: s.param_value for s in db.query(Setting).filter(Setting.param_group == 'operasional').all()}
            return {"pso": pso, "operasional": ops}
        finally:
            db.close()

    def save_simulation_results(run_date: str, results: list[dict]) -> dict:
        from db.database import get_session
        from db.models import SimulationResult
        saved = 0
        with get_session() as session:
            for r in results:
                session.add(SimulationResult(run_date=run_date, **r))
                saved += 1
        return {"message": f"{saved} hasil disimpan ke DB."}

    def update_truck_status(truck_id: int, status: str, current_city_id: int = None) -> dict:
        from db.database import get_session
        from db.models import Truck
        with get_session() as session:
            t = session.query(Truck).filter(Truck.id == truck_id).first()
            if t:
                t.operational_status = status
                if current_city_id:
                    t.current_city_id = current_city_id
        return {"message": f"Status truk #{truck_id} → {status}"}

    def show_connection_status():
        st.sidebar.info("🗄️ Mode: Shared Database")

    class LaravelApiError(Exception):
        pass
