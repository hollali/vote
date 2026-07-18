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

if (!in_array($std, ['voter', 'group', 'admin'])) {
    set_flash('error', 'Invalid request');
    header('Location: ../');
    exit;
}

log_login_attempt($con, $ip, $username);

$stmt = $con->prepare("SELECT * FROM userdata WHERE username = ? AND idNum = ? AND standard = ?");
$stmt->bind_param("sss", $username, $idNum, $std);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $data = $result->fetch_assoc();

    if ($data['login_attempts'] >= 10) {
        set_flash('error', 'Account locked due to too many failed attempts. Contact an admin.');
        header('Location: ../');
        exit;
    }

    if (!password_verify($password, $data['password'])) {
        $upd = $con->prepare("UPDATE userdata SET login_attempts = login_attempts + 1 WHERE id = ?");
        $upd->bind_param("i", $data['id']);
        $upd->execute();
        $upd->close();
        set_flash('error', 'Invalid credentials');
        header('Location: ../');
        exit;
    }

    clear_login_attempts($con, $ip);

    $reset_att = $con->prepare("UPDATE userdata SET login_attempts = 0 WHERE id = ?");
    $reset_att->bind_param("i", $data['id']);
    $reset_att->execute();
    $reset_att->close();

    session_regenerate_id(true);

    unset($data['password']);
    $_SESSION['id'] = $data['id'];
    $_SESSION['data'] = $data;
    unset($_SESSION['csrf_token']);

    $active = get_active_election($con);
    if ($active) {
        $vc = $con->prepare("SELECT id FROM election_votes WHERE election_id = ? AND user_id = ?");
        $vc->bind_param("ii", $active['id'], $data['id']);
        $vc->execute();
        $_SESSION['has_voted'] = $vc->get_result()->num_rows > 0;
        $vc->close();
    } else {
        $_SESSION['has_voted'] = false;
    }

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
