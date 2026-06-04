<?php
require_once __DIR__ . '/../includes/functions.php';
if (!isAdmin()) {
    redirect('/admin/login.php');
}
$adminName = sanitize($_SESSION['user_id'] ? getCurrentUser()['name'] : 'Admin');
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
                        brand: '#1d4ed8',
                    },
                },
            },
        }
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />
</head>
<body class="min-h-screen bg-slate-50 text-slate-900 font-body">
<header class="bg-slate-900 text-slate-100 shadow-sm">
    <div class="mx-auto flex max-w-7xl flex-col gap-4 px-4 py-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between gap-4">
            <a href="<?= BASE_URL ?>/admin/dashboard.php" class="text-xl font-semibold">Admin Panel</a>
            <div class="flex items-center gap-3">
                <span class="text-sm text-slate-300">Logged in as <?= $adminName ?></span>
                <a href="<?= BASE_URL ?>/admin/logout.php" class="rounded-full border border-slate-700 bg-slate-800 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">Logout</a>
            </div>
        </div>
        <nav class="flex flex-wrap gap-2 text-sm font-medium text-slate-300">
            <a href="<?= BASE_URL ?>/admin/dashboard.php" class="rounded-full bg-slate-800 px-4 py-2 hover:bg-slate-700">Dashboard</a>
            <a href="<?= BASE_URL ?>/admin/products.php" class="rounded-full px-4 py-2 hover:bg-slate-700">Products</a>
            <a href="<?= BASE_URL ?>/admin/slides.php" class="rounded-full px-4 py-2 hover:bg-slate-700">Slides</a>
            <a href="<?= BASE_URL ?>/admin/box-options.php" class="rounded-full px-4 py-2 hover:bg-slate-700">Box Options</a>
            <a href="<?= BASE_URL ?>/admin/page-meta.php" class="rounded-full px-4 py-2 hover:bg-slate-700">Page Meta</a>
            <a href="<?= BASE_URL ?>/admin/categories.php" class="rounded-full px-4 py-2 hover:bg-slate-700">Categories</a>
            <a href="<?= BASE_URL ?>/admin/orders.php" class="rounded-full px-4 py-2 hover:bg-slate-700">Orders</a>
            <a href="<?= BASE_URL ?>/admin/users.php" class="rounded-full px-4 py-2 hover:bg-slate-700">Users</a>
            <a href="<?= BASE_URL ?>/admin/coupons.php" class="rounded-full px-4 py-2 hover:bg-slate-700">Coupons</a>
            <a href="<?= BASE_URL ?>/admin/messages.php" class="rounded-full px-4 py-2 hover:bg-slate-700">Messages</a>
        </nav>
    </div>
</header>
<main class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
