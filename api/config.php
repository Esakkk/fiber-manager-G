<?php
// Secure session cookie parameters then start session
$secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => $_SERVER['HTTP_HOST'] ?? '',
    'secure' => $secure,
    'httponly' => true,
    'samesite' => 'Lax'
]);
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// =============================================
// CORS HANDLING (PERBAIKAN UTAMA)
// =============================================
// Daftar origin yang diizinkan
$allowed_origins = [
    'http://localhost',
    'http://localhost:80',
    'http://127.0.0.1',
    'http://localhost/fiber-manager'
];

// Determine request origin and allow only configured origins
$origin = '';
if (isset($_SERVER['HTTP_ORIGIN'])) {
    $origin = $_SERVER['HTTP_ORIGIN'];
} elseif (isset($_SERVER['HTTP_REFERER'])) {
    $parsed = parse_url($_SERVER['HTTP_REFERER']);
    if ($parsed && isset($parsed['scheme']) && isset($parsed['host'])) {
        $origin = $parsed['scheme'] . '://' . $parsed['host'];
        if (isset($parsed['port'])) $origin .= ':' . $parsed['port'];
    }
}

// Allow only origins listed in $allowed_origins or localhost/127.0.0.1 during development
if ($origin) {
    if (in_array($origin, $allowed_origins) || strpos($origin, 'localhost') !== false || strpos($origin, '127.0.0.1') !== false) {
        header("Access-Control-Allow-Origin: $origin");
    }
}

header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

// Basic security headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('Referrer-Policy: no-referrer-when-downgrade');
header('X-XSS-Protection: 1; mode=block');
if ($secure) {
    // Recommend HSTS only when served over HTTPS
    header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
}

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// =============================================
// DATABASE CONNECTION
// =============================================
$host = 'localhost';
$dbname = 'fiber_manager';
$username = 'qcnet';
$password = 'Qcnet5758oke';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    // Use native prepared statements when possible
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
} catch(PDOException $e) {
    // Do not leak DB error details to clients; log instead
    error_log('Database connection failed: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Connection failed']);
    exit();
}

// =============================================
// AUTH FUNCTIONS
// =============================================
function isAuthenticated() {
    return isset($_SESSION['user_id']) && isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
}

function requireAuth() {
    if (!isAuthenticated()) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized', 'message' => 'Silakan login terlebih dahulu']);
        exit();
    }
}

function getCurrentUser() {
    if (!isAuthenticated()) return null;
    
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT id, username, full_name, role FROM users WHERE id = ? AND is_active = 1");
        $stmt->execute([$_SESSION['user_id']]);
        return $stmt->fetch();
    } catch(PDOException $e) {
        return null;
    }
}

function checkRole($allowedRoles) {
    $user = getCurrentUser();
    if (!$user) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized', 'message' => 'Silakan login terlebih dahulu']);
        exit();
    }
    
    if (!in_array($user['role'], $allowedRoles)) {
        http_response_code(403);
        echo json_encode(['error' => 'Forbidden', 'message' => 'Anda tidak memiliki akses untuk operasi ini']);
        exit();
    }
}

function sendResponse($data, $status = 200) {
    http_response_code($status);
    echo json_encode($data);
    exit();
}

function getRequestData() {
    $data = json_decode(file_get_contents("php://input"), true);
    if ($data === null) {
        $data = $_POST;
    }
    return $data;
}
?>