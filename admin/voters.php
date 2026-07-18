<?php
$page_title = 'Voters - Admin';
require_once __DIR__ . '/includes/admin_header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token()) {
        set_flash('error', 'Invalid request');
        header('Location: ../admin/voters.php');
        exit;
    }

    $action = $_POST['action'] ?? '';

    if ($action === 'delete_voter') {
        $vid = intval($_POST['voter_id']);
        $stmt = $con->prepare("DELETE FROM userdata WHERE id = ? AND standard = 'voter'");
        $stmt->bind_param("i", $vid);
        $stmt->execute();
        $stmt->close();
        log_audit($con, $_SESSION['id'], 'voter_delete', "Voter #$vid deleted");
        set_flash('success', 'Voter deleted');
    }

    if ($action === 'unlock_account') {
        $uid = intval($_POST['user_id']);
        $stmt = $con->prepare("UPDATE userdata SET login_attempts = 0 WHERE id = ?");
        $stmt->bind_param("i", $uid);
        $stmt->execute();
        $stmt->close();
        log_audit($con, $_SESSION['id'], 'account_unlock', "Account #$uid unlocked");
        set_flash('success', 'Account unlocked');
    }

    header('Location: ../admin/voters.php');
    exit;
}

$voters = $con->query("SELECT id, username, idNum, photo, login_attempts, created_at FROM userdata WHERE standard = 'voter' ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);
?>

<div class="flex items-center justify-between mb-8">
    <div class="icon-fade">
        <h1 class="text-lg font-semibold text-neutral-200">Voters</h1>
        <p class="text-neutral-500 text-xs mt-1">View and manage registered voters</p>
    </div>
</div>

<div class="card p-5 icon-fade" style="animation-delay: 0.1s">
    <h2 class="text-sm font-semibold text-neutral-300 mb-3 flex items-center gap-1.5">
        <svg class="w-3.5 h-3.5 text-neutral-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
        All Voters (<?= count($voters) ?>)
    </h2>
    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead>
                <tr class="text-[11px] text-neutral-500 border-b border-neutral-800/50">
                    <th class="pb-2 font-medium">Name</th>
                    <th class="pb-2 font-medium">ID</th>
                    <th class="pb-2 font-medium">Status</th>
                    <th class="pb-2 font-medium">Joined</th>
                    <th class="pb-2 font-medium text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($voters as $v): ?>
                <tr class="border-b border-neutral-800/30">
                    <td class="py-3 text-xs text-neutral-200 font-medium"><?= sanitize($v['username']) ?></td>
                    <td class="py-3 text-xs text-neutral-500"><?= sanitize($v['idNum']) ?></td>
                    <td class="py-3 text-xs">
                        <?php if ($v['login_attempts'] >= 10): ?>
                            <span class="text-yellow-400 flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                Locked
                            </span>
                        <?php else: ?>
                            <span class="text-neutral-500">Active</span>
                        <?php endif; ?>
                    </td>
                    <td class="py-3 text-[11px] text-neutral-600"><?= date('M d, Y', strtotime($v['created_at'])) ?></td>
                    <td class="py-3 text-right">
                        <div class="flex items-center justify-end gap-1">
                            <?php if ($v['login_attempts'] >= 10): ?>
                                <form method="POST" class="inline">
                                    <input type="hidden" name="action" value="unlock_account">
                                    <input type="hidden" name="user_id" value="<?= $v['id'] ?>">
                                    <?php echo csrf_field(); ?>
                                    <button class="text-[11px] text-yellow-400 hover:text-yellow-300 transition cursor-pointer px-2 py-1 rounded hover:bg-white/[0.04] flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 11V7a4 4 0 118 0m-4 4v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"/></svg>
                                        Unlock
                                    </button>
                                </form>
                            <?php endif; ?>
                            <form method="POST" onsubmit="return confirm('Delete this voter? This cannot be undone.')" class="inline">
                                <input type="hidden" name="action" value="delete_voter">
                                <input type="hidden" name="voter_id" value="<?= $v['id'] ?>">
                                <?php echo csrf_field(); ?>
                                <button class="text-[11px] text-neutral-600 hover:text-red-400 transition cursor-pointer px-2 py-1 rounded hover:bg-white/[0.04]">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($voters)): ?>
                <tr>
                    <td colspan="5" class="py-6 text-center text-neutral-600 text-xs">No voters registered yet</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
