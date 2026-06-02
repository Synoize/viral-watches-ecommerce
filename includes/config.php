<?php
session_start();

define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'ecommerce_db');
define('DB_USER', 'root');
define('DB_PASS', '');

define('BASE_URL', '/new');

define('RAZORPAY_KEY_ID', 'YOUR_RAZORPAY_KEY_ID');
define('RAZORPAY_KEY_SECRET', 'YOUR_RAZORPAY_SECRET');

// Set secure session cookie settings
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
