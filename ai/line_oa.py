import json
import ssl
import time
from urllib import request, error

LINE_KEY = "line"
ALERT_COOLDOWN = 1

_last_status = {}
_last_sent_at = {}


def _parse_json_value(raw):
    if isinstance(raw, (bytes, bytearray)):
        raw = raw.decode("utf-8")
    if not raw:
        return {}
    data = json.loads(raw)
    return data if isinstance(data, dict) else {}


def _is_enabled(cfg):
    value = cfg.get("enabled")
    return value in (True, 1, "1", "true", "True")


def load_line_cfg(get_db_connection):
    conn = get_db_connection()
    cur = conn.cursor()
    try:
        cur.execute(
            "SELECT setting_value FROM app_settings WHERE setting_key = %s",
            (LINE_KEY,),
        )
        row = cur.fetchone()
        if not row:
            return {}
        return _parse_json_value(row[0])
    except Exception as err:
        print("LINE config error:", err)
        return {}
    finally:
        cur.close()
        conn.close()


def send_line_text(text, get_db_connection):
    cfg = load_line_cfg(get_db_connection)
    if not _is_enabled(cfg):
        return False, "ยังไม่เปิดแจ้งเตือน LINE"
    token = (cfg.get("channel_token") or "").strip()
    if not token:
        return False, "ยังไม่มี Channel Access Token"

    user_id = (cfg.get("user_id") or "").strip()
    if user_id:
        url = "https://api.line.me/v2/bot/message/push"
        payload = {
            "to": user_id,
            "messages": [{"type": "text", "text": text}],
        }
    else:
        url = "https://api.line.me/v2/bot/message/broadcast"
        payload = {"messages": [{"type": "text", "text": text}]}

    req = request.Request(
        url,
        data=json.dumps(payload, ensure_ascii=False).encode("utf-8"),
        headers={
            "Content-Type": "application/json",
            "Authorization": "Bearer " + token,
        },
        method="POST",
    )
    try:
        try:
            import certifi
            ctx = ssl.create_default_context(cafile=certifi.where())
        except Exception:
            ctx = ssl.create_default_context()
        with request.urlopen(req, timeout=15, context=ctx) as res:
            res.read()
        return True, "ส่ง LINE แล้ว"
    except error.HTTPError as err:
        body = err.read().decode("utf-8", errors="ignore")
        return False, f"LINE error {err.code}: {body}"
    except Exception as err:
        return False, str(err)


def _stock_status(qty, low):
    if qty <= 0:
        return "out"
    if qty <= low:
        return "low"
    return "ok"


def _should_alert(shelf_code, class_name, status, old_qty):
    key = (shelf_code, class_name)
    prev_status = _last_status.get(key)
    now = time.time()

    if status == "ok":
        _last_status[key] = "ok"
        return False

    if status == "out" and old_qty <= 0 and prev_status not in ("low", "ok"):
        return False

    if prev_status == status:
        return False

    last_t = _last_sent_at.get(key, 0)
    if now - last_t < ALERT_COOLDOWN:
        _last_status[key] = status
        return False

    _last_status[key] = status
    _last_sent_at[key] = now
    return True


def notify_stock(
    shelf_code,
    product_counter,
    product_map,
    get_db_connection,
    previous_qty=None,
):
    previous_qty = previous_qty or {}
    lines = []
    conn = get_db_connection()
    cur = conn.cursor()
    try:
        for class_name, product_id in product_map.items():
            qty = int(product_counter.get(class_name, 0))
            old_qty = int(previous_qty.get(class_name, 0))
            cur.execute(
                """
                SELECT p.product_name, si.capacity, si.low_stock_threshold
                FROM products p
                JOIN shelf_inventory si ON si.product_id = p.id
                JOIN shelves s ON s.id = si.shelf_id
                WHERE p.id = %s AND s.shelf_code = %s
                """,
                (product_id, shelf_code),
            )
            row = cur.fetchone()
            if not row:
                continue
            name, capacity, low = row
            low = int(low or 2)
            capacity = int(capacity or 8)
            status = _stock_status(qty, low)
            if not _should_alert(shelf_code, class_name, status, old_qty):
                continue
            if status == "out":
                lines.append(f"หมด: {name} ({shelf_code})")
            else:
                lines.append(
                    f"เหลือน้อย: {name} เหลือ {qty}/{capacity} ({shelf_code})"
                )
    finally:
        cur.close()
        conn.close()

    if not lines:
        return False, "ไม่มีสินค้าที่ต้องแจ้งเตือน"

    text = "แจ้งเตือนสต็อก\n" + "\n".join(lines)
    return send_line_text(text, get_db_connection)
