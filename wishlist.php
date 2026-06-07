<?php
require_once __DIR__ . '/includes/functions.php';

function wishlistRedirectPath($value, $fallback = '/wishlist')
{
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
    $redirectTo = wishlistRedirectPath($_POST['redirect_to'] ?? '/wishlist');

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
    $_SESSION['redirect_after_login'] = '/wishlist';
    flash('error', 'Please login to view your wishlist.');
    redirect('/login.php');
}

$items = getWishlistItems();
$wishedProductIds = array_map('intval', array_column($items, 'id'));
?>
<?php include __DIR__ . '/includes/header.php'; ?>
<div class="mx-auto max-w-[1920px] px-4 py-10 md:px-10">
    <div class="flex  gap-4 items-center justify-between">
        <div class="flex items-center justify-between">
            <h2 class="text-[32px] md:text-[42px] font-serif text-black">
                My Wishlist
            </h2>
        </div>

        <a href="<?= BASE_URL ?>/collection"
            class="inline-flex items-center justify-center rounded-full border border-black hover:bg-black px-7 py-3 text-xs md:text-sm hover:text-white transition-all duration-300">
            Browse Products
        </a>
    </div>


    <?php if ($items): ?>
        <div class="mt-8 grid grid-cols-2 gap-3 md:grid-cols-3 md:gap-6 lg:grid-cols-4">
            <?php foreach ($items as $product): ?>
                <?php
                $gallery = json_decode($product['gallery'] ?? '[]', true) ?: [];
                $mainImage = resolveAssetUrl($product['images'] ?: ($gallery[0] ?? ''));
                $hoverImage = resolveAssetUrl($gallery[1] ?? ($gallery[0] ?? $product['images']));
                $hasOffer = (float)$product['offer_price'] > 0 && (float)$product['offer_price'] < (float)$product['price'];
                $displayPrice = $hasOffer ? (float)$product['offer_price'] : (float)$product['price'];
                $stock = (int)$product['stock'];

                if ($stock <= 0) {
                    $badgeText = 'Out of Stock';
                    $badgeClass = 'bg-red-600 text-white';
                } elseif ($stock < 10) {
                    $badgeText = $stock . ' Only Left';
                    $badgeClass = 'bg-orange-500 text-white';
                } else {
                    $badgeText = 'Sale';
                    $badgeClass = 'bg-black text-white';
                }
                ?>
                <article class="relative group">
                    <a href="<?= publicUrl('/product?id=' . (int)$product['id']) ?>" class="block">
                        <div class="relative bg-white rounded-md overflow-hidden">
                            <?php if ($mainImage): ?>
                                <img src="<?= sanitize($mainImage) ?>" alt="<?= sanitize($product['name']) ?>"
                                    class="w-full md:w-[400px] h-[180px] md:h-[440px] object-contain p-5 md:p-8 transition-all duration-500 group-hover:opacity-0" />
                            <?php else: ?>
                                <div class="flex h-[180px] w-full items-center justify-center bg-slate-100 p-5 text-center text-sm text-slate-500 md:h-[440px] md:w-[400px]">Image not found</div>
                            <?php endif; ?>

                            <?php if ($hoverImage): ?>
                                <img src="<?= sanitize($hoverImage) ?>" alt="<?= sanitize($product['name']) ?>"
                                    class="absolute inset-0 h-full w-full object-cover opacity-0 transition-all duration-500 group-hover:opacity-100" />
                            <?php endif; ?>

                            <span
                                class="absolute left-2 md:left-5 bottom-2 md:bottom-5 text-[12px] px-3 md:px-4 py-1 md:py-1.5 rounded-full z-10 <?= $badgeClass ?>">
                                <?= htmlspecialchars($badgeText) ?>
                            </span>
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
                        <a href="<?= publicUrl('/cart?action=add&id=' . (int)$product['id']) ?>" class="mt-4 inline-flex w-full items-center justify-center bg-slate-900 px-4 py-3 text-sm font-semibold uppercase text-white hover:bg-slate-800">Add to Cart</a>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="col-span-full text-center h-[68vh] md:h-[56vh] flex flex-col items-center justify-center">

            <i class="fa-regular fa-heart text-5xl text-slate-300"></i>

            <h3 class="mt-6 text-2xl font-serif text-[#222]">
                Your wishlist is empty
            </h3>

            <p class="mt-3 text-slate-500">
                Save products you like and find them here later.
            </p>

            <a href="<?= BASE_URL ?>/collection"
                class="inline-block mt-8 border border-black px-8 py-3 text-sm tracking-widest uppercase hover:bg-black hover:text-white transition">
                View Collection
            </a>

        </div>
    <?php endif; ?>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
