<?php
require_once __DIR__ . '/../includes/functions.php';
if (empty($_SESSION['order_id'])) {
    redirect('/checkout.php');
}
$user = getCurrentUser();
$orderId = (int)$_SESSION['order_id'];
$stmt = $pdo->prepare('SELECT * FROM orders WHERE id = ? AND payment_method = ?');
$stmt->execute([$orderId, 'Razorpay']);
$order = $stmt->fetch();
if (!$order) {
    redirect('/checkout.php');
}
$amountPaise = $order['total_amount'] * 100;
$data = [
    'amount' => (int)$amountPaise,
    'currency' => 'INR',
    'receipt' => 'order_' . $orderId,
    'payment_capture' => 1,
];
$payload = json_encode($data);
$ch = curl_init('https://api.razorpay.com/v1/orders');
curl_setopt($ch, CURLOPT_USERPWD, RAZORPAY_KEY_ID . ':' . RAZORPAY_KEY_SECRET);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
$orderData = json_decode($response, true);
if ($httpCode !== 200 || empty($orderData['id'])) {
    die('Unable to create Razorpay order. Please try again later.');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Razorpay Checkout</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="d-flex align-items-center justify-content-center vh-100 bg-light">
<div class="card p-4 shadow-sm" style="width:100%; max-width:500px;">
    <h4 class="mb-3">Complete your payment</h4>
    <p>Order #<?= $orderId ?> · Amount ₹<?= number_format($order['total_amount'], 2) ?></p>
    <button id="payButton" class="btn btn-primary w-100">Pay with Razorpay</button>
</div>
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
    document.getElementById('payButton').addEventListener('click', function (e) {
        var options = {
            key: '<?= RAZORPAY_KEY_ID ?>',
            amount: '<?= (int)$amountPaise ?>',
            currency: 'INR',
            name: 'ShopMaster',
            description: 'Order #<?= $orderId ?>',
            order_id: '<?= $orderData['id'] ?>',
            handler: function (response) {
                var form = document.createElement('form');
                form.method = 'POST';
                form.action = '<?= BASE_URL ?>/payments/payment-success.php';
                var fields = {
                    razorpay_payment_id: response.razorpay_payment_id,
                    razorpay_order_id: response.razorpay_order_id,
                    razorpay_signature: response.razorpay_signature,
                    order_id: '<?= $orderId ?>'
                };
                for (var name in fields) {
                    var input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = name;
                    input.value = fields[name];
                    form.appendChild(input);
                }
                document.body.appendChild(form);
                form.submit();
            },
            prefill: {
                name: '<?= sanitize($user['name'] ?? 'Guest') ?>',
                email: '<?= sanitize($user['email'] ?? '') ?>'
            },
            theme: { color: '#0d6efd' }
        };
        var rzp = new Razorpay(options);
        rzp.open();
        e.preventDefault();
    });
</script>
</body>
</html>
