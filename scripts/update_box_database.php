<?php
require_once __DIR__ . '/../includes/config.php';

$pdo = new PDO(
    'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
    DB_USER,
    DB_PASS,
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

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

$columns = $pdo->query('SHOW COLUMNS FROM order_items')->fetchAll(PDO::FETCH_COLUMN);

if (!in_array('box_option_id', $columns, true)) {
    $pdo->exec('ALTER TABLE order_items ADD COLUMN box_option_id INT UNSIGNED DEFAULT NULL');
}

if (!in_array('box_quantity', $columns, true)) {
    $pdo->exec('ALTER TABLE order_items ADD COLUMN box_quantity INT UNSIGNED NOT NULL DEFAULT 0');
}

if (!in_array('box_price', $columns, true)) {
    $pdo->exec('ALTER TABLE order_items ADD COLUMN box_price DECIMAL(10,2) NOT NULL DEFAULT 0.00');
}

echo "Box database schema updated.\n";
