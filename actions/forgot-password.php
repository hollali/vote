<?php
session_start();
require_once __DIR__ . '/../actions/connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token()) {
        set_flash('error', 'Invalid request');
        header('Location: ../partials/forgot-password.php');
        exit;
    }

    $idNum = trim($_POST['idNum']);
    $email = trim($_POST['email']);

    if (!preg_match('/^\d{10}$/', $idNum) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        set_flash('error', 'Invalid Voter ID or email');
        header('Location: ../partials/forgot-password.php');
        exit;
    }

    $stmt = $con->prepare("SELECT id, email FROM userdata WHERE idNum = ? AND email IS NOT NULL AND email != ''");
    $stmt->bind_param("s", $idNum);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($result && $result['email'] === $email) {
        $token = bin2hex(random_bytes(32));
        $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

        $stmt2 = $con->prepare("UPDATE userdata SET reset_token = ?, reset_expiry = ? WHERE id = ?");
        $stmt2->bind_param("ssi", $token, $expiry, $result['id']);
        $stmt2->execute();
        $stmt2->close();

        // In production, send email. For now, show token.
        set_flash('success', 'Password reset token: ' . $token . ' (In production this would be emailed to you)');
        header('Location: ../partials/reset-password.php?token=' . $token);
        exit;
    } else {
        set_flash('error', 'No account found with that Voter ID and email combination');
        header('Location: ../partials/forgot-password.php');
        exit;
    }
}

header('Location: ../partials/forgot-password.php');
exit;
