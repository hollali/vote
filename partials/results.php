<?php
$page_title = 'Results - Voting System';
require_once __DIR__ . '/../includes/header.php';
require_login();

$sort = $_GET['sort'] ?? 'votes';
$order = $_GET['order'] ?? 'DESC';
$search = $_GET['search'] ?? '';

$allowed_sorts = ['votes', 'username', 'created_at'];
if (!in_array($sort, $allowed_sorts)) $sort = 'votes';
$order = strtoupper($order) === 'ASC' ? 'ASC' : 'DESC';

$sql = "SELECT username, photo, votes, id, created_at FROM userdata WHERE standard = 'group'";
$params = [];
$types = '';

if ($search) {
    $sql .= " AND username LIKE ?";
    $params[] = "%$search%";
    $types .= 's';
}

$sql .= " ORDER BY $sort $order";

$stmt = $con->prepare($sql);
if ($types) $stmt->bind_param($types, ...$params);
$stmt->execute();
$groups = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$total_votes = 0;
foreach ($groups as $g) $total_votes += $g['votes'];
$max_votes = max(array_column($groups, 'votes') ?: [0]);
?>

<div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-8">
    <div class="icon-fade">
        <h1 class="text-lg font-semibold text-neutral-200">Results</h1>
        <p class="text-neutral-500 text-xs mt-1"><?= count($groups) ?> candidate<?= count($groups) !== 1 ? 's' : '' ?> &middot; <?= $total_votes ?> total vote<?= $total_votes !== 1 ? 's' : '' ?></p>
    </div>

    <form method="GET" class="flex flex-wrap items-center gap-2 icon-fade" style="animation-delay: 0.05s">
        <input type="text" name="search" value="<?= sanitize($search) ?>" placeholder="Search..."
            class="input-field w-36 text-xs py-2">
        <select name="sort" class="input-field text-xs py-2 appearance-none cursor-pointer w-auto pr-8">
            <option value="votes" <?= $sort === 'votes' ? 'selected' : '' ?>>Votes</option>
            <option value="username" <?= $sort === 'username' ? 'selected' : '' ?>>Name</option>
        </select>
        <select name="order" class="input-field text-xs py-2 appearance-none cursor-pointer w-auto pr-8">
            <option value="DESC" <?= $order === 'DESC' ? 'selected' : '' ?>>Desc</option>
            <option value="ASC" <?= $order === 'ASC' ? 'selected' : '' ?>>Asc</option>
        </select>
        <button type="submit" class="bg-white/[0.08] hover:bg-white/[0.14] text-neutral-300 text-xs px-3 py-2 rounded-md transition font-medium cursor-pointer">Filter</button>
    </form>
</div>

<div class="space-y-3 stagger">
    <?php foreach ($groups as $i => $g): ?>
        <?php $pct = $total_votes > 0 ? round(($g['votes'] / $total_votes) * 100) : 0; ?>
        <div class="card p-4">
            <div class="flex items-center gap-3 mb-2.5">
                <div class="w-10 h-10 rounded-lg bg-neutral-800 flex-shrink-0 overflow-hidden ring-1 ring-neutral-700">
                    <img src="<?= $base_url ?>/uploads/<?= htmlspecialchars($g['photo']) ?>" class="w-full h-full object-cover" alt="">
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2">
                        <span class="text-[11px] font-bold text-neutral-600">#<?= $i + 1 ?></span>
                        <h3 class="text-sm text-neutral-200 font-medium"><?= sanitize($g['username']) ?></h3>
                    </div>
                </div>
                <div class="text-right">
                    <span class="text-lg font-bold text-neutral-200"><?= $g['votes'] ?></span>
                    <span class="text-[11px] text-neutral-500 block"><?= $pct ?>%</span>
                </div>
            </div>
            <div class="w-full bg-neutral-800/50 rounded-full h-1.5 overflow-hidden">
                <div class="h-full rounded-full progress-bar <?= $g['votes'] === $max_votes && $max_votes > 0 ? 'bg-neutral-300' : 'bg-neutral-600' ?>"
                    style="width: <?= $total_votes > 0 ? ($g['votes'] / $total_votes) * 100 : 0 ?>%"></div>
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

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
