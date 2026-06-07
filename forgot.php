<?php
require_once __DIR__ . '/includes/functions.php';
if (isLoggedIn()) {
    redirect('/');
}
$error = null;
$success = null;
$previewLink = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
    if (!$email) {
        $error = 'Please enter a valid email address.';
    } else {
        $result = generatePasswordResetToken($email);
        if (!empty($result['error'])) {
            $error = $result['error'];
        } else {
            $success = 'If this email exists, a password reset link has been sent.';
            if (!$result['email_sent']) {
                $previewLink = $result['reset_url'];
            }
        }
    }
}
?>
<?php include __DIR__ . '/includes/head.php'; ?>
<div class="mx-auto flex min-h-[calc(100vh-6rem)] items-center justify-center px-4 py-10 sm:px-6 lg:px-8">
    <div class="w-full max-w-md rounded-3xl bg-white p-8 shadow-sm">
        <h3 class="text-3xl font-semibold text-slate-900">Forgot Password</h3>
        <p class="mt-2 text-sm text-slate-600">Enter the email address associated with your account and we’ll send a password reset link.</p>
        <?php if ($error): ?><div class="mt-4 rounded-3xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-700"><?= sanitize($error) ?></div><?php endif; ?>
        <?php if ($success): ?><div class="mt-4 rounded-3xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-700"><?= sanitize($success) ?></div><?php endif; ?>
        <?php if ($previewLink): ?><div class="mt-4 rounded-3xl border border-slate-200 bg-slate-50 p-4 text-sm text-slate-700">Preview reset link: <a class="font-medium text-brand underline" href="<?= sanitize($previewLink) ?>"><?= sanitize($previewLink) ?></a></div><?php endif; ?>
        <form method="post" class="mt-8 space-y-5">
            <div><label class="block text-sm font-medium text-slate-700">Email</label><input type="email" name="email" required class="mt-2 w-full rounded-3xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 outline-none focus:border-slate-900" /></div>
            <button class="inline-flex w-full items-center justify-center rounded-3xl bg-slate-900 px-6 py-3 text-sm font-semibold text-white hover:bg-slate-800">Send Reset Link</button>
        </form>
        <p class="mt-6 text-center text-sm text-slate-600">Remembered your password? <a href="<?= publicUrl('/login') ?>" class="font-medium text-slate-900 underline">Login</a></p>
    </div>
</div>
<?php include __DIR__ . '/includes/foot.php'; ?>
