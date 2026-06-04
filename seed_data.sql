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
'["https://i.ibb.co/wNgqxrPS/Chat-GPT-Image-May-29-2026-02-40-12-PM.png","gshock-2.jpg","gshock-3.jpg"]'),

(2, 'G-Shock Mudmaster', 'Rugged outdoor adventure watch', 1, 22000.00, 19999.00, 25, 1,
'https://i.ibb.co/RpTyPxFV/G-SHOCK-US-Official-Website-CASIO.jpg',
'["https://i.ibb.co/wNgqxrPS/Chat-GPT-Image-May-29-2026-02-40-12-PM.png","mudmaster-2.jpg","mudmaster-3.jpg"]'),

(3, 'G-Shock Rangeman', 'Tactical digital watch', 1, 25000.00, 22999.00, 20, 0,
'https://i.ibb.co/RpTyPxFV/G-SHOCK-US-Official-Website-CASIO.jpg',
'["https://i.ibb.co/wNgqxrPS/Chat-GPT-Image-May-29-2026-02-40-12-PM.png","rangeman-2.jpg","rangeman-3.jpg"]'),

(4, 'G-Shock Gravitymaster', 'Pilot inspired durable watch', 1, 28000.00, 25999.00, 18, 1,
'https://i.ibb.co/RpTyPxFV/G-SHOCK-US-Official-Website-CASIO.jpg',
'["https://i.ibb.co/wNgqxrPS/Chat-GPT-Image-May-29-2026-02-40-12-PM.png","gravity-2.jpg","gravity-3.jpg"]'),

(5, 'G-Shock GA-2100', 'Slim carbon core sports watch', 1, 18000.00, 16999.00, 30, 0,
'https://i.ibb.co/RpTyPxFV/G-SHOCK-US-Official-Website-CASIO.jpg',
'["https://i.ibb.co/wNgqxrPS/Chat-GPT-Image-May-29-2026-02-40-12-PM.png","ga2100-2.jpg","ga2100-3.jpg"]'),

-- FOR MAN (Category 2)

(6, 'Rolex Submariner', 'Premium Swiss luxury diving watch', 2, 120000.00, 110000.00, 15, 1,
'https://i.ibb.co/KzXc2YPT/The-Best-Dad-Friendly-Gifts-for-Father-s-Day.jpg',
'["https://i.ibb.co/Dfs3XRKB/Chat-GPT-Image-Jun-2-2026-03-10-26-PM.png","rolex-2.jpg","rolex-3.jpg"]'),

(7, 'Omega Seamaster', 'Luxury automatic wrist watch', 2, 95000.00, 90000.00, 20, 0,
'https://i.ibb.co/KzXc2YPT/The-Best-Dad-Friendly-Gifts-for-Father-s-Day.jpg',
'["https://i.ibb.co/Dfs3XRKB/Chat-GPT-Image-Jun-2-2026-03-10-26-PM.png","omega-2.jpg","omega-3.jpg"]'),

(8, 'Citizen Eco Drive', 'Solar powered premium watch', 2, 18000.00, 16999.00, 25, 1,
'https://i.ibb.co/KzXc2YPT/The-Best-Dad-Friendly-Gifts-for-Father-s-Day.jpg',
'["https://i.ibb.co/Dfs3XRKB/Chat-GPT-Image-Jun-2-2026-03-10-26-PM.png","citizen-2.jpg","citizen-3.jpg"]'),

(9, 'Titan Neo', 'Elegant analog watch for men', 2, 8500.00, 7499.00, 40, 0,
'https://i.ibb.co/KzXc2YPT/The-Best-Dad-Friendly-Gifts-for-Father-s-Day.jpg',
'["https://i.ibb.co/Dfs3XRKB/Chat-GPT-Image-Jun-2-2026-03-10-26-PM.png","neo-2.jpg","neo-3.jpg"]'),

(10, 'Seiko Chronograph', 'Stylish chronograph watch', 2, 22000.00, 19999.00, 22, 1,
'https://i.ibb.co/KzXc2YPT/The-Best-Dad-Friendly-Gifts-for-Father-s-Day.jpg',
'["https://i.ibb.co/Dfs3XRKB/Chat-GPT-Image-Jun-2-2026-03-10-26-PM.png","seiko-2.jpg","seiko-3.jpg"]'),

-- FOR WOMAN (Category 3)

(11, 'Titan Raga', 'Classic women designer watch', 3, 9000.00, 7999.00, 30, 1,
'https://i.ibb.co/NcQ4206/Fossil-Raquel-Gold-Watch-ES5304.jpg',
'["https://i.ibb.co/Wp7Ht5S5/Chat-GPT-Image-Jun-2-2026-03-40-11-PM.png","raga-2.jpg","raga-3.jpg"]'),

(12, 'Michael Kors Parker', 'Luxury crystal women watch', 3, 20000.00, 18499.00, 20, 1,
'https://i.ibb.co/NcQ4206/Fossil-Raquel-Gold-Watch-ES5304.jpg',
'["https://i.ibb.co/Wp7Ht5S5/Chat-GPT-Image-Jun-2-2026-03-40-11-PM.png","mk-2.jpg","mk-3.jpg"]'),

(13, 'Fossil Carlie', 'Elegant ladies wrist watch', 3, 12000.00, 10999.00, 25, 0,
'https://i.ibb.co/NcQ4206/Fossil-Raquel-Gold-Watch-ES5304.jpg',
'["https://i.ibb.co/Wp7Ht5S5/Chat-GPT-Image-Jun-2-2026-03-40-11-PM.png","carlie-2.jpg","carlie-3.jpg"]'),

(14, 'Casio Sheen', 'Premium fashion watch for women', 3, 14000.00, 12999.00, 22, 0,
'https://i.ibb.co/NcQ4206/Fossil-Raquel-Gold-Watch-ES5304.jpg',
'["https://i.ibb.co/Wp7Ht5S5/Chat-GPT-Image-Jun-2-2026-03-40-11-PM.png","sheen-2.jpg","sheen-3.jpg"]'),

(15, 'Anne Klein Diamond', 'Elegant luxury women watch', 3, 16000.00, 14999.00, 18, 1,
'https://i.ibb.co/NcQ4206/Fossil-Raquel-Gold-Watch-ES5304.jpg',
'["https://i.ibb.co/Wp7Ht5S5/Chat-GPT-Image-Jun-2-2026-03-40-11-PM.png","anne-2.jpg","anne-3.jpg"]'),

-- AUTOMATIC (Category 4)

(16, 'Seiko 5 Sports', 'Reliable automatic sports watch', 4, 25000.00, 22999.00, 20, 1,
'https://i.ibb.co/jvmWzcf0/Invicta-Men-s-Pro-Diver-Collection-Coin-Edge-Automatic-Watch.jpg',
'["https://i.ibb.co/Hf2M0bkf/Chat-GPT-Image-Jun-2-2026-03-24-42-PM.png","seiko5-2.jpg","seiko5-3.jpg"]'),

(17, 'Tissot Le Locle', 'Swiss automatic dress watch', 4, 45000.00, 41999.00, 15, 1,
'https://i.ibb.co/jvmWzcf0/Invicta-Men-s-Pro-Diver-Collection-Coin-Edge-Automatic-Watch.jpg',
'["https://i.ibb.co/Hf2M0bkf/Chat-GPT-Image-Jun-2-2026-03-24-42-PM.png","tissot-2.jpg","tissot-3.jpg"]'),

(18, 'Orient Bambino', 'Classic automatic leather watch', 4, 18000.00, 16999.00, 28, 0,
'https://i.ibb.co/jvmWzcf0/Invicta-Men-s-Pro-Diver-Collection-Coin-Edge-Automatic-Watch.jpg',
'["https://i.ibb.co/Hf2M0bkf/Chat-GPT-Image-Jun-2-2026-03-24-42-PM.png","orient-2.jpg","orient-3.jpg"]'),

(19, 'Hamilton Khaki Field', 'Military style automatic watch', 4, 52000.00, 49999.00, 12, 1,
'https://i.ibb.co/jvmWzcf0/Invicta-Men-s-Pro-Diver-Collection-Coin-Edge-Automatic-Watch.jpg',
'["https://i.ibb.co/Hf2M0bkf/Chat-GPT-Image-Jun-2-2026-03-24-42-PM.png","hamilton-2.jpg","hamilton-3.jpg"]'),

(20, 'Citizen Automatic NH8350', 'Japanese automatic mechanical watch', 4, 21000.00, 18999.00, 25, 0,
'https://i.ibb.co/jvmWzcf0/Invicta-Men-s-Pro-Diver-Collection-Coin-Edge-Automatic-Watch.jpg',
'["citizen-auto-1.jpg","citizen-auto-2.jpg","citizen-auto-3.jpg"]')

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
