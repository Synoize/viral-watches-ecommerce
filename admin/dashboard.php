<?php
require_once __DIR__ . '/_header.php';
$totalUsers = $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
$totalOrders = $pdo->query('SELECT COUNT(*) FROM orders')->fetchColumn();
$totalProducts = $pdo->query('SELECT COUNT(*) FROM products')->fetchColumn();
$totalRevenue = $pdo->query('SELECT COALESCE(SUM(total_amount),0) FROM orders WHERE payment_status = "Paid"')->fetchColumn();
?>
<div class="row g-4">
    <div class="col-md-3"><div class="card p-4 shadow-sm"><h5>Users</h5><p class="display-6"><?= $totalUsers ?></p></div></div>
    <div class="col-md-3"><div class="card p-4 shadow-sm"><h5>Products</h5><p class="display-6"><?= $totalProducts ?></p></div></div>
    <div class="col-md-3"><div class="card p-4 shadow-sm"><h5>Orders</h5><p class="display-6"><?= $totalOrders ?></p></div></div>
    <div class="col-md-3"><div class="card p-4 shadow-sm"><h5>Revenue</h5><p class="display-6">₹<?= number_format($totalRevenue, 2) ?></p></div></div>
</div>
<div class="card mt-4 p-4 shadow-sm">
    <h5>Quick Actions</h5>
    <div class="row g-3 mt-3">
        <div class="col-md-4"><a class="btn btn-primary w-100" href="<?= BASE_URL ?>/admin/products.php">Manage Products</a></div>
        <div class="col-md-4"><a class="btn btn-secondary w-100" href="<?= BASE_URL ?>/admin/orders.php">Manage Orders</a></div>
        <div class="col-md-4"><a class="btn btn-success w-100" href="<?= BASE_URL ?>/admin/coupons.php">Manage Coupons</a></div>
    </div>
</div>
<?php require_once __DIR__ . '/_footer.php'; ?>
