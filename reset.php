<?php
require_once __DIR__ . '/includes/functions.php';
if (isLoggedIn()) {
    redirect('/index.php');
}
$error = null;
$success = null;
$token = $_GET['token'] ?? ($_POST['token'] ?? '');
$validToken = $token ? validatePasswordResetToken($token) : false;
if (!$token || !$validToken) {
    $error = 'This password reset link is invalid or has expired.';
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $token && $validToken) {
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    if (!$password || !$confirm) {
        $error = 'Please enter and confirm your new password.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        $result = resetPasswordWithToken($token, $password);
        if (!empty($result['error'])) {
            $error = $result['error'];
        } else {
            $success = 'Your password has been reset successfully. You can now log in.';
        }
    }
}
?>
<?php include __DIR__ . '/includes/header.php'; ?>
<div class="mx-auto flex min-h-[calc(100vh-6rem)] items-center justify-center px-4 py-10 sm:px-6 lg:px-8">
    <div class="w-full max-w-md rounded-3xl bg-white p-8 shadow-sm">
        <h3 class="text-3xl font-semibold text-slate-900">Reset Password</h3>
        <?php if ($error): ?><div class="mt-4 rounded-3xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-700"><?= sanitize($error) ?></div><?php endif; ?>
        <?php if ($success): ?><div class="mt-4 rounded-3xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-700"><?= sanitize($success) ?></div><?php endif; ?>
        <?php if (!$success): ?>
            <form method="post" class="mt-8 space-y-5">
                <input type="hidden" name="token" value="<?= sanitize($token) ?>" />
                <div><label class="block text-sm font-medium text-slate-700">New Password</label><input type="password" name="password" required class="mt-2 w-full rounded-3xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 outline-none focus:border-slate-900" /></div>
                <div><label class="block text-sm font-medium text-slate-700">Confirm Password</label><input type="password" name="confirm_password" required class="mt-2 w-full rounded-3xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 outline-none focus:border-slate-900" /></div>
                <button class="inline-flex w-full items-center justify-center rounded-3xl bg-slate-900 px-6 py-3 text-sm font-semibold text-white hover:bg-slate-800">Reset Password</button>
            </form>
        <?php endif; ?>
        <p class="mt-6 text-center text-sm text-slate-600">Remembered your password? <a href="<?= BASE_URL ?>/login.php" class="font-medium text-slate-900 underline">Login</a></p>
    </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
