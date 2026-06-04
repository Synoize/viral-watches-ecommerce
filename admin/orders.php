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
<div>
    <h2 class="text-2xl font-semibold text-slate-900">Orders</h2>
    <?php if ($msg = flash('success')): ?><div class="mt-6 rounded-3xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-700"><?= sanitize($msg) ?></div><?php endif; ?>
    <div class="mt-6 overflow-hidden rounded-[2rem] border border-slate-200">
        <table class="w-full border-separate border-spacing-0 text-left text-sm">
            <thead class="bg-slate-100 text-slate-600">
                <tr><th class="px-6 py-4">ID</th><th class="px-6 py-4">User</th><th class="px-6 py-4">Amount</th><th class="px-6 py-4">Payment</th><th class="px-6 py-4">Status</th><th class="px-6 py-4">Placed</th><th class="px-6 py-4"></th></tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                    <tr class="border-t border-slate-200 bg-white">
                        <td class="px-6 py-4">#<?= $order['id'] ?></td>
                        <td class="px-6 py-4"><?= sanitize($order['user_name'] ?? 'Guest') ?></td>
                        <td class="px-6 py-4">₹<?= number_format($order['total_amount'], 2) ?></td>
                        <td class="px-6 py-4"><?= sanitize($order['payment_method']) ?> / <?= sanitize($order['payment_status']) ?></td>
                        <td class="px-6 py-4"><?= sanitize($order['status']) ?></td>
                        <td class="px-6 py-4"><?= date('j M Y', strtotime($order['created_at'])) ?></td>
                        <td class="px-6 py-4">
                            <form class="flex flex-col gap-2" method="post">
                                <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                <select name="status" class="rounded-3xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none focus:border-slate-900">
                                    <?php foreach (['Pending','Confirmed','Shipped','Delivered','Cancelled'] as $status): ?>
                                        <option value="<?= $status ?>" <?= $order['status'] === $status ? 'selected' : '' ?>><?= $status ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <button class="inline-flex items-center justify-center rounded-3xl bg-slate-900 px-4 py-3 text-sm font-semibold text-white hover:bg-slate-800">Save</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$orders): ?>
                    <tr class="bg-white"><td colspan="7" class="px-6 py-8 text-center text-slate-500">Orders not found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require_once __DIR__ . '/_footer.php'; ?>
