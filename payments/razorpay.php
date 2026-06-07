<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/config.php';
if (!isLoggedIn() || empty($_SESSION['order_id'])) {
    redirect('/checkout.php');
}
$user = getCurrentUser();
$stmt = $pdo->prepare('SELECT * FROM orders WHERE id = ? AND user_id = ?');
$stmt->execute([$_SESSION['order_id'], $_SESSION['user_id']]);
$order = $stmt->fetch();
if (!$order) {
    redirect('/checkout.php');
}
$amount = (int)round($order['total_amount'] * 100);
$razorpayOrder = createRazorpayOrder($amount);
if (empty($razorpayOrder['id']) || !empty($razorpayOrder['error'])) {
    die('Razorpay order creation failed: ' . sanitize($razorpayOrder['error'] ?? 'Unknown error'));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Complete Payment</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { theme: { extend: { fontFamily: { body: ['Inter', 'sans-serif'] }, colors: { brand: '#1d4ed8' } } } };
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
</head>
<body class="min-h-screen bg-slate-100 text-slate-900 font-body">
    <div class="flex min-h-screen items-center justify-center px-4 py-10">
        <div class="w-full max-w-xl rounded-[2rem] bg-white p-8 shadow-xl">
            <h1 class="text-3xl font-semibold text-slate-900">Complete Your Payment</h1>
            <p class="mt-4 text-slate-600">Pay ₹<?= number_format($order['total_amount'], 2) ?> for order #<?= $order['id'] ?> securely using Razorpay.</p>
            <div class="mt-8 rounded-[1.75rem] border border-slate-200 bg-slate-50 p-6">
                <p class="text-sm font-medium text-slate-700">Order ID</p>
                <p class="mt-2 text-lg text-slate-900">#<?= $order['id'] ?></p>
                <p class="mt-4 text-sm font-medium text-slate-700">Amount</p>
                <p class="mt-2 text-2xl font-semibold text-brand">₹<?= number_format($order['total_amount'], 2) ?></p>
            </div>
            <form id="razorpay-payment-form" action="<?= publicUrl('/payments/payment-success') ?>" method="POST">
                <input type="hidden" name="razorpay_order_id" value="<?= sanitize($razorpayOrder['id']) ?>" />
                <input type="hidden" name="razorpay_payment_id" value="" />
                <input type="hidden" name="razorpay_signature" value="" />
                <input type="hidden" name="order_id" value="<?= $order['id'] ?>" />
                <button id="pay-now-button" type="button" class="mt-8 inline-flex w-full items-center justify-center rounded-3xl bg-slate-900 px-6 py-4 text-sm font-semibold text-white hover:bg-slate-800">Pay Now</button>
            </form>
        </div>
    </div>
    <script>
        const razorpayOptions = {
            key: '<?= sanitize(RAZORPAY_KEY_ID) ?>',
            amount: <?= $amount ?>,
            currency: 'INR',
            name: 'ShopMaster',
            description: 'Order #<?= $order['id'] ?>',
            order_id: '<?= sanitize($razorpayOrder['id']) ?>',
            handler: function (response) {
                document.querySelector('[name="razorpay_payment_id"]').value = response.razorpay_payment_id;
                document.querySelector('[name="razorpay_order_id"]').value = response.razorpay_order_id;
                document.querySelector('[name="razorpay_signature"]').value = response.razorpay_signature;
                document.getElementById('razorpay-payment-form').submit();
            },
            prefill: {
                name: '<?= sanitize($user['name'] ?? '') ?>',
                email: '<?= sanitize($user['email'] ?? '') ?>'
            },
            theme: {
                color: '#1d4ed8'
            }
        };

        document.getElementById('pay-now-button').addEventListener('click', function (event) {
            event.preventDefault();
            const rzp = new Razorpay(razorpayOptions);
            rzp.open();
        });
    </script>
</body>
</html>
