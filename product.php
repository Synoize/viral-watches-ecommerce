<?php
require_once __DIR__ . '/includes/functions.php';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$stmt = $pdo->prepare('SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON p.category = c.id WHERE p.id = ?');
$stmt->execute([$id]);
$product = $stmt->fetch();
if (!$product) {
    redirect('/shop.php');
}
$gallery = json_decode($product['gallery'], true) ?: [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['quantity'])) {
    $quantity = max(1, min(10, (int)$_POST['quantity']));
    if (!isset($_SESSION['cart'][$id])) {
        $_SESSION['cart'][$id] = ['quantity' => 0];
    }
    $_SESSION['cart'][$id]['quantity'] += $quantity;
    flash('success', 'Product added to cart.');
    redirect('/cart.php');
}
?>
<?php include __DIR__ . '/includes/header.php'; ?>
<div class="container mt-5">
    <?php if ($message = flash('success')): ?><div class="alert alert-success"><?= $message ?></div><?php endif; ?>
    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card shadow-sm">
                <div class="ratio ratio-4x3">
                    <img src="<?= sanitize($gallery[0] ?? $product['images']) ?>" class="w-100 h-100 object-fit-cover" alt="<?= sanitize($product['name']) ?>">
                </div>
                <?php if ($gallery): ?>
                    <div class="row g-2 mt-2 p-2">
                        <?php foreach ($gallery as $src): ?>
                            <div class="col-3">
                                <img src="<?= sanitize($src) ?>" class="img-fluid rounded cursor-pointer product-thumb" data-src="<?= sanitize($src) ?>">
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <div class="col-lg-6">
            <h1 class="h3"><?= sanitize($product['name']) ?></h1>
            <p class="text-muted small mb-1">Category: <?= sanitize($product['category_name'] ?? 'General') ?></p>
            <h3 class="text-primary">₹<?= number_format($product['price'], 2) ?></h3>
            <p class="mb-3"><?= nl2br(sanitize($product['description'])) ?></p>
            <p><strong>Status:</strong> <?= $product['stock'] > 0 ? 'In stock' : 'Out of stock' ?></p>
            <form method="post" class="row g-3 align-items-end">
                <div class="col-auto">
                    <label class="form-label">Quantity</label>
                    <input type="number" name="quantity" class="form-control" value="1" min="1" max="<?= $product['stock'] ?>">
                </div>
                <div class="col-auto">
                    <button class="btn btn-primary" <?= $product['stock'] ? '' : 'disabled' ?>>Add to Cart</button>
                </div>
                <div class="col-auto">
                    <a href="<?= BASE_URL ?>/checkout.php?product_id=<?= $product['id'] ?>" class="btn btn-outline-secondary <?= $product['stock'] ? '' : 'disabled' ?>">Buy Now</a>
                </div>
            </form>
        </div>
    </div>
    <section class="mt-5">
        <h4>Related Products</h4>
        <div class="row g-4 mt-2">
            <?php
            $relStmt = $pdo->prepare('SELECT id, name, price, images FROM products WHERE category = ? AND id != ? LIMIT 4');
            $relStmt->execute([$product['category'], $product['id']]);
            $related = $relStmt->fetchAll();
            foreach ($related as $item): $itemGallery = json_decode($item['gallery'] ?? '[]', true) ?: []; ?>
                <div class="col-md-3">
                    <div class="card h-100">
                        <img src="<?= sanitize($itemGallery[0] ?? $item['images']) ?>" class="card-img-top" style="height:180px; object-fit:cover;">
                        <div class="card-body">
                            <h6 class="card-title"><?= sanitize($item['name']) ?></h6>
                            <p class="mb-0">₹<?= number_format($item['price'], 2) ?></p>
                            <a href="<?= BASE_URL ?>/product.php?id=<?= $item['id'] ?>" class="stretched-link"></a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
