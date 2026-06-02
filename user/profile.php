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
            if (!password_verify($current, $user['password'])) {
                $error = 'Current password is incorrect.';
            } elseif ($newPassword !== $confirmPassword) {
                $error = 'New passwords do not match.';
            }
        }
        if (!$error) {
            if ($newPassword) {
                $hash = password_hash($newPassword, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare('UPDATE users SET name = ?, phone = ?, password = ? WHERE id = ?');
                $stmt->execute([$name, $phone, $hash, $_SESSION['user_id']]);
            } else {
                $stmt = $pdo->prepare('UPDATE users SET name = ?, phone = ? WHERE id = ?');
                $stmt->execute([$name, $phone, $_SESSION['user_id']]);
            }
            $success = 'Profile updated successfully.';
        }
    }
}
?>
<?php include __DIR__ . '/../includes/header.php'; ?>
<div class="mx-auto max-w-4xl px-4 py-10 sm:px-6 lg:px-8">
    <div class="rounded-[2rem] bg-white p-8 shadow-sm">
        <h1 class="text-3xl font-semibold text-slate-900">My Profile</h1>
        <?php if ($success): ?><div class="mt-6 rounded-3xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-700"><?= sanitize($success) ?></div><?php endif; ?>
        <?php if ($error): ?><div class="mt-6 rounded-3xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-700"><?= sanitize($error) ?></div><?php endif; ?>
        <form method="post" class="mt-8 grid gap-6">
            <div class="grid gap-6 sm:grid-cols-2">
                <label class="space-y-2 text-sm font-medium text-slate-700">Name<input name="name" value="<?= sanitize($user['name']) ?>" required class="w-full rounded-3xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 outline-none focus:border-slate-900" /></label>
                <label class="space-y-2 text-sm font-medium text-slate-700">Phone<input name="phone" value="<?= sanitize($user['phone']) ?>" required pattern="\d{10}" class="w-full rounded-3xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 outline-none focus:border-slate-900" /></label>
            </div>
            <div class="pt-6 text-sm font-semibold text-slate-900">Change Password</div>
            <div class="grid gap-6 sm:grid-cols-2">
                <label class="space-y-2 text-sm font-medium text-slate-700">Current Password<input type="password" name="current_password" class="w-full rounded-3xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 outline-none focus:border-slate-900" /></label>
                <label class="space-y-2 text-sm font-medium text-slate-700">New Password<input type="password" name="new_password" class="w-full rounded-3xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 outline-none focus:border-slate-900" /></label>
            </div>
            <label class="space-y-2 text-sm font-medium text-slate-700">Confirm Password<input type="password" name="confirm_password" class="w-full rounded-3xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 outline-none focus:border-slate-900" /></label>
            <button class="inline-flex items-center justify-center rounded-3xl bg-slate-900 px-6 py-3 text-sm font-semibold text-white hover:bg-slate-800">Save Changes</button>
        </form>
    </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
