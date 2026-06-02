<?php
require_once __DIR__ . '/includes/functions.php';
if (isLoggedIn()) {
    redirect('/index.php');
}
$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
    $phone = preg_replace('/\D/', '', $_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    if (!$name || !$email || !$phone || !$password || !$confirm) {
        $error = 'All fields are required.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = 'Email is already registered.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('INSERT INTO users (name, phone, email, password, role) VALUES (?, ?, ?, ?, ? )');
            $stmt->execute([$name, $phone, $email, $hash, 'user']);
            flash('success', 'Registration successful. Please log in.');
            redirect('/login.php');
        }
    }
}
?>
<?php include __DIR__ . '/includes/header.php'; ?>
<div class="mx-auto flex min-h-[calc(100vh-6rem)] items-center justify-center px-4 py-10 sm:px-6 lg:px-8">
    <div class="w-full max-w-md rounded-3xl bg-white p-8 shadow-sm">
        <h3 class="text-3xl font-semibold text-slate-900">Register</h3>
        <?php if ($error): ?><div class="mt-4 rounded-3xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-700"><?= sanitize($error) ?></div><?php endif; ?>
        <form method="post" class="mt-8 space-y-5">
            <div><label class="block text-sm font-medium text-slate-700">Name</label><input name="name" required class="mt-2 w-full rounded-3xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 outline-none focus:border-slate-900" /></div>
            <div><label class="block text-sm font-medium text-slate-700">Email</label><input type="email" name="email" required class="mt-2 w-full rounded-3xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 outline-none focus:border-slate-900" /></div>
            <div><label class="block text-sm font-medium text-slate-700">Phone</label><input type="text" name="phone" required pattern="\d{10}" class="mt-2 w-full rounded-3xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 outline-none focus:border-slate-900" /></div>
            <div><label class="block text-sm font-medium text-slate-700">Password</label><input type="password" name="password" required class="mt-2 w-full rounded-3xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 outline-none focus:border-slate-900" /></div>
            <div><label class="block text-sm font-medium text-slate-700">Confirm Password</label><input type="password" name="confirm_password" required class="mt-2 w-full rounded-3xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 outline-none focus:border-slate-900" /></div>
            <button class="inline-flex w-full items-center justify-center rounded-3xl bg-slate-900 px-6 py-3 text-sm font-semibold text-white hover:bg-slate-800">Register</button>
        </form>
        <p class="mt-6 text-center text-sm text-slate-600">Already have an account? <a href="<?= BASE_URL ?>/login.php" class="font-medium text-slate-900 underline">Login</a></p>
    </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
