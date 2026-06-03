<?php
require_once __DIR__ . '/includes/functions.php';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$stmt = $pdo->prepare('SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON p.category = c.id WHERE p.id = ?');
$stmt->execute([$id]);
$product = $stmt->fetch();
if (!$product) {
    redirect('/collection');
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
<div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
    <?php if ($message = flash('success')): ?><div class="rounded-3xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-700"><?= $message ?></div><?php endif; ?>
    <div class="mt-8 grid gap-10 lg:grid-cols-[1fr_420px] lg:items-start">
        <div class="space-y-6">
            <div class="overflow-hidden rounded-[2rem] bg-white shadow-sm">
                <img src="<?= sanitize($gallery[0] ?? $product['images']) ?>" alt="<?= sanitize($product['name']) ?>" class="product-main-image h-[520px] w-full object-cover" />
            </div>
            <?php if ($gallery): ?>
                <div class="grid grid-cols-4 gap-3">
                    <?php foreach ($gallery as $src): ?>
                        <button type="button" class="product-thumb overflow-hidden rounded-3xl border border-slate-200 bg-slate-50 p-1" data-src="<?= sanitize($src) ?>">
                            <img src="<?= sanitize($src) ?>" alt="<?= sanitize($product['name']) ?>" class="h-24 w-full object-cover" />
                        </button>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        <div class="space-y-6">
            <div class="rounded-[2rem] border border-slate-200 bg-white p-8 shadow-sm">
                <p class="text-sm text-slate-500">Category: <?= sanitize($product['category_name'] ?? 'General') ?></p>
                <h1 class="mt-3 text-4xl font-semibold text-slate-900"><?= sanitize($product['name']) ?></h1>
                <p class="mt-4 text-3xl font-semibold text-brand">₹<?= number_format($product['price'], 2) ?></p>
                <p class="mt-4 text-slate-600 leading-7"><?= nl2br(sanitize($product['description'])) ?></p>
                <p class="mt-6 text-sm font-medium text-slate-700">Status: <span class="font-semibold text-slate-900"><?= $product['stock'] > 0 ? 'In stock' : 'Out of stock' ?></span></p>
                <form method="post" class="mt-8 grid gap-4">
                    <label class="space-y-2 text-sm font-medium text-slate-700">Quantity
                        <input type="number" name="quantity" value="1" min="1" max="<?= $product['stock'] ?>" class="mt-2 w-full rounded-3xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 outline-none focus:border-slate-900" />
                    </label>
                    <button class="inline-flex w-full items-center justify-center rounded-3xl bg-slate-900 px-6 py-4 text-sm font-semibold text-white hover:bg-slate-800" <?= $product['stock'] ? '' : 'disabled' ?>>Add to Cart</button>
                    <a href="<?= BASE_URL ?>/checkout.php?product_id=<?= $product['id'] ?>" class="inline-flex w-full items-center justify-center rounded-3xl border border-slate-200 bg-white px-6 py-4 text-sm font-semibold text-slate-900 hover:bg-slate-50 <?= $product['stock'] ? '' : 'pointer-events-none opacity-60' ?>">Buy Now</a>
                </form>
            </div>
            <div class="rounded-[2rem] border border-slate-200 bg-white p-8 shadow-sm">
                <h2 class="text-2xl font-semibold text-slate-900">Related Products</h2>
                <div class="mt-6 grid gap-4 sm:grid-cols-2">
                    <?php
                    $relStmt = $pdo->prepare('SELECT id, name, price, images, gallery FROM products WHERE category = ? AND id != ? LIMIT 4');
                    $relStmt->execute([$product['category'], $product['id']]);
                    $related = $relStmt->fetchAll();
                    foreach ($related as $item): $itemGallery = json_decode($item['gallery'] ?? '[]', true) ?: []; ?>
                        <a href="<?= BASE_URL ?>/product.php?id=<?= $item['id'] ?>" class="group overflow-hidden rounded-3xl border border-slate-200 bg-slate-50 transition hover:-translate-y-0.5 hover:shadow-sm">
                            <img src="<?= sanitize($itemGallery[0] ?? $item['images']) ?>" alt="<?= sanitize($item['name']) ?>" class="h-40 w-full object-cover" />
                            <div class="p-4">
                                <h3 class="text-base font-semibold text-slate-900"><?= sanitize($item['name']) ?></h3>
                                <p class="mt-2 text-sm text-brand">₹<?= number_format($item['price'], 2) ?></p>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
