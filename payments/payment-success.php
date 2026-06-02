<?php
require_once __DIR__ . '/../includes/functions.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/index.php');
}
$paymentId = $_POST['razorpay_payment_id'] ?? '';
$orderId = $_POST['razorpay_order_id'] ?? '';
$signature = $_POST['razorpay_signature'] ?? '';
$localOrderId = (int)($_POST['order_id'] ?? 0);
if (!$paymentId || !$orderId || !$signature || !$localOrderId) {
    die('Payment information is incomplete.');
}
$generatedSignature = hash_hmac('sha256', $orderId . '|' . $paymentId, RAZORPAY_KEY_SECRET);
if (!hash_equals($generatedSignature, $signature)) {
    die('Payment verification failed.');
}
$stmt = $pdo->prepare('UPDATE orders SET payment_status = ?, status = ? WHERE id = ?');
$stmt->execute(['Paid', 'Confirmed', $localOrderId]);
unset($_SESSION['order_id']);
flash('success', 'Payment completed successfully. Your order is confirmed.');
redirect('/user/orders.php');
