<?php
require_once __DIR__ . '/includes/functions.php';
if (!isLoggedIn()) {
    flash('success', 'Please log in to complete checkout.');
    redirect('/login.php');
}
$items = getCartItems();
if (empty($items)) {
    redirect('/cart.php');
}
$subtotal = calculateCartTotal();
$couponResult = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!checkCsrfToken($_POST['csrf_token'] ?? '')) {
        flash('error', 'Invalid request.');
        redirect('/checkout.php');
    }
    $countryCode = $_POST['country_code'] ?? '91';
    $phoneInput = $_POST['phone'] ?? '';
    $phoneValidation = validateInternationalPhone($countryCode, $phoneInput);
    if (!empty($phoneValidation['error'])) {
        flash('error', $phoneValidation['error']);
        redirect('/checkout.php');
    }
    $address = [
        'line1' => $_POST['address_line1'] ?? '',
        'line2' => $_POST['address_line2'] ?? '',
        'city' => $_POST['city'] ?? '',
        'state' => $_POST['state'] ?? '',
        'zipcode' => $_POST['zipcode'] ?? '',
        'phone' => $phoneValidation['formatted'],
    ];
    $paymentMethod = $_POST['payment_method'] === 'cod' ? 'COD' : 'Razorpay';
    $paymentStatus = $paymentMethod === 'COD' ? 'Pending' : 'Processing';
    $couponCode = !empty($_POST['coupon_code']) ? strtoupper(sanitize($_POST['coupon_code'])) : null;
    if ($couponCode) {
        $couponResult = applyCoupon($couponCode);
        if (!empty($couponResult['error'])) {
            flash('error', $couponResult['error']);
            redirect('/checkout.php');
        }
    }
    $orderId = createOrder($_SESSION['user_id'], $address, [], $paymentMethod, $paymentStatus, $couponCode);
    if ($orderId) {
        if ($paymentMethod === 'Razorpay') {
            $_SESSION['order_id'] = $orderId;
            redirect('/payments/razorpay.php');
        }
        flash('success', 'Order placed successfully with Cash on Delivery.');
        redirect('/user/orders.php');
    }
}
?>
<?php include __DIR__ . '/includes/header.php'; ?>
<div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
    <div class="grid gap-8 lg:grid-cols-[1.2fr_0.8fr]">
        <section class="rounded-[2rem] border border-slate-200 bg-white p-8 shadow-sm">
            <h1 class="text-3xl font-semibold text-slate-900">Checkout</h1>
            <?php if ($error = flash('error')): ?><div class="mt-6 rounded-3xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-700"><?= sanitize($error) ?></div><?php endif; ?>
            <form method="post" class="mt-8 space-y-6">
                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                <div class="grid gap-6 sm:grid-cols-2">
                    <label class="space-y-2 text-sm font-medium text-slate-700">Full Name<input type="text" name="name" readonly value="<?= sanitize($user['name'] ?? '') ?>" class="w-full rounded-3xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 outline-none" /></label>
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-slate-700">Phone</label>
                        <div class="flex gap-2">
                            <select name="country_code" class="w-24 rounded-3xl border border-slate-200 bg-slate-50 px-3 py-3 text-slate-900 outline-none focus:border-slate-900">
                                <?php $selected = isset($_POST['country_code']) ? $_POST['country_code'] : '91'; ?>
                                <?php foreach (getCountryCodes() as $code => $label): ?>
                                    <option value="<?= $code ?>" <?= $selected == $code ? 'selected' : '' ?>><?= $code ?></option>
                                <?php endforeach; ?>
                            </select>
                            <input type="text" name="phone" required placeholder="Phone number" value="<?= sanitize($_POST['phone'] ?? '') ?>" class="flex-1 rounded-3xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 outline-none focus:border-slate-900" />
                        </div>
                    </div>
                </div>
                <div class="grid gap-6">
                    <label class="space-y-2 text-sm font-medium text-slate-700">Address Line 1<input type="text" name="address_line1" required class="w-full rounded-3xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 outline-none" /></label>
                    <label class="space-y-2 text-sm font-medium text-slate-700">Address Line 2<input type="text" name="address_line2" class="w-full rounded-3xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 outline-none" /></label>
                    <div class="grid gap-6 sm:grid-cols-3">
                        <label class="space-y-2 text-sm font-medium text-slate-700">City<input type="text" name="city" required class="w-full rounded-3xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 outline-none" /></label>
                        <label class="space-y-2 text-sm font-medium text-slate-700">State<input type="text" name="state" required class="w-full rounded-3xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 outline-none" /></label>
                        <label class="space-y-2 text-sm font-medium text-slate-700">Pincode<input type="text" name="zipcode" required pattern="\d{6}" class="w-full rounded-3xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 outline-none" /></label>
                    </div>
                </div>
                <div class="rounded-[1.75rem] border border-slate-200 bg-slate-50 p-5">
                    <h2 class="text-lg font-semibold text-slate-900">Coupon</h2>
                    <div class="mt-4 flex flex-col gap-3 sm:flex-row">
                        <input name="coupon_code" type="text" placeholder="Enter coupon code" class="w-full rounded-3xl border border-slate-200 bg-white px-4 py-3 text-slate-900 outline-none focus:border-slate-900" />
                        <button class="inline-flex items-center justify-center rounded-3xl bg-slate-900 px-6 py-3 text-sm font-semibold text-white hover:bg-slate-800">Apply</button>
                    </div>
                    <?php if ($couponResult && !empty($couponResult['discount'])): ?>
                        <div class="mt-4 rounded-3xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-700">Coupon applied! Discount &#8377;<?= number_format($couponResult['discount'], 2) ?>.</div>
                    <?php endif; ?>
                </div>
                <div class="rounded-[1.75rem] border border-slate-200 bg-slate-50 p-5">
                    <h2 class="text-lg font-semibold text-slate-900">Payment</h2>
                    <div class="mt-4 space-y-3">
                        <label class="flex items-center gap-3 rounded-3xl border border-slate-200 bg-white px-4 py-3 text-sm font-medium text-slate-900">
                            <input type="radio" name="payment_method" value="razorpay" checked class="h-4 w-4 text-brand accent-brand" /> Razorpay
                        </label>
                        <label class="flex items-center gap-3 rounded-3xl border border-slate-200 bg-white px-4 py-3 text-sm font-medium text-slate-900">
                            <input type="radio" name="payment_method" value="cod" class="h-4 w-4 text-brand accent-brand" /> Cash on Delivery (&#8377;50 advance)
                        </label>
                    </div>
                </div>
                <button type="submit" class="inline-flex w-full items-center justify-center rounded-3xl bg-brand px-6 py-4 text-sm font-semibold text-white hover:bg-slate-800">Place Order</button>
            </form>
        </section>
        <aside class="space-y-6 rounded-[2rem] border border-slate-200 bg-white p-8 shadow-sm">
            <h2 class="text-2xl font-semibold text-slate-900">Order Summary</h2>
            <div class="space-y-4">
                <?php foreach ($items as $item): ?>
                    <div class="rounded-3xl bg-slate-50 p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="font-semibold text-slate-900"><?= sanitize($item['name']) ?></p>
                                <p class="mt-1 text-sm text-slate-500">Qty <?= (int)$item['quantity'] ?></p>
                                <?php if (!empty($item['box_id'])): ?>
                                    <div class="mt-3 flex items-center gap-3 rounded-2xl bg-white p-3">
                                        <img src="<?= sanitize(resolveAssetUrl($item['box_image'] ?: 'assets/images/cartier-box.svg')) ?>" alt="<?= sanitize($item['box_name']) ?>" class="h-12 w-12 object-contain">
                                        <div>
                                            <p class="text-sm font-medium text-slate-900"><?= sanitize($item['box_name']) ?></p>
                                            <p class="text-xs text-slate-500">Box qty <?= (int)$item['box_quantity'] ?> x &#8377;<?= number_format($item['box_price'], 2) ?></p>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <p class="text-slate-900">&#8377;<?= number_format($item['line_total'], 2) ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="rounded-[1.75rem] border border-slate-200 bg-slate-50 p-6">
                <div class="flex items-center justify-between text-sm text-slate-600"><span>Subtotal</span><span>&#8377;<?= number_format($subtotal, 2) ?></span></div>
                <?php if ($couponResult && !empty($couponResult['discount'])): ?>
                    <div class="mt-3 flex items-center justify-between text-sm text-slate-600"><span>Discount</span><span>-&#8377;<?= number_format($couponResult['discount'], 2) ?></span></div>
                <?php endif; ?>
                <div class="mt-5 flex items-center justify-between text-xl font-semibold text-slate-900"><span>Total</span><span>&#8377;<?= number_format($couponResult['total'] ?? $subtotal, 2) ?></span></div>
            </div>
        </aside>
    </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
