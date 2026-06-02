<?php
require_once __DIR__ . '/functions.php';
$categories = getCategories();
$cartCount = getCartCount();
$user = getCurrentUser();
$csrfToken = generateCsrfToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>eCommerce Platform</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/6.4.0/mdb.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body>
<header class="sticky-top bg-white shadow-sm">
    <div class="container-fluid">
        <nav class="navbar navbar-expand-lg navbar-white">
            <div class="container-fluid">
                <a class="navbar-brand fw-bold" href="<?= BASE_URL ?>/index.php">ShopMaster</a>
                <button class="navbar-toggler" type="button" data-mdb-toggle="collapse" data-mdb-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-mdb-toggle="dropdown">Categories</a>
                            <ul class="dropdown-menu">
                                <?php foreach ($categories as $category): ?>
                                    <li><a class="dropdown-item" href="<?= BASE_URL ?>/shop.php?category=<?= $category['id'] ?>"><?= sanitize($category['name']) ?></a></li>
                                <?php endforeach; ?>
                            </ul>
                        </li>
                        <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/shop.php">Shop</a></li>
                        <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/about.php">About</a></li>
                        <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/contact.php">Help</a></li>
                    </ul>
                    <form class="d-flex input-group w-auto" action="<?= BASE_URL ?>/shop.php" method="get">
                        <input type="search" class="form-control" name="search" placeholder="Search products" aria-label="Search">
                        <button class="btn btn-primary" type="submit"><i class="fas fa-search"></i></button>
                    </form>
                    <ul class="navbar-nav ms-3 align-items-center">
                        <li class="nav-item me-3"><a class="nav-link position-relative" href="<?= BASE_URL ?>/cart.php"> <i class="fas fa-shopping-cart"></i> <span class="badge bg-danger rounded-pill position-absolute top-0 start-100 translate-middle"><?= $cartCount ?></span></a></li>
                        <?php if ($user): ?>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" data-mdb-toggle="dropdown"><?= sanitize($user['name']) ?></a>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><a class="dropdown-item" href="<?= BASE_URL ?>/user/profile.php">Profile</a></li>
                                    <li><a class="dropdown-item" href="<?= BASE_URL ?>/user/orders.php">Orders</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="<?= BASE_URL ?>/logout.php">Logout</a></li>
                                </ul>
                            </li>
                        <?php else: ?>
                            <li class="nav-item"><a class="btn btn-outline-primary btn-sm" href="<?= BASE_URL ?>/login.php">Login</a></li>
                            <li class="nav-item ms-2"><a class="btn btn-primary btn-sm" href="<?= BASE_URL ?>/register.php">Register</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </nav>
    </div>
</header>
<main class="py-4">