<?php
session_start();
include('connect.php');

if (!isset($_SESSION['id'])) {
    header('Location: ../');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../partials/dashboard.php');
    exit;
}

if (!verify_csrf_token()) {
    set_flash('error', 'Invalid request');
    header('Location: ../partials/dashboard.php');
    exit;
}

if ($_SESSION['status'] == 1) {
    set_flash('error', 'You have already voted');
    header('Location: ../partials/dashboard.php');
    exit;
}

$gid = intval($_POST['groupid']);
$uid = intval($_SESSION['id']);

if ($gid <= 0 || $uid <= 0) {
    set_flash('error', 'Invalid request');
    header('Location: ../partials/dashboard.php');
    exit;
}

$updatevotes = $con->prepare("UPDATE userdata SET votes = votes + 1 WHERE id = ?");
$updatevotes->bind_param("i", $gid);
$updatevotes->execute();

$updatestatus = $con->prepare("UPDATE userdata SET status = 1 WHERE id = ?");
$updatestatus->bind_param("i", $uid);
$updatestatus->execute();

if ($updatevotes->affected_rows >= 0 && $updatestatus->affected_rows >= 0) {
    $getgroups = $con->prepare("SELECT username, photo, votes, id FROM userdata WHERE standard = 'group'");
    $getgroups->execute();
    $groups = $getgroups->get_result()->fetch_all(MYSQLI_ASSOC);
    $_SESSION['groups'] = $groups;
    $_SESSION['status'] = 1;

    log_audit($con, $uid, 'vote_cast', "Voted for candidate #$gid");
    set_flash('success', 'Vote cast successfully!');
} else {
    set_flash('error', 'Technical error! Please try again.');
}

header('Location: ../partials/dashboard.php');
exit;
