<?php
/**
 * Database config — ค่าเดียวกับ webcam.py
 * Schema จริง:
 *   products(id, product_code, product_name, yolo_class_name, created_at, updated_at)
 *   shelves(id, shelf_code, shelf_name, created_at, updated_at)
 *   shelf_inventory(id, shelf_id, product_id, quantity, capacity, low_stock_threshold, last_detected_at, created_at, updated_at)
 */
define('DB_HOST', '127.0.0.1');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'shelf_inventory_db');
define('DB_CHARSET', 'utf8mb4');
define('JWT_SECRET', 'shelf-detection-jwt-secret-change-me');
define('JWT_EXPIRE_SECONDS', 86400);

function getDB(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $dsn = sprintf(
        'mysql:host=%s;dbname=%s;charset=%s',
        DB_HOST,
        DB_NAME,
        DB_CHARSET
    );

    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);

    return $pdo;
}

function jsonResponse(array $data, int $code = 200): void
{
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function handleCors(): void
{
    if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        http_response_code(204);
        exit;
    }
}

function getJsonInput(): array
{
    $raw = file_get_contents('php://input');
    if (!$raw) {
        return [];
    }
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

/** quantity / capacity / low_stock_threshold อยู่ที่ shelf_inventory */
function calcStockStatus(int $qty, int $threshold, int $capacity): string
{
    if ($qty <= 0) {
        return 'Out of Stock';
    }
    if ($qty <= $threshold) {
        return 'Low Stock';
    }
    if ($qty >= $capacity) {
        return 'Full';
    }
    return 'Normal';
}

function base64UrlEncode(string $data): string
{
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

function base64UrlDecode(string $data): string
{
    $remainder = strlen($data) % 4;
    if ($remainder) {
        $data .= str_repeat('=', 4 - $remainder);
    }
    return base64_decode(strtr($data, '-_', '+/')) ?: '';
}

function generateJwt(array $payload): string
{
    $header = ['typ' => 'JWT', 'alg' => 'HS256'];
    $payload['iat'] = time();
    $payload['exp'] = time() + JWT_EXPIRE_SECONDS;

    $headerEnc = base64UrlEncode(json_encode($header, JSON_UNESCAPED_UNICODE));
    $payloadEnc = base64UrlEncode(json_encode($payload, JSON_UNESCAPED_UNICODE));
    $sig = hash_hmac('sha256', $headerEnc . '.' . $payloadEnc, JWT_SECRET, true);

    return $headerEnc . '.' . $payloadEnc . '.' . base64UrlEncode($sig);
}

function decodeJwt(string $token): ?array
{
    $parts = explode('.', $token);
    if (count($parts) !== 3) {
        return null;
    }

    [$headerEnc, $payloadEnc, $sigEnc] = $parts;
    $expected = base64UrlEncode(hash_hmac('sha256', $headerEnc . '.' . $payloadEnc, JWT_SECRET, true));
    if (!hash_equals($expected, $sigEnc)) {
        return null;
    }

    $payload = json_decode(base64UrlDecode($payloadEnc), true);
    if (!is_array($payload)) {
        return null;
    }
    if (isset($payload['exp']) && time() >= (int) $payload['exp']) {
        return null;
    }

    return $payload;
}

function getBearerToken(): ?string
{
    $header = $_SERVER['HTTP_AUTHORIZATION']
        ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION']
        ?? '';

    if ($header === '' && function_exists('getallheaders')) {
        foreach (getallheaders() as $key => $value) {
            if (strcasecmp((string) $key, 'Authorization') === 0) {
                $header = (string) $value;
                break;
            }
        }
    }

    if (preg_match('/Bearer\s+(\S+)/i', $header, $matches)) {
        return $matches[1];
    }

    return null;
}

function requireAuth(): array
{
    $token = getBearerToken();
    if (!$token) {
        jsonResponse(['success' => false, 'message' => 'Invalid or expired token'], 401);
    }

    $payload = decodeJwt($token);
    if (!$payload || empty($payload['sub'])) {
        jsonResponse(['success' => false, 'message' => 'Invalid or expired token'], 401);
    }

    return $payload;
}

function requireAdmin(): array
{
    $auth = requireAuth();
    if (($auth['role'] ?? '') !== 'admin') {
        jsonResponse(['success' => false, 'message' => 'Admin only'], 403);
    }
    return $auth;
}
