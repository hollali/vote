<?php
$page_title = 'Activity - Admin';
require_once __DIR__ . '/includes/admin_header.php';

$audit = $con->query("SELECT a.*, u.username FROM audit_log a LEFT JOIN userdata u ON a.user_id = u.id ORDER BY a.created_at DESC LIMIT 50")->fetch_all(MYSQLI_ASSOC);
?>

<div class="flex items-center justify-between mb-8">
    <div class="icon-fade">
        <h1 class="text-lg font-semibold text-neutral-200">Activity Log</h1>
        <p class="text-neutral-500 text-xs mt-1">Recent system activity and audit trail</p>
    </div>
</div>

<div class="card p-5 icon-fade" style="animation-delay: 0.1s">
    <div class="space-y-1">
        <?php foreach ($audit as $a): ?>
            <div class="flex items-center gap-3 text-xs bg-white/[0.02] rounded-md px-4 py-2 border border-neutral-800/30">
                <span class="text-[11px] text-neutral-600 w-24 flex-shrink-0"><?= date('M d, H:i:s', strtotime($a['created_at'])) ?></span>
                <span class="text-neutral-400 font-medium w-20 flex-shrink-0 truncate"><?= sanitize($a['username'] ?? 'System') ?></span>
                <span class="text-neutral-500 font-medium w-24 flex-shrink-0 truncate"><?= sanitize($a['action']) ?></span>
                <span class="text-neutral-500 truncate"><?= $a['details'] ? sanitize($a['details']) : '—' ?></span>
            </div>
        <?php endforeach; ?>
        <?php if (empty($audit)): ?>
            <p class="text-neutral-600 text-xs text-center py-8">No activity recorded yet</p>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
