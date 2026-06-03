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
    $stmt = $pdo->prepare('SELECT id, name, email FROM users WHERE id = ?');
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

function createRazorpayOrder($amount, $currency = 'INR') {
    if (empty(RAZORPAY_KEY_ID) || empty(RAZORPAY_KEY_SECRET)) {
        return ['error' => 'Razorpay credentials are not configured.'];
    }
    if ($amount <= 0) {
        return ['error' => 'Invalid payment amount.'];
    }

    $payload = json_encode([
        'amount' => (int)$amount,
        'currency' => $currency,
        'receipt' => 'order_' . time(),
        'payment_capture' => 1,
    ]);

    $ch = curl_init('https://api.razorpay.com/v1/orders');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERPWD, RAZORPAY_KEY_ID . ':' . RAZORPAY_KEY_SECRET);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

    $response = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($response === false || ($status !== 200 && $status !== 201)) {
        return ['error' => 'Unable to create Razorpay order. ' . ($error ?: 'Please check your Razorpay credentials.')];
    }

    return json_decode($response, true);
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

function ensurePasswordResetTableExists() {
    global $pdo;
    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS password_reset_tokens (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            token VARCHAR(100) NOT NULL UNIQUE,
            expires_at DATETIME NOT NULL,
            used TINYINT(1) NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB"
    );
}

function sendPasswordResetEmail($email, $resetUrl) {
    $subject = 'ShopMaster Password Reset Request';
    $message = "Hello,\n\nWe received a request to reset the password for your account.\n\nPlease click the link below to reset your password:\n\n" . $resetUrl . "\n\nIf you did not request this, you can safely ignore this message.\n";
    $headers = 'From: no-reply@' . ($_SERVER['SERVER_NAME'] ?? 'localhost') . "\r\n";
    $headers .= 'Content-Type: text/plain; charset=utf-8';
    if (function_exists('mail')) {
        return mail($email, $subject, $message, $headers);
    }
    return false;
}

function generatePasswordResetToken($email) {
    global $pdo;
    $email = filter_var($email, FILTER_VALIDATE_EMAIL);
    if (!$email) {
        return ['error' => 'Please enter a valid email address.'];
    }
    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    if (!$user) {
        return ['error' => 'No account is associated with that email address.'];
    }
    ensurePasswordResetTableExists();
    $token = bin2hex(random_bytes(24));
    $expiresAt = date('Y-m-d H:i:s', time() + 3600);
    $stmt = $pdo->prepare('INSERT INTO password_reset_tokens (user_id, token, expires_at) VALUES (?, ?, ?)');
    $stmt->execute([$user['id'], $token, $expiresAt]);
    $resetUrl = BASE_URL . '/reset.php?token=' . urlencode($token);
    $emailSent = sendPasswordResetEmail($email, $resetUrl);
    return ['token' => $token, 'reset_url' => $resetUrl, 'email_sent' => $emailSent];
}

function validatePasswordResetToken($token) {
    global $pdo;
    if (empty($token)) {
        return false;
    }
    ensurePasswordResetTableExists();
    $stmt = $pdo->prepare('SELECT prt.id, prt.user_id, prt.expires_at, prt.used, u.email FROM password_reset_tokens prt JOIN users u ON u.id = prt.user_id WHERE prt.token = ? LIMIT 1');
    $stmt->execute([$token]);
    $row = $stmt->fetch();
    if (!$row || $row['used']) {
        return false;
    }
    if (strtotime($row['expires_at']) < time()) {
        return false;
    }
    return $row;
}

function resetPasswordWithToken($token, $password) {
    global $pdo;
    if (!$token || !$password) {
        return ['error' => 'Invalid reset request.'];
    }
    $row = validatePasswordResetToken($token);
    if (!$row) {
        return ['error' => 'This password reset link is invalid or has expired.'];
    }
    if (strlen($password) < 8) {
        return ['error' => 'Password must be at least 8 characters long.'];
    }
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare('UPDATE users SET password = ? WHERE id = ?');
    $stmt->execute([$hash, $row['user_id']]);
    $stmt = $pdo->prepare('UPDATE password_reset_tokens SET used = 1 WHERE token = ?');
    $stmt->execute([$token]);
    return ['success' => true];
}

function formatInternationalPhone($countryCode, $phone) {
    $phone = preg_replace('/\D/', '', $phone);
    if (empty($phone)) return null;
    $countryCode = ltrim($countryCode, '+');
    return '+' . $countryCode . $phone;
}

function validateInternationalPhone($countryCode, $phone) {
    $countryCode = ltrim($countryCode, '+');
    $phone = preg_replace('/\D/', '', $phone);
    if (empty($countryCode) || empty($phone)) {
        return ['error' => 'Country code and phone number are required.'];
    }
    if (strlen($countryCode) < 1 || strlen($countryCode) > 3) {
        return ['error' => 'Invalid country code.'];
    }
    if (strlen($phone) < 7 || strlen($phone) > 15) {
        return ['error' => 'Phone number must be between 7 and 15 digits.'];
    }
    return ['valid' => true, 'formatted' => '+' . $countryCode . $phone];
}

function getCountryCodes() {
    return [
        '91' => 'India (+91)',
        '1' => 'USA (+1)',
        '44' => 'UK (+44)',
        '61' => 'Australia (+61)',
        '81' => 'Japan (+81)',
        '86' => 'China (+86)',
        '33' => 'France (+33)',
        '49' => 'Germany (+49)',
        '39' => 'Italy (+39)',
        '34' => 'Spain (+34)',
    ];
}
