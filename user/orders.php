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
<div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
    <h1 class="text-3xl font-semibold text-slate-900">Order History</h1>
    <?php if (!$orders): ?>
        <div class="mt-8 rounded-[2rem] border border-slate-200 bg-white p-8 text-center text-slate-600 shadow-sm">You have no orders yet. <a href="<?= BASE_URL ?>/shop.php" class="font-semibold text-brand hover:text-brand/80">Start shopping</a>.</div>
    <?php else: ?>
        <div class="mt-8 space-y-5">
            <?php foreach ($orders as $order): ?>
                <div class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h2 class="text-xl font-semibold text-slate-900">Order #<?= $order['id'] ?></h2>
                            <p class="mt-1 text-sm text-slate-500">Placed on <?= date('j M Y, H:i', strtotime($order['created_at'])) ?></p>
                            <p class="mt-2 text-sm text-slate-600">Payment: <?= sanitize($order['payment_method']) ?> · Status: <?= sanitize($order['status']) ?></p>
                        </div>
                        <div class="text-right">
                            <p class="text-2xl font-semibold text-slate-900">₹<?= number_format($order['total_amount'], 2) ?></p>
                            <span class="mt-2 inline-flex rounded-full bg-slate-100 px-3 py-1 text-sm font-medium text-slate-700"><?= sanitize($order['payment_status']) ?></span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
