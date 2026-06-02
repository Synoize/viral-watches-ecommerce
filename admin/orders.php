<?php
require_once __DIR__ . '/_header.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['order_id'])) {
    $status = sanitize($_POST['status'] ?? 'Pending');
    $stmt = $pdo->prepare('UPDATE orders SET status = ? WHERE id = ?');
    $stmt->execute([$status, (int)$_POST['order_id']]);
    flash('success', 'Order status updated.');
    redirect('/admin/orders.php');
}
$orders = $pdo->query('SELECT o.*, u.name AS user_name FROM orders o LEFT JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC')->fetchAll();
?>
<div class="card p-4 shadow-sm">
    <h4>Orders</h4>
    <?php if ($msg = flash('success')): ?><div class="alert alert-success"><?= sanitize($msg) ?></div><?php endif; ?>
    <table class="table table-hover mt-3">
        <thead><tr><th>ID</th><th>User</th><th>Amount</th><th>Payment</th><th>Status</th><th>Placed</th><th></th></tr></thead>
        <tbody>
            <?php foreach ($orders as $order): ?>
                <tr>
                    <td>#<?= $order['id'] ?></td>
                    <td><?= sanitize($order['user_name'] ?? 'Guest') ?></td>
                    <td>₹<?= number_format($order['total_amount'], 2) ?></td>
                    <td><?= sanitize($order['payment_method']) ?> / <?= sanitize($order['payment_status']) ?></td>
                    <td><?= sanitize($order['status']) ?></td>
                    <td><?= date('j M Y', strtotime($order['created_at'])) ?></td>
                    <td>
                        <form class="d-flex gap-2" method="post">
                            <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                            <select name="status" class="form-select form-select-sm">
                                <?php foreach (['Pending','Confirmed','Shipped','Delivered','Cancelled'] as $status): ?>
                                    <option value="<?= $status ?>" <?= $order['status'] === $status ? 'selected' : '' ?>><?= $status ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button class="btn btn-sm btn-primary">Save</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php require_once __DIR__ . '/_footer.php'; ?>
