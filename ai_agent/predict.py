import json
import sys
from statistics import mean
from datetime import datetime


def load_payload():
    raw = sys.stdin.read()
    if not raw.strip():
        raise ValueError("Payload vacío")
    return json.loads(raw)


def seasonal_boost(weekly_sales, events):
    today = datetime.today()
    boost = 0
    for event in events:
        if event.get("month") == today.month:
            boost += 0.15
    return boost


def forecast_product(product, events):
    weekly = product.get("weekly_sales", [])
    max_cap = product.get("max_quantity")
    min_cap = product.get("min_quantity", 0)

    if not weekly:
        return {
            "product_id": product["product_id"],
            "name": product["name"],
            "forecast": 0,
            "trend": "sin_datos",
            "history": [],
            "capacity_flag": False,
            "capacity_note": None,
        }

    tail = weekly[-6:]
    base = mean(tail)

    # Tendencia más sensible usando los 3 últimos puntos
    trend = "estable"
    if len(tail) >= 3:
        avg_prev = mean(tail[-3:-1])
        last = tail[-1]
        delta = last - avg_prev
        if delta > avg_prev * 0.1:
            trend = "alza"
        elif delta < -avg_prev * 0.1:
            trend = "baja"

    forecast_raw = base * (1 + seasonal_boost(weekly, events))
    forecast_rounded = max(int(round(forecast_raw)), 0)

    capacity_flag = False
    capacity_note = None
    capped_forecast = forecast_rounded

    if max_cap:
        if forecast_rounded > max_cap:
            capacity_flag = True
            capacity_note = f"Sugerencia: aumentar capacidad (max {max_cap} uds). Prediccion: {forecast_rounded} uds."
            capped_forecast = max_cap
        elif forecast_rounded < min_cap:
            # respetar mínimo operativo sin marcarlo como alerta estética
            capped_forecast = min_cap

    return {
        "product_id": product["product_id"],
        "name": product["name"],
        "forecast": capped_forecast,
        "trend": trend,
        "history": [int(round(x)) for x in tail],
        "capacity_flag": capacity_flag,
        "capacity_note": capacity_note,
        "forecast_raw": forecast_rounded,
        "max_quantity": max_cap,
        "min_quantity": min_cap,
    }


def build_response(payload):
    events = payload.get("seasonal_events", [])
    result = {
        "generated_at": payload.get("generated_at"),
        "forecast": [],
        "restock": [],
        "capacity_alerts": [],
        "alerts": {
            "low_stock": [],
            "overstock": [],
            "expiring": [],
        }
    }

    for product in payload.get("products", []):
        forecast = forecast_product(product, events)
        result["forecast"].append(forecast)
        if forecast.get("capacity_flag"):
            result["capacity_alerts"].append({
                "product_id": product["product_id"],
                "name": product["name"],
                "note": forecast.get("capacity_note"),
                "max_quantity": forecast.get("max_quantity"),
                "forecast_raw": forecast.get("forecast_raw"),
            })

        inventory = product.get("inventory", {})
        quantity = inventory.get("quantity") or 0
        expires_in = inventory.get("expires_in_days")

        needed = max(0, forecast["forecast"] * 2 - quantity)
        if needed > 0:
            result["restock"].append({
                "product_id": product["product_id"],
                "name": product["name"],
                "suggested_qty": int(round(needed)),
                "reason": f"Demanda esperada {forecast['forecast']} uds vs stock {quantity} uds",
                "capacity_flag": forecast.get("capacity_flag", False),
                "capacity_note": forecast.get("capacity_note"),
            })

        if quantity < forecast["forecast"]:
            result["alerts"]["low_stock"].append({
                "name": product["name"],
                "stock": quantity,
                "forecast": forecast["forecast"],
            })

        if quantity > forecast["forecast"] * 4:
            result["alerts"]["overstock"].append({
                "name": product["name"],
                "stock": quantity,
                "forecast": forecast["forecast"],
            })

        if expires_in is not None and expires_in <= 30:
            result["alerts"]["expiring"].append({
                "name": product["name"],
                "expires_in_days": expires_in,
                "stock": quantity,
            })

    return result


def main():
    payload = load_payload()
    response = build_response(payload)
    sys.stdout.write(json.dumps(response))


if __name__ == "__main__":
    main()
