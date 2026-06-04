USE new_db;

-- USERS
INSERT INTO users (id, name, phone, email, password, role)
VALUES
(
    1,
    'Admin',
    '+919876543210',
    'admin@watchstore.com',
    '$2y$10$HuZFYH11qin0LzYkV1W3luJX5r8xvViWPYO7dJyZhZDnn0vSeXpJ6',
    'admin'
)
ON DUPLICATE KEY UPDATE
    name = VALUES(name),
    phone = VALUES(phone),
    email = VALUES(email),
    role = VALUES(role);

-- CATEGORIES
INSERT INTO categories (id, name, category_image)
VALUES
(1, 'Luxury Watches', 'luxury-category.jpg'),
(2, 'Smart Watches', 'smart-category.jpg'),
(3, 'Sports Watches', 'sports-category.jpg'),
(4, 'Classic Watches', 'classic-category.jpg')
ON DUPLICATE KEY UPDATE
    name = VALUES(name),
    category_image = VALUES(category_image);

-- PRODUCTS
INSERT INTO products
(id, name, description, category, price, offer_price, stock, is_best_seller, demo_image, images, gallery)
VALUES
(
    1,
    'Rolex Submariner',
    'Premium Swiss luxury diving watch',
    1,
    120000.00,
    110000.00,
    15,
    1,
    'rolex-demo.jpg',
    'rolex-main.jpg',
    '["rolex-1.jpg","rolex-2.jpg","rolex-3.jpg"]'
),
(
    2,
    'Omega Seamaster',
    'Luxury automatic wrist watch',
    1,
    95000.00,
    90000.00,
    20,
    0,
    'omega-demo.jpg',
    'omega-main.jpg',
    '["omega-1.jpg","omega-2.jpg","omega-3.jpg"]'
),
(
    3,
    'Apple Watch Ultra',
    'Advanced smartwatch with GPS tracking',
    2,
    85000.00,
    79999.00,
    30,
    1,
    'apple-demo.jpg',
    'apple-main.jpg',
    '["apple-1.jpg","apple-2.jpg","apple-3.jpg"]'
),
(
    4,
    'Samsung Galaxy Watch',
    'Smart fitness watch with health monitoring',
    2,
    35000.00,
    31999.00,
    40,
    0,
    'samsung-demo.jpg',
    'samsung-main.jpg',
    '["samsung-1.jpg","samsung-2.jpg","samsung-3.jpg"]'
),
(
    5,
    'Casio G-Shock',
    'Shock resistant sports watch',
    3,
    15000.00,
    12999.00,
    50,
    1,
    'gshock-demo.jpg',
    'gshock-main.jpg',
    '["gshock-1.jpg","gshock-2.jpg","gshock-3.jpg"]'
),
(
    6,
    'Titan Edge',
    'Ultra slim formal watch',
    4,
    12000.00,
    10999.00,
    40,
    1,
    'titan-demo.jpg',
    'titan-main.jpg',
    '["titan-1.jpg","titan-2.jpg","titan-3.jpg"]'
)
ON DUPLICATE KEY UPDATE
    name = VALUES(name),
    description = VALUES(description),
    category = VALUES(category),
    price = VALUES(price),
    offer_price = VALUES(offer_price),
    stock = VALUES(stock),
    is_best_seller = VALUES(is_best_seller),
    demo_image = VALUES(demo_image),
    images = VALUES(images),
    gallery = VALUES(gallery);

-- BOX OPTIONS
INSERT INTO box_options (id, name, image, price, is_active)
VALUES
(1, 'Standard Box', 'standard-box.jpg', 0.00, 1),
(2, 'Premium Gift Box', 'premium-box.jpg', 299.00, 1),
(3, 'Luxury Wooden Box', 'wooden-box.jpg', 999.00, 1)
ON DUPLICATE KEY UPDATE
    name = VALUES(name),
    image = VALUES(image),
    price = VALUES(price),
    is_active = VALUES(is_active);

-- PAGE META
INSERT INTO page_meta
(page_key, page_name, path, title, description, keywords, is_active)
VALUES
('home', 'Home', '/', 'Luxury Watch Store', 'Premium watches at best prices', 'watches,luxury watches,smart watches', 1),
('collection', 'Collections', '/collection', 'Shop Watches', 'Browse all watches', 'shop,watches', 1),
('about', 'About', '/about', 'About Us', 'Learn more about our company', 'about', 1),
('contact', 'Contact', '/contact', 'Contact Us', 'Get in touch with us', 'contact', 1),
('cart', 'Cart', '/cart', 'Shopping Cart', 'Review your selected products and checkout securely.', 'cart,checkout', 1),
('checkout', 'Checkout', '/checkout', 'Checkout', 'Complete your order securely.', 'checkout,payment', 1),
('login', 'Login', '/login', 'Login', 'Log in to your account.', 'login,account', 1),
('register', 'Register', '/register', 'Create Account', 'Create your customer account.', 'register,account', 1),
('product', 'Product Detail', '/product', '{product_name}', 'Buy {product_name} online for {product_price}.', 'product,watches', 1)
ON DUPLICATE KEY UPDATE
    page_name = VALUES(page_name),
    path = VALUES(path),
    title = VALUES(title),
    description = VALUES(description),
    keywords = VALUES(keywords),
    is_active = VALUES(is_active);

-- COUPONS
INSERT INTO coupons
(id, code, type, value, min_order, max_discount, starts_at, expiry_date, usage_limit, per_user_limit, used_count, status)
VALUES
(
    1,
    'WELCOME10',
    'percent',
    10.00,
    1000.00,
    5000.00,
    NULL,
    '2027-12-31',
    100,
    1,
    0,
    1
),
(
    2,
    'FLAT2000',
    'fixed',
    2000.00,
    20000.00,
    2000.00,
    NULL,
    '2027-12-31',
    50,
    NULL,
    0,
    1
),
(
    3,
    'ALLTIME5',
    'percent',
    5.00,
    0.00,
    0.00,
    NULL,
    NULL,
    NULL,
    NULL,
    0,
    1
),
(
    4,
    'ONETIME100',
    'fixed',
    100.00,
    500.00,
    100.00,
    NULL,
    NULL,
    NULL,
    1,
    0,
    1
),
(
    5,
    'LIMITED25',
    'percent',
    25.00,
    1000.00,
    1000.00,
    NULL,
    '2027-12-31',
    25,
    1,
    0,
    1
)
ON DUPLICATE KEY UPDATE
    code = VALUES(code),
    type = VALUES(type),
    value = VALUES(value),
    min_order = VALUES(min_order),
    max_discount = VALUES(max_discount),
    starts_at = VALUES(starts_at),
    expiry_date = VALUES(expiry_date),
    usage_limit = VALUES(usage_limit),
    per_user_limit = VALUES(per_user_limit),
    status = VALUES(status);

-- COUPON USER RULES
INSERT INTO coupon_users (coupon_id, user_id, is_allowed, used_count)
VALUES
(5, 1, 1, 0)
ON DUPLICATE KEY UPDATE
    is_allowed = VALUES(is_allowed);

-- CONTACT MESSAGES
INSERT INTO contact_messages (id, name, email, phone, message)
VALUES
(
    1,
    'Karan Patel',
    'karan@gmail.com',
    '9876500001',
    'Do you provide international shipping?'
),
(
    2,
    'Neha Gupta',
    'neha@gmail.com',
    '9876500002',
    'Need quotation for bulk watch orders.'
)
ON DUPLICATE KEY UPDATE
    name = VALUES(name),
    email = VALUES(email),
    phone = VALUES(phone),
    message = VALUES(message);
