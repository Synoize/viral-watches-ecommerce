USE new_db;

-- DEFAULT ADMIN USER
INSERT INTO users (name, phone, email, password, role)
VALUES (
    'Admin User',
    '9876543210',
    'admin@shopmaster.com',
    '$2y$10$ViCqcab/Z3PTpXi9PygiAOIDu9vVYL4zTRy6hszaPMtqW6WBmykDi',
    'admin'
)
ON DUPLICATE KEY UPDATE
    name = VALUES(name),
    phone = VALUES(phone),
    role = VALUES(role);

-- DEFAULT PAGE META
INSERT INTO page_meta
(page_key, page_name, path, title, description, keywords, is_active)
VALUES
('home', 'Home', '/', 'ShopMaster | Premium Watches and Accessories', 'Shop premium watches, accessories, gift boxes, and secure online deals at ShopMaster.', 'watches, accessories, online shopping', 1),
('collection', 'Collections', '/collection', 'Collections | ShopMaster', 'Browse ShopMaster collections by category, price, and latest available products.', 'collections, products, watches', 1),
('about', 'About', '/about', 'About ShopMaster', 'Learn about ShopMaster, our ecommerce experience, product quality, and customer support.', 'about shopmaster, ecommerce', 1),
('contact', 'Contact', '/contact', 'Contact ShopMaster Support', 'Contact ShopMaster for order help, product questions, payments, returns, and support.', 'contact, support, help', 1),
('cart', 'Cart', '/cart', 'Shopping Cart | ShopMaster', 'Review your selected products, box options, quantities, and order subtotal.', 'cart, checkout', 1),
('checkout', 'Checkout', '/checkout', 'Checkout | ShopMaster', 'Complete your ShopMaster order with secure payment and delivery details.', 'checkout, payment', 1),
('login', 'Login', '/login', 'Login | ShopMaster', 'Log in to your ShopMaster account to manage orders and checkout faster.', 'login, account', 1),
('register', 'Register', '/register', 'Create Account | ShopMaster', 'Create a ShopMaster account for faster checkout and order management.', 'register, account', 1),
('product', 'Product Detail', '/product', '{product_name} | ShopMaster', 'Buy {product_name} online for {product_price}. Choose gift box options and checkout securely.', 'product, watch, box', 1)
ON DUPLICATE KEY UPDATE
    page_name = VALUES(page_name),
    path = VALUES(path);
