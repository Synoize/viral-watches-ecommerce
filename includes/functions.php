<?php
require_once __DIR__ . '/db.php';

function sanitize($value) {
    return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
}

function resolveAssetUrl($path) {
    $path = trim((string)$path);
    if ($path === '') return '';
    if (preg_match('/^(https?:)?\/\//i', $path) || strpos($path, '/') === 0) {
        return $path;
    }
    return rtrim(BASE_URL, '/') . '/' . ltrim($path, '/');
}

function saveAdminImageUpload($file, $folder, $prefix = 'image') {
    if (empty($file['name']) || empty($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
        return null;
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['error' => 'Image upload failed.'];
    }

    $allowed = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];
    $mime = mime_content_type($file['tmp_name']);
    if (!isset($allowed[$mime])) {
        return ['error' => 'Upload a JPG, PNG, or WEBP image.'];
    }

    $folder = trim(preg_replace('/[^a-z0-9_-]+/i', '-', $folder), '-');
    $uploadDir = __DIR__ . '/../assets/images/' . $folder;
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0775, true);
    }

    $filename = trim(preg_replace('/[^a-z0-9_-]+/i', '-', $prefix), '-') . '-' . date('YmdHis') . '-' . bin2hex(random_bytes(4)) . '.' . $allowed[$mime];
    $target = $uploadDir . DIRECTORY_SEPARATOR . $filename;
    if (!move_uploaded_file($file['tmp_name'], $target)) {
        return ['error' => 'Could not save uploaded image.'];
    }

    return ['path' => 'assets/images/' . $folder . '/' . $filename];
}

function saveAdminVideoUpload($file, $folder, $prefix = 'video') {
    if (empty($file['name']) || empty($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
        return null;
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['error' => 'Video upload failed.'];
    }

    $allowed = [
        'video/mp4' => 'mp4',
        'video/webm' => 'webm',
        'video/quicktime' => 'mov',
        'video/x-m4v' => 'm4v',
    ];
    $mime = mime_content_type($file['tmp_name']);
    if (!isset($allowed[$mime])) {
        return ['error' => 'Upload an MP4, WEBM, MOV, or M4V video.'];
    }

    $folder = trim(preg_replace('/[^a-z0-9_-]+/i', '-', $folder), '-');
    $uploadDir = __DIR__ . '/../assets/videos/' . $folder;
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0775, true);
    }

    $filename = trim(preg_replace('/[^a-z0-9_-]+/i', '-', $prefix), '-') . '-' . date('YmdHis') . '-' . bin2hex(random_bytes(4)) . '.' . $allowed[$mime];
    $target = $uploadDir . DIRECTORY_SEPARATOR . $filename;
    if (!move_uploaded_file($file['tmp_name'], $target)) {
        return ['error' => 'Could not save uploaded video.'];
    }

    return ['path' => 'assets/videos/' . $folder . '/' . $filename];
}

function normalizeMultipleUploads($files) {
    if (empty($files['name']) || !is_array($files['name'])) return [];
    $uploads = [];
    foreach ($files['name'] as $index => $name) {
        $uploads[] = [
            'name' => $name,
            'type' => $files['type'][$index] ?? '',
            'tmp_name' => $files['tmp_name'][$index] ?? '',
            'error' => $files['error'][$index] ?? UPLOAD_ERR_NO_FILE,
            'size' => $files['size'][$index] ?? 0,
        ];
    }
    return $uploads;
}

function ensurePageMetaTableExists() {
    global $pdo;
    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS page_meta (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            page_key VARCHAR(80) NOT NULL,
            page_name VARCHAR(150) NOT NULL,
            path VARCHAR(180) NOT NULL,
            title VARCHAR(255) NOT NULL,
            description TEXT DEFAULT NULL,
            keywords VARCHAR(255) DEFAULT NULL,
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY uq_page_meta_key (page_key),
            UNIQUE KEY uq_page_meta_path (path)
        ) ENGINE=InnoDB"
    );
}

function seedDefaultPageMeta() {
    global $pdo;
    ensurePageMetaTableExists();

    $defaults = [
        ['home', 'Home', '/', 'ShopMaster | Premium Watches and Accessories', 'Shop premium watches, accessories, gift boxes, and secure online deals at ShopMaster.', 'watches, accessories, online shopping'],
        ['collection', 'Collections', '/collection', 'Collections | ShopMaster', 'Browse ShopMaster collections by category, price, and latest available products.', 'collections, products, watches'],
        ['about', 'About', '/about', 'About ShopMaster', 'Learn about ShopMaster, our ecommerce experience, product quality, and customer support.', 'about shopmaster, ecommerce'],
        ['contact', 'Contact', '/contact', 'Contact ShopMaster Support', 'Contact ShopMaster for order help, product questions, payments, returns, and support.', 'contact, support, help'],
        ['cart', 'Cart', '/cart', 'Shopping Cart | ShopMaster', 'Review your selected products, box options, quantities, and order subtotal.', 'cart, checkout'],
        ['wishlist', 'Wishlist', '/wishlist', 'Wishlist | ShopMaster', 'Save your favorite ShopMaster products and return to them later.', 'wishlist, saved products, favorites'],
        ['checkout', 'Checkout', '/checkout', 'Checkout | ShopMaster', 'Complete your ShopMaster order with secure payment and delivery details.', 'checkout, payment'],
        ['login', 'Login', '/login', 'Login | ShopMaster', 'Log in to your ShopMaster account to manage orders and checkout faster.', 'login, account'],
        ['register', 'Register', '/register', 'Create Account | ShopMaster', 'Create a ShopMaster account for faster checkout and order management.', 'register, account'],
        ['product', 'Product Detail', '/product', '{product_name} | ShopMaster', 'Buy {product_name} online for {product_price}. Choose gift box options and checkout securely.', 'product, watch, box'],
    ];

    $stmt = $pdo->prepare(
        'INSERT INTO page_meta (page_key, page_name, path, title, description, keywords, is_active)
         VALUES (?, ?, ?, ?, ?, ?, 1)
         ON DUPLICATE KEY UPDATE
            page_name = VALUES(page_name),
            path = VALUES(path)'
    );
    foreach ($defaults as $meta) {
        $stmt->execute($meta);
    }
}

function normalizePageMetaPath($path) {
    $path = parse_url($path, PHP_URL_PATH) ?: '/';
    $basePrefix = defined('BASE_URL') ? rtrim(BASE_URL, '/') : '';
    if ($basePrefix !== '' && strpos($path, $basePrefix) === 0) {
        $path = substr($path, strlen($basePrefix));
    }
    $path = '/' . trim($path, '/');
    if ($path === '/index.php' || $path === '/index' || $path === '') return '/';
    if (substr($path, -4) === '.php') {
        $path = substr($path, 0, -4);
    }
    if (strpos($path, '/collection/') === 0) return '/collection';
    if ($path === '/product') return '/product';
    return rtrim($path, '/') ?: '/';
}

function applyPageMetaTokens($value, $tokens = []) {
    $value = (string)$value;
    foreach ($tokens as $key => $tokenValue) {
        $value = str_replace('{' . $key . '}', (string)$tokenValue, $value);
    }
    return $value;
}

function getPageMeta($path = null, $overrides = []) {
    global $pdo;
    ensurePageMetaTableExists();

    $normalizedPath = normalizePageMetaPath($path ?: ($_SERVER['REQUEST_URI'] ?? '/'));
    $stmt = $pdo->prepare('SELECT * FROM page_meta WHERE path = ? AND is_active = 1 LIMIT 1');
    $stmt->execute([$normalizedPath]);
    $meta = $stmt->fetch();

    if (!$meta) {
        $meta = [
            'title' => 'ShopMaster',
            'description' => 'Shop products securely at ShopMaster.',
            'keywords' => '',
        ];
    }

    $tokens = $overrides['tokens'] ?? [];
    $title = $overrides['title'] ?? applyPageMetaTokens($meta['title'] ?? 'ShopMaster', $tokens);
    $description = $overrides['description'] ?? applyPageMetaTokens($meta['description'] ?? '', $tokens);
    $keywords = $overrides['keywords'] ?? applyPageMetaTokens($meta['keywords'] ?? '', $tokens);

    return [
        'title' => trim($title) ?: 'ShopMaster',
        'description' => trim($description) ?: 'Shop products securely at ShopMaster.',
        'keywords' => trim($keywords),
    ];
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

function ensureProductBestSellerColumn() {
    global $pdo;
    static $checked = false;
    if ($checked) return true;

    try {
        $columns = $pdo->query('SHOW COLUMNS FROM products')->fetchAll(PDO::FETCH_COLUMN);
        if (!in_array('is_best_seller', $columns, true)) {
            $pdo->exec('ALTER TABLE products ADD COLUMN is_best_seller TINYINT(1) NOT NULL DEFAULT 0 AFTER stock');
        }
        $checked = true;
        return true;
    } catch (Throwable $e) {
        return false;
    }
}

function ensureProductVideosTableExists() {
    global $pdo;
    try {
        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS products_video (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                product_id INT NOT NULL,
                file_path VARCHAR(255) NOT NULL,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                KEY idx_products_video_product (product_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );
        return true;
    } catch (Throwable $e) {
        return false;
    }
}

function getCategoryBySlug($slug) {
    global $pdo;
    $stmt = $pdo->query('SELECT id, name FROM categories ORDER BY name');
    $categories = $stmt->fetchAll();
    $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', trim($slug)));
    foreach ($categories as $cat) {
        $catSlug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', trim($cat['name'])));
        if ($catSlug === $slug) return $cat;
    }
    return null;
}

function getCartCount() {
    if (empty($_SESSION['cart'])) return 0;
    return array_sum(array_column($_SESSION['cart'], 'quantity'));
}

function ensureWishlistTableExists() {
    global $pdo;

    try {
        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS wishlists (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                product_id INT NOT NULL,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY uq_wishlist_user_product (user_id, product_id),
                KEY idx_wishlist_user (user_id),
                KEY idx_wishlist_product (product_id),
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );
        return true;
    } catch (Throwable $e) {
        return false;
    }
}

function getWishlistCount($userId = null) {
    global $pdo;
    $userId = $userId ?: ($_SESSION['user_id'] ?? 0);
    if (!$userId || !ensureWishlistTableExists()) return 0;

    $stmt = $pdo->prepare('SELECT COUNT(*) FROM wishlists WHERE user_id = ?');
    $stmt->execute([(int)$userId]);
    return (int)$stmt->fetchColumn();
}

function getWishlistProductIds($productIds = [], $userId = null) {
    global $pdo;
    $userId = $userId ?: ($_SESSION['user_id'] ?? 0);
    if (!$userId || !ensureWishlistTableExists()) return [];

    $productIds = array_values(array_unique(array_filter(array_map('intval', $productIds))));
    if (!$productIds) return [];

    $placeholders = implode(',', array_fill(0, count($productIds), '?'));
    $stmt = $pdo->prepare("SELECT product_id FROM wishlists WHERE user_id = ? AND product_id IN ($placeholders)");
    $stmt->execute(array_merge([(int)$userId], $productIds));
    return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
}

function isProductInWishlist($productId, $userId = null) {
    return in_array((int)$productId, getWishlistProductIds([(int)$productId], $userId), true);
}

function addWishlistItem($productId, $userId = null) {
    global $pdo;
    $userId = $userId ?: ($_SESSION['user_id'] ?? 0);
    $productId = (int)$productId;
    if (!$userId) return ['error' => 'Please login to add products to your wishlist.'];
    if ($productId <= 0 || !ensureWishlistTableExists()) return ['error' => 'Invalid wishlist request.'];

    $stmt = $pdo->prepare('SELECT id FROM products WHERE id = ? LIMIT 1');
    $stmt->execute([$productId]);
    if (!$stmt->fetchColumn()) {
        return ['error' => 'Product not found.'];
    }

    $stmt = $pdo->prepare('INSERT IGNORE INTO wishlists (user_id, product_id) VALUES (?, ?)');
    $stmt->execute([(int)$userId, $productId]);
    return ['success' => 'Product added to wishlist.'];
}

function removeWishlistItem($productId, $userId = null) {
    global $pdo;
    $userId = $userId ?: ($_SESSION['user_id'] ?? 0);
    $productId = (int)$productId;
    if (!$userId) return ['error' => 'Please login to update your wishlist.'];
    if ($productId <= 0 || !ensureWishlistTableExists()) return ['error' => 'Invalid wishlist request.'];

    $stmt = $pdo->prepare('DELETE FROM wishlists WHERE user_id = ? AND product_id = ?');
    $stmt->execute([(int)$userId, $productId]);
    return ['success' => 'Product removed from wishlist.'];
}

function getWishlistItems($userId = null) {
    global $pdo;
    $userId = $userId ?: ($_SESSION['user_id'] ?? 0);
    if (!$userId || !ensureWishlistTableExists()) return [];

    $stmt = $pdo->prepare(
        'SELECT p.*, w.created_at AS wished_at
         FROM wishlists w
         INNER JOIN products p ON p.id = w.product_id
         WHERE w.user_id = ?
         ORDER BY w.created_at DESC'
    );
    $stmt->execute([(int)$userId]);
    return $stmt->fetchAll();
}

function renderWishlistButton($productId, $isWished = false, $classes = '') {
    $productId = (int)$productId;
    $action = $isWished ? 'remove' : 'add';
    $label = $isWished ? 'Remove from wishlist' : 'Add to wishlist';
    $iconClass = $isWished ? 'fa-solid fa-heart' : 'fa-regular fa-heart';
    $stateClass = $isWished ? 'bg-rose-50 text-rose-600 border-rose-100' : 'bg-white text-slate-700 border-slate-200';
    $redirectTo = $_SERVER['REQUEST_URI'] ?? '/';

    return '<form method="post" action="' . BASE_URL . '/wishlist.php" class="' . sanitize($classes) . '">' .
        '<input type="hidden" name="action" value="' . $action . '">' .
        '<input type="hidden" name="product_id" value="' . $productId . '">' .
        '<input type="hidden" name="redirect_to" value="' . sanitize($redirectTo) . '">' .
        '<button type="submit" aria-label="' . $label . '" title="' . $label . '" class="inline-flex h-10 w-10 items-center justify-center rounded-full border shadow-sm transition hover:bg-rose-50 hover:text-rose-600 ' . $stateClass . '">' .
        '<i class="' . $iconClass . ' text-sm"></i>' .
        '</button>' .
    '</form>';
}

function ensureBoxOptionsTableExists() {
    global $pdo;
    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS box_options (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(150) NOT NULL,
            image VARCHAR(255) DEFAULT NULL,
            price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY uq_box_options_name (name),
            CONSTRAINT chk_box_options_price_non_negative CHECK (price >= 0)
        ) ENGINE=InnoDB"
    );
}

function getActiveBoxOptions() {
    global $pdo;
    ensureBoxOptionsTableExists();

    $stmt = $pdo->query('SELECT id, name, image, price FROM box_options WHERE is_active = 1 ORDER BY name');
    return $stmt->fetchAll();
}

function ensureSlidesTableExists() {
    global $pdo;
    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS slides (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            type VARCHAR(20) NOT NULL DEFAULT 'hero',
            file_path VARCHAR(255) NOT NULL,
            mobile_file_path VARCHAR(255) DEFAULT NULL,
            sort_order INT UNSIGNED NOT NULL DEFAULT 0,
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    );

    try {
        $columns = $pdo->query('SHOW COLUMNS FROM slides')->fetchAll(PDO::FETCH_COLUMN);
        if (!in_array('mobile_file_path', $columns, true)) {
            $pdo->exec('ALTER TABLE slides ADD COLUMN mobile_file_path VARCHAR(255) DEFAULT NULL AFTER file_path');
        }
        if (!in_array('sort_order', $columns, true)) {
            $pdo->exec('ALTER TABLE slides ADD COLUMN sort_order INT UNSIGNED NOT NULL DEFAULT 0 AFTER mobile_file_path');
        }
        if (!in_array('is_active', $columns, true)) {
            $pdo->exec('ALTER TABLE slides ADD COLUMN is_active TINYINT(1) NOT NULL DEFAULT 1 AFTER sort_order');
        }
    } catch (Throwable $e) {
        return false;
    }

    return true;
}

function getActiveHeroSlides() {
    global $pdo;
    ensureSlidesTableExists();

    $stmt = $pdo->prepare("SELECT * FROM slides WHERE type = ? AND is_active = 1 ORDER BY sort_order ASC, id ASC");
    $stmt->execute(['hero']);
    return $stmt->fetchAll();
}

function getCartItems() {
    if (empty($_SESSION['cart'])) return [];
    global $pdo;
    $ids = array_values(array_unique(array_map('intval', array_keys($_SESSION['cart']))));
    if (!$ids) return [];
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
    $stmt->execute($ids);
    $products = $stmt->fetchAll();
    $boxIds = [];
    foreach ($_SESSION['cart'] as $entry) {
        if (!empty($entry['box_id'])) {
            $boxIds[] = (int)$entry['box_id'];
        }
    }
    $boxesById = [];
    if ($boxIds) {
        ensureBoxOptionsTableExists();
        $boxIds = array_values(array_unique($boxIds));
        $boxPlaceholders = implode(',', array_fill(0, count($boxIds), '?'));
        $boxStmt = $pdo->prepare("SELECT id, name, image, price FROM box_options WHERE id IN ($boxPlaceholders)");
        $boxStmt->execute($boxIds);
        foreach ($boxStmt->fetchAll() as $box) {
            $boxesById[(int)$box['id']] = $box;
        }
    }
    foreach ($products as &$product) {
        $entry = $_SESSION['cart'][(int)$product['id']] ?? [];
        $product['quantity'] = max(1, (int)($entry['quantity'] ?? 1));
        $boxId = !empty($entry['box_id']) ? (int)$entry['box_id'] : null;
        $box = $boxId && isset($boxesById[$boxId]) ? $boxesById[$boxId] : null;
        $product['box_id'] = $box ? (int)$box['id'] : null;
        $product['box_name'] = $box['name'] ?? null;
        $product['box_image'] = $box['image'] ?? null;
        $product['box_price'] = $box ? (float)$box['price'] : 0;
        $product['box_quantity'] = $box ? max(1, min(10, (int)($entry['box_quantity'] ?? 1))) : 0;
        $product['box_total'] = $product['box_price'] * $product['box_quantity'];
        $product['line_total'] = ($product['price'] * $product['quantity']) + $product['box_total'];
    }
    return $products;
}

function calculateCartTotal() {
    $items = getCartItems();
    $total = 0;
    foreach ($items as $item) {
        $total += $item['line_total'];
    }
    return $total;
}

function orderItemsSupportBoxColumns() {
    global $pdo;
    static $supports = null;
    if ($supports !== null) return $supports;

    try {
        $columns = $pdo->query('SHOW COLUMNS FROM order_items')->fetchAll(PDO::FETCH_COLUMN);
        $needed = ['box_option_id', 'box_quantity', 'box_price'];
        foreach ($needed as $column) {
            if (in_array($column, $columns, true)) continue;
            if ($column === 'box_option_id') {
                $pdo->exec('ALTER TABLE order_items ADD COLUMN box_option_id INT UNSIGNED DEFAULT NULL');
            } elseif ($column === 'box_quantity') {
                $pdo->exec('ALTER TABLE order_items ADD COLUMN box_quantity INT UNSIGNED NOT NULL DEFAULT 0');
            } elseif ($column === 'box_price') {
                $pdo->exec('ALTER TABLE order_items ADD COLUMN box_price DECIMAL(10,2) NOT NULL DEFAULT 0.00');
            }
        }
        $columns = $pdo->query('SHOW COLUMNS FROM order_items')->fetchAll(PDO::FETCH_COLUMN);
        $supports = count(array_intersect($needed, $columns)) === count($needed);
    } catch (Throwable $e) {
        $supports = false;
    }

    return $supports;
}

function applyCouponLegacy($code) {
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

function ensureCouponRuleTablesExist() {
    global $pdo;

    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS coupon_users (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            coupon_id INT NOT NULL,
            user_id INT NOT NULL,
            is_allowed TINYINT(1) NOT NULL DEFAULT 0,
            used_count INT UNSIGNED NOT NULL DEFAULT 0,
            last_order_id INT DEFAULT NULL,
            last_used_at TIMESTAMP NULL DEFAULT NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY uq_coupon_user (coupon_id, user_id),
            KEY idx_coupon_users_allowed (coupon_id, is_allowed),
            FOREIGN KEY (coupon_id) REFERENCES coupons(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (last_order_id) REFERENCES orders(id) ON DELETE SET NULL
        ) ENGINE=InnoDB"
    );

    try {
        $columns = $pdo->query('SHOW COLUMNS FROM coupons')->fetchAll(PDO::FETCH_COLUMN);
        if (!in_array('starts_at', $columns, true)) {
            $pdo->exec('ALTER TABLE coupons ADD COLUMN starts_at DATE DEFAULT NULL AFTER max_discount');
        }
        if (!in_array('per_user_limit', $columns, true)) {
            $pdo->exec('ALTER TABLE coupons ADD COLUMN per_user_limit INT UNSIGNED DEFAULT NULL AFTER usage_limit');
        }
        if (in_array('expiry_date', $columns, true)) {
            $pdo->exec('ALTER TABLE coupons MODIFY expiry_date DATE DEFAULT NULL');
        }
        if (in_array('usage_limit', $columns, true)) {
            $pdo->exec('ALTER TABLE coupons MODIFY usage_limit INT UNSIGNED DEFAULT NULL');
        }
    } catch (Throwable $e) {
        return false;
    }

    return true;
}

function getCouponAllowedUserIds($couponId) {
    global $pdo;
    ensureCouponRuleTablesExist();
    $stmt = $pdo->prepare('SELECT user_id FROM coupon_users WHERE coupon_id = ? AND is_allowed = 1 ORDER BY user_id');
    $stmt->execute([(int)$couponId]);
    return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
}

function syncCouponAllowedUsers($couponId, $userIds) {
    global $pdo;
    ensureCouponRuleTablesExist();
    $couponId = (int)$couponId;
    $userIds = array_values(array_unique(array_filter(array_map('intval', $userIds))));

    $pdo->prepare('UPDATE coupon_users SET is_allowed = 0 WHERE coupon_id = ?')->execute([$couponId]);
    if (!$userIds) return;

    $stmt = $pdo->prepare(
        'INSERT INTO coupon_users (coupon_id, user_id, is_allowed)
         VALUES (?, ?, 1)
         ON DUPLICATE KEY UPDATE is_allowed = 1'
    );
    foreach ($userIds as $userId) {
        $stmt->execute([$couponId, $userId]);
    }
}

function calculateCouponDiscount($coupon, $subtotal) {
    if ($coupon['type'] === 'percent') {
        $discount = round($subtotal * ((float)$coupon['value'] / 100));
    } else {
        $discount = (float)$coupon['value'];
    }
    if ((float)$coupon['max_discount'] > 0) {
        $discount = min($discount, (float)$coupon['max_discount']);
    }
    return min($discount, $subtotal);
}

function applyCoupon($code, $userId = null) {
    global $pdo;
    ensureCouponRuleTablesExist();
    $code = strtoupper(sanitize($code));
    $stmt = $pdo->prepare('SELECT * FROM coupons WHERE code = ? AND status = 1');
    $stmt->execute([$code]);
    $coupon = $stmt->fetch();
    if (!$coupon) return ['error' => 'Invalid coupon.'];

    if (!empty($coupon['starts_at']) && $coupon['starts_at'] > date('Y-m-d')) {
        return ['error' => 'This coupon is not active yet.'];
    }
    if (!empty($coupon['expiry_date']) && $coupon['expiry_date'] < date('Y-m-d')) {
        return ['error' => 'This coupon has expired.'];
    }
    if (!empty($coupon['usage_limit']) && (int)$coupon['used_count'] >= (int)$coupon['usage_limit']) {
        return ['error' => 'This coupon usage limit has been reached.'];
    }

    if ($userId) {
        $allowedIds = getCouponAllowedUserIds($coupon['id']);
        if ($allowedIds && !in_array((int)$userId, $allowedIds, true)) {
            return ['error' => 'This coupon is not available for your account.'];
        }

        if (!empty($coupon['per_user_limit'])) {
            $stmtUsed = $pdo->prepare('SELECT COALESCE(used_count, 0) FROM coupon_users WHERE coupon_id = ? AND user_id = ?');
            $stmtUsed->execute([(int)$coupon['id'], (int)$userId]);
            if ((int)$stmtUsed->fetchColumn() >= (int)$coupon['per_user_limit']) {
                return ['error' => 'You have already used this coupon.'];
            }
        }
    }

    $subtotal = calculateCartTotal();
    if ($subtotal < $coupon['min_order']) {
        return ['error' => 'Order must be at least Rs. ' . number_format($coupon['min_order'], 2) . ' for this coupon.'];
    }
    $discount = calculateCouponDiscount($coupon, $subtotal);
    return ['coupon' => $coupon, 'discount' => $discount, 'subtotal' => $subtotal, 'total' => max(0, $subtotal - $discount)];
}

function createOrder($userId, $address, $billing, $paymentMethod, $paymentStatus, $couponCode = null) {
    global $pdo;
    $cartItems = getCartItems();
    if (!$cartItems) return false;
    $subtotal = calculateCartTotal();
    $discount = 0;
    $coupon = null;
    if ($couponCode) {
        $result = applyCoupon($couponCode, $userId);
        if (empty($result['error'])) {
            $discount = $result['discount'];
            $coupon = $result['coupon'];
        }
    }
    $totalAmount = $subtotal - $discount;
    $stmt = $pdo->prepare('INSERT INTO orders (user_id, total_amount, status, payment_method, payment_status, address_line1, address_line2, city, state, zipcode, phone, created_at, coupon_code, discount_amount) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?)');
    $stmt->execute([$userId, $totalAmount, 'Pending', $paymentMethod, $paymentStatus, sanitize($address['line1']), sanitize($address['line2']), sanitize($address['city']), sanitize($address['state']), sanitize($address['zipcode']), sanitize($address['phone']), $couponCode, $discount]);
    $orderId = $pdo->lastInsertId();
    $supportsBoxColumns = orderItemsSupportBoxColumns();
    foreach ($cartItems as $item) {
        if ($supportsBoxColumns) {
            $stmtItem = $pdo->prepare('INSERT INTO order_items (order_id, product_id, quantity, price, box_option_id, box_quantity, box_price) VALUES (?, ?, ?, ?, ?, ?, ?)');
            $stmtItem->execute([$orderId, $item['id'], $item['quantity'], $item['price'], $item['box_id'], $item['box_quantity'], $item['box_price']]);
        } else {
            $stmtItem = $pdo->prepare('INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)');
            $stmtItem->execute([$orderId, $item['id'], $item['quantity'], $item['price']]);
        }
        $updateStock = $pdo->prepare('UPDATE products SET stock = GREATEST(stock - ?, 0) WHERE id = ?');
        $updateStock->execute([$item['quantity'], $item['id']]);
    }
    if ($couponCode) {
        $updateCoupon = $pdo->prepare('UPDATE coupons SET used_count = used_count + 1 WHERE code = ?');
        $updateCoupon->execute([$couponCode]);
        if ($coupon) {
            ensureCouponRuleTablesExist();
            $stmtRedemption = $pdo->prepare(
                'INSERT INTO coupon_users (coupon_id, user_id, used_count, last_order_id, last_used_at)
                 VALUES (?, ?, 1, ?, NOW())
                 ON DUPLICATE KEY UPDATE
                    used_count = used_count + 1,
                    last_order_id = VALUES(last_order_id),
                    last_used_at = VALUES(last_used_at)'
            );
            $stmtRedemption->execute([(int)$coupon['id'], (int)$userId, (int)$orderId]);
        }
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
