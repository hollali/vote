<?php
$page_title = 'Dashboard - Voting System';
require_once __DIR__ . '/../includes/header.php';
require_login();

$data = $_SESSION['data'];

$has_voted = false;
if ($active_election) {
    $check = $con->prepare("SELECT id FROM election_votes WHERE election_id = ? AND user_id = ?");
    $check->bind_param("ii", $active_election['id'], $_SESSION['id']);
    $check->execute();
    $has_voted = $check->get_result()->num_rows > 0;
    $check->close();
}
$statusText = $has_voted ? 'Voted' : 'Not Voted';

$stmt = $con->prepare("SELECT username, photo, votes, id FROM userdata WHERE standard = 'group'");
$stmt->execute();
$groups = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$_SESSION['groups'] = $groups;
$stmt->close();
?>

<!-- Welcome Banner -->
<div class="card p-5 mb-8 icon-fade">
    <div class="flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl bg-neutral-800 overflow-hidden flex-shrink-0 ring-1 ring-neutral-700">
            <img src="<?= $base_url ?>/uploads/<?= htmlspecialchars($data['photo']) ?>" alt="Profile" class="w-full h-full object-cover">
        </div>
        <div>
            <h1 class="text-base font-semibold text-neutral-200">Welcome, <?= sanitize($data['username']) ?></h1>
            <p class="text-neutral-500 text-xs mt-0.5">
                Voter ID: <?= sanitize($data['idNum']) ?>
                &middot;
                <span class="<?= $has_voted ? 'text-neutral-300' : 'text-neutral-500' ?> font-medium"><?= $statusText ?></span>
            </p>
        </div>
    </div>
</div>

<?php if ($active_election): ?>
<div class="card p-3.5 mb-6 flex items-center gap-3 icon-fade" style="animation-delay: 0.05s">
    <span class="relative flex h-1.5 w-1.5 flex-shrink-0">
        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-neutral-400 opacity-75"></span>
        <span class="relative inline-flex rounded-full h-1.5 w-1.5 bg-neutral-500"></span>
    </span>
    <div>
        <p class="text-xs font-medium text-neutral-300"><?= sanitize($active_election['name']) ?></p>
        <p class="text-[11px] text-neutral-600">Ends <?= date('M d, Y g:i A', strtotime($active_election['end_time'])) ?></p>
    </div>
</div>
<?php endif; ?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Candidates -->
    <div class="lg:col-span-2 space-y-3">
        <div class="flex items-center justify-between mb-1">
            <h2 class="text-sm font-semibold text-neutral-300">Candidates</h2>
            <?php if ($has_voted): ?>
                <span class="inline-flex items-center gap-1 text-[11px] font-medium text-neutral-400 bg-white/[0.04] px-2.5 py-1 rounded-full border border-neutral-800">
                    <svg class="w-3 h-3 icon-draw" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Voted
                </span>
            <?php endif; ?>
        </div>

        <?php if (!empty($groups)): ?>
            <div class="stagger">
            <?php foreach ($groups as $g): ?>
                <div class="card p-4">
                    <div class="flex items-center gap-3">
                        <div class="w-11 h-11 rounded-lg bg-neutral-800 flex-shrink-0 overflow-hidden ring-1 ring-neutral-700">
                            <img src="<?= $base_url ?>/uploads/<?= htmlspecialchars($g['photo']) ?>" alt="" class="w-full h-full object-cover">
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="text-sm text-neutral-200 font-medium"><?= sanitize($g['username']) ?></h3>
                            <div class="flex items-center gap-1 mt-0.5">
                                <svg class="w-3 h-3 text-neutral-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                                <span class="text-[11px] text-neutral-500"><span class="font-semibold text-neutral-300"><?= intval($g['votes']) ?></span> votes</span>
                            </div>
                        </div>
                        <div class="flex-shrink-0">
                            <?php if ($has_voted): ?>
                                <span class="inline-flex items-center gap-1 text-[11px] font-medium text-neutral-400 bg-white/[0.04] px-3 py-1.5 rounded-md border border-neutral-800">
                                    <svg class="w-3 h-3 icon-draw" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    Voted
                                </span>
                            <?php else: ?>
                                <form action="../actions/voting.php" method="POST" data-loading>
                                    <input type="hidden" name="groupid" value="<?= $g['id'] ?>">
                                    <?php echo csrf_field(); ?>
                                    <button type="submit" class="inline-flex items-center gap-1 text-xs font-medium text-neutral-200 bg-white/[0.08] hover:bg-white/[0.14] px-3 py-1.5 rounded-md transition cursor-pointer active:scale-[0.97]">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        Vote
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="card p-10 text-center icon-fade">
                <svg class="w-10 h-10 text-neutral-700 mx-auto mb-2 icon-float" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                <p class="text-neutral-500 text-xs">No candidates available</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Profile Sidebar -->
    <div class="lg:col-span-1">
        <div class="card p-5 sticky top-20 icon-fade" style="animation-delay: 0.1s">
            <h2 class="text-sm font-semibold text-neutral-300 mb-4">Your Profile</h2>
            <div class="flex flex-col items-center text-center">
                <div class="w-20 h-20 rounded-xl bg-neutral-800 overflow-hidden mb-3 ring-2 ring-neutral-700/50">
                    <img src="<?= $base_url ?>/uploads/<?= htmlspecialchars($data['photo']) ?>" class="w-full h-full object-cover" alt="">
                </div>
                <h3 class="text-neutral-200 font-medium text-sm"><?= sanitize($data['username']) ?></h3>
                <div class="w-full mt-4 space-y-2">
                    <div class="flex items-center justify-between bg-white/[0.02] rounded-md px-3 py-2">
                        <span class="text-neutral-500 text-xs">Voter ID</span>
                        <span class="text-neutral-300 text-xs font-medium"><?= sanitize($data['idNum']) ?></span>
                    </div>
                    <div class="flex items-center justify-between bg-white/[0.02] rounded-md px-3 py-2">
                        <span class="text-neutral-500 text-xs">Status</span>
                        <span class="text-xs font-medium <?= $has_voted ? 'text-neutral-300' : 'text-neutral-500' ?>"><?= $statusText ?></span>
                    </div>
                </div>
                <a href="<?= $base_url ?>/partials/profile.php" class="w-full mt-3 block text-center text-xs text-neutral-500 hover:text-neutral-300 font-medium transition bg-white/[0.03] hover:bg-white/[0.06] rounded-md py-2 border border-neutral-800/50">
                    Edit Profile
                </a>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
