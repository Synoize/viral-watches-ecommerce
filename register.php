<?php
require_once __DIR__ . '/includes/functions.php';
if (isLoggedIn()) {
    redirect('/');
}
$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
    $countryCode = $_POST['country_code'] ?? '91';
    $phone = $_POST['phone'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    if (!$name || !$email || !$phone || !$password || !$confirm) {
        $error = 'All fields are required.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        $phoneValidation = validateInternationalPhone($countryCode, $phone);
        if (!empty($phoneValidation['error'])) {
            $error = $phoneValidation['error'];
        } else {
            $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $error = 'Email is already registered.';
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $formattedPhone = $phoneValidation['formatted'];
                $stmt = $pdo->prepare('INSERT INTO users (name, phone, email, password, role) VALUES (?, ?, ?, ?, ? )');
                $stmt->execute([$name, $formattedPhone, $email, $hash, 'user']);
                $redirectAfterLogin = $_SESSION['redirect_after_login'] ?? null;
                if ($redirectAfterLogin) {
                    $_SESSION['user_id'] = $pdo->lastInsertId();
                    $_SESSION['user_role'] = 'user';
                    unset($_SESSION['redirect_after_login']);
                    flash('success', 'Welcome, ' . sanitize($name) . '!');
                    redirect($redirectAfterLogin);
                }
                flash('success', 'Registration successful. Please log in.');
                redirect('/login.php');
            }
        }
    }
}
?>
<?php include __DIR__ . '/includes/head.php'; ?>
<div class="mx-auto flex min-h-screen items-center justify-center px-4 py-10 sm:px-6 lg:px-8">
    <div class="w-full max-w-md">
        <h3 class="text-3xl font-semibold text-slate-900">Register</h3>
        <form method="post" class="mt-8 space-y-5">
            <div><label class="block text-sm font-medium text-slate-700">Name</label><input name="name" required class="mt-2 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 outline-none focus:border-slate-900" /></div>
            <div><label class="block text-sm font-medium text-slate-700">Email</label><input type="email" name="email" required class="mt-2 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 outline-none focus:border-slate-900" /></div>
            <div class="grid gap-2 sm:grid-cols-[100px_1fr]">
                <label class="space-y-2 text-sm font-medium text-slate-700">Country Code
                    <select name="country_code" class="mt-2 w-full rounded-xl border border-slate-200 bg-slate-50 px-2 py-3 text-slate-900 outline-none focus:border-slate-900">
                        <?php $selected = isset($_POST['country_code']) ? $_POST['country_code'] : '91'; ?>
                        <?php foreach (getCountryCodes() as $code => $label): ?>
                            <option value="<?= $code ?>" <?= $selected == $code ? 'selected' : '' ?>><?= $label ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label class="space-y-2 text-sm font-medium text-slate-700">Phone Number
                    <input type="text" name="phone" required placeholder="e.g., 9876543210" value="<?= sanitize($_POST['phone'] ?? '') ?>" class="mt-2 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 outline-none focus:border-slate-900" />
                </label>
            </div>
            <div><label class="block text-sm font-medium text-slate-700">Password</label><input type="password" name="password" required class="mt-2 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 outline-none focus:border-slate-900" /></div>
            <div><label class="block text-sm font-medium text-slate-700">Confirm Password</label><input type="password" name="confirm_password" required class="mt-2 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 outline-none focus:border-slate-900" /></div>
            <button class="inline-flex w-full items-center justify-center rounded-xl bg-slate-900 px-6 py-4 text-sm font-semibold text-white hover:bg-slate-800">Register</button>
        </form>
        <p class="mt-6 text-center text-sm text-slate-600">Already have an account? <a href="<?= BASE_URL ?>/login" class="font-medium text-slate-900 underline">Login</a></p>
    </div>
</div>
<?php include __DIR__ . '/includes/foot.php'; ?>
