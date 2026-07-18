<?php
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

$gid = intval($_POST['groupid']);
$uid = intval($_SESSION['id']);

if ($gid <= 0 || $uid <= 0) {
    set_flash('error', 'Invalid request');
    header('Location: ../partials/dashboard.php');
    exit;
}

$active = get_active_election($con);
if (!$active) {
    set_flash('error', 'No active election at this time');
    header('Location: ../partials/dashboard.php');
    exit;
}

$check = $con->prepare("SELECT id, username FROM userdata WHERE id = ? AND standard = 'group'");
$check->bind_param("i", $gid);
$check->execute();
$candidate = $check->get_result()->fetch_assoc();
if (!$candidate) {
    $check->close();
    set_flash('error', 'Invalid candidate');
    header('Location: ../partials/dashboard.php');
    exit;
}
$check->close();

$election_id = $active['id'];

try {
    $con->begin_transaction();

    $stmt = $con->prepare("INSERT INTO election_votes (election_id, user_id, candidate_id) VALUES (?, ?, ?)");
    $stmt->bind_param("iii", $election_id, $uid, $gid);
    $stmt->execute();
    $stmt->close();

    $inc = $con->prepare("UPDATE userdata SET votes = votes + 1 WHERE id = ?");
    $inc->bind_param("i", $gid);
    $inc->execute();
    $inc->close();

    $con->commit();

    $_SESSION['vote_confirmed'] = [
        'election_id' => $election_id,
        'election_name' => $active['name'],
        'candidate_id' => $gid,
        'candidate_name' => $candidate['username'],
        'timestamp' => date('Y-m-d H:i:s'),
    ];

    log_audit($con, $uid, 'vote_cast', "Voted for candidate #$gid (" . $candidate['username'] . ") in election #$election_id");
    header('Location: ../partials/confirmation.php');
    exit;
} catch (mysqli_sql_exception $e) {
    $con->rollback();
    set_flash('error', 'You have already voted in this election');
    header('Location: ../partials/dashboard.php');
    exit;
}
