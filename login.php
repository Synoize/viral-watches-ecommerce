<?php
require_once __DIR__ . '/includes/functions.php';
if (isLoggedIn()) {
    redirect('/');
}
$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'] ?? '';
    if (!$email || !$password) {
        $error = 'Please provide valid credentials.';
    } else {
        $stmt = $pdo->prepare('SELECT id, name, email, password, role FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_role'] = $user['role'];
            $redirectAfterLogin = $_SESSION['redirect_after_login'] ?? '/index.php';
            $pendingWishlistAction = $_SESSION['pending_wishlist_action'] ?? null;
            unset($_SESSION['redirect_after_login']);
            unset($_SESSION['pending_wishlist_action']);

            if ($pendingWishlistAction && !empty($pendingWishlistAction['product_id'])) {
                $wishlistResult = ($pendingWishlistAction['action'] ?? 'add') === 'remove'
                    ? removeWishlistItem((int)$pendingWishlistAction['product_id'])
                    : addWishlistItem((int)$pendingWishlistAction['product_id']);
                flash(empty($wishlistResult['error']) ? 'success' : 'error', $wishlistResult['success'] ?? $wishlistResult['error']);
            } else {
                flash('success', 'Welcome back, ' . sanitize($user['name']) . '!');
            }
            redirect($redirectAfterLogin);
        }
        $error = 'Email or password is incorrect.';
    }
}
?>
<?php include __DIR__ . '/includes/head.php'; ?>
<div class="mx-auto flex min-h-[calc(100vh-6rem)] items-center justify-center px-4 py-10 sm:px-6 lg:px-8">
    <div class="w-full max-w-md rounded-3xl bg-white p-8 shadow-sm">
        <h3 class="text-3xl font-semibold text-slate-900">Login</h3>
        <?php if ($error): ?><div class="mt-4 rounded-3xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-700"><?= sanitize($error) ?></div><?php endif; ?>
        <?php if ($message = flash('error')): ?><div class="mt-4 rounded-3xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-700"><?= sanitize($message) ?></div><?php endif; ?>
        <?php if ($message = flash('success')): ?><div class="mt-4 rounded-3xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-700"><?= sanitize($message) ?></div><?php endif; ?>
        <form method="post" class="mt-8 space-y-5">
            <div><label class="block text-sm font-medium text-slate-700">Email</label><input type="email" name="email" required class="mt-2 w-full rounded-3xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 outline-none focus:border-slate-900" /></div>
            <div><label class="block text-sm font-medium text-slate-700">Password</label><input type="password" name="password" required class="mt-2 w-full rounded-3xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 outline-none focus:border-slate-900" /></div>
            <button class="inline-flex w-full items-center justify-center rounded-3xl bg-slate-900 px-6 py-3 text-sm font-semibold text-white hover:bg-slate-800">Login</button>
        </form>
        <p class="mt-4 text-center text-sm text-slate-600"><a href="<?= BASE_URL ?>/forgot.php" class="font-medium text-brand underline">Forgot password?</a></p>
        <p class="mt-6 text-center text-sm text-slate-600">Don't have an account? <a href="<?= BASE_URL ?>/register" class="font-medium text-slate-900 underline">Register</a></p>
    </div>
</div>
<?php include __DIR__ . '/includes/foot.php'; ?>
