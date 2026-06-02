<?php
require_once __DIR__ . '/../includes/functions.php';
if (!isLoggedIn()) {
    redirect('/login.php');
}
$stmt = $pdo->prepare('SELECT id, name, email, phone, password FROM users WHERE id = ?');
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();
$success = null;
$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $phone = preg_replace('/\D/', '', $_POST['phone'] ?? '');
    $current = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    if (!$name || !$phone) {
        $error = 'Name and phone are required.';
    } else {
        if ($newPassword) {
            if (!password_verify($current, $user['password'] ?? '')) {
                $error = 'Current password is incorrect.';
            } elseif ($newPassword !== $confirmPassword) {
                $error = 'New passwords do not match.';
            }
        }
        if (!$error) {
            $params = [$name, $phone, $_SESSION['user_id']];
            $sql = 'UPDATE users SET name = ?, phone = ? WHERE id = ?';
            if ($newPassword) {
                $hash = password_hash($newPassword, PASSWORD_DEFAULT);
                $sql = 'UPDATE users SET name = ?, phone = ?, password = ? WHERE id = ?';
                $params = [$name, $phone, $hash, $_SESSION['user_id']];
            }
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $success = 'Profile updated successfully.';
        }
    }
}
?>
<?php include __DIR__ . '/../includes/header.php'; ?>
<div class="container mt-5"><div class="row justify-content-center"><div class="col-lg-8">
    <div class="card shadow-sm p-4">
        <h3>My Profile</h3>
        <?php if ($success): ?><div class="alert alert-success"><?= sanitize($success) ?></div><?php endif; ?>
        <?php if ($error): ?><div class="alert alert-danger"><?= sanitize($error) ?></div><?php endif; ?>
        <form method="post">
            <div class="row g-3">
                <div class="col-md-6"><label class="form-label">Name</label><input name="name" class="form-control" value="<?= sanitize($user['name']) ?>" required></div>
                <div class="col-md-6"><label class="form-label">Phone</label><input name="phone" class="form-control" value="<?= sanitize($user['phone']) ?>" required pattern="\d{10}"></div>
                <div class="col-12 mt-3"><h5>Change Password</h5></div>
                <div class="col-md-6"><label class="form-label">Current Password</label><input type="password" name="current_password" class="form-control"></div>
                <div class="col-md-6"><label class="form-label">New Password</label><input type="password" name="new_password" class="form-control"></div>
                <div class="col-md-6"><label class="form-label">Confirm Password</label><input type="password" name="confirm_password" class="form-control"></div>
            </div>
            <button class="btn btn-primary mt-4">Save Changes</button>
        </form>
    </div>
</div></div></div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
