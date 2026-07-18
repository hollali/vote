<?php
$page_title = 'Dashboard - Admin';
require_once __DIR__ . '/includes/admin_header.php';

$total_users = $con->query("SELECT COUNT(*) FROM userdata")->fetch_row()[0];
$total_candidates = $con->query("SELECT COUNT(*) FROM userdata WHERE standard = 'group'")->fetch_row()[0];
$total_voters = $con->query("SELECT COUNT(*) FROM userdata WHERE standard = 'voter'")->fetch_row()[0];
$total_admins = $con->query("SELECT COUNT(*) FROM userdata WHERE standard = 'admin'")->fetch_row()[0];
$total_elections = $con->query("SELECT COUNT(*) FROM elections")->fetch_row()[0];
$active_elections = $con->query("SELECT COUNT(*) FROM elections WHERE is_active = 1 AND start_time <= NOW() AND end_time >= NOW()")->fetch_row()[0];

$active_eid = $active_election ? $active_election['id'] : 0;
if ($active_eid) {
    $ev_stmt = $con->prepare("SELECT COUNT(*) FROM election_votes WHERE election_id = ?");
    $ev_stmt->bind_param("i", $active_eid);
    $ev_stmt->execute();
    $ev_count = $ev_stmt->get_result()->fetch_row()[0];
    $ev_stmt->close();
} else {
    $ev_count = 0;
}

$locked = $con->query("SELECT COUNT(*) FROM userdata WHERE login_attempts >= 10")->fetch_row()[0];

$recent_audit = $con->query("SELECT a.*, u.username FROM audit_log a LEFT JOIN userdata u ON a.user_id = u.id ORDER BY a.created_at DESC LIMIT 8")->fetch_all(MYSQLI_ASSOC);
?>

<div class="mb-8 icon-fade">
    <h1 class="text-lg font-semibold text-neutral-200">Dashboard</h1>
    <p class="text-neutral-500 text-xs mt-1">Overview of your voting system</p>
</div>

<!-- Stats -->
<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3 mb-8 stagger">
    <?php
    $stats = [
        ['label' => 'Total Users', 'value' => $total_users, 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>'],
        ['label' => 'Candidates', 'value' => $total_candidates, 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>'],
        ['label' => 'Voters', 'value' => $total_voters, 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>'],
        ['label' => 'Admins', 'value' => $total_admins, 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>'],
        ['label' => 'Elections', 'value' => $total_elections, 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>'],
        ['label' => 'Active', 'value' => $active_elections, 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>'],
        ['label' => 'Votes Cast', 'value' => $ev_count, 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>'],
        ['label' => 'Locked', 'value' => $locked, 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>'],
    ];
    foreach ($stats as $s): ?>
        <div class="card p-4 text-center">
            <svg class="w-5 h-5 text-neutral-600 mx-auto mb-2 icon-hover-scale" fill="none" stroke="currentColor" viewBox="0 0 24 24"><?= $s['icon'] ?></svg>
            <p class="text-xl font-bold text-neutral-200"><?= $s['value'] ?></p>
            <p class="text-[11px] text-neutral-500 mt-0.5"><?= $s['label'] ?></p>
        </div>
    <?php endforeach; ?>
</div>

<!-- Active Election + Quick Actions -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <div class="card p-5 icon-fade" style="animation-delay: 0.1s">
        <h2 class="text-sm font-semibold text-neutral-300 mb-3 flex items-center gap-1.5">
            <svg class="w-3.5 h-3.5 text-neutral-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Active Election
        </h2>
        <?php if ($active_election): ?>
            <div class="bg-white/[0.04] rounded-lg p-4 border border-neutral-800/50">
                <p class="text-sm font-medium text-neutral-200"><?= sanitize($active_election['name']) ?></p>
                <?php if ($active_election['description']): ?>
                    <p class="text-xs text-neutral-500 mt-1"><?= sanitize($active_election['description']) ?></p>
                <?php endif; ?>
                <div class="flex items-center gap-4 mt-3 text-[11px] text-neutral-500">
                    <span>Starts: <?= date('M d, g:i A', strtotime($active_election['start_time'])) ?></span>
                    <span>Ends: <?= date('M d, g:i A', strtotime($active_election['end_time'])) ?></span>
                </div>
                <p class="text-xs text-neutral-400 mt-2"><?= $ev_count ?> vote<?= $ev_count !== 1 ? 's' : '' ?> cast</p>
            </div>
        <?php else: ?>
            <p class="text-neutral-600 text-xs py-4 text-center">No active election</p>
        <?php endif; ?>
    </div>

    <div class="card p-5 icon-fade" style="animation-delay: 0.15s">
        <h2 class="text-sm font-semibold text-neutral-300 mb-3 flex items-center gap-1.5">
            <svg class="w-3.5 h-3.5 text-neutral-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            Quick Actions
        </h2>
        <div class="grid grid-cols-2 gap-2">
            <a href="<?= $base_url ?>/admin/elections.php" class="bg-white/[0.04] hover:bg-white/[0.08] border border-neutral-800/50 rounded-lg p-3 text-center transition group">
                <svg class="w-5 h-5 text-neutral-600 mx-auto mb-1.5 group-hover:text-neutral-400 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4v16m8-8H4"/></svg>
                <p class="text-xs text-neutral-400 font-medium">New Election</p>
            </a>
            <a href="<?= $base_url ?>/admin/candidates.php" class="bg-white/[0.04] hover:bg-white/[0.08] border border-neutral-800/50 rounded-lg p-3 text-center transition group">
                <svg class="w-5 h-5 text-neutral-600 mx-auto mb-1.5 group-hover:text-neutral-400 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                <p class="text-xs text-neutral-400 font-medium">Add Candidate</p>
            </a>
            <a href="<?= $base_url ?>/admin/voters.php" class="bg-white/[0.04] hover:bg-white/[0.08] border border-neutral-800/50 rounded-lg p-3 text-center transition group">
                <svg class="w-5 h-5 text-neutral-600 mx-auto mb-1.5 group-hover:text-neutral-400 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                <p class="text-xs text-neutral-400 font-medium">Manage Voters</p>
            </a>
            <a href="<?= $base_url ?>/admin/audit.php" class="bg-white/[0.04] hover:bg-white/[0.08] border border-neutral-800/50 rounded-lg p-3 text-center transition group">
                <svg class="w-5 h-5 text-neutral-600 mx-auto mb-1.5 group-hover:text-neutral-400 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <p class="text-xs text-neutral-400 font-medium">Activity Log</p>
            </a>
        </div>
    </div>
</div>

<!-- Recent Activity -->
<div class="card p-5 icon-fade" style="animation-delay: 0.2s">
    <div class="flex items-center justify-between mb-3">
        <h2 class="text-sm font-semibold text-neutral-300 flex items-center gap-1.5">
            <svg class="w-3.5 h-3.5 text-neutral-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Recent Activity
        </h2>
        <a href="<?= $base_url ?>/admin/audit.php" class="text-[11px] text-neutral-500 hover:text-neutral-300 transition">View all</a>
    </div>
    <div class="space-y-1">
        <?php foreach ($recent_audit as $a): ?>
            <div class="flex items-center gap-2 text-xs bg-white/[0.02] rounded-md px-3 py-1.5 border border-neutral-800/30">
                <span class="text-[11px] text-neutral-600 w-16 flex-shrink-0"><?= date('M d H:i', strtotime($a['created_at'])) ?></span>
                <span class="text-neutral-400 font-medium w-16 flex-shrink-0 truncate"><?= sanitize($a['username'] ?? 'System') ?></span>
                <span class="text-neutral-500 truncate"><?= sanitize($a['action']) ?><?= $a['details'] ? ' - ' . sanitize($a['details']) : '' ?></span>
            </div>
        <?php endforeach; ?>
        <?php if (empty($recent_audit)): ?>
            <p class="text-neutral-600 text-xs text-center py-3">No activity yet</p>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
