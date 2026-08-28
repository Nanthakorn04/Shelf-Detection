<?php
require_once __DIR__ . '/database.php';

function lineDefaults(): array
{
    return ['enabled' => false, 'channel_token' => '', 'user_id' => ''];
}

function ensureSettingsTable(PDO $db): void
{
    $db->exec("
        CREATE TABLE IF NOT EXISTS app_settings (
            setting_key VARCHAR(64) PRIMARY KEY,
            setting_value TEXT NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
}

function lineConfig(): array
{
    $default = lineDefaults();
    try {
        $db = getDB();
        ensureSettingsTable($db);
        $stmt = $db->prepare("SELECT setting_value FROM app_settings WHERE setting_key = 'line'");
        $stmt->execute();
        $row = $stmt->fetch();
        if ($row) {
            $data = json_decode((string) $row['setting_value'], true);
            if (is_array($data)) {
                return array_merge($default, $data);
            }
        }
    } catch (Throwable $e) {
        // ใช้ค่าว่างถ้าตารางยังไม่มี
    }
    return $default;
}

function saveLineConfig(array $cfg): bool
{
    try {
        $db = getDB();
        ensureSettingsTable($db);
        $json = json_encode($cfg, JSON_UNESCAPED_UNICODE);
        $stmt = $db->prepare("
            INSERT INTO app_settings (setting_key, setting_value)
            VALUES ('line', ?)
            ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)
        ");
        $stmt->execute([$json]);
        return true;
    } catch (Throwable $e) {
        return false;
    }
}

function sendLineText(string $text, bool $requireEnabled = false): array
{
    $cfg = lineConfig();
    if ($requireEnabled && empty($cfg['enabled'])) {
        return ['ok' => false, 'message' => 'ยังไม่เปิดแจ้งเตือน LINE'];
    }
    $token = trim((string) ($cfg['channel_token'] ?? ''));
    if ($token === '') {
        return ['ok' => false, 'message' => 'ยังไม่ได้ใส่ Channel Access Token'];
    }

    $userId = trim((string) ($cfg['user_id'] ?? ''));
    if ($userId !== '') {
        $url = 'https://api.line.me/v2/bot/message/push';
        $payload = [
            'to' => $userId,
            'messages' => [['type' => 'text', 'text' => $text]],
        ];
    } else {
        $url = 'https://api.line.me/v2/bot/message/broadcast';
        $payload = [
            'messages' => [['type' => 'text', 'text' => $text]],
        ];
    }

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $token,
        ],
        CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 15,
    ]);
    $raw = curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err = curl_error($ch);
    curl_close($ch);

    if ($raw === false) {
        return ['ok' => false, 'message' => $err ?: 'ส่ง LINE ไม่ได้'];
    }
    if ($code >= 200 && $code < 300) {
        return ['ok' => true, 'message' => 'ส่ง LINE แล้ว'];
    }

    $json = json_decode((string) $raw, true);
    $msg = is_array($json) ? ($json['message'] ?? $raw) : $raw;
    return ['ok' => false, 'message' => 'LINE error: ' . $msg];
}

function notifyInventoryChange(PDO $db, int $inventoryId, ?int $oldQty = null): void
{
    $stmt = $db->prepare("
        SELECT
            p.product_name,
            s.shelf_code,
            si.quantity,
            si.capacity,
            si.low_stock_threshold
        FROM shelf_inventory si
        JOIN products p ON p.id = si.product_id
        JOIN shelves s ON s.id = si.shelf_id
        WHERE si.id = ?
    ");
    $stmt->execute([$inventoryId]);
    $row = $stmt->fetch();
    if (!$row) {
        return;
    }

    $qty = (int) $row['quantity'];
    $low = (int) $row['low_stock_threshold'];
    $capacity = (int) $row['capacity'];
    $newStatus = calcStockStatus($qty, $low, $capacity);

    if ($oldQty !== null) {
        $oldStatus = calcStockStatus($oldQty, $low, $capacity);
        if ($oldStatus === $newStatus) {
            return;
        }
    }

    if (!in_array($newStatus, ['Low Stock', 'Out of Stock'], true)) {
        return;
    }

    $name = $row['product_name'];
    $shelf = $row['shelf_code'];
    if ($newStatus === 'Out of Stock') {
        $text = "แจ้งเตือนสต็อก\nหมด: {$name} ({$shelf})";
    } else {
        $text = "แจ้งเตือนสต็อก\nเหลือน้อย: {$name} เหลือ {$qty}/{$capacity} ({$shelf})";
    }

    sendLineText($text, true);
}

function stockAlertLines(PDO $db): array
{
    $stmt = $db->query("
        SELECT
            p.product_name,
            s.shelf_code,
            si.quantity,
            si.capacity,
            si.low_stock_threshold
        FROM shelf_inventory si
        JOIN products p ON p.id = si.product_id
        JOIN shelves s ON s.id = si.shelf_id
        ORDER BY s.shelf_code ASC, p.product_name ASC
    ");

    $out = [];
    $low = [];
    foreach ($stmt->fetchAll() as $row) {
        $qty = (int) $row['quantity'];
        $threshold = (int) $row['low_stock_threshold'];
        $capacity = (int) $row['capacity'];
        $status = calcStockStatus($qty, $threshold, $capacity);
        $name = $row['product_name'];
        $shelf = $row['shelf_code'];

        if ($status === 'Out of Stock') {
            $out[] = "หมด: {$name} ({$shelf})";
        } elseif ($status === 'Low Stock') {
            $low[] = "เหลือน้อย: {$name} เหลือ {$qty}/{$capacity} ({$shelf})";
        }
    }

    return array_merge($out, $low);
}

function sendCurrentStockAlerts(): array
{
    $lines = stockAlertLines(getDB());
    if (!$lines) {
        return ['ok' => false, 'message' => 'ไม่มีสินค้าที่เหลือน้อยหรือหมดให้แจ้งเตือน'];
    }

    $result = sendLineText("แจ้งเตือนสต็อก\n" . implode("\n", $lines));
    if ($result['ok']) {
        $result['message'] = 'ส่งแจ้งเตือนสต็อกแล้ว ' . count($lines) . ' รายการ';
    }
    return $result;
}
