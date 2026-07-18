<?php
$page_title = 'Elections - Admin';
require_once __DIR__ . '/includes/admin_header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token()) {
        set_flash('error', 'Invalid request');
        header('Location: ../admin/elections.php');
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
        $stmt = $con->prepare("UPDATE elections SET is_active = NOT is_active WHERE id = ?");
        $stmt->bind_param("i", $eid);
        $stmt->execute();
        $stmt->close();
        log_audit($con, $_SESSION['id'], 'election_toggle', "Election #$eid toggled");
        set_flash('success', 'Election updated');
    }

    if ($action === 'delete_election') {
        $eid = intval($_POST['election_id']);
        $stmt = $con->prepare("DELETE FROM elections WHERE id = ?");
        $stmt->bind_param("i", $eid);
        $stmt->execute();
        $stmt->close();
        log_audit($con, $_SESSION['id'], 'election_delete', "Election #$eid deleted");
        set_flash('success', 'Election deleted');
    }

    if ($action === 'reset_votes') {
        $active = get_active_election($con);
        if ($active) {
            $stmt = $con->prepare("DELETE FROM election_votes WHERE election_id = ?");
            $stmt->bind_param("i", $active['id']);
            $stmt->execute();
            $stmt->close();
        }
        $con->query("UPDATE userdata SET votes = 0 WHERE standard = 'group'");
        log_audit($con, $_SESSION['id'], 'votes_reset', 'All votes reset');
        set_flash('success', 'All votes reset');
    }

    header('Location: ../admin/elections.php');
    exit;
}

$elections = $con->query("SELECT * FROM elections ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);
?>

<div class="flex items-center justify-between mb-8">
    <div class="icon-fade">
        <h1 class="text-lg font-semibold text-neutral-200">Elections</h1>
        <p class="text-neutral-500 text-xs mt-1">Create and manage elections</p>
    </div>
</div>

<!-- Create Election -->
<div class="card p-5 mb-8 icon-fade" style="animation-delay: 0.1s">
    <h2 class="text-sm font-semibold text-neutral-300 mb-3 flex items-center gap-1.5">
        <svg class="w-3.5 h-3.5 text-neutral-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4v16m8-8H4"/></svg>
        Create Election
    </h2>
    <form action="../admin/elections.php" method="POST" class="space-y-3">
        <input type="text" name="name" required placeholder="Election name"
            class="input-field text-xs py-2.5">
        <input type="text" name="description" placeholder="Description (optional)"
            class="input-field text-xs py-2.5">
        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="text-[11px] text-neutral-500 mb-1 block">Start Time</label>
                <input type="datetime-local" name="start_time" required class="input-field text-xs py-2.5">
            </div>
            <div>
                <label class="text-[11px] text-neutral-500 mb-1 block">End Time</label>
                <input type="datetime-local" name="end_time" required class="input-field text-xs py-2.5">
            </div>
        </div>
        <input type="hidden" name="action" value="create_election">
        <?php echo csrf_field(); ?>
        <button type="submit" class="bg-white/[0.08] hover:bg-white/[0.14] text-neutral-300 text-xs font-medium px-4 py-2 rounded-md transition cursor-pointer">Create Election</button>
    </form>
</div>

<!-- Elections List -->
<div class="card p-5 icon-fade" style="animation-delay: 0.15s">
    <div class="flex items-center justify-between mb-3">
        <h2 class="text-sm font-semibold text-neutral-300 flex items-center gap-1.5">
            <svg class="w-3.5 h-3.5 text-neutral-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            All Elections (<?= count($elections) ?>)
        </h2>
        <form action="../admin/elections.php" method="POST" onsubmit="return confirm('Reset ALL votes for the active election? This cannot be undone.')">
            <input type="hidden" name="action" value="reset_votes">
            <?php echo csrf_field(); ?>
            <button class="text-[11px] bg-white/[0.04] text-neutral-500 hover:text-yellow-400 hover:bg-white/[0.06] px-2.5 py-1 rounded-md transition font-medium cursor-pointer flex items-center gap-1">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                Reset All Votes
            </button>
        </form>
    </div>
    <div class="space-y-2">
        <?php foreach ($elections as $e): ?>
            <div class="flex items-center justify-between bg-white/[0.02] rounded-lg px-4 py-3 border border-neutral-800/50">
                <div class="min-w-0">
                    <div class="flex items-center gap-2">
                        <p class="text-neutral-200 text-xs font-medium"><?= sanitize($e['name']) ?></p>
                        <span class="text-[10px] px-2 py-0.5 rounded-full <?= $e['is_active'] ? 'bg-white/[0.08] text-neutral-300' : 'bg-neutral-800 text-neutral-600' ?>"><?= $e['is_active'] ? 'Active' : 'Off' ?></span>
                    </div>
                    <?php if ($e['description']): ?>
                        <p class="text-[11px] text-neutral-500 mt-0.5"><?= sanitize($e['description']) ?></p>
                    <?php endif; ?>
                    <p class="text-[11px] text-neutral-600 mt-0.5"><?= date('M d, Y g:i A', strtotime($e['start_time'])) ?> - <?= date('M d, Y g:i A', strtotime($e['end_time'])) ?></p>
                </div>
                <div class="flex items-center gap-2 flex-shrink-0">
                    <form method="POST" class="inline">
                        <input type="hidden" name="action" value="toggle_election">
                        <input type="hidden" name="election_id" value="<?= $e['id'] ?>">
                        <?php echo csrf_field(); ?>
                        <button class="text-[11px] text-neutral-500 hover:text-neutral-300 transition cursor-pointer px-2 py-1 rounded hover:bg-white/[0.04]">Toggle</button>
                    </form>
                    <form method="POST" class="inline" onsubmit="return confirm('Delete this election?')">
                        <input type="hidden" name="action" value="delete_election">
                        <input type="hidden" name="election_id" value="<?= $e['id'] ?>">
                        <?php echo csrf_field(); ?>
                        <button class="text-[11px] text-neutral-600 hover:text-red-400 transition cursor-pointer px-2 py-1 rounded hover:bg-white/[0.04]">Delete</button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
        <?php if (empty($elections)): ?>
            <p class="text-neutral-600 text-xs text-center py-6">No elections yet. Create one above.</p>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
