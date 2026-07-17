<?php
include('connect.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../');
    exit;
}

if (!verify_csrf_token()) {
    set_flash('error', 'Invalid request. Please try again.');
    header('Location: ../');
    exit;
}

$ip = get_client_ip();
if (is_rate_limited($con, $ip)) {
    set_flash('error', 'Too many attempts. Please wait 15 minutes.');
    header('Location: ../');
    exit;
}

$username = trim($_POST['username']);
$idNum = trim($_POST['idNum']);
$password = $_POST['password'];
$std = $_POST['std'];

log_login_attempt($con, $ip, $username);

$stmt = $con->prepare("SELECT * FROM userdata WHERE username = ? AND idNum = ? AND standard = ?");
$stmt->bind_param("sss", $username, $idNum, $std);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $data = $result->fetch_assoc();

    if (!password_verify($password, $data['password'])) {
        set_flash('error', 'Invalid credentials');
        header('Location: ../');
        exit;
    }

    clear_login_attempts($con, $ip);
    session_regenerate_id(true);
    $_SESSION['id'] = $data['id'];
    $_SESSION['status'] = $data['status'];
    $_SESSION['data'] = $data;
    unset($_SESSION['csrf_token']);

    $stmt_update = $con->prepare("UPDATE userdata SET last_login = NOW() WHERE id = ?");
    $stmt_update->bind_param("i", $data['id']);
    $stmt_update->execute();
    $stmt_update->close();

    $stmt2 = $con->prepare("SELECT username, photo, votes, id FROM userdata WHERE standard = 'group'");
    $stmt2->execute();
    $groups = $stmt2->get_result();
    if ($groups->num_rows > 0) {
        $_SESSION['groups'] = $groups->fetch_all(MYSQLI_ASSOC);
    }

    log_audit($con, $data['id'], 'login', 'User logged in');
    header('Location: ../partials/dashboard.php');
    exit;
} else {
    set_flash('error', 'Invalid credentials');
    header('Location: ../');
    exit;
}
