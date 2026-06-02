<?php
require_once __DIR__ . '/db.php';

function sanitize($value) {
    return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
}

function redirect($path) {
    header('Location: ' . BASE_URL . $path);
    exit;
}

function isLoggedIn() {
    return !empty($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

function getCurrentUser() {
    global $pdo;
    if (!isLoggedIn()) return null;
    $stmt = $pdo->prepare('SELECT id, name, email, phone FROM users WHERE id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}

function flash($key, $message = null) {
    if ($message === null) {
        if (!empty($_SESSION['flash'][$key])) {
            $msg = $_SESSION['flash'][$key];
            unset($_SESSION['flash'][$key]);
            return $msg;
        }
        return null;
    }
    $_SESSION['flash'][$key] = $message;
}

function getCategories() {
    global $pdo;
    $stmt = $pdo->query('SELECT id, name FROM categories ORDER BY name');
    return $stmt->fetchAll();
}

function getCartCount() {
    if (empty($_SESSION['cart'])) return 0;
    return array_sum(array_column($_SESSION['cart'], 'quantity'));
}

function getCartItems() {
    if (empty($_SESSION['cart'])) return [];
    global $pdo;
    $ids = array_keys($_SESSION['cart']);
    if (!$ids) return [];
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
    $stmt->execute($ids);
    $products = $stmt->fetchAll();
    foreach ($products as &$product) {
        $product['quantity'] = $_SESSION['cart'][$product['id']]['quantity'];
    }
    return $products;
}

function calculateCartTotal() {
    $items = getCartItems();
    $total = 0;
    foreach ($items as $item) {
        $total += $item['price'] * $item['quantity'];
    }
    return $total;
}

function applyCoupon($code) {
    global $pdo;
    $code = strtoupper(sanitize($code));
    $stmt = $pdo->prepare('SELECT * FROM coupons WHERE code = ? AND status = 1 AND expiry_date >= CURDATE() AND used_count < usage_limit');
    $stmt->execute([$code]);
    $coupon = $stmt->fetch();
    if (!$coupon) return ['error' => 'Invalid or expired coupon.'];
    $subtotal = calculateCartTotal();
    if ($subtotal < $coupon['min_order']) {
        return ['error' => 'Order must be at least ₹' . $coupon['min_order'] . ' for this coupon.'];
    }
    $discount = 0;
    if ($coupon['type'] === 'percent') {
        $discount = round($subtotal * ($coupon['value'] / 100));
    } else {
        $discount = $coupon['value'];
    }
    if ($coupon['max_discount'] > 0) {
        $discount = min($discount, $coupon['max_discount']);
    }
    return ['coupon' => $coupon, 'discount' => $discount, 'subtotal' => $subtotal, 'total' => max(0, $subtotal - $discount)];
}

function createOrder($userId, $address, $billing, $paymentMethod, $paymentStatus, $couponCode = null) {
    global $pdo;
    $cartItems = getCartItems();
    if (!$cartItems) return false;
    $subtotal = calculateCartTotal();
    $discount = 0;
    if ($couponCode) {
        $result = applyCoupon($couponCode);
        if (empty($result['error'])) {
            $discount = $result['discount'];
        }
    }
    $totalAmount = $subtotal - $discount;
    $stmt = $pdo->prepare('INSERT INTO orders (user_id, total_amount, status, payment_method, payment_status, address_line1, address_line2, city, state, zipcode, phone, created_at, coupon_code, discount_amount) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?)');
    $stmt->execute([$userId, $totalAmount, 'Pending', $paymentMethod, $paymentStatus, sanitize($address['line1']), sanitize($address['line2']), sanitize($address['city']), sanitize($address['state']), sanitize($address['zipcode']), sanitize($address['phone']), $couponCode, $discount]);
    $orderId = $pdo->lastInsertId();
    foreach ($cartItems as $item) {
        $stmtItem = $pdo->prepare('INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)');
        $stmtItem->execute([$orderId, $item['id'], $item['quantity'], $item['price']]);
        $updateStock = $pdo->prepare('UPDATE products SET stock = GREATEST(stock - ?, 0) WHERE id = ?');
        $updateStock->execute([$item['quantity'], $item['id']]);
    }
    if ($couponCode) {
        $updateCoupon = $pdo->prepare('UPDATE coupons SET used_count = used_count + 1 WHERE code = ?');
        $updateCoupon->execute([$couponCode]);
    }
    unset($_SESSION['cart']);
    unset($_SESSION['coupon']);
    return $orderId;
}

function checkCsrfToken($token) {
    return !empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function generateCsrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}
