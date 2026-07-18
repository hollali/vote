<?php
$page_title = 'Admins - Admin';
require_once __DIR__ . '/includes/admin_header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token()) {
        set_flash('error', 'Invalid request');
        header('Location: ../admin/admins.php');
        exit;
    }

    $action = $_POST['action'] ?? '';

    if ($action === 'add_admin') {
        $username = trim($_POST['username']);
        $idNum = trim($_POST['idNum']);
        $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

        if (!$username || !$idNum) {
            set_flash('error', 'All fields required');
        } elseif (strlen($idNum) !== 10) {
            set_flash('error', 'ID must be 10 digits');
        } else {
            $check = $con->prepare("SELECT id FROM userdata WHERE idNum = ?");
            $check->bind_param("s", $idNum);
            $check->execute();
            if ($check->get_result()->num_rows > 0) {
                set_flash('error', 'ID number already exists');
            } else {
                $stmt = $con->prepare("INSERT INTO userdata (username, idNum, password, photo, standard, status, votes) VALUES (?, ?, ?, 'default.png', 'admin', 0, 0)");
                $stmt->bind_param("sss", $username, $idNum, $password);
                $stmt->execute();
                $stmt->close();
                log_audit($con, $_SESSION['id'], 'admin_add', $username);
                set_flash('success', 'Admin added');
            }
            $check->close();
        }
    }

    if ($action === 'remove_admin') {
        $aid = intval($_POST['admin_id']);
        if ($aid == $_SESSION['id']) {
            set_flash('error', 'You cannot remove yourself');
        } else {
            $stmt = $con->prepare("UPDATE userdata SET standard = 'voter' WHERE id = ? AND standard = 'admin'");
            $stmt->bind_param("i", $aid);
            $stmt->execute();
            $stmt->close();
            log_audit($con, $_SESSION['id'], 'admin_remove', "Admin #$aid demoted");
            set_flash('success', 'Admin removed');
        }
    }

    header('Location: ../admin/admins.php');
    exit;
}

$admins = $con->query("SELECT id, username, idNum, created_at FROM userdata WHERE standard = 'admin' ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);
?>

<div class="flex items-center justify-between mb-8">
    <div class="icon-fade">
        <h1 class="text-lg font-semibold text-neutral-200">Admin Accounts</h1>
        <p class="text-neutral-500 text-xs mt-1">Manage admin access</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Current Admins -->
    <div class="card p-5 icon-fade" style="animation-delay: 0.1s">
        <h2 class="text-sm font-semibold text-neutral-300 mb-3 flex items-center gap-1.5">
            <svg class="w-3.5 h-3.5 text-neutral-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
            Current Admins (<?= count($admins) ?>)
        </h2>
        <div class="space-y-2">
            <?php foreach ($admins as $a): ?>
                <div class="flex items-center justify-between bg-white/[0.02] rounded-lg px-4 py-3 border border-neutral-800/50">
                    <div class="min-w-0">
                        <p class="text-neutral-200 text-xs font-medium"><?= sanitize($a['username']) ?></p>
                        <p class="text-[11px] text-neutral-600"><?= sanitize($a['idNum']) ?></p>
                        <p class="text-[11px] text-neutral-600">Since <?= date('M d, Y', strtotime($a['created_at'])) ?></p>
                    </div>
                    <?php if ($a['id'] != $_SESSION['id']): ?>
                        <form method="POST" onsubmit="return confirm('Remove this admin? They will be demoted to voter.')" class="flex-shrink-0">
                            <input type="hidden" name="action" value="remove_admin">
                            <input type="hidden" name="admin_id" value="<?= $a['id'] ?>">
                            <?php echo csrf_field(); ?>
                            <button class="text-[11px] text-neutral-600 hover:text-red-400 transition cursor-pointer px-2 py-1 rounded hover:bg-white/[0.04]">Remove</button>
                        </form>
                    <?php else: ?>
                        <span class="text-[11px] text-neutral-500 px-2 py-1">You</span>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Add Admin -->
    <div class="card p-5 icon-fade" style="animation-delay: 0.15s">
        <h2 class="text-sm font-semibold text-neutral-300 mb-3 flex items-center gap-1.5">
            <svg class="w-3.5 h-3.5 text-neutral-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
            Add New Admin
        </h2>
        <form action="../admin/admins.php" method="POST" class="space-y-3">
            <input type="text" name="username" required placeholder="Full name"
                class="input-field text-xs py-2.5">
            <input type="text" name="idNum" required maxlength="10" placeholder="10-digit ID"
                class="input-field text-xs py-2.5">
            <input type="password" name="password" required placeholder="Password (min 8 chars)"
                class="input-field text-xs py-2.5">
            <input type="hidden" name="action" value="add_admin">
            <?php echo csrf_field(); ?>
            <button type="submit" class="bg-white/[0.08] hover:bg-white/[0.14] text-neutral-300 text-xs font-medium px-4 py-2 rounded-md transition cursor-pointer">Add Admin</button>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
