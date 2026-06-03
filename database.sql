CREATE DATABASE IF NOT EXISTS new_db
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE new_db;

-- USERS
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    phone VARCHAR(20) DEFAULT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('user','admin') NOT NULL DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- PASSWORD RESET TOKENS
CREATE TABLE IF NOT EXISTS password_reset_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token VARCHAR(100) NOT NULL UNIQUE,
    expires_at DATETIME NOT NULL,
    used TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- CATEGORIES
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    category_image VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- PRODUCTS
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    description TEXT,
    category INT DEFAULT NULL,
    price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    offer_price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    stock INT NOT NULL DEFAULT 0,
    demo_image VARCHAR(255) DEFAULT NULL,
    images VARCHAR(255) DEFAULT NULL,
    gallery LONGTEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category) REFERENCES categories(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- BOX OPTIONS
CREATE TABLE IF NOT EXISTS box_options (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  image VARCHAR(255) DEFAULT NULL,
  price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_box_options_name (name),
  CONSTRAINT chk_box_options_price_non_negative CHECK (price >= 0)
) ENGINE=InnoDB;

-- ORDERS
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    status VARCHAR(50) NOT NULL DEFAULT 'Pending',
    payment_method VARCHAR(50) NOT NULL,
    payment_status VARCHAR(50) NOT NULL DEFAULT 'Pending',

    address_line1 VARCHAR(255) NOT NULL,
    address_line2 VARCHAR(255) DEFAULT NULL,
    city VARCHAR(100) NOT NULL,
    state VARCHAR(100) NOT NULL,
    zipcode VARCHAR(20) NOT NULL,
    phone VARCHAR(20) NOT NULL,

    coupon_code VARCHAR(50) DEFAULT NULL,
    discount_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ORDER ITEMS
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,

    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- COUPONS
CREATE TABLE IF NOT EXISTS coupons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NOT NULL UNIQUE,
    type ENUM('percent','fixed') NOT NULL DEFAULT 'fixed',
    value DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    min_order DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    max_discount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    expiry_date DATE NOT NULL,
    usage_limit INT NOT NULL DEFAULT 1,
    used_count INT NOT NULL DEFAULT 0,
    status TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- CONTACT MESSAGES
CREATE TABLE IF NOT EXISTS contact_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(150) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- DEFAULT ADMIN USER
INSERT INTO users (name, phone, email, password, role)
VALUES (
    'Admin User',
    '9876543210',
    'admin@shopmaster.com',
    '$2y$10$ViCqcab/Z3PTpXi9PygiAOIDu9vVYL4zTRy6hszaPMtqW6WBmykDi',
    'admin'
);

-- CATEGORIES
INSERT INTO categories (name, category_image) VALUES
('Electronics', 'electronics.jpg'),
('Fashion', 'fashion.jpg'),
('Home & Kitchen', 'home-kitchen.jpg');

-- PRODUCTS
INSERT INTO products
(name, description, category, price, stock, images, gallery)
VALUES
(
'Wireless Earbuds',
'Noise-cancelling earbuds with long battery life.',
1,
1499.00,
35,
'earbuds.jpg',
'["earbuds.jpg","earbuds2.jpg"]'
),
(
'Smart Watch',
'Track your fitness and stay connected.',
1,
2999.00,
22,
'smartwatch.jpg',
'["smartwatch.jpg","smartwatch2.jpg"]'
),
(
'Denim Jacket',
'Classic denim jacket with comfortable fit.',
2,
2199.00,
18,
'jacket.jpg',
'["jacket.jpg","jacket2.jpg"]'
),
(
'Coffee Maker',
'Brew perfect coffee quickly at home.',
3,
2499.00,
14,
'coffee-maker.jpg',
'["coffee-maker.jpg","coffee-maker2.jpg"]'
);

-- COUPONS
INSERT INTO coupons
(code, type, value, min_order, max_discount, expiry_date, usage_limit)
VALUES
(
'WELCOME50',
'fixed',
50,
500,
50,
DATE_ADD(CURDATE(), INTERVAL 30 DAY),
100
),
(
'SAVE10',
'percent',
10,
1000,
200,
DATE_ADD(CURDATE(), INTERVAL 60 DAY),
200
);

-- CONTACT MESSAGES
INSERT INTO contact_messages
(name, email, phone, message)
VALUES
(
'Priya Singh',
'priya@example.com',
'9123456780',
'I need help with my order status.'
),
(
'Amit Kumar',
'amit@example.com',
'9988776655',
'Can you update me on the delivery timeline?'
);