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
<div class="grid gap-6 xl:grid-cols-[2fr_1fr]">
    <div class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="text-2xl font-semibold text-slate-900">Coupons</h2>
        <?php if ($msg = flash('success')): ?><div class="mt-6 rounded-3xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-700"><?= sanitize($msg) ?></div><?php endif; ?>
        <div class="mt-6 overflow-hidden rounded-[2rem] border border-slate-200">
            <table class="w-full border-separate border-spacing-0 text-left text-sm">
                <thead class="bg-slate-100 text-slate-600"><tr><th class="px-6 py-4">Code</th><th class="px-6 py-4">Type</th><th class="px-6 py-4">Value</th><th class="px-6 py-4">Expiry</th><th class="px-6 py-4">Status</th><th class="px-6 py-4"></th></tr></thead>
                <tbody>
                    <?php foreach ($coupons as $coupon): ?>
                        <tr class="border-t border-slate-200 bg-white">
                            <td class="px-6 py-4"><?= sanitize($coupon['code']) ?></td>
                            <td class="px-6 py-4"><?= sanitize($coupon['type']) ?></td>
                            <td class="px-6 py-4"><?= sanitize($coupon['value']) ?><?= $coupon['type'] === 'percent' ? '%' : '' ?></td>
                            <td class="px-6 py-4"><?= sanitize($coupon['expiry_date']) ?></td>
                            <td class="px-6 py-4"><?= $coupon['status'] ? 'Active' : 'Disabled' ?></td>
                            <td class="px-6 py-4">
                                <form class="inline" method="post" onsubmit="return confirm('Delete coupon?');"><input type="hidden" name="delete_id" value="<?= $coupon['id'] ?>"><button class="inline-flex rounded-full bg-rose-50 px-4 py-2 text-sm font-semibold text-rose-700 hover:bg-rose-100">Delete</button></form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$coupons): ?>
                        <tr class="bg-white"><td colspan="6" class="px-6 py-8 text-center text-slate-500">Coupons not found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <aside class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="text-2xl font-semibold text-slate-900">Add Coupon</h2>
        <?php if (!empty($error)): ?><div class="mt-6 rounded-3xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-700"><?= sanitize($error) ?></div><?php endif; ?>
        <form method="post" class="mt-6 space-y-4">
            <label class="block text-sm font-medium text-slate-700">Code<input name="code" class="mt-2 w-full rounded-3xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 outline-none focus:border-slate-900" required /></label>
            <label class="block text-sm font-medium text-slate-700">Type<select name="type" class="mt-2 w-full rounded-3xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 outline-none focus:border-slate-900"><option value="fixed">Fixed</option><option value="percent">Percent</option></select></label>
            <label class="block text-sm font-medium text-slate-700">Value<input type="number" step="0.01" name="value" class="mt-2 w-full rounded-3xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 outline-none focus:border-slate-900" required /></label>
            <label class="block text-sm font-medium text-slate-700">Min Order<input type="number" step="0.01" name="min_order" class="mt-2 w-full rounded-3xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 outline-none focus:border-slate-900" required /></label>
            <label class="block text-sm font-medium text-slate-700">Max Discount<input type="number" step="0.01" name="max_discount" class="mt-2 w-full rounded-3xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 outline-none focus:border-slate-900" /></label>
            <label class="block text-sm font-medium text-slate-700">Expiry Date<input type="date" name="expiry_date" class="mt-2 w-full rounded-3xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 outline-none focus:border-slate-900" required /></label>
            <label class="block text-sm font-medium text-slate-700">Usage Limit<input type="number" name="usage_limit" value="1" class="mt-2 w-full rounded-3xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 outline-none focus:border-slate-900" required /></label>
            <label class="flex items-center gap-3 text-sm font-medium text-slate-700"><input type="checkbox" name="status" class="h-5 w-5 rounded border-slate-300 text-brand focus:ring-brand" checked /> Active</label>
            <button class="inline-flex w-full items-center justify-center rounded-3xl bg-slate-900 px-6 py-3 text-sm font-semibold text-white hover:bg-slate-800">Create Coupon</button>
        </form>
    </aside>
</div>
<?php require_once __DIR__ . '/_footer.php'; ?>
