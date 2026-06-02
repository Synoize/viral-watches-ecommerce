<?php
require_once __DIR__ . '/includes/functions.php';
$featuredCategories = getCategories();
$stmt = $pdo->query('SELECT * FROM products WHERE stock > 0 ORDER BY id DESC LIMIT 8');
$trending = $stmt->fetchAll();
?>
<?php include __DIR__ . '/includes/header.php'; ?>
<div class="container mt-5">
    <section class="mb-5">
        <div id="heroCarousel" class="carousel slide" data-mdb-ride="carousel">
            <div class="carousel-inner rounded-4 shadow-sm">
                <div class="carousel-item active" style="background:url('https://images.unsplash.com/photo-1523275335684-37898b6baf30?auto=format&fit=crop&w=1400&q=80') center/cover; min-height: 520px;">
                    <div class="carousel-caption d-flex flex-column justify-content-center align-items-start h-100 text-start">
                        <h1 class="display-4 fw-bold">Shop the latest styles</h1>
                        <p class="lead">Premium products, fast shipping, and secure checkout.</p>
                        <a class="btn btn-primary btn-lg" href="<?= BASE_URL ?>/shop.php">Shop Now</a>
                    </div>
                </div>
                <div class="carousel-item" style="background:url('https://images.unsplash.com/photo-1512436991641-6745cdb1723f?auto=format&fit=crop&w=1400&q=80') center/cover; min-height: 520px;">
                    <div class="carousel-caption d-flex flex-column justify-content-center align-items-start h-100 text-start">
                        <h1 class="display-4 fw-bold">Discover trending products</h1>
                        <p class="lead">Curated collections for every occasion.</p>
                        <a class="btn btn-outline-light btn-lg" href="<?= BASE_URL ?>/shop.php">Browse Collection</a>
                    </div>
                </div>
            </div>
            <button class="carousel-control-prev" type="button" data-mdb-target="#heroCarousel" data-mdb-slide="prev">
                <span class="carousel-control-prev-icon"></span>
            </button>
            <button class="carousel-control-next" type="button" data-mdb-target="#heroCarousel" data-mdb-slide="next">
                <span class="carousel-control-next-icon"></span>
            </button>
        </div>
    </section>

    <section class="mb-5">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="h4">Featured Categories</h2>
            <a href="<?= BASE_URL ?>/shop.php" class="text-decoration-none">View all</a>
        </div>
        <div class="row g-3">
            <?php foreach ($featuredCategories as $category): ?>
                <div class="col-sm-6 col-lg-3">
                    <div class="card category-card h-100">
                        <div class="card-body d-flex flex-column justify-content-center align-items-center text-center">
                            <i class="fas fa-tag fa-3x mb-3 text-primary"></i>
                            <h5 class="card-title"><?= sanitize($category['name']) ?></h5>
                            <a href="<?= BASE_URL ?>/shop.php?category=<?= $category['id'] ?>" class="stretched-link"></a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="mb-5">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="h4">Trending Products</h2>
            <a href="<?= BASE_URL ?>/shop.php" class="text-decoration-none">See all products</a>
        </div>
        <div class="row g-4">
            <?php foreach ($trending as $product): ?>
                <?php $gallery = json_decode($product['gallery'], true) ?: []; ?>
                <div class="col-md-6 col-xl-3">
                    <div class="card h-100 shadow-sm">
                        <img src="<?= sanitize($gallery[0] ?? $product['images']) ?>" class="card-img-top" alt="<?= sanitize($product['name']) ?>">
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title"><?= sanitize($product['name']) ?></h5>
                            <p class="text-muted mb-2">₹<?= number_format($product['price'], 2) ?></p>
                            <p class="small text-secondary"><?= substr(sanitize($product['description']), 0, 80) ?>...</p>
                            <div class="mt-auto d-flex gap-2">
                                <a href="<?= BASE_URL ?>/product.php?id=<?= $product['id'] ?>" class="btn btn-outline-primary btn-sm flex-grow-1">View</a>
                                <a href="<?= BASE_URL ?>/cart.php?action=add&id=<?= $product['id'] ?>" class="btn btn-primary btn-sm">Add</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="mb-5 bg-light rounded-4 p-4 shadow-sm">
        <div class="row align-items-center g-4">
            <div class="col-lg-6">
                <div class="ratio ratio-16x9 rounded-4 overflow-hidden shadow-sm">
                    <iframe src="https://www.youtube.com/embed/dQw4w9WgXcQ" title="Company Video" allowfullscreen></iframe>
                </div>
            </div>
            <div class="col-lg-6">
                <h2 class="h4">Why Shop With Us?</h2>
                <p>High quality products, secure payments, and fast delivery with responsive support.</p>
                <div class="row g-3">
                    <div class="col-6">
                        <div class="feature-box p-3 rounded-3 bg-white shadow-sm">
                            <h5>Fast shipping</h5>
                            <p class="small text-muted">Reliable delivery across India.</p>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="feature-box p-3 rounded-3 bg-white shadow-sm">
                            <h5>Secure checkout</h5>
                            <p class="small text-muted">PCI-compliant payment flow.</p>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="feature-box p-3 rounded-3 bg-white shadow-sm">
                            <h5>Easy returns</h5>
                            <p class="small text-muted">Hassle-free order support.</p>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="feature-box p-3 rounded-3 bg-white shadow-sm">
                            <h5>24/7 support</h5>
                            <p class="small text-muted">Friendly customer service.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="mb-5">
        <h2 class="h4 mb-3">FAQ</h2>
        <div class="accordion" id="faqAccordion">
            <div class="accordion-item">
                <h2 class="accordion-header" id="faqOne">
                    <button class="accordion-button" type="button" data-mdb-toggle="collapse" data-mdb-target="#collapseOne">How do I track my order?</button>
                </h2>
                <div id="collapseOne" class="accordion-collapse collapse show" data-mdb-parent="#faqAccordion">
                    <div class="accordion-body">Your order details and status are available in your account dashboard under Orders.</div>
                </div>
            </div>
            <div class="accordion-item">
                <h2 class="accordion-header" id="faqTwo">
                    <button class="accordion-button collapsed" type="button" data-mdb-toggle="collapse" data-mdb-target="#collapseTwo">Can I return products?</button>
                </h2>
                <div id="collapseTwo" class="accordion-collapse collapse" data-mdb-parent="#faqAccordion">
                    <div class="accordion-body">Yes, returns are accepted within the policy period. Contact support through the Help page.</div>
                </div>
            </div>
            <div class="accordion-item">
                <h2 class="accordion-header" id="faqThree">
                    <button class="accordion-button collapsed" type="button" data-mdb-toggle="collapse" data-mdb-target="#collapseThree">What payment options are available?</button>
                </h2>
                <div id="collapseThree" class="accordion-collapse collapse" data-mdb-parent="#faqAccordion">
                    <div class="accordion-body">We support Razorpay and Cash on Delivery with ₹50 advance for COD orders.</div>
                </div>
            </div>
        </div>
    </section>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
