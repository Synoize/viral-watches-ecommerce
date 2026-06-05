USE new_db;

-- USERS
INSERT INTO users (id, name, phone, email, password, role)
VALUES
(
    1,
    'Admin',
    '+919876543210',
    'admin@gmail.com',
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
(1, 'G-Shock', 'g-shock.jpg'),
(2, 'For Man', 'man.jpg'),
(3, 'For Woman', 'woman.jpg'),
(4, 'Automatic', 'automatic.jpg')
ON DUPLICATE KEY UPDATE
    name = VALUES(name),
    category_image = VALUES(category_image);

-- PRODUCTS

INSERT INTO products
(id, name, description, category, price, offer_price, stock, is_best_seller, images, gallery)
VALUES

-- G-SHOCK (Category 1)

(1, 'Casio G-Shock Classic', 'Shock resistant sports watch', 1, 15000.00, 12999.00, 50, 1,
'https://i.ibb.co/RpTyPxFV/G-SHOCK-US-Official-Website-CASIO.jpg',
'["https://i.ibb.co/wNgqxrPS/Chat-GPT-Image-May-29-2026-02-40-12-PM.png"]'),

(2, 'G-Shock Mudmaster', 'Rugged outdoor adventure watch', 1, 22000.00, 19999.00, 25, 1,
'https://i.ibb.co/RpTyPxFV/G-SHOCK-US-Official-Website-CASIO.jpg',
'["https://i.ibb.co/wNgqxrPS/Chat-GPT-Image-May-29-2026-02-40-12-PM.png"]'),

(3, 'G-Shock Rangeman', 'Tactical digital watch', 1, 25000.00, 22999.00, 20, 0,
'https://i.ibb.co/RpTyPxFV/G-SHOCK-US-Official-Website-CASIO.jpg',
'["https://i.ibb.co/wNgqxrPS/Chat-GPT-Image-May-29-2026-02-40-12-PM.png"]'),

(4, 'G-Shock Gravitymaster', 'Pilot inspired durable watch', 1, 28000.00, 25999.00, 18, 1,
'https://i.ibb.co/RpTyPxFV/G-SHOCK-US-Official-Website-CASIO.jpg',
'["https://i.ibb.co/wNgqxrPS/Chat-GPT-Image-May-29-2026-02-40-12-PM.png"]'),

(5, 'G-Shock GA-2100', 'Slim carbon core sports watch', 1, 18000.00, 16999.00, 30, 0,
'https://i.ibb.co/RpTyPxFV/G-SHOCK-US-Official-Website-CASIO.jpg',
'["https://i.ibb.co/wNgqxrPS/Chat-GPT-Image-May-29-2026-02-40-12-PM.png"]'),

-- FOR MAN (Category 2)

(6, 'Rolex Submariner', 'Premium Swiss luxury diving watch', 2, 120000.00, 110000.00, 15, 1,
'https://i.ibb.co/KzXc2YPT/The-Best-Dad-Friendly-Gifts-for-Father-s-Day.jpg',
'["https://i.ibb.co/Dfs3XRKB/Chat-GPT-Image-Jun-2-2026-03-10-26-PM.png"]'),

(7, 'Omega Seamaster', 'Luxury automatic wrist watch', 2, 95000.00, 90000.00, 20, 0,
'https://i.ibb.co/KzXc2YPT/The-Best-Dad-Friendly-Gifts-for-Father-s-Day.jpg',
'["https://i.ibb.co/Dfs3XRKB/Chat-GPT-Image-Jun-2-2026-03-10-26-PM.png"]'),

(8, 'Citizen Eco Drive', 'Solar powered premium watch', 2, 18000.00, 16999.00, 25, 1,
'https://i.ibb.co/KzXc2YPT/The-Best-Dad-Friendly-Gifts-for-Father-s-Day.jpg',
'["https://i.ibb.co/Dfs3XRKB/Chat-GPT-Image-Jun-2-2026-03-10-26-PM.png"]'),

(9, 'Titan Neo', 'Elegant analog watch for men', 2, 8500.00, 7499.00, 40, 0,
'https://i.ibb.co/KzXc2YPT/The-Best-Dad-Friendly-Gifts-for-Father-s-Day.jpg',
'["https://i.ibb.co/Dfs3XRKB/Chat-GPT-Image-Jun-2-2026-03-10-26-PM.png"]'),

(10, 'Seiko Chronograph', 'Stylish chronograph watch', 2, 22000.00, 19999.00, 22, 1,
'https://i.ibb.co/KzXc2YPT/The-Best-Dad-Friendly-Gifts-for-Father-s-Day.jpg',
'["https://i.ibb.co/Dfs3XRKB/Chat-GPT-Image-Jun-2-2026-03-10-26-PM.png"]'),

-- FOR WOMAN (Category 3)

(11, 'Titan Raga', 'Classic women designer watch', 3, 9000.00, 7999.00, 30, 1,
'https://i.ibb.co/NcQ4206/Fossil-Raquel-Gold-Watch-ES5304.jpg',
'["https://i.ibb.co/Wp7Ht5S5/Chat-GPT-Image-Jun-2-2026-03-40-11-PM.png"]'),

(12, 'Michael Kors Parker', 'Luxury crystal women watch', 3, 20000.00, 18499.00, 20, 1,
'https://i.ibb.co/NcQ4206/Fossil-Raquel-Gold-Watch-ES5304.jpg',
'["https://i.ibb.co/Wp7Ht5S5/Chat-GPT-Image-Jun-2-2026-03-40-11-PM.png"]'),

(13, 'Fossil Carlie', 'Elegant ladies wrist watch', 3, 12000.00, 10999.00, 25, 0,
'https://i.ibb.co/NcQ4206/Fossil-Raquel-Gold-Watch-ES5304.jpg',
'["https://i.ibb.co/Wp7Ht5S5/Chat-GPT-Image-Jun-2-2026-03-40-11-PM.png"]'),

(14, 'Casio Sheen', 'Premium fashion watch for women', 3, 14000.00, 12999.00, 22, 0,
'https://i.ibb.co/NcQ4206/Fossil-Raquel-Gold-Watch-ES5304.jpg',
'["https://i.ibb.co/Wp7Ht5S5/Chat-GPT-Image-Jun-2-2026-03-40-11-PM.png"]'),

(15, 'Anne Klein Diamond', 'Elegant luxury women watch', 3, 16000.00, 14999.00, 18, 1,
'https://i.ibb.co/NcQ4206/Fossil-Raquel-Gold-Watch-ES5304.jpg',
'["https://i.ibb.co/Wp7Ht5S5/Chat-GPT-Image-Jun-2-2026-03-40-11-PM.png"]'),

-- AUTOMATIC (Category 4)

(16, 'Seiko 5 Sports', 'Reliable automatic sports watch', 4, 25000.00, 22999.00, 20, 1,
'https://i.ibb.co/jvmWzcf0/Invicta-Men-s-Pro-Diver-Collection-Coin-Edge-Automatic-Watch.jpg',
'["https://i.ibb.co/Hf2M0bkf/Chat-GPT-Image-Jun-2-2026-03-24-42-PM.png"]'),

(17, 'Tissot Le Locle', 'Swiss automatic dress watch', 4, 45000.00, 41999.00, 15, 1,
'https://i.ibb.co/jvmWzcf0/Invicta-Men-s-Pro-Diver-Collection-Coin-Edge-Automatic-Watch.jpg',
'["https://i.ibb.co/Hf2M0bkf/Chat-GPT-Image-Jun-2-2026-03-24-42-PM.png"]'),

(18, 'Orient Bambino', 'Classic automatic leather watch', 4, 18000.00, 16999.00, 28, 0,
'https://i.ibb.co/jvmWzcf0/Invicta-Men-s-Pro-Diver-Collection-Coin-Edge-Automatic-Watch.jpg',
'["https://i.ibb.co/Hf2M0bkf/Chat-GPT-Image-Jun-2-2026-03-24-42-PM.png"]'),

(19, 'Hamilton Khaki Field', 'Military style automatic watch', 4, 52000.00, 49999.00, 12, 1,
'https://i.ibb.co/jvmWzcf0/Invicta-Men-s-Pro-Diver-Collection-Coin-Edge-Automatic-Watch.jpg',
'["https://i.ibb.co/Hf2M0bkf/Chat-GPT-Image-Jun-2-2026-03-24-42-PM.png"]'),

(20, 'Citizen Automatic NH8350', 'Japanese automatic mechanical watch', 4, 21000.00, 18999.00, 25, 0,
'https://i.ibb.co/jvmWzcf0/Invicta-Men-s-Pro-Diver-Collection-Coin-Edge-Automatic-Watch.jpg',
'["https://i.ibb.co/Hf2M0bkf/Chat-GPT-Image-Jun-2-2026-03-24-42-PM.png"]')

ON DUPLICATE KEY UPDATE
    name = VALUES(name),
    description = VALUES(description),
    category = VALUES(category),
    price = VALUES(price),
    offer_price = VALUES(offer_price),
    stock = VALUES(stock),
    is_best_seller = VALUES(is_best_seller),
    images = VALUES(images),
    gallery = VALUES(gallery);

-- BOX OPTIONS
INSERT INTO box_options (id, name, image, price, is_active)
VALUES
(1, 'Standard Box', 'https://i.ibb.co/mVj8dGNP/s-box.webp', 0.00, 1),
(2, 'Premium Gift Box', 'https://i.ibb.co/RTTdZQtL/p-box.webp', 299.00, 1),
(3, 'Luxury Wooden Box', 'https://i.ibb.co/RTTdZQtL/p-box.webp', 999.00, 1)
ON DUPLICATE KEY UPDATE
    name = VALUES(name),
    image = VALUES(image),
    price = VALUES(price),
    is_active = VALUES(is_active);

-- PAGE META
INSERT INTO page_meta
(page_key, page_name, path, title, description, keywords, is_active)
VALUES
('home', 'Home', '/', 'ViralWatches - Premium Watches Online', 'Shop premium watches online at ViralWatches with curated collections, secure checkout, fast delivery, and stylish gift box options.', 'premium watches, online watch store, viralwatches, luxury watches', 1),
('collection', 'Collections', '/collection', 'Watch Collections - ViralWatches', 'Explore ViralWatches collections by category, latest arrivals, price, offers, and best-selling watch styles.', 'watch collections, mens watches, womens watches, best seller watches', 1),
('about', 'About', '/about', 'About ViralWatches - Premium Watch Store', 'Learn about ViralWatches, our watch collections, product quality, secure shopping experience, and customer support.', 'about viralwatches, watch store, premium watches', 1),
('contact', 'Contact', '/contact', 'Contact ViralWatches Support', 'Contact ViralWatches for order support, product questions, payment help, delivery updates, and returns assistance.', 'contact viralwatches, watch support, order help', 1),
('cart', 'Cart', '/cart', 'Shopping Cart - ViralWatches', 'Review your ViralWatches cart, update quantities, choose gift box options, and continue to secure checkout.', 'shopping cart, watch cart, checkout', 1),
('checkout', 'Checkout', '/checkout', 'Secure Checkout - ViralWatches', 'Complete your ViralWatches order with secure checkout, delivery details, coupon discounts, and payment options.', 'secure checkout, watch order, payment', 1),
('login', 'Login', '/login', 'Login - ViralWatches', 'Log in to your ViralWatches account to manage your wishlist, orders, profile, and checkout faster.', 'login, customer account, viralwatches account', 1),
('register', 'Register', '/register', 'Create Account - ViralWatches', 'Create a ViralWatches account for faster checkout, order tracking, wishlist access, and account management.', 'register, create account, watch shopping account', 1),
('forgot', 'Forgot Password', '/forgot', 'Forgot Password - ViralWatches', 'Reset your ViralWatches account password securely and regain access to your orders and wishlist.', 'forgot password, reset account, viralwatches login help', 1),
('reset', 'Reset Password', '/reset', 'Reset Password - ViralWatches', 'Set a new ViralWatches account password using your secure password reset link.', 'reset password, account security, new password', 1),
('profile', 'Profile', '/user/profile', 'My Profile - ViralWatches', 'Manage your ViralWatches profile details, contact information, password, and account settings.', 'my profile, account settings, customer profile', 1),
('orders', 'Orders', '/user/orders', 'My Orders - ViralWatches', 'View your ViralWatches order history, payment status, order status, and purchase details.', 'my orders, order history, watch orders', 1),
('product', 'Product Detail', '/product', '{product_name} - ViralWatches', 'Buy {product_name} online for {product_price}. View product details, images, availability, and checkout securely.', 'product, watch, viralwatches, buy watch online', 1),
('wishlist', 'Wishlist', '/wishlist', 'Wishlist - ViralWatches', 'View saved watches in your ViralWatches wishlist and quickly return to your favorite products.', 'wishlist, saved watches, favorite watches', 1)
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

-- SLIDES
INSERT INTO slides (id, type, file_path, mobile_file_path, sort_order, is_active)
VALUES
(
    1,
    'hero',
    'https://i.ibb.co/fVV6MSGV/Untitled-design-36.png',
    'https://i.ibb.co/WpqS6MVp/Chat-GPT-Image-Jun-1-2026-03-47-29-PM.png',
    1,
    1
),
(
    2,
    'hero',
    'https://i.ibb.co/4Z4P7hL4/Untitled-design-37.png',
    'https://i.ibb.co/fdvH4XyM/pposter-2.webp',
    2,
    1
)
ON DUPLICATE KEY UPDATE
    type = VALUES(type),
    file_path = VALUES(file_path),
    mobile_file_path = VALUES(mobile_file_path),
    sort_order = VALUES(sort_order),
    is_active = VALUES(is_active);
