<?php
/**
 * GET  อ่านค่า LINE OA (admin)
 * POST { action: "save", enabled, channel_token, user_id }
 * POST { action: "test" }  ส่งรายการสินค้าเหลือน้อย/หมด
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/line.php';

handleCors();
requireAdmin();

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'GET') {
    $cfg = lineConfig();
    $token = (string) $cfg['channel_token'];
    jsonResponse([
        'success' => true,
        'enabled' => (bool) $cfg['enabled'],
        'user_id' => $cfg['user_id'],
        'has_token' => $token !== '',
        'token_tail' => $token !== '' ? substr($token, -4) : '',
    ]);
}

if ($method === 'POST') {
    $in = getJsonInput();
    $action = $in['action'] ?? 'save';

    if ($action === 'test' || $action === 'save') {
        $cfg = lineConfig();
        if (array_key_exists('enabled', $in)) {
            $cfg['enabled'] = !empty($in['enabled']);
        }
        if (array_key_exists('user_id', $in)) {
            $cfg['user_id'] = trim((string) $in['user_id']);
        }
        $token = trim((string) ($in['channel_token'] ?? ''));
        if ($token !== '') {
            $cfg['channel_token'] = $token;
        }
        if (!saveLineConfig($cfg)) {
            jsonResponse(['success' => false, 'message' => 'บันทึกค่า LINE ไม่ได้'], 500);
        }
    }

    if ($action === 'test') {
        $result = sendCurrentStockAlerts();
        $message = $result['message'];
        if ($result['ok'] && empty($cfg['enabled'])) {
            $message .= ' แต่สวิตช์แจ้งเตือนยังปิด กล้องจะยังไม่ส่งอัตโนมัติ';
        }
        jsonResponse(['success' => $result['ok'], 'message' => $message], $result['ok'] ? 200 : 400);
    }

    jsonResponse(['success' => true, 'message' => 'บันทึกการตั้งค่า LINE แล้ว']);
}

jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
