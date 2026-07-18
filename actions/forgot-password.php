<?php
require_once __DIR__ . '/connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token()) {
        set_flash('error', 'Invalid request');
        header('Location: ../partials/forgot-password.php');
        exit;
    }

    $ip = get_client_ip();
    if (is_rate_limited($con, $ip, 3, 15)) {
        set_flash('error', 'Too many attempts. Please wait 15 minutes.');
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

        // In production, send email. For now, redirect with token in URL only.
        set_flash('success', 'Reset link generated. You will be redirected now.');
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
