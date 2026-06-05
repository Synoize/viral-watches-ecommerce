<?php
require_once __DIR__ . '/includes/functions.php';

function wishlistRedirectPath($value, $fallback = '/wishlist.php') {
    $value = trim((string)$value);
    if ($value === '') return $fallback;

    $path = parse_url($value, PHP_URL_PATH) ?: '';
    $query = parse_url($value, PHP_URL_QUERY);
    $basePrefix = defined('BASE_URL') ? rtrim(BASE_URL, '/') : '';
    if ($basePrefix !== '' && strpos($path, $basePrefix) === 0) {
        $path = substr($path, strlen($basePrefix));
    }
    $path = '/' . ltrim($path, '/');
    if ($path === '/') $path = $fallback;

    return $path . ($query ? '?' . $query : '');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $redirectTo = wishlistRedirectPath($_POST['redirect_to'] ?? '/wishlist.php');

    if (!isLoggedIn()) {
        $_SESSION['redirect_after_login'] = $redirectTo;
        $_SESSION['pending_wishlist_action'] = [
            'action' => $action === 'remove' ? 'remove' : 'add',
            'product_id' => (int)($_POST['product_id'] ?? 0),
        ];
        flash('error', 'Please login to save products to your wishlist.');
        redirect('/login.php');
    }

    $productId = (int)($_POST['product_id'] ?? 0);
    $action = $_POST['action'] ?? 'add';
    $result = $action === 'remove'
        ? removeWishlistItem($productId)
        : addWishlistItem($productId);

    if (!empty($result['error'])) {
        flash('error', $result['error']);
    } else {
        flash('success', $result['success']);
    }
    redirect($redirectTo);
}

if (!isLoggedIn()) {
    $_SESSION['redirect_after_login'] = '/wishlist.php';
    flash('error', 'Please login to view your wishlist.');
    redirect('/login.php');
}

$items = getWishlistItems();
$wishedProductIds = array_map('intval', array_column($items, 'id'));
?>
<?php include __DIR__ . '/includes/header.php'; ?>
<div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-sm uppercase tracking-[0.3em] text-slate-500">Saved products</p>
            <h1 class="mt-2 text-3xl font-semibold text-slate-900">My Wishlist</h1>
        </div>
        <a href="<?= BASE_URL ?>/collection" class="inline-flex items-center justify-center rounded-full border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-900 hover:bg-slate-50">Browse Products</a>
    </div>

    <?php if ($message = flash('success')): ?><div class="mt-6 rounded-3xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-700"><?= sanitize($message) ?></div><?php endif; ?>
    <?php if ($message = flash('error')): ?><div class="mt-6 rounded-3xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-700"><?= sanitize($message) ?></div><?php endif; ?>

    <?php if ($items): ?>
        <div class="mt-8 grid grid-cols-2 gap-3 md:grid-cols-3 md:gap-6 lg:grid-cols-4">
            <?php foreach ($items as $product): ?>
                <?php
                $gallery = json_decode($product['gallery'] ?? '[]', true) ?: [];
                $mainImage = resolveAssetUrl($product['images'] ?: ($gallery[0] ?? ''));
                $hasOffer = (float)$product['offer_price'] > 0 && (float)$product['offer_price'] < (float)$product['price'];
                $displayPrice = $hasOffer ? (float)$product['offer_price'] : (float)$product['price'];
                ?>
                <article class="relative group">
                    <a href="<?= BASE_URL ?>/product.php?id=<?= (int)$product['id'] ?>" class="block">
                        <div class="relative overflow-hidden rounded-md bg-white">
                            <?php if ($mainImage): ?>
                                <img src="<?= sanitize($mainImage) ?>" alt="<?= sanitize($product['name']) ?>" class="h-[180px] w-full object-contain p-5 transition-all duration-500 md:h-[360px] md:p-8" />
                            <?php else: ?>
                                <div class="flex h-[180px] w-full items-center justify-center bg-slate-100 p-5 text-center text-sm text-slate-500 md:h-[360px]">Image not found</div>
                            <?php endif; ?>
                            <span class="absolute left-2 bottom-2 rounded-full bg-black px-3 py-1 text-[12px] text-white md:left-5 md:bottom-5"><?= (int)$product['stock'] > 0 ? 'In Stock' : 'Out of Stock' ?></span>
                        </div>
                        <div class="pt-3 md:pt-5">
                            <h2 class="mb-2 text-[14px] leading-[1.3] text-[#222] md:text-[16px] md:leading-[1.4]"><?= sanitize($product['name']) ?></h2>
                            <div class="flex flex-col gap-1 md:flex-row md:items-center md:gap-4">
                                <?php if ($hasOffer): ?>
                                    <span class="text-[12px] text-[#666] line-through md:text-[14px]">Rs. <?= number_format((float)$product['price'], 2) ?></span>
                                <?php endif; ?>
                                <span class="text-[14px] font-medium text-black md:text-[16px]">Rs. <?= number_format($displayPrice, 2) ?></span>
                            </div>
                        </div>
                    </a>
                    <?= renderWishlistButton($product['id'], true, 'absolute right-3 top-3 z-20') ?>
                    <?php if ((int)$product['stock'] > 0): ?>
                        <a href="<?= BASE_URL ?>/cart.php?action=add&id=<?= (int)$product['id'] ?>" class="mt-4 inline-flex w-full items-center justify-center bg-slate-900 px-4 py-3 text-sm font-semibold uppercase text-white hover:bg-slate-800">Add to Cart</a>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="mt-8 rounded-[2rem] border border-slate-200 bg-white p-10 text-center shadow-sm">
            <i class="fa-regular fa-heart text-5xl text-slate-300"></i>
            <h2 class="mt-6 text-2xl font-serif text-[#222]">Your wishlist is empty</h2>
            <p class="mt-3 text-slate-500">Save products you like and find them here later.</p>
            <a href="<?= BASE_URL ?>/collection" class="mt-8 inline-flex items-center justify-center rounded-full bg-slate-900 px-6 py-3 text-sm font-semibold text-white hover:bg-slate-800">Explore Collection</a>
        </div>
    <?php endif; ?>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
