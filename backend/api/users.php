<?php
/**
 * GET    รายชื่อผู้ใช้
 * POST   { username, password }  สร้างพนักงาน (role=staff)
 * DELETE ?id=2  ลบพนักงาน (ห้ามลบ admin / ตัวเอง)
 */
require_once __DIR__ . '/../config/database.php';

handleCors();

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

try {
    $auth = requireAdmin();
    $db = getDB();

    if ($method === 'GET') {
        $rows = $db->query(
            "SELECT id, username, role, created_at FROM users ORDER BY id ASC"
        )->fetchAll();
        jsonResponse(['success' => true, 'count' => count($rows), 'data' => $rows]);
    }

    if ($method === 'POST') {
        $in = getJsonInput();
        $username = trim($in['username'] ?? '');
        $password = (string) ($in['password'] ?? '');

        if ($username === '' || $password === '') {
            jsonResponse(['success' => false, 'message' => 'username and password required'], 400);
        }
        if (strlen($username) < 3 || strlen($password) < 4) {
            jsonResponse(['success' => false, 'message' => 'username อย่างน้อย 3 ตัว รหัสผ่านอย่างน้อย 4 ตัว'], 400);
        }

        $stmt = $db->prepare(
            "INSERT INTO users (username, password_hash, role) VALUES (?, ?, 'staff')"
        );
        $stmt->execute([$username, password_hash($password, PASSWORD_DEFAULT)]);

        jsonResponse([
            'success' => true,
            'message' => 'Staff created',
            'id' => (int) $db->lastInsertId(),
        ], 201);
    }

    if ($method === 'DELETE') {
        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) {
            jsonResponse(['success' => false, 'message' => 'id required'], 400);
        }
        if ($id === (int) $auth['sub']) {
            jsonResponse(['success' => false, 'message' => 'Cannot delete your own account'], 400);
        }

        $stmt = $db->prepare("SELECT id, role FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $user = $stmt->fetch();
        if (!$user) {
            jsonResponse(['success' => false, 'message' => 'User not found'], 404);
        }
        if ($user['role'] === 'admin') {
            jsonResponse(['success' => false, 'message' => 'Cannot delete admin'], 400);
        }

        $db->prepare("DELETE FROM users WHERE id = ?")->execute([$id]);
        jsonResponse(['success' => true, 'message' => 'Staff deleted']);
    }

    jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
} catch (PDOException $e) {
    if ($e->getCode() === '23000') {
        jsonResponse(['success' => false, 'message' => 'Username already exists'], 409);
    }
    jsonResponse(['success' => false, 'message' => 'Database error', 'error' => $e->getMessage()], 500);
} catch (Throwable $e) {
    jsonResponse(['success' => false, 'message' => 'Server error', 'error' => $e->getMessage()], 500);
}
