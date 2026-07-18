<?php
$page_title = 'Vote Recorded - Voting System';
require_once __DIR__ . '/../includes/header.php';
require_login();

if (!isset($_SESSION['vote_confirmed'])) {
    header('Location: ../partials/dashboard.php');
    exit;
}

$vote_info = $_SESSION['vote_confirmed'];
unset($_SESSION['vote_confirmed']);
?>

<div class="max-w-md mx-auto text-center py-8">
    <!-- Success Animation -->
    <div class="icon-fade mb-6">
        <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-[var(--accent-light)] border-2 border-[var(--accent-border)] mb-4" style="animation: accentGlow 2s ease-in-out infinite">
            <svg class="w-10 h-10 text-[var(--accent)]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10" class="check-circle" style="stroke: var(--accent)"/>
                <path d="M8 12l2.5 2.5L16 9" class="icon-draw" style="stroke: var(--accent)"/>
            </svg>
        </div>
    </div>

    <div class="icon-fade" style="animation-delay: 0.1s">
        <h1 class="text-xl font-bold text-neutral-100 mb-2">Vote Recorded</h1>
        <p class="text-sm text-neutral-500 mb-6">Your vote has been successfully cast and securely stored.</p>
    </div>

    <!-- Vote Receipt -->
    <div class="card p-5 text-left icon-fade" style="animation-delay: 0.15s">
        <h2 class="text-[11px] text-[var(--accent)] uppercase tracking-wider font-semibold mb-3">Vote Receipt</h2>
        <div class="space-y-2.5">
            <div class="flex items-center justify-between">
                <span class="text-xs text-neutral-500">Election</span>
                <span class="text-xs text-neutral-200 font-medium"><?= sanitize($vote_info['election_name']) ?></span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-xs text-neutral-500">Candidate</span>
                <span class="text-xs text-neutral-200 font-medium"><?= sanitize($vote_info['candidate_name']) ?></span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-xs text-neutral-500">Date</span>
                <span class="text-xs text-neutral-200 font-medium"><?= date('M d, Y g:i A', strtotime($vote_info['timestamp'])) ?></span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-xs text-neutral-500">Receipt ID</span>
                <span class="text-xs text-[var(--accent)] font-mono"><?= strtoupper(substr(md5($vote_info['election_id'] . $vote_info['candidate_name'] . $vote_info['timestamp']), 0, 8)) ?></span>
            </div>
        </div>
    </div>

    <!-- Actions -->
    <div class="flex gap-3 mt-6 icon-fade" style="animation-delay: 0.2s">
        <a href="<?= $base_url ?>/partials/results.php" class="flex-1 bg-white/[0.06] hover:bg-white/[0.1] text-neutral-300 text-xs font-medium px-4 py-3 rounded-lg transition text-center border border-neutral-800/50">
            View Results
        </a>
        <a href="<?= $base_url ?>/partials/dashboard.php" class="btn-accent flex-1 text-xs py-3 rounded-lg text-center">
            Dashboard
        </a>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
