<?php
require_once __DIR__ . '/../includes/functions.php';
if (!isAdmin()) {
    redirect('/admin/login.php');
}

ensureCouponRuleTablesExist();

$coupon = null;
$selectedUserIds = [];
$users = $pdo->query('SELECT id, name, email FROM users ORDER BY name')->fetchAll();

if (!empty($_GET['edit'])) {
    $stmt = $pdo->prepare('SELECT * FROM coupons WHERE id = ?');
    $stmt->execute([(int)$_GET['edit']]);
    $coupon = $stmt->fetch();
    if ($coupon) {
        $selectedUserIds = getCouponAllowedUserIds($coupon['id']);
    }
}

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
    $startsAt = !empty($_POST['starts_at']) ? $_POST['starts_at'] : null;
    $expiry = !empty($_POST['expiry_date']) ? $_POST['expiry_date'] : null;
    $usageLimit = $_POST['usage_limit'] !== '' ? max(1, (int)$_POST['usage_limit']) : null;
    $perUserLimit = $_POST['per_user_limit'] !== '' ? max(1, (int)$_POST['per_user_limit']) : null;
    $status = isset($_POST['status']) ? 1 : 0;
    $allowedUserIds = $_POST['allowed_user_ids'] ?? [];

    if ($code === '' || !in_array($type, ['fixed', 'percent'], true) || $value <= 0) {
        $error = 'Coupon code, type, and positive value are required.';
    } elseif ($type === 'percent' && $value > 100) {
        $error = 'Percent coupon value cannot be more than 100.';
    } elseif ($startsAt && $expiry && $startsAt > $expiry) {
        $error = 'Start date cannot be after expiry date.';
    } else {
        try {
            if (!empty($_POST['coupon_id'])) {
                $stmt = $pdo->prepare('UPDATE coupons SET code = ?, type = ?, value = ?, min_order = ?, max_discount = ?, starts_at = ?, expiry_date = ?, usage_limit = ?, per_user_limit = ?, status = ? WHERE id = ?');
                $stmt->execute([$code, $type, $value, $minOrder, $maxDiscount, $startsAt, $expiry, $usageLimit, $perUserLimit, $status, (int)$_POST['coupon_id']]);
                $couponId = (int)$_POST['coupon_id'];
                flash('success', 'Coupon updated.');
            } else {
                $stmt = $pdo->prepare('INSERT INTO coupons (code, type, value, min_order, max_discount, starts_at, expiry_date, usage_limit, per_user_limit, used_count, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 0, ?)');
                $stmt->execute([$code, $type, $value, $minOrder, $maxDiscount, $startsAt, $expiry, $usageLimit, $perUserLimit, $status]);
                $couponId = (int)$pdo->lastInsertId();
                flash('success', 'Coupon created.');
            }
            syncCouponAllowedUsers($couponId, $allowedUserIds);
            redirect('/admin/coupons.php');
        } catch (PDOException $e) {
            $error = 'A coupon with this code already exists.';
        }
    }
}

$coupons = $pdo->query(
    'SELECT c.*, COUNT(cau.id) AS allowed_user_count
     FROM coupons c
     LEFT JOIN coupon_users cau ON cau.coupon_id = c.id AND cau.is_allowed = 1
     GROUP BY c.id
     ORDER BY c.id DESC'
)->fetchAll();
require_once __DIR__ . '/_header.php';
?>
<div class="grid gap-6 xl:grid-cols-[68%_30%]">
    <div>
        <h2 class="text-2xl font-semibold text-slate-900">Coupons</h2>
        <div class="mt-6 overflow-hidden rounded-[2rem] border border-slate-200">
            <table class="w-full border-separate border-spacing-0 text-left text-sm">
                <thead class="bg-slate-100 text-slate-600">
                    <tr>
                        <th class="px-6 py-4">Code</th>
                        <th class="px-6 py-4">Discount</th>
                        <th class="px-6 py-4">Limits</th>
                        <th class="px-6 py-4">Validity</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($coupons as $item): ?>
                        <tr class="border-t border-slate-200 bg-white">
                            <td class="px-6 py-4">
                                <p class="font-semibold text-slate-900"><?= sanitize($item['code']) ?></p>
                                <p class="mt-1 text-xs text-slate-500">Min order &#8377;<?= number_format($item['min_order'], 2) ?></p>
                            </td>
                            <td class="px-6 py-4">
                                <?= $item['type'] === 'percent' ? sanitize($item['value']) . '%' : '&#8377;' . number_format($item['value'], 2) ?>
                                <?php if ((float)$item['max_discount'] > 0): ?>
                                    <p class="mt-1 text-xs text-slate-500">Max &#8377;<?= number_format($item['max_discount'], 2) ?></p>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 text-slate-700">
                                <p>Total: <?= $item['usage_limit'] ? (int)$item['used_count'] . '/' . (int)$item['usage_limit'] : 'Unlimited' ?></p>
                                <p class="mt-1">Per user: <?= $item['per_user_limit'] ? (int)$item['per_user_limit'] : 'Unlimited' ?></p>
                                <p class="mt-1">Users: <?= $item['allowed_user_count'] ? (int)$item['allowed_user_count'] . ' selected' : 'All users' ?></p>
                            </td>
                            <td class="px-6 py-4 text-slate-700">
                                <p>Start: <?= $item['starts_at'] ? sanitize($item['starts_at']) : 'Now' ?></p>
                                <p class="mt-1">End: <?= $item['expiry_date'] ? sanitize($item['expiry_date']) : 'All time' ?></p>
                            </td>
                            <td class="px-6 py-4"><?= $item['status'] ? 'Active' : 'Disabled' ?></td>
                            <td class="px-6 py-4 space-x-2 flex ">
                                <a class="inline-flex rounded-full border border-slate-200 bg-slate-100 px-4 py-2 text-sm text-slate-900 hover:bg-slate-50" href="<?= publicUrl('/admin/coupons?edit=' . $item['id']) ?>">Edit</a>
                                <form class="inline" method="post" onsubmit="return confirm('Delete coupon?');"><input type="hidden" name="delete_id" value="<?= $item['id'] ?>"><button class="inline-flex rounded-full bg-rose-50 px-4 py-2 text-sm font-semibold text-rose-700 hover:bg-rose-100">Delete</button></form>
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
        <h2 class="text-2xl font-semibold text-slate-900"><?= $coupon ? 'Edit Coupon' : 'Add Coupon' ?></h2>
        <form method="post" class="mt-6 space-y-4">
            <input type="hidden" name="coupon_id" value="<?= sanitize($coupon['id'] ?? '') ?>">
            <label class="block text-sm font-medium text-slate-700">Code<input name="code" value="<?= sanitize($coupon['code'] ?? '') ?>" class="mt-2 w-full rounded-3xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 outline-none focus:border-slate-900" required /></label>
            <label class="block text-sm font-medium text-slate-700">Type<select name="type" class="mt-2 w-full rounded-3xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 outline-none focus:border-slate-900"><option value="fixed" <?= ($coupon['type'] ?? '') === 'fixed' ? 'selected' : '' ?>>Fixed</option><option value="percent" <?= ($coupon['type'] ?? '') === 'percent' ? 'selected' : '' ?>>Percent</option></select></label>
            <label class="block text-sm font-medium text-slate-700">Value<input type="number" step="0.01" name="value" value="<?= sanitize($coupon['value'] ?? '') ?>" class="mt-2 w-full rounded-3xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 outline-none focus:border-slate-900" required /></label>
            <label class="block text-sm font-medium text-slate-700">Min Order<input type="number" step="0.01" name="min_order" value="<?= sanitize($coupon['min_order'] ?? '0') ?>" class="mt-2 w-full rounded-3xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 outline-none focus:border-slate-900" required /></label>
            <label class="block text-sm font-medium text-slate-700">Max Discount<input type="number" step="0.01" name="max_discount" value="<?= sanitize($coupon['max_discount'] ?? '0') ?>" class="mt-2 w-full rounded-3xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 outline-none focus:border-slate-900" /></label>
            <label class="block text-sm font-medium text-slate-700">Start Date<input type="date" name="starts_at" value="<?= sanitize($coupon['starts_at'] ?? '') ?>" class="mt-2 w-full rounded-3xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 outline-none focus:border-slate-900" /></label>
            <label class="block text-sm font-medium text-slate-700">Expiry Date<input type="date" name="expiry_date" value="<?= sanitize($coupon['expiry_date'] ?? '') ?>" class="mt-2 w-full rounded-3xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 outline-none focus:border-slate-900" /></label>
            <label class="block text-sm font-medium text-slate-700">Total Usage Limit<input type="number" min="1" name="usage_limit" value="<?= sanitize($coupon['usage_limit'] ?? '') ?>" placeholder="Blank for unlimited" class="mt-2 w-full rounded-3xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 outline-none focus:border-slate-900" /></label>
            <label class="block text-sm font-medium text-slate-700">Per User Limit<input type="number" min="1" name="per_user_limit" value="<?= sanitize($coupon['per_user_limit'] ?? '') ?>" placeholder="1 for one-time per user, blank for unlimited" class="mt-2 w-full rounded-3xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 outline-none focus:border-slate-900" /></label>
            <label class="block text-sm font-medium text-slate-700">Allowed Users<select name="allowed_user_ids[]" multiple size="5" class="mt-2 w-full rounded-3xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 outline-none focus:border-slate-900">
                <?php foreach ($users as $itemUser): ?>
                    <option value="<?= (int)$itemUser['id'] ?>" <?= in_array((int)$itemUser['id'], $selectedUserIds, true) ? 'selected' : '' ?>><?= sanitize($itemUser['name']) ?> - <?= sanitize($itemUser['email']) ?></option>
                <?php endforeach; ?>
            </select></label>
            <p class="text-xs leading-5 text-slate-500">Leave allowed users empty for all users. Use Per User Limit = 1 for one-time per user. Leave Expiry Date empty for all-time coupons.</p>
            <label class="flex items-center gap-3 text-sm font-medium text-slate-700"><input type="checkbox" name="status" class="h-5 w-5 rounded border-slate-300 text-brand focus:ring-brand" <?= !isset($coupon['status']) || $coupon['status'] ? 'checked' : '' ?> /> Active</label>
            <button class="inline-flex w-full items-center justify-center rounded-3xl bg-slate-900 px-6 py-3 text-sm font-semibold text-white hover:bg-slate-800">Save Coupon</button>
            <?php if ($coupon): ?>
                <a href="<?= publicUrl('/admin/coupons') ?>" class="inline-flex w-full items-center justify-center rounded-3xl border border-slate-200 bg-white px-6 py-3 text-sm font-semibold text-slate-900 hover:bg-slate-50">Cancel Edit</a>
            <?php endif; ?>
        </form>
    </aside>
</div>
<?php require_once __DIR__ . '/_footer.php'; ?>
