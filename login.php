<?php
require_once __DIR__ . '/includes/functions.php';
if (isLoggedIn()) {
    redirect('/index.php');
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
            flash('success', 'Welcome back, ' . sanitize($user['name']) . '!');
            redirect('/index.php');
        }
        $error = 'Email or password is incorrect.';
    }
}
?>
<?php include __DIR__ . '/includes/header.php'; ?>
<div class="container mt-5"><div class="row justify-content-center"><div class="col-md-6">
    <div class="card shadow-sm p-4">
        <h3 class="mb-4">Login</h3>
        <?php if ($error): ?><div class="alert alert-danger"><?= sanitize($error) ?></div><?php endif; ?>
        <?php if ($message = flash('success')): ?><div class="alert alert-success"><?= sanitize($message) ?></div><?php endif; ?>
        <form method="post">
            <div class="mb-3"><label class="form-label">Email</label><input type="email" name="email" class="form-control" required></div>
            <div class="mb-3"><label class="form-label">Password</label><input type="password" name="password" class="form-control" required></div>
            <button class="btn btn-primary w-100">Login</button>
        </form>
        <div class="mt-3 text-center">
            <a href="<?= BASE_URL ?>/register.php">Create Account</a>
        </div>
    </div>
</div></div></div>
<?php include __DIR__ . '/includes/footer.php'; ?>
