<?php
require_once __DIR__ . '/../includes/functions.php';
if (!isLoggedIn()) {
    redirect('/login.php');
}
$stmt = $pdo->prepare('SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC');
$stmt->execute([$_SESSION['user_id']]);
$orders = $stmt->fetchAll();
?>
<?php include __DIR__ . '/../includes/header.php'; ?>
<div class="container mt-5">
    <h3>Order History</h3>
    <?php if (!$orders): ?>
        <div class="alert alert-info">You have no orders yet. <a href="<?= BASE_URL ?>/shop.php">Start shopping</a>.</div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($orders as $order): ?>
                <div class="col-12">
                    <div class="card shadow-sm p-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5>Order #<?= $order['id'] ?></h5>
                                <p class="mb-1 small text-muted">Placed on <?= date('j M Y, H:i', strtotime($order['created_at'])) ?></p>
                                <p class="mb-0 small">Payment: <?= sanitize($order['payment_method']) ?> · Status: <?= sanitize($order['status']) ?></p>
                            </div>
                            <div class="text-end">
                                <h4 class="mb-0">₹<?= number_format($order['total_amount'], 2) ?></h4>
                                <span class="badge bg-secondary"><?= sanitize($order['payment_status']) ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
