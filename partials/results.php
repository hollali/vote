<?php
$page_title = 'Results - Voting System';
require_once __DIR__ . '/../includes/header.php';
require_login();

$sort = $_GET['sort'] ?? 'vote_count';
$order = $_GET['order'] ?? 'DESC';
$search = $_GET['search'] ?? '';

$active = get_active_election($con);

$allowed_sorts = ['vote_count', 'username', 'created_at'];
if (!in_array($sort, $allowed_sorts)) $sort = 'vote_count';
$order = strtoupper($order) === 'ASC' ? 'ASC' : 'DESC';

$sort_col = $sort === 'vote_count' ? 'vote_count' : "u.$sort";

$sql = "SELECT u.username, u.photo, u.bio, u.id, u.created_at,
        COALESCE(ev.vote_count, 0) AS votes
        FROM userdata u
        LEFT JOIN (
            SELECT candidate_id, COUNT(*) AS vote_count
            FROM election_votes
            WHERE election_id = ?
            GROUP BY candidate_id
        ) ev ON u.id = ev.candidate_id
        WHERE u.standard = 'group'";

$params = [$active ? $active['id'] : 0];
$types = 'i';

if ($search) {
    $sql .= " AND u.username LIKE ?";
    $params[] = "%$search%";
    $types .= 's';
}

$sql .= " ORDER BY $sort_col $order";

$stmt = $con->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$groups = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$total_votes = 0;
foreach ($groups as $g) $total_votes += $g['votes'];
$max_votes = !empty($groups) ? max(array_column($groups, 'votes')) : 0;

$has_voted = false;
if ($active) {
    $vc = $con->prepare("SELECT id FROM election_votes WHERE election_id = ? AND user_id = ?");
    $vc->bind_param("ii", $active['id'], $_SESSION['id']);
    $vc->execute();
    $has_voted = $vc->get_result()->num_rows > 0;
    $vc->close();
}
?>

<div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-8">
    <div class="icon-fade">
        <h1 class="text-lg font-semibold text-neutral-200">Results</h1>
        <p class="text-neutral-500 text-xs mt-1">
            <?php if ($active): ?>
                <?= sanitize($active['name']) ?> &middot;
            <?php endif; ?>
            <?= count($groups) ?> candidate<?= count($groups) !== 1 ? 's' : '' ?> &middot; <?= $total_votes ?> total vote<?= $total_votes !== 1 ? 's' : '' ?>
        </p>
    </div>

    <form method="GET" class="flex flex-wrap items-center gap-2 icon-fade" style="animation-delay: 0.05s">
        <input type="text" name="search" value="<?= sanitize($search) ?>" placeholder="Search..."
            class="input-field w-36 text-xs py-2">
        <select name="sort" class="input-field text-xs py-2 appearance-none cursor-pointer w-auto pr-8">
            <option value="vote_count" <?= $sort === 'vote_count' ? 'selected' : '' ?>>Votes</option>
            <option value="username" <?= $sort === 'username' ? 'selected' : '' ?>>Name</option>
        </select>
        <select name="order" class="input-field text-xs py-2 appearance-none cursor-pointer w-auto pr-8">
            <option value="DESC" <?= $order === 'DESC' ? 'selected' : '' ?>>Desc</option>
            <option value="ASC" <?= $order === 'ASC' ? 'selected' : '' ?>>Asc</option>
        </select>
        <button type="submit" class="bg-white/[0.08] hover:bg-white/[0.14] text-neutral-300 text-xs px-3 py-2 rounded-md transition font-medium cursor-pointer">Filter</button>
    </form>
</div>

<!-- Winner Highlight -->
<?php if ($max_votes > 0 && $total_votes > 0 && $active): ?>
    <?php
    $winner = null;
    foreach ($groups as $g) {
        if ($g['votes'] == $max_votes) { $winner = $g; break; }
    }
    if ($winner):
    ?>
    <div class="card p-5 mb-6 icon-fade" style="animation-delay: 0.08s">
        <div class="flex items-center gap-2 mb-3">
            <svg class="w-4 h-4 text-neutral-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
            <h2 class="text-xs font-semibold text-neutral-400 uppercase tracking-wider">Leading Candidate</h2>
        </div>
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-xl bg-neutral-800 overflow-hidden ring-2 ring-neutral-600/50 flex-shrink-0">
                <img src="<?= $base_url ?>/uploads/<?= htmlspecialchars($winner['photo']) ?>" alt="" class="w-full h-full object-cover">
            </div>
            <div class="flex-1 min-w-0">
                <h3 class="text-base font-bold text-neutral-100"><?= sanitize($winner['username']) ?></h3>
                <div class="flex items-center gap-3 mt-1">
                    <span class="text-sm font-bold text-neutral-200"><?= intval($winner['votes']) ?> vote<?= intval($winner['votes']) !== 1 ? 's' : '' ?></span>
                    <span class="text-[11px] text-neutral-500"><?= $total_votes > 0 ? round(($winner['votes'] / $total_votes) * 100) : 0 ?>% of total</span>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
<?php endif; ?>

<!-- Bar Chart Visualization -->
<?php if ($total_votes > 0 && !empty($groups)): ?>
<div class="card p-5 mb-6 icon-fade" style="animation-delay: 0.12s">
    <h2 class="text-xs font-semibold text-neutral-400 uppercase tracking-wider mb-4 flex items-center gap-1.5">
        <svg class="w-3.5 h-3.5 text-neutral-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
        Vote Distribution
    </h2>
    <div class="space-y-3">
        <?php foreach ($groups as $i => $g): ?>
            <?php $pct = $total_votes > 0 ? round(($g['votes'] / $total_votes) * 100) : 0; ?>
            <div class="group">
                <div class="flex items-center justify-between mb-1.5">
                    <div class="flex items-center gap-2 min-w-0">
                        <span class="text-[10px] font-bold <?= $g['votes'] === $max_votes && $max_votes > 0 ? 'text-neutral-200' : 'text-neutral-600' ?> bg-white/[0.04] px-1.5 py-0.5 rounded flex-shrink-0">#<?= $i + 1 ?></span>
                        <span class="text-xs text-neutral-300 font-medium truncate"><?= sanitize($g['username']) ?></span>
                    </div>
                    <div class="flex items-center gap-2 flex-shrink-0">
                        <span class="text-xs font-bold text-neutral-200"><?= intval($g['votes']) ?></span>
                        <span class="text-[11px] text-neutral-600 w-8 text-right"><?= $pct ?>%</span>
                    </div>
                </div>
                <div class="w-full bg-neutral-800/50 rounded-full h-2 overflow-hidden">
                    <div class="h-full rounded-full progress-bar <?= $g['votes'] === $max_votes && $max_votes > 0 ? 'bg-neutral-300' : 'bg-neutral-600' ?>"
                        style="width: <?= $pct ?>%"></div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- Detailed Results -->
<div class="space-y-3 stagger">
    <?php foreach ($groups as $i => $g): ?>
        <?php $pct = $total_votes > 0 ? round(($g['votes'] / $total_votes) * 100) : 0; ?>
        <?php $is_winner = $g['votes'] == $max_votes && $max_votes > 0; ?>
        <div class="card p-4 <?= $is_winner ? 'border-neutral-700' : '' ?>">
            <div class="flex items-center gap-3 mb-2.5">
                <div class="w-10 h-10 rounded-lg bg-neutral-800 flex-shrink-0 overflow-hidden ring-1 <?= $is_winner ? 'ring-neutral-500' : 'ring-neutral-700' ?>">
                    <img src="<?= $base_url ?>/uploads/<?= htmlspecialchars($g['photo']) ?>" class="w-full h-full object-cover" alt="">
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2">
                        <span class="text-[11px] font-bold <?= $is_winner ? 'text-neutral-200' : 'text-neutral-600' ?>">#<?= $i + 1 ?></span>
                        <h3 class="text-sm text-neutral-200 font-medium"><?= sanitize($g['username']) ?></h3>
                        <?php if ($is_winner && $total_votes > 0): ?>
                            <span class="text-[10px] bg-white/[0.08] text-neutral-300 px-1.5 py-0.5 rounded font-medium">LEADING</span>
                        <?php endif; ?>
                    </div>
                    <?php if ($g['bio']): ?>
                        <p class="text-[11px] text-neutral-500 mt-0.5 truncate"><?= sanitize($g['bio']) ?></p>
                    <?php endif; ?>
                </div>
                <div class="text-right flex-shrink-0">
                    <span class="text-lg font-bold text-neutral-200"><?= intval($g['votes']) ?></span>
                    <span class="text-[11px] text-neutral-500 block"><?= $pct ?>%</span>
                </div>
            </div>
            <div class="w-full bg-neutral-800/50 rounded-full h-1.5 overflow-hidden">
                <div class="h-full rounded-full progress-bar <?= $is_winner ? 'bg-neutral-300' : 'bg-neutral-600' ?>"
                    style="width: <?= $pct ?>%"></div>
            </div>
        </div>
    <?php endforeach; ?>

    <?php if (empty($groups)): ?>
        <div class="card p-10 text-center icon-fade">
            <svg class="w-10 h-10 text-neutral-700 mx-auto mb-2 icon-float" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            <p class="text-neutral-500 text-xs">No candidates found</p>
        </div>
    <?php endif; ?>
</div>

<?php if (!$has_voted && $active): ?>
<div class="mt-6 text-center icon-fade" style="animation-delay: 0.3s">
    <a href="<?= $base_url ?>/partials/booth.php" class="inline-flex items-center gap-2 bg-white/[0.08] hover:bg-white/[0.14] text-neutral-200 text-xs font-semibold px-5 py-2.5 rounded-lg transition">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5l7 7-7 7"/></svg>
        Go to Voting Booth
    </a>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
