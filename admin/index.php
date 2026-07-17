<?php
$page_title = 'Admin - Voting System';
require_once __DIR__ . '/../includes/header.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token()) {
        set_flash('error', 'Invalid request');
        header('Location: ../admin/');
        exit;
    }

    $action = $_POST['action'] ?? '';

    if ($action === 'create_election') {
        $name = trim($_POST['name']);
        $desc = trim($_POST['description']);
        $start = $_POST['start_time'];
        $end = $_POST['end_time'];

        if (!$name || !$start || !$end) {
            set_flash('error', 'All fields required');
        } else {
            $stmt = $con->prepare("INSERT INTO elections (name, description, start_time, end_time) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $name, $desc, $start, $end);
            $stmt->execute();
            $stmt->close();
            log_audit($con, $_SESSION['id'], 'election_create', $name);
            set_flash('success', 'Election created');
        }
    }

    if ($action === 'toggle_election') {
        $eid = intval($_POST['election_id']);
        $con->query("UPDATE elections SET is_active = NOT is_active WHERE id = $eid");
        log_audit($con, $_SESSION['id'], 'election_toggle', "Election #$eid toggled");
        set_flash('success', 'Election updated');
    }

    if ($action === 'delete_election') {
        $eid = intval($_POST['election_id']);
        $con->query("DELETE FROM elections WHERE id = $eid");
        log_audit($con, $_SESSION['id'], 'election_delete', "Election #$eid deleted");
        set_flash('success', 'Election deleted');
    }

    if ($action === 'add_candidate') {
        $username = trim($_POST['username']);
        $idNum = trim($_POST['idNum']);
        $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

        $check = $con->prepare("SELECT id FROM userdata WHERE idNum = ?");
        $check->bind_param("s", $idNum);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            set_flash('error', 'Voter ID already exists');
        } else {
            $stmt = $con->prepare("INSERT INTO userdata (username, idNum, password, photo, standard, status, votes) VALUES (?, ?, ?, 'default.png', 'group', 0, 0)");
            $stmt->bind_param("sss", $username, $idNum, $password);
            $stmt->execute();
            $stmt->close();
            log_audit($con, $_SESSION['id'], 'candidate_add', $username);
            set_flash('success', 'Candidate added');
        }
        $check->close();
    }

    if ($action === 'delete_candidate') {
        $cid = intval($_POST['candidate_id']);
        $con->query("DELETE FROM userdata WHERE id = $cid AND standard = 'group'");
        log_audit($con, $_SESSION['id'], 'candidate_delete', "Candidate #$cid deleted");
        set_flash('success', 'Candidate deleted');
    }

    if ($action === 'reset_votes') {
        $con->query("UPDATE userdata SET votes = 0, status = 0 WHERE standard = 'group'");
        log_audit($con, $_SESSION['id'], 'votes_reset', 'All votes reset');
        set_flash('success', 'All votes reset');
    }

    header('Location: ../admin/');
    exit;
}

$elections = $con->query("SELECT * FROM elections ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);
$candidates = $con->query("SELECT id, username, idNum, votes, photo, created_at FROM userdata WHERE standard = 'group' ORDER BY votes DESC")->fetch_all(MYSQLI_ASSOC);
$audit = $con->query("SELECT a.*, u.username FROM audit_log a LEFT JOIN userdata u ON a.user_id = u.id ORDER BY a.created_at DESC LIMIT 20")->fetch_all(MYSQLI_ASSOC);
$stats = [
    'total_users' => $con->query("SELECT COUNT(*) FROM userdata")->fetch_row()[0],
    'total_candidates' => $con->query("SELECT COUNT(*) FROM userdata WHERE standard = 'group'")->fetch_row()[0],
    'total_voters' => $con->query("SELECT COUNT(*) FROM userdata WHERE standard = 'voter'")->fetch_row()[0],
    'total_votes_cast' => $con->query("SELECT COALESCE(SUM(votes), 0) FROM userdata WHERE standard = 'group'")->fetch_row()[0],
    'users_voted' => $con->query("SELECT COUNT(*) FROM userdata WHERE status = 1")->fetch_row()[0],
];
?>

<div class="flex items-center justify-between mb-8">
    <div class="icon-fade">
        <h1 class="text-lg font-semibold text-neutral-200">Admin Panel</h1>
        <p class="text-neutral-500 text-xs mt-1">Manage elections, candidates, and view activity</p>
    </div>
</div>

<!-- Stats -->
<div class="grid grid-cols-2 sm:grid-cols-5 gap-3 mb-8 stagger">
    <?php
    $stat_icons = [
        '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>',
        '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>',
        '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>',
        '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>',
        '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>'
    ];
    $stat_labels = ['Total Users', 'Candidates', 'Voters', 'Votes Cast', 'Users Voted'];
    $stat_values = [$stats['total_users'], $stats['total_candidates'], $stats['total_voters'], $stats['total_votes_cast'], $stats['users_voted']];
    foreach ($stat_values as $i => $val): ?>
        <div class="card p-3 text-center">
            <svg class="w-4 h-4 text-neutral-600 mx-auto mb-1.5 icon-hover-scale" fill="none" stroke="currentColor" viewBox="0 0 24 24"><?= $stat_icons[$i] ?></svg>
            <p class="text-lg font-bold text-neutral-200"><?= $val ?></p>
            <p class="text-[11px] text-neutral-500 mt-0.5"><?= $stat_labels[$i] ?></p>
        </div>
    <?php endforeach; ?>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <!-- Elections -->
    <div class="card p-5 icon-fade" style="animation-delay: 0.1s">
        <h2 class="text-sm font-semibold text-neutral-300 mb-3 flex items-center gap-1.5">
            <svg class="w-3.5 h-3.5 text-neutral-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            Elections
        </h2>
        <form action="../admin/" method="POST" class="space-y-2 mb-3">
            <input type="text" name="name" required placeholder="Election name"
                class="input-field text-xs py-2">
            <input type="text" name="description" placeholder="Description (optional)"
                class="input-field text-xs py-2">
            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="text-[11px] text-neutral-500">Start</label>
                    <input type="datetime-local" name="start_time" required class="input-field text-xs py-2">
                </div>
                <div>
                    <label class="text-[11px] text-neutral-500">End</label>
                    <input type="datetime-local" name="end_time" required class="input-field text-xs py-2">
                </div>
            </div>
            <input type="hidden" name="action" value="create_election">
            <?php echo csrf_field(); ?>
            <button type="submit" class="bg-white/[0.08] hover:bg-white/[0.14] text-neutral-300 text-xs font-medium px-3 py-2 rounded-md transition cursor-pointer">Create Election</button>
        </form>
        <div class="space-y-1.5 max-h-44 overflow-y-auto">
            <?php foreach ($elections as $e): ?>
                <div class="flex items-center justify-between bg-white/[0.02] rounded-md px-3 py-2 border border-neutral-800/50">
                    <div class="min-w-0">
                        <p class="text-neutral-200 text-xs font-medium truncate"><?= sanitize($e['name']) ?></p>
                        <p class="text-[11px] text-neutral-600"><?= date('M d, Y', strtotime($e['start_time'])) ?> - <?= date('M d, Y', strtotime($e['end_time'])) ?></p>
                    </div>
                    <div class="flex items-center gap-2 flex-shrink-0">
                        <span class="text-[11px] px-2 py-0.5 rounded-full <?= $e['is_active'] ? 'bg-white/[0.08] text-neutral-300' : 'bg-neutral-800 text-neutral-600' ?>"><?= $e['is_active'] ? 'Active' : 'Off' ?></span>
                        <form method="POST" class="inline"><input type="hidden" name="action" value="toggle_election"><input type="hidden" name="election_id" value="<?= $e['id'] ?>"><?php echo csrf_field(); ?><button class="text-[11px] text-neutral-500 hover:text-neutral-300 transition cursor-pointer">Toggle</button></form>
                        <form method="POST" class="inline"><input type="hidden" name="action" value="delete_election"><input type="hidden" name="election_id" value="<?= $e['id'] ?>"><?php echo csrf_field(); ?><button class="text-[11px] text-neutral-600 hover:text-red-400 transition cursor-pointer">Del</button></form>
                    </div>
                </div>
            <?php endforeach; ?>
            <?php if (empty($elections)): ?>
                <p class="text-neutral-600 text-xs text-center py-3">No elections yet</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Add Candidate -->
    <div class="card p-5 icon-fade" style="animation-delay: 0.15s">
        <h2 class="text-sm font-semibold text-neutral-300 mb-3 flex items-center gap-1.5">
            <svg class="w-3.5 h-3.5 text-neutral-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
            Add Candidate
        </h2>
        <form action="../admin/" method="POST" class="space-y-2">
            <input type="text" name="username" required placeholder="Candidate name"
                class="input-field text-xs py-2">
            <input type="text" name="idNum" required maxlength="10" placeholder="10-digit ID"
                class="input-field text-xs py-2">
            <input type="password" name="password" required placeholder="Password"
                class="input-field text-xs py-2">
            <input type="hidden" name="action" value="add_candidate">
            <?php echo csrf_field(); ?>
            <button type="submit" class="bg-white/[0.08] hover:bg-white/[0.14] text-neutral-300 text-xs font-medium px-3 py-2 rounded-md transition cursor-pointer">Add Candidate</button>
        </form>
    </div>
</div>

<!-- Candidates Table -->
<div class="card p-5 mb-8 icon-fade" style="animation-delay: 0.2s">
    <div class="flex items-center justify-between mb-3">
        <h2 class="text-sm font-semibold text-neutral-300 flex items-center gap-1.5">
            <svg class="w-3.5 h-3.5 text-neutral-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            Candidates (<?= count($candidates) ?>)
        </h2>
        <form action="../admin/" method="POST" onsubmit="return confirm('Reset ALL votes? This cannot be undone.')">
            <input type="hidden" name="action" value="reset_votes">
            <?php echo csrf_field(); ?>
            <button class="text-[11px] bg-white/[0.04] text-neutral-500 hover:text-yellow-400 hover:bg-white/[0.06] px-2.5 py-1 rounded-md transition font-medium cursor-pointer flex items-center gap-1">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                Reset All
            </button>
        </form>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead>
                <tr class="text-[11px] text-neutral-500 border-b border-neutral-800/50">
                    <th class="pb-2 font-medium">Name</th>
                    <th class="pb-2 font-medium">ID</th>
                    <th class="pb-2 font-medium">Votes</th>
                    <th class="pb-2 font-medium">Joined</th>
                    <th class="pb-2 font-medium"></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($candidates as $c): ?>
                <tr class="border-b border-neutral-800/30">
                    <td class="py-2.5 text-xs text-neutral-200 font-medium"><?= sanitize($c['username']) ?></td>
                    <td class="py-2.5 text-xs text-neutral-500"><?= sanitize($c['idNum']) ?></td>
                    <td class="py-2.5 text-xs text-neutral-200 font-bold"><?= $c['votes'] ?></td>
                    <td class="py-2.5 text-[11px] text-neutral-600"><?= date('M d, Y', strtotime($c['created_at'])) ?></td>
                    <td class="py-2.5">
                        <form method="POST" onsubmit="return confirm('Delete this candidate?')" class="inline">
                            <input type="hidden" name="action" value="delete_candidate">
                            <input type="hidden" name="candidate_id" value="<?= $c['id'] ?>">
                            <?php echo csrf_field(); ?>
                            <button class="text-[11px] text-neutral-600 hover:text-red-400 transition cursor-pointer">Delete</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Audit Log -->
<div class="card p-5 icon-fade" style="animation-delay: 0.25s">
    <h2 class="text-sm font-semibold text-neutral-300 mb-3 flex items-center gap-1.5">
        <svg class="w-3.5 h-3.5 text-neutral-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        Recent Activity
    </h2>
    <div class="space-y-1 max-h-56 overflow-y-auto">
        <?php foreach ($audit as $a): ?>
            <div class="flex items-center gap-2 text-xs bg-white/[0.02] rounded-md px-3 py-1.5 border border-neutral-800/30">
                <span class="text-[11px] text-neutral-600 w-20 flex-shrink-0"><?= date('M d H:i', strtotime($a['created_at'])) ?></span>
                <span class="text-neutral-400 font-medium w-20 flex-shrink-0 truncate"><?= sanitize($a['username'] ?? 'System') ?></span>
                <span class="text-neutral-500 truncate"><?= sanitize($a['action']) ?><?= $a['details'] ? ' - ' . sanitize($a['details']) : '' ?></span>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
