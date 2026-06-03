<?php
require_once __DIR__ . '/_header.php';
ensureBoxOptionsTableExists();
seedDefaultPageMeta();
$totalUsers = $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
$totalOrders = $pdo->query('SELECT COUNT(*) FROM orders')->fetchColumn();
$totalProducts = $pdo->query('SELECT COUNT(*) FROM products')->fetchColumn();
$totalBoxes = $pdo->query('SELECT COUNT(*) FROM box_options')->fetchColumn();
$totalPageMeta = $pdo->query('SELECT COUNT(*) FROM page_meta')->fetchColumn();
$totalRevenue = $pdo->query('SELECT COALESCE(SUM(total_amount),0) FROM orders WHERE payment_status = "Paid"')->fetchColumn();
?>
<div class="grid gap-6 xl:grid-cols-6">
    <div class="rounded-[1.75rem] border border-slate-200 bg-white p-6 shadow-sm">
        <p class="text-sm font-medium text-slate-500">Users</p>
        <p class="mt-4 text-4xl font-semibold text-slate-900"><?= $totalUsers ?></p>
    </div>
    <div class="rounded-[1.75rem] border border-slate-200 bg-white p-6 shadow-sm">
        <p class="text-sm font-medium text-slate-500">Products</p>
        <p class="mt-4 text-4xl font-semibold text-slate-900"><?= $totalProducts ?></p>
    </div>
    <div class="rounded-[1.75rem] border border-slate-200 bg-white p-6 shadow-sm">
        <p class="text-sm font-medium text-slate-500">Orders</p>
        <p class="mt-4 text-4xl font-semibold text-slate-900"><?= $totalOrders ?></p>
    </div>
    <div class="rounded-[1.75rem] border border-slate-200 bg-white p-6 shadow-sm">
        <p class="text-sm font-medium text-slate-500">Boxes</p>
        <p class="mt-4 text-4xl font-semibold text-slate-900"><?= $totalBoxes ?></p>
    </div>
    <div class="rounded-[1.75rem] border border-slate-200 bg-white p-6 shadow-sm">
        <p class="text-sm font-medium text-slate-500">Page Meta</p>
        <p class="mt-4 text-4xl font-semibold text-slate-900"><?= $totalPageMeta ?></p>
    </div>
    <div class="rounded-[1.75rem] border border-slate-200 bg-white p-6 shadow-sm">
        <p class="text-sm font-medium text-slate-500">Revenue</p>
        <p class="mt-4 text-4xl font-semibold text-slate-900">₹<?= number_format($totalRevenue, 2) ?></p>
    </div>
</div>
<div class="mt-8 rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
    <h2 class="text-2xl font-semibold text-slate-900">Quick Actions</h2>
    <div class="mt-6 grid gap-4 md:grid-cols-5">
        <a href="<?= BASE_URL ?>/admin/products.php" class="rounded-3xl border border-slate-200 bg-slate-900 px-6 py-4 text-center text-sm font-semibold text-white hover:bg-slate-800">Manage Products</a>
        <a href="<?= BASE_URL ?>/admin/box-options.php" class="rounded-3xl border border-slate-200 bg-slate-100 px-6 py-4 text-center text-sm font-semibold text-slate-900 hover:bg-slate-50">Manage Boxes</a>
        <a href="<?= BASE_URL ?>/admin/page-meta.php" class="rounded-3xl border border-slate-200 bg-slate-100 px-6 py-4 text-center text-sm font-semibold text-slate-900 hover:bg-slate-50">Page Meta</a>
        <a href="<?= BASE_URL ?>/admin/orders.php" class="rounded-3xl border border-slate-200 bg-slate-100 px-6 py-4 text-center text-sm font-semibold text-slate-900 hover:bg-slate-50">Manage Orders</a>
        <a href="<?= BASE_URL ?>/admin/coupons.php" class="rounded-3xl border border-slate-200 bg-slate-100 px-6 py-4 text-center text-sm font-semibold text-slate-900 hover:bg-slate-50">Manage Coupons</a>
    </div>
</div>
<?php require_once __DIR__ . '/_footer.php'; ?>
