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
    $address = [
        'line1' => $_POST['address_line1'] ?? '',
        'line2' => $_POST['address_line2'] ?? '',
        'city' => $_POST['city'] ?? '',
        'state' => $_POST['state'] ?? '',
        'zipcode' => $_POST['zipcode'] ?? '',
        'phone' => $_POST['phone'] ?? '',
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
<div class="container mt-5">
    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card p-4 mb-4">
                <h4>Shipping & Billing</h4>
                <?php if ($error = flash('error')): ?><div class="alert alert-danger"><?= sanitize($error) ?></div><?php endif; ?>
                <form method="post">
                    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Full Name</label>
                            <input type="text" name="name" class="form-control" value="<?= sanitize($user['name'] ?? '') ?>" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" class="form-control" required pattern="\d{10}" placeholder="10-digit phone">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Address Line 1</label>
                            <input type="text" name="address_line1" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Address Line 2</label>
                            <input type="text" name="address_line2" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">City</label>
                            <input type="text" name="city" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">State</label>
                            <input type="text" name="state" class="form-control" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Pincode</label>
                            <input type="text" name="zipcode" class="form-control" required pattern="\d{6}">
                        </div>
                    </div>
                    <div class="mt-4">
                        <h5>Coupon</h5>
                        <div class="input-group">
                            <input type="text" name="coupon_code" class="form-control" placeholder="Enter coupon code">
                            <button class="btn btn-outline-primary" type="submit">Apply</button>
                        </div>
                        <?php if ($couponResult && !empty($couponResult['discount'])): ?>
                            <div class="mt-2 alert alert-success">Coupon applied! Discount ₹<?= number_format($couponResult['discount'], 2) ?>.</div>
                        <?php endif; ?>
                    </div>
                    <div class="mt-4">
                        <h5>Payment</h5>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="payment_method" id="razorpay" value="razorpay" checked>
                            <label class="form-check-label" for="razorpay">Razorpay</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="payment_method" id="cod" value="cod">
                            <label class="form-check-label" for="cod">Cash on Delivery (₹50 advance)</label>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary mt-4">Place Order</button>
                </form>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card p-4 shadow-sm">
                <h4>Order Summary</h4>
                <ul class="list-group list-group-flush mb-3">
                    <?php foreach ($items as $item): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <strong><?= sanitize($item['name']) ?></strong>
                                <div class="small text-muted">Qty <?= $item['quantity'] ?></div>
                            </div>
                            <span>₹<?= number_format($item['price'] * $item['quantity'], 2) ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <div class="border-top pt-3">
                    <div class="d-flex justify-content-between mb-2"><span>Subtotal</span><strong>₹<?= number_format($subtotal, 2) ?></strong></div>
                    <?php if ($couponResult && !empty($couponResult['discount'])): ?>
                        <div class="d-flex justify-content-between mb-2"><span>Discount</span><strong>-₹<?= number_format($couponResult['discount'], 2) ?></strong></div>
                    <?php endif; ?>
                    <div class="d-flex justify-content-between fs-5"><span>Total</span><strong>₹<?= number_format($couponResult['total'] ?? $subtotal, 2) ?></strong></div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
