<?php
session_start();

// --- Database Config ---
$db_host = "localhost";
$db_user = "root";
$db_pass = "Vendetta7080";
$db_name = "vote";

$con = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($con->connect_error) {
    die("Database connection failed: " . $con->connect_error);
}
$con->set_charset("utf8mb4");

// --- CSRF Helpers ---
function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field() {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(generate_csrf_token()) . '">';
}

function verify_csrf_token() {
    if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token'])) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $_POST['csrf_token']);
}

// --- Sanitize ---
function sanitize($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

// --- Rate Limiting ---
function is_rate_limited($con, $ip, $max_attempts = 5, $window_minutes = 15) {
    $stmt = $con->prepare("SELECT COUNT(*) as cnt FROM login_attempts WHERE ip_address = ? AND attempted_at > DATE_SUB(NOW(), INTERVAL ? MINUTE)");
    $stmt->bind_param("si", $ip, $window_minutes);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $result['cnt'] >= $max_attempts;
}

function log_login_attempt($con, $ip, $username) {
    $stmt = $con->prepare("INSERT INTO login_attempts (ip_address, username) VALUES (?, ?)");
    $stmt->bind_param("ss", $ip, $username);
    $stmt->execute();
    $stmt->close();
}

function clear_login_attempts($con, $ip) {
    $stmt = $con->prepare("DELETE FROM login_attempts WHERE ip_address = ?");
    $stmt->bind_param("s", $ip);
    $stmt->execute();
    $stmt->close();
}

function log_audit($con, $user_id, $action, $details = null) {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $stmt = $con->prepare("INSERT INTO audit_log (user_id, action, details, ip_address) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $user_id, $action, $details, $ip);
    $stmt->execute();
    $stmt->close();
}

// --- Auth Helpers ---
function is_logged_in() {
    return isset($_SESSION['id']);
}

function is_admin() {
    return isset($_SESSION['data']) && $_SESSION['data']['standard'] === 'admin';
}

function require_login() {
    if (!is_logged_in()) {
        header('Location: ../');
        exit;
    }
}

function require_admin() {
    require_login();
    if (!is_admin()) {
        header('Location: ../partials/dashboard.php');
        exit;
    }
}

function get_client_ip() {
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

// --- Flash Messages ---
function set_flash($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function get_flash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

// --- Upload Validation ---
function validate_image_upload($file, $max_size = 2097152) {
    $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['valid' => false, 'error' => 'Upload failed'];
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mime, $allowed)) {
        return ['valid' => false, 'error' => 'Only JPG, PNG, GIF, WebP allowed'];
    }

    if ($file['size'] > $max_size) {
        return ['valid' => false, 'error' => 'File must be under 2MB'];
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $safe_name = bin2hex(random_bytes(16)) . '.' . $ext;

    return ['valid' => true, 'name' => $safe_name, 'tmp' => $file['tmp_name']];
}

// --- Election Helpers ---
function get_active_election($con) {
    $stmt = $con->prepare("SELECT * FROM elections WHERE is_active = 1 AND start_time <= NOW() AND end_time >= NOW() ORDER BY id DESC LIMIT 1");
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $result;
}
