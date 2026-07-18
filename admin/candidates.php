<?php
$page_title = 'Candidates - Admin';
require_once __DIR__ . '/includes/admin_header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token()) {
        set_flash('error', 'Invalid request');
        header('Location: ../admin/candidates.php');
        exit;
    }

    $action = $_POST['action'] ?? '';

    if ($action === 'add_candidate') {
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
                $stmt = $con->prepare("INSERT INTO userdata (username, idNum, password, photo, standard, status, votes) VALUES (?, ?, ?, 'default.png', 'group', 0, 0)");
                $stmt->bind_param("sss", $username, $idNum, $password);
                $stmt->execute();
                $stmt->close();
                log_audit($con, $_SESSION['id'], 'candidate_add', $username);
                set_flash('success', 'Candidate added');
            }
            $check->close();
        }
    }

    if ($action === 'edit_candidate') {
        $cid = intval($_POST['candidate_id']);
        $username = trim($_POST['username']);
        $idNum = trim($_POST['idNum']);

        if (!$username || !$idNum) {
            set_flash('error', 'Name and ID are required');
        } elseif (strlen($idNum) !== 10) {
            set_flash('error', 'ID must be 10 digits');
        } else {
            $check = $con->prepare("SELECT id FROM userdata WHERE idNum = ? AND id != ?");
            $check->bind_param("si", $idNum, $cid);
            $check->execute();
            if ($check->get_result()->num_rows > 0) {
                set_flash('error', 'ID number already in use');
            } else {
                $photo = 'default.png';
                if (!empty($_FILES['photo']['name'])) {
                    $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
                    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                    if (in_array($ext, $allowed) && $_FILES['photo']['size'] <= 5 * 1024 * 1024) {
                        $photo = 'candidate_' . $cid . '.' . $ext;
                        $dest = __DIR__ . '/../uploads/' . $photo;
                        if (move_uploaded_file($_FILES['photo']['tmp_name'], $dest)) {
                            $get_old = $con->prepare("SELECT photo FROM userdata WHERE id = ?");
                            $get_old->bind_param("i", $cid);
                            $get_old->execute();
                            $old = $get_old->get_result()->fetch_assoc();
                            $get_old->close();
                            if ($old && $old['photo'] !== 'default.png' && $old['photo'] !== $photo) {
                                @unlink(__DIR__ . '/../uploads/' . $old['photo']);
                            }
                        }
                    }
                }

                $bio = trim($_POST['bio'] ?? '');

                $stmt = $con->prepare("UPDATE userdata SET username = ?, idNum = ?, photo = ?, bio = ? WHERE id = ? AND standard = 'group'");
                $stmt->bind_param("ssssi", $username, $idNum, $photo, $bio, $cid);
                $stmt->execute();
                $stmt->close();
                log_audit($con, $_SESSION['id'], 'candidate_edit', "Candidate #$cid updated");
                set_flash('success', 'Candidate updated');
            }
            $check->close();
        }
    }

    if ($action === 'edit_candidate_password') {
        $cid = intval($_POST['candidate_id']);
        $pw = $_POST['password'];
        if (strlen($pw) < 8) {
            set_flash('error', 'Password must be at least 8 characters');
        } else {
            $hash = password_hash($pw, PASSWORD_BCRYPT);
            $stmt = $con->prepare("UPDATE userdata SET password = ? WHERE id = ? AND standard = 'group'");
            $stmt->bind_param("si", $hash, $cid);
            $stmt->execute();
            $stmt->close();
            log_audit($con, $_SESSION['id'], 'candidate_pw_change', "Candidate #$cid password changed");
            set_flash('success', 'Candidate password updated');
        }
    }

    if ($action === 'delete_candidate') {
        $cid = intval($_POST['candidate_id']);
        $stmt = $con->prepare("DELETE FROM userdata WHERE id = ? AND standard = 'group'");
        $stmt->bind_param("i", $cid);
        $stmt->execute();
        $stmt->close();
        log_audit($con, $_SESSION['id'], 'candidate_delete', "Candidate #$cid deleted");
        set_flash('success', 'Candidate deleted');
    }

    header('Location: ../admin/candidates.php');
    exit;
}

$active_eid = $active_election ? $active_election['id'] : 0;

$cand_stmt = $con->prepare("SELECT u.id, u.username, u.idNum, u.photo, u.bio, u.created_at, COALESCE(ev.votes, 0) as votes FROM userdata u LEFT JOIN (SELECT candidate_id, COUNT(*) as votes FROM election_votes WHERE election_id = ? GROUP BY candidate_id) ev ON u.id = ev.candidate_id WHERE u.standard = 'group' ORDER BY votes DESC");
$cand_stmt->bind_param("i", $active_eid);
$cand_stmt->execute();
$candidates = $cand_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$cand_stmt->close();
?>

<div class="flex items-center justify-between mb-8">
    <div class="icon-fade">
        <h1 class="text-lg font-semibold text-neutral-200">Candidates</h1>
        <p class="text-neutral-500 text-xs mt-1">Add, edit, and manage candidates</p>
    </div>
</div>

<!-- Add Candidate -->
<div class="card p-5 mb-8 icon-fade" style="animation-delay: 0.1s">
    <h2 class="text-sm font-semibold text-neutral-300 mb-3 flex items-center gap-1.5">
        <svg class="w-3.5 h-3.5 text-neutral-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
        Add Candidate
    </h2>
    <form action="../admin/candidates.php" method="POST" class="space-y-3">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <input type="text" name="username" required placeholder="Candidate name"
                class="input-field text-xs py-2.5">
            <input type="text" name="idNum" required maxlength="10" placeholder="10-digit ID"
                class="input-field text-xs py-2.5">
            <input type="password" name="password" required placeholder="Password (min 8 chars)"
                class="input-field text-xs py-2.5">
        </div>
        <input type="hidden" name="action" value="add_candidate">
        <?php echo csrf_field(); ?>
        <button type="submit" class="bg-white/[0.08] hover:bg-white/[0.14] text-neutral-300 text-xs font-medium px-4 py-2 rounded-md transition cursor-pointer">Add Candidate</button>
    </form>
</div>

<!-- Candidates List -->
<div class="card p-5 icon-fade" style="animation-delay: 0.15s">
    <h2 class="text-sm font-semibold text-neutral-300 mb-3 flex items-center gap-1.5">
        <svg class="w-3.5 h-3.5 text-neutral-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
        All Candidates (<?= count($candidates) ?>)
    </h2>
    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead>
                <tr class="text-[11px] text-neutral-500 border-b border-neutral-800/50">
                    <th class="pb-2 font-medium">Name</th>
                    <th class="pb-2 font-medium">ID</th>
                    <th class="pb-2 font-medium">Votes</th>
                    <th class="pb-2 font-medium">Joined</th>
                    <th class="pb-2 font-medium text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($candidates as $c): ?>
                <tr class="border-b border-neutral-800/30">
                    <td class="py-3 text-xs text-neutral-200 font-medium max-w-[120px] truncate" title="<?= sanitize($c['bio'] ?? '') ?>"><?= sanitize($c['username']) ?></td>
                    <td class="py-3 text-xs text-neutral-500"><?= sanitize($c['idNum']) ?></td>
                    <td class="py-3 text-xs text-neutral-200 font-bold"><?= intval($c['votes']) ?></td>
                    <td class="py-3 text-[11px] text-neutral-600"><?= date('M d, Y', strtotime($c['created_at'])) ?></td>
                    <td class="py-3 text-right">
                        <div class="flex items-center justify-end gap-1">
                            <button onclick="openEditCandidate(<?= $c['id'] ?>, '<?= sanitize(addslashes($c['username'])) ?>', '<?= sanitize($c['idNum']) ?>', '<?= $c['photo'] ?>', '<?= sanitize(addslashes($c['bio'] ?? '')) ?>')" class="text-[11px] text-neutral-500 hover:text-neutral-300 transition cursor-pointer px-2 py-1 rounded hover:bg-white/[0.04]">Edit</button>
                            <button onclick="openEditCandidatePw(<?= $c['id'] ?>, '<?= sanitize(addslashes($c['username'])) ?>')" class="text-[11px] text-neutral-500 hover:text-neutral-300 transition cursor-pointer px-2 py-1 rounded hover:bg-white/[0.04]">Password</button>
                            <form method="POST" onsubmit="return confirm('Delete this candidate?')" class="inline">
                                <input type="hidden" name="action" value="delete_candidate">
                                <input type="hidden" name="candidate_id" value="<?= $c['id'] ?>">
                                <?php echo csrf_field(); ?>
                                <button class="text-[11px] text-neutral-600 hover:text-red-400 transition cursor-pointer px-2 py-1 rounded hover:bg-white/[0.04]">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($candidates)): ?>
                <tr>
                    <td colspan="5" class="py-6 text-center text-neutral-600 text-xs">No candidates yet. Add one above.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Edit Candidate Modal -->
<div id="editCandidateModal" class="fixed inset-0 bg-black/70 flex items-center justify-center z-50 hidden">
    <div class="card p-5 w-full max-w-sm mx-4">
        <h3 class="text-sm font-semibold text-neutral-200 mb-3">Edit Candidate</h3>
        <form action="../admin/candidates.php" method="POST" enctype="multipart/form-data" class="space-y-2">
            <input type="hidden" name="action" value="edit_candidate">
            <input type="hidden" name="candidate_id" id="ec_id">
            <input type="text" name="username" id="ec_name" required placeholder="Name"
                class="input-field text-xs py-2">
            <input type="text" name="idNum" id="ec_idNum" required maxlength="10" placeholder="10-digit ID"
                class="input-field text-xs py-2">
            <textarea name="bio" id="ec_bio" placeholder="Bio / description (optional)" rows="2"
                class="input-field text-xs py-2 resize-none"></textarea>
            <div>
                <label class="text-[11px] text-neutral-500">Photo (optional)</label>
                <input type="file" name="photo" accept="image/*"
                    class="input-field text-xs py-2 mt-1">
            </div>
            <div id="ec_current_photo" class="text-[11px] text-neutral-500"></div>
            <div class="flex gap-2">
                <?php echo csrf_field(); ?>
                <button type="submit" class="bg-white/[0.08] hover:bg-white/[0.14] text-neutral-300 text-xs font-medium px-3 py-2 rounded-md transition cursor-pointer">Save</button>
                <button type="button" onclick="closeModal('editCandidateModal')" class="bg-neutral-800 hover:bg-neutral-700 text-neutral-400 text-xs font-medium px-3 py-2 rounded-md transition cursor-pointer">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Candidate Password Modal -->
<div id="editCandidatePwModal" class="fixed inset-0 bg-black/70 flex items-center justify-center z-50 hidden">
    <div class="card p-5 w-full max-w-sm mx-4">
        <h3 class="text-sm font-semibold text-neutral-200 mb-1">Change Password</h3>
        <p class="text-[11px] text-neutral-500 mb-3">For: <span id="ecpw_name" class="text-neutral-400"></span></p>
        <form action="../admin/candidates.php" method="POST" class="space-y-2">
            <input type="hidden" name="action" value="edit_candidate_password">
            <input type="hidden" name="candidate_id" id="ecpw_id">
            <input type="password" name="password" required placeholder="New password (min 8 chars)"
                class="input-field text-xs py-2">
            <div class="flex gap-2">
                <?php echo csrf_field(); ?>
                <button type="submit" class="bg-white/[0.08] hover:bg-white/[0.14] text-neutral-300 text-xs font-medium px-3 py-2 rounded-md transition cursor-pointer">Update</button>
                <button type="button" onclick="closeModal('editCandidatePwModal')" class="bg-neutral-800 hover:bg-neutral-700 text-neutral-400 text-xs font-medium px-3 py-2 rounded-md transition cursor-pointer">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditCandidate(id, name, idNum, photo, bio) {
    document.getElementById('ec_id').value = id;
    document.getElementById('ec_name').value = name;
    document.getElementById('ec_idNum').value = idNum;
    document.getElementById('ec_bio').value = bio || '';
    var photoDiv = document.getElementById('ec_current_photo');
    photoDiv.innerHTML = (photo && photo !== 'default.png') ? 'Current: ' + photo : 'Current: default.png';
    document.getElementById('editCandidateModal').classList.remove('hidden');
}

function openEditCandidatePw(id, name) {
    document.getElementById('ecpw_id').value = id;
    document.getElementById('ecpw_name').textContent = name;
    document.getElementById('editCandidatePwModal').classList.remove('hidden');
}

function closeModal(id) {
    document.getElementById(id).classList.add('hidden');
}

document.querySelectorAll('.fixed').forEach(function(el) {
    el.addEventListener('click', function(e) {
        if (e.target === el) el.classList.add('hidden');
    });
});
</script>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
