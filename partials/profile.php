<?php
$page_title = 'Edit Profile - Voting System';
require_once __DIR__ . '/../includes/header.php';
require_login();

$data = $_SESSION['data'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token()) {
        set_flash('error', 'Invalid request');
        header('Location: ../partials/profile.php');
        exit;
    }

    $username = trim($_POST['username']);
    $email = trim($_POST['email'] ?? '');

    if (strlen($username) < 3 || strlen($username) > 50) {
        set_flash('error', 'Username must be 3-50 characters');
        header('Location: ../partials/profile.php');
        exit;
    }

    if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        set_flash('error', 'Invalid email address');
        header('Location: ../partials/profile.php');
        exit;
    }

    $stmt = $con->prepare("UPDATE userdata SET username = ?, email = ? WHERE id = ?");
    $stmt->bind_param("ssi", $username, $email, $_SESSION['id']);
    $stmt->execute();
    $stmt->close();

    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $validation = validate_image_upload($_FILES['photo']);
        if ($validation['valid']) {
            $old_photo = $_SESSION['data']['photo'];
            if ($old_photo !== 'default.png') {
                unlink("../uploads/$old_photo");
            }
            move_uploaded_file($validation['tmp'], "../uploads/{$validation['name']}");
            $stmt2 = $con->prepare("UPDATE userdata SET photo = ? WHERE id = ?");
            $stmt2->bind_param("si", $validation['name'], $_SESSION['id']);
            $stmt2->execute();
            $stmt2->close();
            $_SESSION['data']['photo'] = $validation['name'];
        } else {
            set_flash('error', $validation['error']);
            header('Location: ../partials/profile.php');
            exit;
        }
    }

    if (!empty($_POST['new_password'])) {
        if (empty($_POST['current_password'])) {
            set_flash('error', 'Current password is required to change password');
            header('Location: ../partials/profile.php');
            exit;
        }

        $check_pw = $con->prepare("SELECT password FROM userdata WHERE id = ?");
        $check_pw->bind_param("i", $_SESSION['id']);
        $check_pw->execute();
        $pw_row = $check_pw->get_result()->fetch_assoc();
        $check_pw->close();

        if (!password_verify($_POST['current_password'], $pw_row['password'])) {
            set_flash('error', 'Current password is incorrect');
            header('Location: ../partials/profile.php');
            exit;
        }

        if (strlen($_POST['new_password']) < 8) {
            set_flash('error', 'New password must be at least 8 characters');
            header('Location: ../partials/profile.php');
            exit;
        }
        if ($_POST['new_password'] !== $_POST['confirm_password']) {
            set_flash('error', 'Passwords do not match');
            header('Location: ../partials/profile.php');
            exit;
        }
        $hashed = password_hash($_POST['new_password'], PASSWORD_BCRYPT);
        $stmt3 = $con->prepare("UPDATE userdata SET password = ? WHERE id = ?");
        $stmt3->bind_param("si", $hashed, $_SESSION['id']);
        $stmt3->execute();
        $stmt3->close();
    }

    $_SESSION['data']['username'] = $username;
    $_SESSION['data']['email'] = $email;

    log_audit($con, $_SESSION['id'], 'profile_update', 'Profile updated');
    set_flash('success', 'Profile updated successfully');
    header('Location: ../partials/profile.php');
    exit;
}

$stmt = $con->prepare("SELECT * FROM userdata WHERE id = ?");
$stmt->bind_param("i", $_SESSION['id']);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();
unset($data['password']);
$_SESSION['data'] = $data;
$stmt->close();
?>

<div class="max-w-lg mx-auto">
    <h1 class="text-lg font-semibold text-neutral-200 mb-5 icon-fade">Edit Profile</h1>

    <div class="card p-6 scale-in">
        <form action="../partials/profile.php" method="POST" enctype="multipart/form-data" class="space-y-4" data-loading>
            <div class="flex items-center gap-4">
                <div class="w-16 h-16 rounded-xl bg-neutral-800 overflow-hidden ring-2 ring-neutral-700/50 flex-shrink-0">
                    <img src="<?= $base_url ?>/uploads/<?= htmlspecialchars($data['photo']) ?>" class="w-full h-full object-cover" alt="">
                </div>
                <div>
                    <label for="photo" class="text-xs text-neutral-400 hover:text-neutral-200 cursor-pointer font-medium transition">
                        <svg class="w-3.5 h-3.5 inline mr-1 icon-hover-scale" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        Change photo
                    </label>
                    <input type="file" name="photo" id="photo" class="hidden" accept="image/*">
                    <p class="text-[11px] text-neutral-600 mt-0.5">JPG, PNG, GIF, WebP (max 2MB)</p>
                </div>
            </div>

            <div>
                <label for="username" class="block text-xs font-medium text-neutral-400 mb-1.5">Username</label>
                <input type="text" name="username" id="username" value="<?= sanitize($data['username']) ?>" required
                    class="input-field">
            </div>

            <div>
                <label for="email" class="block text-xs font-medium text-neutral-400 mb-1.5">Email</label>
                <input type="email" name="email" id="email" value="<?= sanitize($data['email'] ?? '') ?>"
                    class="input-field" placeholder="your@email.com">
            </div>

            <hr class="border-neutral-800/50">

            <div>
                <label for="current_password" class="block text-xs font-medium text-neutral-400 mb-1.5">Current Password</label>
                <input type="password" name="current_password" id="current_password"
                    class="input-field" placeholder="Required to change password">
            </div>

            <p class="text-xs text-neutral-500">Leave blank to keep current password</p>

            <div>
                <label for="new_password" class="block text-xs font-medium text-neutral-400 mb-1.5">New Password</label>
                <input type="password" name="new_password" id="new_password"
                    class="input-field" placeholder="Min 8 characters">
            </div>

            <div>
                <label for="confirm_password" class="block text-xs font-medium text-neutral-400 mb-1.5">Confirm New Password</label>
                <input type="password" name="confirm_password" id="confirm_password"
                    class="input-field" placeholder="Confirm password">
            </div>

            <?php echo csrf_field(); ?>
            <button type="submit" class="w-full bg-white/[0.08] hover:bg-white/[0.14] text-neutral-200 font-medium py-2.5 rounded-lg transition text-sm cursor-pointer active:scale-[0.98] mt-1">
                Save Changes
            </button>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
