from collections import Counter
from datetime import datetime
import time

import cv2
import mysql.connector
from mysql.connector import Error
from ultralytics import YOLO
import line_oa



SHELF_ID = "A1"
SAVE_INTERVAL = 2
STABLE_TIME = 2
PRODUCT_CONF = 0.50
MAX_CAPACITY = 8

DB_CONFIG = {
    "host": "localhost",
    "user": "root",
    "password": "",
    "database": "shelf_inventory_db",
}

PRODUCT_MODEL_PATH = "/Users/nanthakorn.r/Documents/p-01/best (1).pt"


def get_db_connection():
    return mysql.connector.connect(**DB_CONFIG)


def load_shelf_config(shelf_code):
    """โหลด shelf_id และสินค้าที่กำหนดให้อยู่บนชั้นนี้."""
    conn = None
    cursor = None

    try:
        conn = get_db_connection()
        cursor = conn.cursor()
        cursor.execute(
            """
            SELECT
                s.id,
                p.id,
                p.yolo_class_name
            FROM shelves AS s
            JOIN shelf_inventory AS si ON si.shelf_id = s.id
            JOIN products AS p ON p.id = si.product_id
            WHERE s.shelf_code = %s
            """,
            (shelf_code,),
        )
        rows = cursor.fetchall()

        if not rows:
            return None, {}

        shelf_db_id = rows[0][0]
        product_map = {
            yolo_class_name: product_id
            for _, product_id, yolo_class_name in rows
        }
        return shelf_db_id, product_map
    finally:
        if cursor is not None:
            cursor.close()
        if conn is not None and conn.is_connected():
            conn.close()


def save_current_inventory(shelf_db_id, product_counter, product_map):
    """อัปเดตจำนวนในแถวเดิม และไม่สร้างประวัติซ้ำ."""
    conn = None
    cursor = None

    try:
        unknown_classes = sorted(set(product_counter) - set(product_map))
        if unknown_classes:
            print(
                "ไม่บันทึกข้อมูล: คลาสต่อไปนี้ไม่ได้กำหนดไว้บนชั้น "
                f"{SHELF_ID}: "
                + ", ".join(unknown_classes)
            )
            return False, {}

        conn = get_db_connection()
        cursor = conn.cursor()

        cursor.execute(
            """
            SELECT p.yolo_class_name, si.quantity
            FROM shelf_inventory si
            JOIN products p ON p.id = si.product_id
            WHERE si.shelf_id = %s
            """,
            (shelf_db_id,),
        )
        previous_qty = {
            class_name: int(qty or 0)
            for class_name, qty in cursor.fetchall()
            if class_name
        }

        sql_update = """
            UPDATE shelf_inventory
            SET
                last_detected_at = IF(
                    quantity <> %s,
                    %s,
                    last_detected_at
                ),
                quantity = %s
            WHERE shelf_id = %s AND product_id = %s
        """
        detected_at = datetime.now()
        changed_products = []

        for class_name, product_id in product_map.items():
            quantity = product_counter.get(class_name, 0)
            cursor.execute(
                sql_update,
                (
                    quantity,
                    detected_at,
                    quantity,
                    shelf_db_id,
                    product_id,
                ),
            )
            if cursor.rowcount > 0:
                changed_products.append(f"{class_name}={quantity}")

        conn.commit()

        if changed_products:
            print(
                f"[{datetime.now():%H:%M:%S}] อัปเดตสำเร็จ | "
                + ", ".join(changed_products)
            )
        else:
            print(f"[{datetime.now():%H:%M:%S}] จำนวนเท่าเดิม ไม่ต้องอัปเดต")

        return True, previous_qty

    except Error as error:
        print("DB Error:", error)
        if conn is not None:
            conn.rollback()
        return False, {}

    finally:
        if cursor is not None:
            cursor.close()
        if conn is not None and conn.is_connected():
            conn.close()


def get_status(product_count):
    if product_count == 0:
        return "Out of Stock", (0, 0, 255)
    if product_count <= 2:
        return "Low Stock", (0, 255, 255)
    if product_count >= MAX_CAPACITY:
        return "Full", (0, 255, 0)
    return "Normal", (255, 255, 0)


def main():
    print("Loading models...")
    product_model = YOLO(PRODUCT_MODEL_PATH)
    print("Models loaded.")

    try:
        shelf_db_id, product_map = load_shelf_config(SHELF_ID)
    except Error as error:
        print("ไม่สามารถอ่านข้อมูลสินค้าจากฐานข้อมูล:", error)
        return

    if not product_map:
        print(
            f"ไม่พบชั้น {SHELF_ID} หรือยังไม่ได้กำหนดสินค้าใน shelf_inventory"
        )
        return

    print(
        f"โหลดชั้น {SHELF_ID} แล้ว มีสินค้าที่กำหนดไว้ "
        f"{len(product_map)} รายการ"
    )

    cap = cv2.VideoCapture(0)
    if not cap.isOpened():
        print("ไม่สามารถเปิดกล้องได้")
        return

    last_save_time = 0.0
    last_saved_state = None
    pending_state = None
    pending_since = 0.0

    try:
        while True:
            ret, frame = cap.read()
            if not ret:
                print("ไม่สามารถอ่านภาพจากกล้องได้")
                break

            # ตรวจจับทั้งภาพ
            product_results = product_model(
                frame, conf=PRODUCT_CONF, verbose=False
            )

            products = []
            boxes = product_results[0].boxes
            if boxes is not None:
                for box in boxes:
                    class_id = int(box.cls[0])
                    products.append(product_results[0].names[class_id])

            product_counter = Counter(products)
            product_count = sum(product_counter.values())
            status, color = get_status(product_count)

            current_state = (status, tuple(sorted(product_counter.items())))
            current_time = time.time()

            if current_state != pending_state:
                pending_state = current_state
                pending_since = current_time

            is_stable = current_time - pending_since >= STABLE_TIME
            is_changed = current_state != last_saved_state
            is_time_ok = current_time - last_save_time >= SAVE_INTERVAL

            if is_stable and is_changed and is_time_ok:
                saved, previous_qty = save_current_inventory(
                    shelf_db_id,
                    product_counter,
                    product_map,
                )
                if saved:
                    last_save_time = current_time
                    last_saved_state = current_state
                    try:
                        ok, msg = line_oa.notify_stock(
                            SHELF_ID,
                            product_counter,
                            product_map,
                            get_db_connection,
                            previous_qty,
                        )
                        print("LINE:", msg, flush=True)
                    except Exception as err:
                        print("LINE error:", err, flush=True)

            # แสดงผล
            combined = frame.copy()
            combined = product_results[0].plot(img=combined)

            cv2.putText(
                combined,
                f"Shelf: {SHELF_ID}",
                (20, 35),
                cv2.FONT_HERSHEY_SIMPLEX,
                0.8,
                (255, 255, 255),
                2,
            )
            cv2.putText(
                combined,
                f"Status: {status}",
                (20, 70),
                cv2.FONT_HERSHEY_SIMPLEX,
                1.0,
                color,
                2,
            )
            cv2.putText(
                combined,
                f"Products: {product_count}",
                (20, 105),
                cv2.FONT_HERSHEY_SIMPLEX,
                0.7,
                (200, 200, 200),
                2,
            )

            cv2.imshow("Shelf Detection", combined)
            if cv2.waitKey(1) & 0xFF == ord("q"):
                break

    finally:
        cap.release()
        cv2.destroyAllWindows()
        print("ปิดกล้องแล้ว")


if __name__ == "__main__":
    main()