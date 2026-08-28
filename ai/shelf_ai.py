from collections import Counter
from datetime import datetime
from pathlib import Path

import cv2
import mysql.connector
from ultralytics import YOLO

BASE = Path(__file__).resolve().parent
MODELS_DIR = BASE / "models"

DB = {
    "host": "127.0.0.1",
    "user": "root",
    "password": "",
    "database": "shelf_inventory_db",
}

PRODUCT_CONF = 0.50

product_model = None
product_model_path = None


def _pick_model(*keywords):
    files = sorted(MODELS_DIR.glob("*.pt"))
    for f in files:
        name = f.name.lower().replace(" ", "")
        if name.startswith("yolo"):
            continue
        if any(k.replace(" ", "") in name for k in keywords):
            return f
    return None


def load_models():
    global product_model, product_model_path
    product_path = _pick_model("product", "best(1)", "grocery") or _pick_model("best")

    if product_path is None:
        return False

    product_model = YOLO(str(product_path))
    product_model_path = str(product_path)
    return True


def detect_frame(frame):
    names = []
    drawn = frame.copy()

    if product_model is not None:
        result = product_model(frame, conf=PRODUCT_CONF, verbose=False)[0]
        drawn = result.plot(img=drawn)
        if result.boxes is not None:
            for box in result.boxes:
                names.append(result.names[int(box.cls[0])])

    return Counter(names), drawn


def stock_status(count, capacity=8, low=2):
    if count <= 0:
        return "Out of Stock"
    if count <= low:
        return "Low Stock"
    if count >= capacity:
        return "Full"
    return "Normal"


def load_shelf(shelf_code):
    conn = mysql.connector.connect(**DB)
    cur = conn.cursor()
    cur.execute(
        """
        SELECT s.id, p.id, p.yolo_class_name, si.capacity, si.low_stock_threshold
        FROM shelves s
        JOIN shelf_inventory si ON si.shelf_id = s.id
        JOIN products p ON p.id = si.product_id
        WHERE s.shelf_code = %s
        """,
        (shelf_code,),
    )
    rows = cur.fetchall()
    cur.close()
    conn.close()
    if not rows:
        return None, {}
    product_map = {}
    for _, product_id, yolo_class, capacity, low in rows:
        product_map[yolo_class] = {
            "product_id": product_id,
            "capacity": int(capacity or 8),
            "low": int(low or 2),
        }
    return rows[0][0], product_map


def save_inventory(shelf_db_id, counter, product_map):
    unknown = sorted(set(counter) - set(product_map))
    if unknown:
        return False, "คลาสที่ยังไม่ผูกกับชั้นนี้: " + ", ".join(unknown)

    conn = mysql.connector.connect(**DB)
    cur = conn.cursor()
    now = datetime.now()
    for class_name, info in product_map.items():
        qty = int(counter.get(class_name, 0))
        cur.execute(
            """
            UPDATE shelf_inventory
            SET last_detected_at = IF(quantity <> %s, %s, last_detected_at),
                quantity = %s
            WHERE shelf_id = %s AND product_id = %s
            """,
            (qty, now, qty, shelf_db_id, info["product_id"]),
        )
    conn.commit()
    cur.close()
    conn.close()
    return True, "บันทึกแล้ว"
