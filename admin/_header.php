<?php
require_once __DIR__ . '/../includes/functions.php';
if (!isAdmin()) {
    redirect('/admin/login.php');
}

$currentUser = getCurrentUser();
$adminName = sanitize(is_array($currentUser) && !empty($currentUser['name']) ? $currentUser['name'] : 'Admin');
$currentAdminPage = basename($_SERVER['SCRIPT_NAME'] ?? '');
$adminNavItems = [
    ['dashboard.php', 'Dashboard', 'fa-solid fa-chart-pie'],
    ['products.php', 'Products', 'fa-solid fa-box'],
    ['slides.php', 'Slides', 'fa-solid fa-images'],
    ['categories.php', 'Categories', 'fa-solid fa-tags'],
    ['orders.php', 'Orders', 'fa-solid fa-receipt'],
    ['reviews.php', 'Reviews', 'fa-solid fa-star'],
    ['users.php', 'Users', 'fa-solid fa-user-group'],
    ['coupons.php', 'Coupons', 'fa-solid fa-ticket'],
    ['box-options.php', 'Box Options', 'fa-solid fa-gift'],
    ['page-meta.php', 'Page Meta', 'fa-solid fa-file-lines'],
    ['messages.php', 'Messages', 'fa-solid fa-envelope'],
];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        body: ['Inter', 'sans-serif'],
                    },
                    colors: {
                        brand: '#10b981',
                    },
                    boxShadow: {
                        soft: '0 18px 50px rgba(15, 23, 42, 0.06)',
                    },
                },
            },
        }
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />
</head>

<body class="min-h-screen bg-[#f7f8fb] text-slate-900 font-body">
    <div class="min-h-screen lg:grid lg:grid-cols-[280px_1fr]">
        <aside class="border-r border-slate-100 bg-white lg:sticky lg:top-0 lg:h-screen">
            <div class="flex h-full flex-col p-4">
                <a href="<?= BASE_URL ?>/admin/dashboard.php" class="flex items-center gap-3">
                    <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600">
                        <i class="fa-solid fa-store"></i>
                    </span>
                    <span>
                        <span class="block text-2xl font-black tracking-tight text-slate-950">Websolvit.</span>
                        <span class="block text-xs font-medium text-slate-400">Admin Dashboard</span>
                    </span>
                </a>

                <nav class="flex-1 overflow-y-auto mt-8 grid gap-1 text-sm font-semibold text-slate-500">
                    <?php foreach ($adminNavItems as $item): ?>
                        <?php $isActiveAdminItem = $currentAdminPage === $item[0]; ?>
                        <a href="<?= BASE_URL ?>/admin/<?= $item[0] ?>" class="flex items-center gap-3 rounded-2xl px-4 py-3 transition <?= $isActiveAdminItem ? 'bg-emerald-50 text-emerald-700' : 'hover:bg-slate-50 hover:text-slate-900' ?>">
                            <i class="<?= $item[2] ?> w-5 text-center text-sm"></i>
                            <span><?= sanitize($item[1]) ?></span>
                        </a>
                    <?php endforeach; ?>
                </nav>

                <div class="flex items-center gap-3 rounded-2xl bg-white px-3 py-2 shadow-sm mt-2">
                        <div class="flex h-9 w-9 items-center justify-center rounded-full bg-emerald-100 text-sm font-bold text-emerald-700">
                            <?= sanitize(strtoupper(substr($adminName, 0, 1))) ?>
                        </div>
                        <div class="hidden sm:block">
                            <p class="text-sm font-bold text-slate-900"><?= $adminName ?></p>
                            <a href="<?= BASE_URL ?>/admin/logout.php" class="text-xs font-semibold text-slate-400 hover:text-rose-500">Logout</a>
                        </div>
                    </div>

            </div>
        </aside>

        <div class="min-w-0">
            <header class="sticky top-0 z-30 border-b border-slate-100 bg-white">
                <div class="flex gap-4 px-4 py-4 sm:px-6 flex-row items-center justify-end lg:px-8">
                    <a href="<?= BASE_URL ?>/" target="_blank" class="hidden rounded-2xl flex items-center border border-slate-100 bg-white px-4 py-3 text-sm font-semibold text-slate-600 flex gap-2 hover:text-slate-950 sm:inline-flex"><i class="fa-solid fa-store"></i>Visit Store</a>
                </div>
            </header>

            <main class="px-4 py-8 sm:px-6 lg:px-8">
