<?php
$page_title = 'Voting Booth - Voting System';
require_once __DIR__ . '/../includes/header.php';
require_login();

if (!$active_election) {
    set_flash('warning', 'No active election at this time');
    header('Location: ../partials/dashboard.php');
    exit;
}

$uid = $_SESSION['id'];

$check = $con->prepare("SELECT id FROM election_votes WHERE election_id = ? AND user_id = ?");
$check->bind_param("ii", $active_election['id'], $uid);
$check->execute();
$has_voted = $check->get_result()->num_rows > 0;
$check->close();

if ($has_voted) {
    set_flash('info', 'You have already voted in this election');
    header('Location: ../partials/dashboard.php');
    exit;
}

$stmt = $con->prepare("SELECT id, username, photo, bio, votes FROM userdata WHERE standard = 'group' ORDER BY username ASC");
$stmt->execute();
$candidates = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<div class="max-w-2xl mx-auto">
    <!-- Election Header -->
    <div class="text-center mb-8 icon-fade">
        <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-white/[0.06] mb-4">
            <svg class="w-7 h-7 text-neutral-400 icon-float" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
            </svg>
        </div>
        <h1 class="text-lg font-semibold text-neutral-200">Voting Booth</h1>
        <p class="text-neutral-500 text-xs mt-1">Select your candidate for this election</p>
    </div>

    <!-- Active Election Banner -->
    <div class="card p-4 mb-6 icon-fade" style="animation-delay: 0.05s">
        <div class="flex items-center gap-3">
            <span class="relative flex h-2 w-2 flex-shrink-0">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-neutral-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-2 w-2 bg-neutral-500"></span>
            </span>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-neutral-200"><?= sanitize($active_election['name']) ?></p>
                <?php if ($active_election['description']): ?>
                    <p class="text-[11px] text-neutral-500 mt-0.5"><?= sanitize($active_election['description']) ?></p>
                <?php endif; ?>
            </div>
            <div class="text-right flex-shrink-0">
                <p class="text-[11px] text-neutral-500">Ends</p>
                <p class="text-xs text-neutral-300 font-medium"><?= date('M d, g:i A', strtotime($active_election['end_time'])) ?></p>
            </div>
        </div>
    </div>

    <!-- Instruction -->
    <div class="flex items-center gap-2 mb-4 px-1">
        <svg class="w-4 h-4 text-neutral-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <p class="text-[11px] text-neutral-500">Click on a candidate to review your choice, then confirm to cast your vote. This action cannot be undone.</p>
    </div>

    <!-- Candidate Cards -->
    <div class="space-y-3 stagger">
        <?php foreach ($candidates as $i => $c): ?>
            <div class="candidate-card card p-5 cursor-pointer hover:border-neutral-600 group"
                 onclick="selectCandidate(<?= $c['id'] ?>, '<?= sanitize(addslashes($c['username'])) ?>', '<?= $c['photo'] ?>')"
                 data-id="<?= $c['id'] ?>">
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 rounded-xl bg-neutral-800 overflow-hidden ring-2 ring-neutral-700/50 group-hover:ring-neutral-600/50 flex-shrink-0 transition">
                        <img src="<?= $base_url ?>/uploads/<?= htmlspecialchars($c['photo']) ?>" alt="" class="w-full h-full object-cover">
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2">
                            <span class="text-[10px] font-bold text-neutral-600 bg-white/[0.04] px-1.5 py-0.5 rounded">#<?= $i + 1 ?></span>
                            <h3 class="text-sm text-neutral-200 font-semibold"><?= sanitize($c['username']) ?></h3>
                        </div>
                        <?php if ($c['bio']): ?>
                            <p class="text-xs text-neutral-500 mt-1 line-clamp-2"><?= sanitize($c['bio']) ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 rounded-full bg-white/[0.04] border border-neutral-700 group-hover:border-neutral-500 group-hover:bg-white/[0.08] flex items-center justify-center transition">
                            <svg class="w-4 h-4 text-neutral-500 group-hover:text-neutral-300 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5l7 7-7 7"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <?php if (empty($candidates)): ?>
        <div class="card p-10 text-center icon-fade">
            <svg class="w-10 h-10 text-neutral-700 mx-auto mb-2 icon-float" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
            <p class="text-neutral-500 text-xs">No candidates available</p>
        </div>
    <?php endif; ?>

    <div class="mt-6 text-center">
        <a href="<?= $base_url ?>/partials/dashboard.php" class="text-xs text-neutral-500 hover:text-neutral-300 transition">Back to Dashboard</a>
    </div>
</div>

<!-- Confirmation Modal -->
<div id="confirmModal" class="fixed inset-0 bg-black/80 flex items-center justify-center z-50 hidden">
    <div class="card p-6 w-full max-w-sm mx-4 scale-in">
        <div class="text-center">
            <div class="w-16 h-16 rounded-2xl bg-neutral-800 overflow-hidden mx-auto mb-4 ring-2 ring-neutral-700/50">
                <img id="confirm_photo" src="" alt="" class="w-full h-full object-cover">
            </div>
            <h3 class="text-base font-semibold text-neutral-200 mb-1">Confirm Your Vote</h3>
            <p class="text-xs text-neutral-500 mb-1">You are about to vote for:</p>
            <p class="text-lg font-bold text-neutral-100 mb-4" id="confirm_name"></p>
            <p class="text-[11px] text-neutral-600 mb-5">
                <svg class="w-3 h-3 inline text-yellow-500/70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                This action cannot be undone.
            </p>
            <div class="flex gap-2">
                <button onclick="closeConfirm()" class="flex-1 bg-neutral-800 hover:bg-neutral-700 text-neutral-400 text-xs font-medium px-4 py-2.5 rounded-lg transition cursor-pointer">Cancel</button>
                <form id="confirmForm" action="../actions/voting.php" method="POST" class="flex-1" data-loading>
                    <input type="hidden" name="groupid" id="confirm_id">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="w-full bg-white/[0.1] hover:bg-white/[0.18] text-neutral-200 text-xs font-semibold px-4 py-2.5 rounded-lg transition cursor-pointer active:scale-[0.97]">
                        Confirm Vote
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function selectCandidate(id, name, photo) {
    document.getElementById('confirm_id').value = id;
    document.getElementById('confirm_name').textContent = name;
    document.getElementById('confirm_photo').src = '<?= $base_url ?>/uploads/' + photo;
    document.getElementById('confirmModal').classList.remove('hidden');
}

function closeConfirm() {
    document.getElementById('confirmModal').classList.add('hidden');
}

document.getElementById('confirmModal').addEventListener('click', function(e) {
    if (e.target === this) closeConfirm();
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
