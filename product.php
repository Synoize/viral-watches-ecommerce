<?php
require_once __DIR__ . '/includes/functions.php';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$stmt = $pdo->prepare('SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON p.category = c.id WHERE p.id = ?');
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    $pageMetaOverrides = [
        'title' => 'Products not found | ShopMaster',
        'description' => 'The requested product is not available.',
    ];
    include __DIR__ . '/includes/header.php';
?>
    <div class="col-span-full text-center h-[82vh] md:h-[78vh] flex flex-col items-center justify-center">

        <i class="fa-solid fa-shopping-bag text-5xl text-slate-300"></i>

        <h3 class="mt-6 text-2xl font-serif text-[#222]">
            Products not found.
        </h3>

        <p class="mt-3 text-slate-500">
            This product is not available right now.
        </p>

        <a href="<?= BASE_URL ?>/collection"
            class="inline-block mt-8 border border-black px-8 py-3 text-sm tracking-widest uppercase hover:bg-black hover:text-white transition">
            Browse Products
        </a>

    </div>
<?php
    include __DIR__ . '/includes/footer.php';
    exit;
}

$relStmt = $pdo->prepare('
    SELECT id, name, price, offer_price, stock, images, gallery
    FROM products
    WHERE category = ? AND id != ?
    LIMIT 4
');
$relStmt->execute([$product['category'], $product['id']]);
$related = $relStmt->fetchAll();

$relatedWishlistIds = $related
    ? getWishlistProductIds(array_map('intval', array_column($related, 'id')))
    : [];

$gallery = json_decode($product['gallery'], true) ?: [];
$boxOptions = getActiveBoxOptions();
$defaultBox = $boxOptions[0] ?? null;
$productMaxQuantity = max(1, min(10, (int)$product['stock']));

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'submit_review') {
    if (!checkCsrfToken($_POST['csrf_token'] ?? '')) {
        flash('error', 'Invalid review request. Please try again.');
        redirect('/product.php?id=' . $id . '#customer-reviews');
    }

    $reviewImages = [];
    $reviewUploads = normalizeMultipleUploads($_FILES['review_images'] ?? []);
    if (count($reviewUploads) > 3) {
        flash('error', 'You can upload up to 3 review photos.');
        redirect('/product.php?id=' . $id . '#customer-reviews');
    }

    foreach ($reviewUploads as $uploadFile) {
        $reviewUpload = saveAdminImageUpload($uploadFile, 'reviews', 'product-review');
        if (!empty($reviewUpload['error'])) {
            deleteLocalAssetsIfUnused($reviewImages);
            flash('error', $reviewUpload['error']);
            redirect('/product.php?id=' . $id . '#customer-reviews');
        }
        if (!empty($reviewUpload['path'])) {
            $reviewImages[] = $reviewUpload['path'];
        }
    }

    $currentReviewUser = getCurrentUser();
    $reviewResult = createProductReview($id, [
        'name' => $_POST['review_name'] ?? ($currentReviewUser['name'] ?? ''),
        'email' => $_POST['review_email'] ?? ($currentReviewUser['email'] ?? ''),
        'rating' => $_POST['rating'] ?? 0,
        'comment' => $_POST['comment'] ?? '',
        'image' => $reviewImages,
    ], $currentReviewUser['id'] ?? null);

    if (!empty($reviewResult['error'])) {
        deleteLocalAssetsIfUnused($reviewImages);
        flash('error', $reviewResult['error']);
    } else {
        flash('success', 'Thank you. Your review was submitted and will appear after admin approval.');
    }
    redirect('/product.php?id=' . $id . '#customer-reviews');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['quantity'])) {
    if ((int)$product['stock'] <= 0) {
        flash('error', 'This product is currently out of stock.');
        redirect('/product.php?id=' . $id);
    }

    $quantity = max(1, min($productMaxQuantity, (int)$_POST['quantity']));
    $buyWithBox = ($_POST['buy_with_box'] ?? '0') === '1';
    $boxQuantity = max(1, min(10, (int)($_POST['box_quantity'] ?? 1)));
    $boxId = null;

    if ($buyWithBox) {
        foreach ($boxOptions as $boxOption) {
            if ((int)$boxOption['id'] === (int)($_POST['box_option_id'] ?? 0)) {
                $boxId = (int)$boxOption['id'];
                break;
            }
        }

        if (!$boxId && $defaultBox) {
            $boxId = (int)$defaultBox['id'];
        }
    }

    if (!isset($_SESSION['cart'][$id])) {
        $_SESSION['cart'][$id] = ['quantity' => 0];
    }

    $_SESSION['cart'][$id]['quantity'] += $quantity;
    $_SESSION['cart'][$id]['quantity'] = min($productMaxQuantity, $_SESSION['cart'][$id]['quantity']);
    $_SESSION['cart'][$id]['box_id'] = $boxId;
    $_SESSION['cart'][$id]['box_quantity'] = $boxId ? $boxQuantity : 0;

    $action = $_POST['action'] ?? 'add_cart';
    if ($action === 'buy_now') {
        redirect('/checkout.php');
    }

    flash('success', 'Product added to cart.');
    redirect('/cart.php');
}

$initialBox = $defaultBox ?: ['id' => '', 'name' => 'No box selected', 'image' => '', 'price' => 0];
$boxOptionsForJs = array_map(function ($box) {
    return [
        'id' => (int)$box['id'],
        'name' => $box['name'],
        'image' => resolveAssetUrl($box['image'] ?: 'assets/images/cartier-box.svg'),
        'price' => (float)$box['price'],
    ];
}, $boxOptions);
$pageMetaOverrides = [
    'tokens' => [
        'product_name' => $product['name'],
        'product_price' => 'Rs. ' . number_format($product['price'], 2),
        'category_name' => $product['category_name'] ?? 'General',
    ],
];
$isWished = isProductInWishlist($product['id']);
$reviewSummary = getApprovedProductReviewSummary($product['id']);
$approvedReviews = getApprovedProductReviews($product['id']);
$reviewTotal = (int)$reviewSummary['total'];
$reviewAverage = (float)$reviewSummary['average'];
$reviewDistribution = $reviewSummary['distribution'];
$reviewUser = getCurrentUser();
$productShareUrl = BASE_URL . '/product.php?id=' . (int)$product['id'];
?>
<?php include __DIR__ . '/includes/header.php'; ?>
<div class="mx-auto max-w-[1400px] px-4 py-10 md:px-10">
    <?php if ($message = flash('success')): ?><div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-700"><?= sanitize($message) ?></div><?php endif; ?>
    <?php if ($message = flash('error')): ?><div class="rounded-2xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-700"><?= sanitize($message) ?></div><?php endif; ?>

    <div class="mt-8 grid gap-10 lg:grid-cols-[520px_1fr] lg:items-start">
        <div class="space-y-6 lg:sticky lg:top-40">
            <div class="relative overflow-hidden rounded-2xl bg-white shadow-sm">
                <span class="absolute right-4 top-4 z-50">
                    <?= renderWishlistButton($product['id'], $isWished) ?>
                </span>

                <img
                    src="<?= sanitize(resolveAssetUrl($gallery[0] ?? $product['images'])) ?>"
                    alt="<?= sanitize($product['name']) ?>"
                    class="product-main-image max-h-[420px] w-full object-cover" />
            </div>
            <?php if ($gallery): ?>
                <div class="grid grid-cols-4 gap-3">
                    <?php foreach ($gallery as $src): ?>
                        <button type="button" class="product-thumb overflow-hidden rounded-xl border border-slate-200 bg-slate-50" data-src="<?= sanitize(resolveAssetUrl($src)) ?>">
                            <img src="<?= sanitize(resolveAssetUrl($src)) ?>" alt="<?= sanitize($product['name']) ?>" class="h-24 w-full object-cover hover:scale-105 transition duration-300" />
                        </button>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="space-y-6">
            <div class="space-y-6">

                <!-- Category -->
                <div>
                    <span class="inline-flex items-center rounded-full bg-slate-100 px-4 py-1.5 text-xs font-medium tracking-wide text-slate-700">
                        <?= sanitize($product['category_name'] ?? 'General') ?>
                    </span>

                    <h1 class="mt-4 text-3xl md:text-4xl font-medium leading-tight text-slate-900">
                        <?= sanitize($product['name']) ?>
                    </h1>

                    <div class="flex flex-wrap items-center gap-3 mt-4">

                        <div class="flex gap-1 text-amber-500">
                            <?php for ($star = 1; $star <= 5; $star++): ?>
                                <i class="<?= $star <= round($reviewAverage) ? 'fa-solid' : 'fa-regular' ?> fa-star text-lg"></i>
                            <?php endfor; ?>
                        </div>

                        <a href="#customer-reviews" class="text-lg font-serif text-[#222] hover:underline">
                            <?= $reviewTotal ?> <?= $reviewTotal === 1 ? 'Review' : 'Reviews' ?>
                        </a>

                    </div>
                </div>

                <!-- Price -->
                <div class="flex flex-wrap items-center gap-4">

                    <?php if (!empty($product['offer_price']) && $product['offer_price'] < $product['price']): ?>

                        <span class="text-xl md:text-2xl text-slate-900">
                            Rs. <?= number_format($product['offer_price'], 2) ?>
                        </span>

                        <span class="text-lg text-slate-400 line-through">
                            Rs. <?= number_format($product['price'], 2) ?>
                        </span>

                        <?php
                        $discount = round((($product['price'] - $product['offer_price']) / $product['price']) * 100);
                        ?>

                        <span class="rounded-full bg-green-50 px-3 py-1 text-sm font-semibold text-green-700">
                            <?= $discount ?>% OFF
                        </span>

                    <?php else: ?>

                        <span class="text-xl md:text-2xl text-slate-900">
                            Rs. <?= number_format($product['price'], 2) ?>
                        </span>

                    <?php endif; ?>

                </div>

                <!-- Availability -->
                <div>
                    <?php if ($product['stock'] > 10): ?>

                        <span class="inline-flex items-center gap-2 rounded-full bg-black px-6 py-2 text-sm text-white">
                            Sale
                        </span>

                    <?php elseif ($product['stock'] > 0): ?>

                        <span class="inline-flex items-center rounded-full bg-amber-500 px-6 py-2 text-sm text-white">
                            Only <?= (int)$product['stock'] ?> left
                        </span>

                    <?php else: ?>

                        <span class="inline-flex items-center rounded-full bg-red-600 px-6 py-2 text-sm text-white">
                            Out of Stock
                        </span>

                    <?php endif; ?>
                </div>

            </div>

            <form method="post" id="product-purchase-form" class="space-y-7" data-product-price="<?= (float)$product['price'] ?>">
                <div class="space-y-4">
                    <h2 class="text-2xl font-medium text-slate-900">Buy with Box <span class="text-rose-500">*</span></h2>
                    <div class="grid gap-4 grid-cols-2">
                        <label class="flex h-14 items-center gap-3 rounded-2xl border border-slate-200 bg-white px-5 text-base text-slate-900">
                            <input type="radio" name="buy_with_box" value="1" class="h-4 w-4 accent-black" <?= $boxOptions ? '' : 'disabled' ?>>
                            Yes
                        </label>
                        <label class="flex h-14 items-center gap-3 rounded-2xl border border-slate-200 bg-white px-5 text-base text-slate-900">
                            <input type="radio" name="buy_with_box" value="0" class="h-4 w-4 accent-black" checked>
                            No
                        </label>
                    </div>
                </div>

                <div id="box-panel" class="hidden rounded-2xl border border-slate-200 bg-white p-5 sm:p-6">
                    <div class="grid gap-6 md:grid-cols-[220px_1fr]">
                        <div>
                            <label for="box-option-select" class="text-base font-medium text-slate-600">Choose box</label>
                            <select id="box-option-select" name="box_option_id" class="mt-3 h-12 w-full rounded-xl border border-slate-200 bg-white px-4 text-sm text-slate-700 outline-none focus:border-slate-900" disabled>
                                <?php foreach ($boxOptions as $box): ?>
                                    <option value="<?= (int)$box['id'] ?>"><?= sanitize($box['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="grid gap-6 sm:grid-cols-[150px_1fr]">
                            <div class="flex aspect-square items-center justify-center border border-slate-200 bg-white p-3">
                                <img id="box-preview-image" src="<?= sanitize(resolveAssetUrl($initialBox['image'] ?: 'assets/images/cartier-box.svg')) ?>" alt="<?= sanitize($initialBox['name']) ?>" class="h-full w-full object-contain">
                            </div>

                            <div class="space-y-5">
                                <div>
                                    <h3 id="box-preview-name" class="text-xl font-medium text-slate-900"><?= sanitize($initialBox['name']) ?></h3>
                                    <p id="box-preview-price" class="mt-3 text-lg font-medium text-slate-900">Rs. <?= number_format($initialBox['price'], 2) ?></p>
                                </div>

                                <div class="grid gap-5 grid-cols-[1fr_144px] items-end">
                                    <div>
                                        <p class="text-sm uppercase tracking-wide text-slate-500">Box Amount</p>
                                        <p id="box-amount" class="mt-2 text-lg font-medium text-slate-900"><?= number_format($initialBox['price'], 2) ?></p>
                                    </div>
                                    <div>
                                        <p class="mb-3 text-center text-sm uppercase tracking-wide text-slate-500">Box Quantity</p>
                                        <div class="grid h-12 grid-cols-3 overflow-hidden rounded-xl border border-slate-200 bg-white">
                                            <button type="button" class="box-step flex items-center justify-center text-slate-500 hover:bg-slate-50" data-target="box_quantity" data-step="-1">
                                                <i data-lucide="minus" class="h-5 w-5 stroke-[1.5]"></i>
                                            </button>
                                            <input id="box-quantity" name="box_quantity" value="1" min="1" max="10" class="w-full border-x border-slate-200 text-center text-lg outline-none" readonly disabled>
                                            <button type="button" class="box-step flex items-center justify-center text-slate-500 hover:bg-slate-50" data-target="box_quantity" data-step="1">
                                                <i data-lucide="plus" class="h-5 w-5 stroke-[1.5]"></i>
                                            </button>
                                        </div>
                                        <p class="mt-2 text-right text-sm text-slate-500">Max: 10</p>
                                    </div>
                                </div>

                                <div class="border-t border-slate-200 pt-4">
                                    <div class="flex items-center justify-between gap-4">
                                        <span class="text-lg text-slate-600">Final total</span>
                                        <span id="final-total" class="text-2xl font-semibold text-slate-900">&#8377;<?= number_format($product['price'], 2) ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid gap-5 sm:grid-cols-[180px_1fr] sm:items-end">
                    <div>
                        <label class="text-lg text-slate-700 mr-2 md:mr-0">Quantity</label>
                        <div class="mt-4 inline-flex items-center overflow-hidden rounded-xl border border-slate-200 bg-white">

                            <button
                                type="button"
                                class="quantity-step flex h-14 w-14 items-center justify-center text-slate-700 transition-all duration-200 hover:bg-slate-100 active:scale-95"
                                data-target="quantity"
                                data-step="-1">

                                <i data-lucide="minus" class="h-5 w-5 stroke-[1.5]"></i>

                            </button>

                            <input
                                id="product-quantity"
                                type="number"
                                name="quantity"
                                value="1"
                                min="1"
                                max="<?= $productMaxQuantity ?>"
                                readonly
                                class="h-14 pl-2 w-16 border-x border-slate-200 bg-white text-center text-lg text-slate-900 outline-none" />

                            <button
                                type="button"
                                class="quantity-step flex h-14 w-14 items-center justify-center text-slate-700 transition-all duration-200 hover:bg-slate-100 active:scale-95"
                                data-target="quantity"
                                data-step="1">

                                <i data-lucide="plus" class="h-5 w-5 stroke-[1.5]"></i>

                            </button>

                        </div>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <button type="submit" name="action" value="add_cart" class="inline-flex h-14 items-center justify-center bg-slate-200/90 px-6 text-sm font-semibold uppercase text-slate-900 hover:bg-slate-200" <?= $product['stock'] ? '' : 'disabled' ?>>Add to Cart</button>
                        <button type="submit" name="action" value="buy_now" class="inline-flex h-14 items-center justify-center bg-black/90 px-6 text-sm font-semibold uppercase text-white hover:bg-black" <?= $product['stock'] ? '' : 'disabled' ?>>Buy Now</button>
                    </div>
                </div>

            </form>

            <!-- Payment Icons -->
            <div class="py-4">
                <h3 class="text-lg text-slate-700">
                    Secure Checkout With
                </h3>

                <div class="mt-4 flex flex-wrap gap-3">

                    <!-- Mastercard -->
                    <div class="group flex h-12 w-16 md:h-14 md:w-24 items-center justify-center rounded-xl border border-slate-200 bg-white shadow-sm transition hover:shadow-md">
                        <img src="https://upload.wikimedia.org/wikipedia/commons/2/2a/Mastercard-logo.svg"
                            alt="Mastercard"
                            class="max-h-7 md:max-h-9 w-auto object-contain">
                    </div>

                    <!-- Paytm -->
                    <div class="group flex h-12 w-16 md:h-14 md:w-24 items-center justify-center overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm transition hover:shadow-md">
                        <img src="https://i.ibb.co/RTDHZRbC/paytm-color-card.jpg"
                            alt="Paytm"
                            class="h-full w-full object-cover">
                    </div>

                    <!-- Visa -->
                    <div class="group flex h-12 w-16 md:h-14 md:w-24 items-center justify-center overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm transition hover:shadow-md">
                        <img src="https://i.ibb.co/pjYkM62V/visa-1-color-card.jpg"
                            alt="Visa"
                            class="h-full w-full object-cover">
                    </div>

                    <!-- Google Pay -->
                    <div class="group flex h-12 w-16 md:h-14 md:w-24 items-center justify-center rounded-xl border border-slate-200 bg-white shadow-sm transition hover:shadow-md">
                        <img src="https://upload.wikimedia.org/wikipedia/commons/f/f2/Google_Pay_Logo.svg"
                            alt="Google Pay"
                            class="max-h-7 md:max-h-8 w-auto object-contain px-2">
                    </div>

                </div>
            </div>

            <!-- Timeline -->
            <div class="max-w-xl mx-auto p-5">

                <div class="flex items-center justify-between gap-4">

                    <!-- Ordered -->
                    <div class="flex flex-col items-center text-center">
                        <i data-lucide="shopping-bag" class="h-8 w-8 stroke-[1]"></i>

                        <h4 class="mt-3 text-sm font-serif text-[#303030]">
                            Ordered
                        </h4>

                        <p id="ordered-date" class="text-sm text-slate-700"></p>
                    </div>

                    <i data-lucide="chevron-right" class="h-6 w-6 stroke-[1] text-slate-500"></i>

                    <!-- Ready -->
                    <div class="flex flex-col items-center text-center">
                        <i data-lucide="truck" class="h-8 w-8 stroke-[1]"></i>

                        <h4 class="mt-3 text-sm font-serif text-[#303030]">
                            Order Ready
                        </h4>

                        <p id="ready-date" class="text-sm text-slate-700"></p>
                    </div>

                    <i data-lucide="chevron-right" class="h-6 w-6 stroke-[1] text-slate-500"></i>

                    <!-- Delivered -->
                    <div class="flex flex-col items-center text-center">
                        <i data-lucide="map-pin" class="h-8 w-8 stroke-[1]"></i>

                        <h4 class="mt-3 text-sm font-serif text-[#303030]">
                            Delivered
                        </h4>

                        <p id="delivery-date" class="text-sm text-slate-700"></p>
                    </div>

                </div>

                <script>
                    function formatDate(date) {
                        return date.toLocaleDateString('en-US', {
                            month: 'short',
                            day: '2-digit'
                        });
                    }

                    const today = new Date();

                    const readyDate = new Date(today);
                    readyDate.setDate(today.getDate() + 1);

                    const deliveryStart = new Date(today);
                    deliveryStart.setDate(today.getDate() + 3);

                    const deliveryEnd = new Date(today);
                    deliveryEnd.setDate(today.getDate() + 4);

                    document.getElementById('ordered-date').textContent =
                        formatDate(today);

                    document.getElementById('ready-date').textContent =
                        formatDate(readyDate);

                    document.getElementById('delivery-date').textContent =
                        formatDate(deliveryStart) + ' - ' + formatDate(deliveryEnd);

                    if (typeof lucide !== 'undefined') {
                        lucide.createIcons();
                    }
                </script>

            </div>

            <div class="space-y-3 rounded-xl border border bg-white p-6">
                <details open class="group border-b pb-4">
                    <summary class="flex cursor-pointer list-none items-center justify-between text-sm uppercase tracking-[0.16em] text-slate-900">
                        Product Description
                        <span class="transition duration-300 group-open:rotate-180">
                            <i data-lucide="chevron-down" class="w-5 h-5 stroke-[1]"></i>
                        </span>
                    </summary>
                    <div class="pt-4 text-sm leading-6 text-slate-700">
                        <p><?= nl2br(sanitize($product['description'])) ?></p>
                        <p class="mt-3">If you choose a box option, its price is added to the final total and stored with the order details.</p>
                    </div>
                </details>

                <details class="group border-b pt-2 pb-4">
                    <summary class="flex cursor-pointer list-none items-center justify-between text-sm uppercase tracking-[0.16em] text-slate-900">
                        Replacement policy
                        <span class="transition duration-300 group-open:rotate-180">
                            <i data-lucide="chevron-down" class="w-5 h-5 stroke-[1]"></i>
                        </span>
                    </summary>
                    <div class="pt-4 text-sm leading-6 text-slate-700">
                        Please note that we do not offer returns on any products. Replacement is only available if the product is damaged from our side.
                        <br>
                        To request a replacement in case of damage:
                        You must record a proper unboxing video while opening the package for the first time and send on Whatsapp. The video should clearly show the sealed package, the opening process, and the product condition. Replacement requests without an unboxing video will not be accepted.
                    </div>
                </details>

                <details class="group pt-2">
                    <summary class="flex cursor-pointer list-none items-center justify-between text-sm uppercase tracking-[0.16em] text-slate-900">
                        Box Policy
                        <span class="transition duration-300 group-open:rotate-180">
                            <i data-lucide="chevron-down" class="w-5 h-5 stroke-[1]"></i>
                        </span>
                    </summary>
                    <div class="pt-4 text-sm leading-6 text-slate-700">
                        Standard packaging: Every watch is shipped in a secure, high-quality protective box suitable for safe delivery. This is included with every order at no extra cost.
                        <br>
                        Original / Brand box: Extra charges apply if an original or brand box is available for your selected watch. The extra fee will be shown at checkout. If the charge can't be displayed automatically, our team will confirm the box option and additional charges before shipping.
                    </div>
                </details>
            </div>


            <button
                type="button"
                class="product-share-button inline-flex items-center gap-2 rounded-full border border-slate-900 px-4 py-2 text-sm text-slate-700 transition hover:bg-slate-900 hover:text-white"
                data-share-title="<?= sanitize($product['name']) ?>"
                data-share-text="<?= sanitize('Check out ' . $product['name']) ?>"
                data-share-url="<?= sanitize($productShareUrl) ?>">
                <i data-lucide="share-2" class="h-4 w-4 stroke-[1.5]"></i>
                Share
            </button>
        </div>
    </div>

    <section id="customer-reviews" class="pt-12 md:pt-20">
        <div class="flex items-start justify-between gap-6">
            <h2 class="text-[30px] md:text-[40px] leading-none font-serif text-[#303030]">Customer Reviews</h2>
            <?php if ($reviewTotal > 0): ?>
                <button
                    type="button"
                    class="open-review-modal inline-flex items-center gap-2 text-sm text-slate-900 hover:text-black transition-colors">

                    <i data-lucide="pencil-line" class="w-4 h-4 stroke-[1]"></i>

                    Write a Review

                </button>
            <?php endif; ?>
        </div>

        <?php if ($reviewTotal > 0): ?>
            <div class="mt-8 grid gap-8 md:grid-cols-[220px_1fr] md:items-center">
                <div class="text-center md:border-r md:border-slate-200 md:pr-8">
                    <p class="text-5xl font-semibold text-slate-950"><?= number_format($reviewAverage, 1) ?></p>
                    <div class="mt-4 flex justify-center gap-1 text-amber-500">
                        <?php for ($star = 1; $star <= 5; $star++): ?>
                            <i class="<?= $star <= round($reviewAverage) ? 'fa-solid' : 'fa-regular' ?> fa-star"></i>
                        <?php endfor; ?>
                    </div>
                    <p class="mt-4 text-sm text-slate-600"><?= $reviewTotal ?> <?= $reviewTotal === 1 ? 'review' : 'reviews' ?></p>
                </div>

                <div class="space-y-3">
                    <?php foreach ([5, 4, 3, 2, 1] as $ratingRow): ?>
                        <?php
                        $ratingCount = (int)($reviewDistribution[$ratingRow] ?? 0);
                        $ratingPercent = $reviewTotal > 0 ? min(100, round(($ratingCount / $reviewTotal) * 100)) : 0;
                        ?>
                        <div class="grid grid-cols-[54px_1fr_28px] items-center gap-3 text-sm text-slate-700">
                            <span><?= $ratingRow ?> Star</span>
                            <span class="h-2 overflow-hidden rounded-full bg-slate-200">
                                <span class="block h-full rounded-full bg-amber-500" style="width: <?= $ratingPercent ?>%"></span>
                            </span>
                            <span class="text-slate-900"><?= $ratingCount ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php else: ?>
            <div class="flex min-h-[40vh] flex-col items-center justify-center py-12 text-center">
                <div class="flex justify-center gap-1 text-2xl text-slate-300">
                    <?php for ($star = 1; $star <= 5; $star++): ?>
                        <i class="fa-solid fa-star"></i>
                    <?php endfor; ?>
                </div>
                <p class="mt-3 font-serif text-2xl text-slate-950">There are no reviews yet.</p>
                <button type="button" class="open-review-modal inline-block mt-8 border border-black px-8 py-3 text-sm tracking-widest uppercase hover:bg-black hover:text-white transition">
                    Write a review
                </button>
            </div>
        <?php endif; ?>

        <?php if ($approvedReviews): ?>
            <div class="mt-12 grid gap-6 grid-cols-3 sm:grid-cols-5 lg:grid-cols-6">

                <?php foreach ($approvedReviews as $review): ?>
                    <?php $reviewImages = getReviewImages($review['image'] ?? ''); ?>

                    <article class="overflow-hidden">

                        <!-- Review Image -->
                        <?php if ($reviewImages): ?>
                            <?php
                            $reviewImageUrls = array_map('resolveAssetUrl', $reviewImages);
                            $reviewImageJson = json_encode($reviewImageUrls, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
                            ?>
                            <button
                                type="button"
                                class="review-gallery-open relative block w-full overflow-hidden"
                                data-images='<?= sanitize($reviewImageJson) ?>'
                                data-index="0"
                                aria-label="Open review images">
                                <img
                                    src="<?= sanitize($reviewImageUrls[0]) ?>"
                                    alt="<?= sanitize($review['name']) ?> review image"
                                    class="w-full aspect-[4/5] object-cover transition duration-300 hover:scale-105">
                                <?php if (count($reviewImageUrls) > 1): ?>
                                    <span class="absolute bottom-2 right-2 rounded-full bg-black/70 px-2 py-1 text-xs font-medium text-white">
                                        1/<?= count($reviewImageUrls) ?>
                                    </span>
                                <?php endif; ?>
                            </button>
                        <?php else: ?>
                            <div class="aspect-[4/5] flex items-center justify-center bg-slate-50 text-slate-400">
                                <i class="fa-regular fa-image text-4xl"></i>
                            </div>
                        <?php endif; ?>

                        <!-- Content -->
                        <div class="py-4 px-2">

                            <!-- Rating -->
                            <div class="flex gap-1 text-[#f4a300] text-sm md:text-lg mb-2">
                                <?php for ($star = 1; $star <= 5; $star++): ?>
                                    <i class="<?= $star <= (int)$review['rating'] ? 'fa-solid' : 'fa-regular' ?> fa-star"></i>
                                <?php endfor; ?>
                            </div>

                            <!-- Name -->
                            <h3 class="mt-2 text-base font-serif text-black">
                                <?= sanitize($review['name']) ?>
                            </h3>

                            <!-- Verified Purchase -->
                            <?php if (!empty($review['is_verified_purchase'])): ?>
                                <div class="mt-1 flex items-center gap-1.5 text-xs text-slate-600">
                                    <i class="fa-solid fa-circle-check text-[11px]"></i>
                                    <span>Verified purchase</span>
                                </div>
                            <?php endif; ?>

                            <!-- Review Text -->
                            <p class="mt-1 text-sm leading-6 font-serif text-slate-700 line-clamp-3">
                                <?= nl2br(sanitize($review['comment'])) ?>
                            </p>

                            <!-- Date -->
                            <p class="mt-1 text-xs text-slate-400 font-serif">
                                <?= date('d M Y', strtotime($review['created_at'])) ?>
                            </p>

                        </div>

                    </article>

                <?php endforeach; ?>

            </div>
        <?php endif; ?>
    </section>

    <div id="review-modal" class="fixed inset-0 z-[9999] hidden items-center justify-center bg-black/50 px-4 py-6">
        <div class="relative max-h-[84vh] w-full max-w-lg overflow-y-auto bg-white p-6 rounded shadow-2xl md:p-8">
            <button type="button" class="close-review-modal absolute right-4 top-4 text-slate-500 hover:text-slate-950" aria-label="Close review form">
                <i data-lucide="x" class="h-6 w-6 stroke-[1]"></i>
            </button>

            <h2 class="pr-10 font-serif text-3xl text-slate-950">Write a review</h2>
            <form method="post" enctype="multipart/form-data" class="review-form mt-6 space-y-5">
                <input type="hidden" name="action" value="submit_review">
                <input type="hidden" name="csrf_token" value="<?= sanitize($csrfToken) ?>">
                <input type="hidden" name="rating" value="" class="review-rating-input">

                <div class="grid gap-4 sm:grid-cols-2">
                    <label class="block text-sm font-medium text-slate-700">
                        Name
                        <input name="review_name" value="<?= sanitize($reviewUser['name'] ?? '') ?>" required class="mt-2 h-12 w-full rounded-xl border border-slate-200 bg-white px-4 text-sm text-slate-900 outline-none focus:border-slate-900">
                    </label>
                    <label class="block text-sm font-medium text-slate-700">
                        Email
                        <input type="email" name="review_email" value="<?= sanitize($reviewUser['email'] ?? '') ?>" class="mt-2 h-12 w-full rounded-xl border border-slate-200 bg-white px-4 text-sm text-slate-900 outline-none focus:border-slate-900">
                    </label>
                </div>

                <fieldset>
                    <legend class="text-base font-serif text-slate-900">Rating</legend>
                    <div class="review-star-picker mt-3 flex gap-1" aria-label="Select rating">
                        <?php for ($ratingOption = 1; $ratingOption <= 5; $ratingOption++): ?>
                            <button type="button" class="review-star text-3xl leading-none text-amber-500 transition hover:scale-105" data-rating="<?= $ratingOption ?>" aria-label="<?= $ratingOption ?> star rating">
                                <i data-lucide="star" class="h-6 w-6 stroke-[1]"></i>
                            </button>
                        <?php endfor; ?>
                    </div>
                </fieldset>

                <label class="block text-base font-serif text-slate-900">
                    Review
                    <span class="relative mt-3 block">
                        <textarea name="comment" rows="4" maxlength="2000" required class="review-comment w-full resize-none rounded-none border-0 border-b border-slate-200 bg-white text-base text-slate-900 outline-none placeholder:text-slate-400 focus:border-slate-900" placeholder="Share your feedback with us now"></textarea>
                        <span class="review-character-count pointer-events-none absolute bottom-4 right-3 text-sm text-slate-400">0/2000</span>
                    </span>
                </label>

                <div>
                    <label class="inline-flex cursor-pointer flex-col items-center gap-2 text-slate-900">
                        <span class="flex h-14 w-14 items-center justify-center text-4xl font-light leading-none">
                            <i data-lucide="plus" class="h-6 w-6 stroke-[1]"></i>
                        </span>
                        <span class="font-serif text-base">Add photo</span>
                        <span class="review-photo-count text-sm text-slate-600">0/3</span>
                        <input type="file" name="review_images[]" accept="image/png,image/jpeg,image/webp" multiple class="review-photo-input sr-only">
                    </label>
                    <div class="review-photo-names mt-3 grid grid-cols-3 gap-2 text-xs text-slate-500"></div>
                </div>

                <button class="inline-flex h-12 w-full items-center justify-center rounded-xl bg-slate-950 px-5 text-sm uppercase tracking-wide text-white hover:bg-slate-800">Submit</button>
                <p class="text-xs text-slate-500">Reviews are published after admin approval.</p>
            </form>
        </div>
    </div>

    <div id="review-gallery-modal" class="fixed inset-0 z-[10000] hidden items-center justify-center bg-black/90 px-4 py-6">
        <button type="button" class="close-review-gallery absolute right-5 top-5 z-20 text-white/80 hover:text-white" aria-label="Close review images">
            <i data-lucide="x" class="h-8 w-8 stroke-[1]"></i>
        </button>

        <button type="button" id="review-gallery-prev" class="absolute left-3 top-1/2 z-20 flex h-11 w-11 -translate-y-1/2 items-center justify-center rounded-full bg-white/15 text-white backdrop-blur hover:bg-white/25 md:left-8" aria-label="Previous review image">
            <i data-lucide="chevron-left" class="h-7 w-7 stroke-[1]"></i>
        </button>

        <figure class="relative flex max-h-[88vh] w-full max-w-5xl flex-col items-center justify-center gap-4">
            <img id="review-gallery-image" src="" alt="Review image" class="max-h-[78vh] max-w-full object-contain rounded">
            <figcaption id="review-gallery-count" class="rounded-full bg-white/10 px-4 py-2 text-sm font-medium text-white"></figcaption>
        </figure>

        <button type="button" id="review-gallery-next" class="absolute right-3 top-1/2 z-20 flex h-11 w-11 -translate-y-1/2 items-center justify-center rounded-full bg-white/15 text-white backdrop-blur hover:bg-white/25 md:right-8" aria-label="Next review image">
            <i data-lucide="chevron-right" class="h-7 w-7 stroke-[1]"></i>
        </button>
    </div>

    <div class="pt-10 md:pt-20 md:pb-14">
        <!-- HEADING -->
        <div class="mb-10 md:mb-14">
            <h2 class="text-[32px] md:text-[42px] leading-none font-serif text-[#303030]">
                Related Products
            </h2>
        </div>

        <?php if ($related): ?>

            <!-- SLIDER -->
            <div class="flex gap-3 md:gap-6 overflow-x-auto snap-x snap-mandatory [scrollbar-width:none] [-ms-overflow-style:none] [&::-webkit-scrollbar]:hidden">

                <?php foreach ($related as $item): ?>

                    <?php
                    $gallery = json_decode($item['gallery'] ?? '[]', true) ?: [];

                    $mainImage = resolveAssetUrl(
                        $item['images'] ?: ($gallery[0] ?? '')
                    );

                    $hoverImage = resolveAssetUrl(
                        $gallery[1] ?? ($gallery[0] ?? $item['images'])
                    );

                    $hasOffer =
                        (float)$item['offer_price'] > 0 &&
                        (float)$item['offer_price'] < (float)$item['price'];

                    $displayPrice = $hasOffer
                        ? (float)$item['offer_price']
                        : (float)$item['price'];

                    $stock = (int)$item['stock'];

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

                    <article class="relative group flex-shrink-0 w-[145px] md:flex-1 snap-start">

                        <a href="<?= BASE_URL ?>/product.php?id=<?= (int)$item['id'] ?>" class="block">

                            <div class="relative bg-white rounded-md overflow-hidden">

                                <?php if ($mainImage): ?>
                                    <img
                                        src="<?= sanitize($mainImage) ?>"
                                        alt="<?= sanitize($item['name']) ?>"
                                        class="w-full md:w-[400px] h-[180px] md:h-[440px] object-contain p-5 md:p-8 transition-all duration-500 group-hover:opacity-0">
                                <?php else: ?>
                                    <div class="flex h-[180px] w-full items-center justify-center bg-slate-100 p-5 text-center text-sm text-slate-500 md:h-[440px] md:w-[400px]">
                                        Image not found
                                    </div>
                                <?php endif; ?>

                                <?php if ($hoverImage): ?>
                                    <img
                                        src="<?= sanitize($hoverImage) ?>"
                                        alt="<?= sanitize($item['name']) ?>"
                                        class="absolute inset-0 h-full w-full object-cover opacity-0 transition-all duration-500 group-hover:opacity-100">
                                <?php endif; ?>

                                <span class="absolute left-2 md:left-5 bottom-2 md:bottom-5 text-[12px] px-3 md:px-4 py-1 md:py-1.5 rounded-full z-10 <?= $badgeClass ?>">
                                    <?= htmlspecialchars($badgeText) ?>
                                </span>

                            </div>

                            <!-- CONTENT -->
                            <div class="pt-3 md:pt-5">

                                <h3 class="text-[14px] md:text-[16px] leading-[1.3] md:leading-[1.4] text-[#222] mb-2 md:mb-3">
                                    <?= sanitize($item['name']) ?>
                                </h3>

                                <div class="flex flex-col md:flex-row md:items-center gap-1 md:gap-4 flex-wrap">

                                    <?php if ($hasOffer): ?>
                                        <span class="text-[12px] md:text-[14px] text-[#666] line-through">
                                            Rs. <?= number_format((float)$item['price'], 2) ?>
                                        </span>
                                    <?php endif; ?>

                                    <span class="text-[14px] md:text-[16px] font-medium text-black">
                                        Rs. <?= number_format($displayPrice, 2) ?>
                                    </span>

                                </div>

                            </div>

                        </a>

                        <?= renderWishlistButton(
                            $item['id'],
                            in_array((int)$item['id'], $relatedWishlistIds, true),
                            'absolute right-2 top-2 z-20 md:right-4 md:top-4'
                        ) ?>

                    </article>

                <?php endforeach; ?>

            </div>

        <?php else: ?>

            <div class="text-center py-16 md:py-24">

                <i class="fa-regular fa-heart text-5xl text-slate-300"></i>

                <h3 class="mt-4 text-2xl font-serif text-[#303030]">
                    No Related Products
                </h3>

                <p class="mt-2 text-slate-500">
                    Related products are currently unavailable.
                </p>

                <a href="<?= BASE_URL ?>/collection"
                    class="inline-block mt-6 bg-[#D3D3D3] text-black px-10 py-4 text-[18px] font-serif hover:bg-[#A9A9A9] transition">
                    View Collection
                </a>

            </div>

        <?php endif; ?>

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('product-purchase-form');
            if (!form) return;

            const boxes = <?= json_encode($boxOptionsForJs, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
            const productPrice = Number(form.dataset.productPrice || 0);
            const productQty = document.getElementById('product-quantity');
            const boxQty = document.getElementById('box-quantity');
            const select = document.getElementById('box-option-select');
            const panel = document.getElementById('box-panel');
            const image = document.getElementById('box-preview-image');
            const name = document.getElementById('box-preview-name');
            const price = document.getElementById('box-preview-price');
            const amount = document.getElementById('box-amount');
            const total = document.getElementById('final-total');
            const radios = form.querySelectorAll('input[name="buy_with_box"]');
            const money = new Intl.NumberFormat('en-IN', {
                style: 'currency',
                currency: 'INR'
            });

            function selectedBox() {
                return boxes.find((box) => String(box.id) === String(select?.value)) || boxes[0] || {
                    name: 'No box selected',
                    image: '',
                    price: 0
                };
            }

            function boxEnabled() {
                const checked = form.querySelector('input[name="buy_with_box"]:checked');
                return checked && checked.value === '1' && boxes.length > 0;
            }

            function renderTotals() {
                const box = selectedBox();
                const productQuantity = Number(productQty.value || 1);
                const boxQuantity = Number(boxQty.value || 1);
                const hasBox = boxEnabled();
                const boxAmount = hasBox ? Number(box.price) * boxQuantity : 0;

                panel.classList.toggle('hidden', !hasBox);
                if (select) select.disabled = !hasBox;
                boxQty.disabled = !hasBox;

                image.src = box.image || '<?= BASE_URL ?>/assets/images/cartier-box.svg';
                image.alt = box.name;
                name.textContent = hasBox ? box.name : 'No box selected';
                price.textContent = hasBox ? money.format(Number(box.price)) : money.format(0);
                amount.textContent = money.format(boxAmount);
                total.textContent = money.format((productPrice * productQuantity) + boxAmount);
            }

            form.querySelectorAll('.quantity-step, .box-step').forEach((button) => {
                button.addEventListener('click', function() {
                    const input = this.dataset.target === 'box_quantity' ? boxQty : productQty;
                    const step = Number(this.dataset.step || 0);
                    const min = Number(input.min || 1);
                    const max = Number(input.max || 10);
                    input.value = Math.max(min, Math.min(max, Number(input.value || min) + step));
                    renderTotals();
                });
            });

            radios.forEach((radio) => radio.addEventListener('change', renderTotals));
            select?.addEventListener('change', renderTotals);
            renderTotals();

            document.querySelectorAll('.product-share-button').forEach((button) => {
                const originalLabel = button.innerHTML;
                button.addEventListener('click', async () => {
                    const shareData = {
                        title: button.dataset.shareTitle || document.title,
                        text: button.dataset.shareText || '',
                        url: button.dataset.shareUrl || window.location.href,
                    };

                    try {
                        if (navigator.share) {
                            await navigator.share(shareData);
                            return;
                        }

                        if (navigator.clipboard?.writeText) {
                            await navigator.clipboard.writeText(shareData.url);
                        } else {
                            const tempInput = document.createElement('input');
                            tempInput.value = shareData.url;
                            document.body.appendChild(tempInput);
                            tempInput.select();
                            document.execCommand('copy');
                            tempInput.remove();
                        }

                        button.innerHTML = '<i data-lucide="check" class="h-4 w-4 stroke-[1.5]"></i> Copied';
                        if (window.lucide) lucide.createIcons();
                        setTimeout(() => {
                            button.innerHTML = originalLabel;
                            if (window.lucide) lucide.createIcons();
                        }, 1800);
                    } catch (error) {
                        if (error?.name !== 'AbortError') {
                            button.innerHTML = '<i data-lucide="copy-x" class="h-4 w-4 stroke-[1.5]"></i> Copy failed';
                            if (window.lucide) lucide.createIcons();
                            setTimeout(() => {
                                button.innerHTML = originalLabel;
                                if (window.lucide) lucide.createIcons();
                            }, 1800);
                        }
                    }
                });
            });

            const reviewModal = document.getElementById('review-modal');
            const openReviewButtons = document.querySelectorAll('.open-review-modal');
            const closeReviewButtons = document.querySelectorAll('.close-review-modal');
            const reviewGalleryModal = document.getElementById('review-gallery-modal');
            const reviewGalleryImage = document.getElementById('review-gallery-image');
            const reviewGalleryCount = document.getElementById('review-gallery-count');
            const reviewGalleryPrev = document.getElementById('review-gallery-prev');
            const reviewGalleryNext = document.getElementById('review-gallery-next');
            let reviewGalleryImages = [];
            let reviewGalleryIndex = 0;

            function openReviewModal() {
                if (!reviewModal) return;
                reviewModal.classList.remove('hidden');
                reviewModal.classList.add('flex');
                document.body.classList.add('overflow-hidden');
            }

            function closeReviewModal() {
                if (!reviewModal) return;
                reviewModal.classList.add('hidden');
                reviewModal.classList.remove('flex');
                document.body.classList.remove('overflow-hidden');
            }

            function renderReviewGallery() {
                if (!reviewGalleryImage || !reviewGalleryImages.length) return;
                reviewGalleryIndex = (reviewGalleryIndex + reviewGalleryImages.length) % reviewGalleryImages.length;
                reviewGalleryImage.src = reviewGalleryImages[reviewGalleryIndex];
                reviewGalleryCount.textContent = `${reviewGalleryIndex + 1}/${reviewGalleryImages.length}`;
                const hasMany = reviewGalleryImages.length > 1;
                reviewGalleryPrev.classList.toggle('hidden', !hasMany);
                reviewGalleryNext.classList.toggle('hidden', !hasMany);
            }

            function openReviewGallery(images, index = 0) {
                if (!reviewGalleryModal || !images.length) return;
                reviewGalleryImages = images;
                reviewGalleryIndex = index;
                renderReviewGallery();
                reviewGalleryModal.classList.remove('hidden');
                reviewGalleryModal.classList.add('flex');
                document.body.classList.add('overflow-hidden');
            }

            function closeReviewGallery() {
                if (!reviewGalleryModal) return;
                reviewGalleryModal.classList.add('hidden');
                reviewGalleryModal.classList.remove('flex');
                reviewGalleryImages = [];
                reviewGalleryIndex = 0;
                if (reviewGalleryImage) reviewGalleryImage.src = '';
                if (reviewModal?.classList.contains('hidden')) {
                    document.body.classList.remove('overflow-hidden');
                }
            }

            openReviewButtons.forEach((button) => button.addEventListener('click', openReviewModal));
            closeReviewButtons.forEach((button) => button.addEventListener('click', closeReviewModal));
            reviewModal?.addEventListener('click', (event) => {
                if (event.target === reviewModal) closeReviewModal();
            });
            document.querySelectorAll('.review-gallery-open').forEach((button) => {
                button.addEventListener('click', () => {
                    let images = [];
                    try {
                        images = JSON.parse(button.dataset.images || '[]');
                    } catch (error) {
                        images = [];
                    }
                    openReviewGallery(images, Number(button.dataset.index || 0));
                });
            });
            document.querySelectorAll('.close-review-gallery').forEach((button) => button.addEventListener('click', closeReviewGallery));
            reviewGalleryModal?.addEventListener('click', (event) => {
                if (event.target === reviewGalleryModal) closeReviewGallery();
            });
            reviewGalleryPrev?.addEventListener('click', () => {
                reviewGalleryIndex -= 1;
                renderReviewGallery();
            });
            reviewGalleryNext?.addEventListener('click', () => {
                reviewGalleryIndex += 1;
                renderReviewGallery();
            });
            document.addEventListener('keydown', (event) => {
                const galleryOpen = reviewGalleryModal && !reviewGalleryModal.classList.contains('hidden');
                if (event.key === 'Escape') {
                    if (galleryOpen) {
                        closeReviewGallery();
                    } else {
                        closeReviewModal();
                    }
                }
                if (galleryOpen && event.key === 'ArrowLeft') {
                    reviewGalleryIndex -= 1;
                    renderReviewGallery();
                }
                if (galleryOpen && event.key === 'ArrowRight') {
                    reviewGalleryIndex += 1;
                    renderReviewGallery();
                }
            });

            document.querySelectorAll('.review-form').forEach((reviewForm) => {
                const ratingInput = reviewForm.querySelector('.review-rating-input');
                const stars = Array.from(reviewForm.querySelectorAll('.review-star'));
                const comment = reviewForm.querySelector('.review-comment');
                const characterCount = reviewForm.querySelector('.review-character-count');
                const photoInput = reviewForm.querySelector('.review-photo-input');
                const photoCount = reviewForm.querySelector('.review-photo-count');
                const photoNames = reviewForm.querySelector('.review-photo-names');
                let selectedReviewFiles = [];

                function paintStars(value) {
                    stars.forEach((star) => {
                        const active = Number(star.dataset.rating || 0) <= value;
                        star.classList.toggle('text-amber-500', active);
                        star.classList.toggle('text-slate-300', !active);
                        star.classList.toggle('[&>svg]:fill-current', active);
                    });
                }

                stars.forEach((star) => {
                    star.classList.remove('text-amber-500');
                    star.classList.add('text-slate-300');
                    star.addEventListener('click', () => {
                        const value = Number(star.dataset.rating || 0);
                        ratingInput.value = value;
                        paintStars(value);
                    });
                    star.addEventListener('mouseenter', () => {
                        paintStars(Number(star.dataset.rating || 0));
                    });
                });

                reviewForm.querySelector('.review-star-picker')?.addEventListener('mouseleave', () => {
                    paintStars(Number(ratingInput.value || 0));
                });

                comment?.addEventListener('input', () => {
                    characterCount.textContent = `${comment.value.length}/2000`;
                });

                function syncReviewPhotoInput() {
                    if (!photoInput) return;
                    const transfer = new DataTransfer();
                    selectedReviewFiles.forEach((file) => transfer.items.add(file));
                    photoInput.files = transfer.files;
                }

                function renderReviewPhotoPreviews() {
                    photoCount.textContent = `${selectedReviewFiles.length}/3`;
                    photoNames.innerHTML = '';

                    selectedReviewFiles.forEach((file, index) => {
                        const preview = document.createElement('div');
                        preview.className = 'relative overflow-hidden rounded-lg border border-slate-200 bg-slate-50';

                        const image = document.createElement('img');
                        image.className = 'aspect-square w-full object-cover';
                        image.alt = file.name;
                        image.src = URL.createObjectURL(file);
                        image.addEventListener('load', () => URL.revokeObjectURL(image.src), {
                            once: true
                        });

                        const remove = document.createElement('button');
                        remove.type = 'button';
                        remove.className = 'absolute right-1 top-1 flex h-6 w-6 items-center justify-center rounded-full bg-black/70 text-xs text-white';
                        remove.setAttribute('aria-label', 'Remove photo');
                        remove.textContent = 'x';
                        remove.addEventListener('click', () => {
                            selectedReviewFiles.splice(index, 1);
                            syncReviewPhotoInput();
                            renderReviewPhotoPreviews();
                        });

                        preview.appendChild(image);
                        preview.appendChild(remove);
                        photoNames.appendChild(preview);
                    });
                }

                photoInput?.addEventListener('change', () => {
                    const files = Array.from(photoInput.files || []);
                    const existingKeys = new Set(selectedReviewFiles.map((file) => `${file.name}-${file.size}-${file.lastModified}`));
                    const newFiles = files.filter((file) => !existingKeys.has(`${file.name}-${file.size}-${file.lastModified}`));

                    if (selectedReviewFiles.length + newFiles.length > 3) {
                        alert('You can upload up to 3 photos.');
                        selectedReviewFiles = selectedReviewFiles.concat(newFiles).slice(0, 3);
                    } else {
                        selectedReviewFiles = selectedReviewFiles.concat(newFiles);
                    }

                    syncReviewPhotoInput();
                    renderReviewPhotoPreviews();
                });
            });
        });
    </script>
    <?php include __DIR__ . '/includes/footer.php'; ?>