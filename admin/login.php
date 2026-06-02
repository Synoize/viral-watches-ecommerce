<?php
require_once __DIR__ . '/../includes/functions.php';
if (isAdmin()) {
    redirect('/admin/dashboard.php');
}
$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'] ?? '';
    if ($email && $password) {
        $stmt = $pdo->prepare('SELECT id, name, email, password, role FROM users WHERE email = ? AND role = ?');
        $stmt->execute([$email, 'admin']);
        $admin = $stmt->fetch();
        if ($admin && password_verify($password, $admin['password'])) {
            $_SESSION['user_id'] = $admin['id'];
            $_SESSION['user_role'] = 'admin';
            redirect('/admin/dashboard.php');
        }
    }
    $error = 'Invalid admin credentials.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { theme: { extend: { fontFamily: { body: ['Inter', 'sans-serif'] }, colors: { brand: '#1d4ed8' } } } };
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />
</head>
<body class="flex min-h-screen items-center justify-center bg-slate-100 text-slate-900 font-body px-4">
    <div class="w-full max-w-md rounded-[2rem] bg-white p-8 shadow-xl">
        <h1 class="text-3xl font-semibold text-slate-900">Admin Login</h1>
        <?php if ($error): ?><div class="mt-6 rounded-3xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-700"><?= sanitize($error) ?></div><?php endif; ?>
        <form method="post" class="mt-8 space-y-5">
            <div><label class="block text-sm font-medium text-slate-700">Email</label><input type="email" name="email" required class="mt-2 w-full rounded-3xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 outline-none focus:border-slate-900" /></div>
            <div><label class="block text-sm font-medium text-slate-700">Password</label><input type="password" name="password" required class="mt-2 w-full rounded-3xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 outline-none focus:border-slate-900" /></div>
            <button class="inline-flex w-full items-center justify-center rounded-3xl bg-slate-900 px-6 py-3 text-sm font-semibold text-white hover:bg-slate-800">Login</button>
        </form>
    </div>
</body>
</html>
