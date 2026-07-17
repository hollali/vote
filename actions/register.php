<?php
session_start();
require_once __DIR__ . '/connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token()) {
        set_flash('error', 'Invalid request');
        header('Location: ../partials/registration.php');
        exit;
    }

    $username = trim($_POST['username']);
    $idNum = trim($_POST['idNum']);
    $password = $_POST['password'];
    $cpassword = $_POST['cpassword'];
    $email = trim($_POST['email'] ?? '');
    $std = $_POST['std'];

    if (strlen($username) < 3 || strlen($username) > 50) {
        set_flash('error', 'Username must be 3-50 characters');
        header('Location: ../partials/registration.php');
        exit;
    }

    if (!preg_match('/^\d{10}$/', $idNum)) {
        set_flash('error', 'Voter ID must be exactly 10 digits');
        header('Location: ../partials/registration.php');
        exit;
    }

    if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        set_flash('error', 'Invalid email address');
        header('Location: ../partials/registration.php');
        exit;
    }

    if (strlen($password) < 6) {
        set_flash('error', 'Password must be at least 6 characters');
        header('Location: ../partials/registration.php');
        exit;
    }

    if ($password !== $cpassword) {
        set_flash('error', 'Passwords do not match');
        header('Location: ../partials/registration.php');
        exit;
    }

    $check = $con->prepare("SELECT id FROM userdata WHERE idNum = ?");
    $check->bind_param("s", $idNum);
    $check->execute();
    if ($check->get_result()->num_rows > 0) {
        set_flash('error', 'This Voter ID is already registered');
        header('Location: ../partials/registration.php');
        exit;
    }
    $check->close();

    $image = 'default.png';
    $image_tmp = null;

    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $validation = validate_image_upload($_FILES['photo']);
        if (!$validation['valid']) {
            set_flash('error', $validation['error']);
            header('Location: ../partials/registration.php');
            exit;
        }
        $image = $validation['name'];
        $image_tmp = $validation['tmp'];
    }

    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    $stmt = $con->prepare("INSERT INTO userdata (username, idNum, email, password, photo, standard, status, votes) VALUES (?, ?, ?, ?, ?, ?, 0, 0)");
    $stmt->bind_param("ssssss", $username, $idNum, $email, $hashed_password, $image, $std);

    if ($stmt->execute()) {
        if ($image_tmp) {
            move_uploaded_file($image_tmp, "../uploads/$image");
        }
        set_flash('success', 'Registration Successful! Please login.');
        header('Location: ../');
        exit;
    } else {
        set_flash('error', 'Registration failed. Please try again.');
        header('Location: ../partials/registration.php');
        exit;
    }
} else {
    header('Location: ../partials/registration.php');
    exit;
}
