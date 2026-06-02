<?php
require_once __DIR__ . '/_header.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_POST['delete_id'])) {
        $stmt = $pdo->prepare('DELETE FROM coupons WHERE id = ?');
        $stmt->execute([(int)$_POST['delete_id']]);
        flash('success', 'Coupon deleted.');
        redirect('/admin/coupons.php');
    }
    $code = strtoupper(sanitize($_POST['code'] ?? ''));
    $type = sanitize($_POST['type'] ?? 'fixed');
    $value = (float)($_POST['value'] ?? 0);
    $minOrder = (float)($_POST['min_order'] ?? 0);
    $maxDiscount = (float)($_POST['max_discount'] ?? 0);
    $expiry = $_POST['expiry_date'] ?? null;
    $usage = (int)($_POST['usage_limit'] ?? 1);
    $status = isset($_POST['status']) ? 1 : 0;
    if ($code && $value > 0 && $expiry) {
        $stmt = $pdo->prepare('INSERT INTO coupons (code, type, value, min_order, max_discount, expiry_date, usage_limit, used_count, status) VALUES (?, ?, ?, ?, ?, ?, ?, 0, ?)');
        $stmt->execute([$code, $type, $value, $minOrder, $maxDiscount, $expiry, $usage, $status]);
        flash('success', 'Coupon created.');
        redirect('/admin/coupons.php');
    } else {
        $error = 'Please complete all coupon fields.';
    }
}
$coupons = $pdo->query('SELECT * FROM coupons ORDER BY id DESC')->fetchAll();
?>
<div class="row g-4">
    <div class="col-lg-7">
        <div class="card p-4 shadow-sm">
            <h4>Coupons</h4>
            <?php if ($msg = flash('success')): ?><div class="alert alert-success"><?= sanitize($msg) ?></div><?php endif; ?>
            <table class="table table-hover mt-3">
                <thead><tr><th>Code</th><th>Type</th><th>Value</th><th>Expiry</th><th>Status</th><th></th></tr></thead>
                <tbody>
                    <?php foreach ($coupons as $coupon): ?>
                        <tr>
                            <td><?= sanitize($coupon['code']) ?></td>
                            <td><?= sanitize($coupon['type']) ?></td>
                            <td><?= sanitize($coupon['value']) ?><?= $coupon['type'] === 'percent' ? '%' : '' ?></td>
                            <td><?= sanitize($coupon['expiry_date']) ?></td>
                            <td><?= $coupon['status'] ? 'Active' : 'Disabled' ?></td>
                            <td>
                                <form class="d-inline" method="post" onsubmit="return confirm('Delete coupon?');">
                                    <input type="hidden" name="delete_id" value="<?= $coupon['id'] ?>">
                                    <button class="btn btn-sm btn-outline-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card p-4 shadow-sm">
            <h4>Add Coupon</h4>
            <?php if (!empty($error)): ?><div class="alert alert-danger"><?= sanitize($error) ?></div><?php endif; ?>
            <form method="post">
                <div class="mb-3"><label class="form-label">Code</label><input name="code" class="form-control" required></div>
                <div class="mb-3"><label class="form-label">Type</label><select name="type" class="form-select"><option value="fixed">Fixed</option><option value="percent">Percent</option></select></div>
                <div class="mb-3"><label class="form-label">Value</label><input type="number" step="0.01" name="value" class="form-control" required></div>
                <div class="mb-3"><label class="form-label">Min Order</label><input type="number" step="0.01" name="min_order" class="form-control" required></div>
                <div class="mb-3"><label class="form-label">Max Discount</label><input type="number" step="0.01" name="max_discount" class="form-control"></div>
                <div class="mb-3"><label class="form-label">Expiry Date</label><input type="date" name="expiry_date" class="form-control" required></div>
                <div class="mb-3"><label class="form-label">Usage Limit</label><input type="number" name="usage_limit" class="form-control" value="1" required></div>
                <div class="form-check mb-3"><input class="form-check-input" type="checkbox" id="status" name="status" checked><label class="form-check-label" for="status">Active</label></div>
                <button class="btn btn-primary w-100">Create Coupon</button>
            </form>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/_footer.php'; ?>
