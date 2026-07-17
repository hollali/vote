<?php
session_start();
require_once __DIR__ . '/../actions/connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token()) {
        set_flash('error', 'Invalid request');
        header('Location: ../partials/reset-password.php');
        exit;
    }

    $token = trim($_POST['token']);
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];

    if (strlen($password) < 6) {
        set_flash('error', 'Password must be at least 6 characters');
        header('Location: ../partials/reset-password.php?token=' . urlencode($token));
        exit;
    }

    if ($password !== $confirm) {
        set_flash('error', 'Passwords do not match');
        header('Location: ../partials/reset-password.php?token=' . urlencode($token));
        exit;
    }

    $stmt = $con->prepare("SELECT id FROM userdata WHERE reset_token = ? AND reset_expiry > NOW()");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($result) {
        $hashed = password_hash($password, PASSWORD_BCRYPT);
        $stmt2 = $con->prepare("UPDATE userdata SET password = ?, reset_token = NULL, reset_expiry = NULL WHERE id = ?");
        $stmt2->bind_param("si", $hashed, $result['id']);
        $stmt2->execute();
        $stmt2->close();

        set_flash('success', 'Password reset successful! Please login with your new password.');
        header('Location: ../');
        exit;
    } else {
        set_flash('error', 'Invalid or expired token');
        header('Location: ../partials/forgot-password.php');
        exit;
    }
}

header('Location: ../partials/reset-password.php');
exit;
