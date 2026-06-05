<?php
require_once __DIR__ . '/includes/functions.php';
ensureProductBestSellerColumn();
ensureProductVideosTableExists();
$featuredCategories = getCategories();
$heroSlides = getActiveHeroSlides();
$makeSlug = function ($value) {
    return strtolower(preg_replace('/[^a-z0-9]+/i', '-', trim($value)));
};
$stmt = $pdo->query('SELECT * FROM products WHERE stock > 0 AND is_best_seller = 1 ORDER BY id DESC LIMIT 8');
$bestSellers = $stmt->fetchAll();
$stmt = $pdo->query('SELECT * FROM products WHERE stock > 0 ORDER BY id DESC LIMIT 8');
$trending = $stmt->fetchAll();
$stmt = $pdo->query(
    "SELECT p.*, pv.file_path AS video_path
     FROM products p
     INNER JOIN (
        SELECT product_id, MAX(id) AS latest_video_id
        FROM products_video
        WHERE file_path <> ''
        GROUP BY product_id
     ) latest ON latest.product_id = p.id
     INNER JOIN products_video pv ON pv.id = latest.latest_video_id
     WHERE p.stock > 0
     ORDER BY pv.id DESC
     LIMIT 12"
);
$watchBuyProducts = $stmt->fetchAll();
$categoryProductSections = [];
if ($featuredCategories) {
    $categoryIds = array_map('intval', array_column($featuredCategories, 'id'));
    $placeholders = implode(',', array_fill(0, count($categoryIds), '?'));
    $stmt = $pdo->prepare("SELECT * FROM products WHERE stock > 0 AND category IN ($placeholders) ORDER BY category ASC, id DESC");
    $stmt->execute($categoryIds);
    $productsByCategory = [];
    foreach ($stmt->fetchAll() as $product) {
        $categoryId = (int)$product['category'];
        if (!isset($productsByCategory[$categoryId])) {
            $productsByCategory[$categoryId] = [];
        }
        if (count($productsByCategory[$categoryId]) < 4) {
            $productsByCategory[$categoryId][] = $product;
        }
    }
    foreach ($featuredCategories as $category) {
        $categoryId = (int)$category['id'];
        $categoryProductSections[] = [
            'category' => $category,
            'products' => $productsByCategory[$categoryId] ?? [],
            'slug' => $makeSlug($category['name']),
        ];
    }
}
?>
<?php include __DIR__ . '/includes/header.php'; ?>

<!-- HERO SECTION -->
<section class="relative w-full overflow-hidden">
    <!-- SLIDER -->
    <div class="swiper heroSlider">
        <div class="swiper-wrapper">
            <?php if ($heroSlides): ?>
                <?php foreach ($heroSlides as $slide): ?>
                    <?php
                    $desktopImage = resolveAssetUrl($slide['file_path'] ?? '');
                    $mobileImage = resolveAssetUrl($slide['mobile_file_path'] ?: ($slide['file_path'] ?? ''));
                    ?>
                    <div class="swiper-slide relative">
                        <picture>
                            <?php if ($mobileImage): ?>
                                <source media="(max-width: 767px)" srcset="<?= sanitize($mobileImage) ?>" />
                            <?php endif; ?>
                            <img src="<?= sanitize($desktopImage) ?>" alt="Hero banner"
                                class="w-full h-[650px] md:h-[750px] lg:h-[550px] object-cover" />
                        </picture>
                        <div class="absolute inset-0 bg-black/20"></div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="swiper-slide relative">
                    <div class="flex h-[650px] w-full items-center justify-center bg-slate-100 px-4 text-center text-slate-600 md:h-[750px] lg:h-[550px]">
                        Slides not found.
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <!-- LEFT ARROW -->
        <div class="swiper-button-prev !text-black !w-[35px] !h-[35px] !bg-white rounded-full [&::after]:!text-[14px]">
        </div>
        <!-- RIGHT ARROW -->
        <div class="swiper-button-next !text-black !w-[35px] !h-[35px] !bg-white rounded-full [&::after]:!text-[14px]">
        </div>
    </div>
</section>

<!-- BEST SELLER SECTION -->
<section class="w-full py-10 md:py-14 overflow-hidden ">
    <div class="max-w-[1920px] mx-auto px-4 md:px-10">
        <!-- HEADING -->
        <div class="text-center mb-10 md:mb-16 px-4">
            <h2 class="text-[32px] md:text-[42px] leading-none font-serif text-[#303030] animate-slide-bottom">
                Best Seller
            </h2>
        </div>
        <?php if ($bestSellers): ?>
            <!-- SLIDER -->
            <div
                class="flex gap-3 md:gap-6 overflow-x-auto snap-x snap-mandatory [scrollbar-width:none] [-ms-overflow-style:none] [&::-webkit-scrollbar]:hidden md:-mt-[25px] scroll-animate-bottom">
                <?php foreach (array_slice($bestSellers, 0, 4) as $product): ?>
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
                    <!-- CARD -->
                    <a href="<?= BASE_URL ?>/product.php?id=<?= (int)$product['id'] ?>" class="group flex-shrink-0 w-[145px] md:flex-1 snap-start">
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

                        <!-- CONTENT -->
                        <div class="pt-3 md:pt-5">
                            <h3 class="text-[14px] md:text-[16px] leading-[1.3] md:leading-[1.4] text-[#222] mb-2 md:mb-3">
                                <?= sanitize($product['name']) ?>
                            </h3>

                            <div class="flex flex-col md:flex-row md:items-center gap-1 md:gap-4 flex-wrap">
                                <?php if ($hasOffer): ?>
                                    <span class="text-[12px] md:text-[14px] text-[#666] line-through">
                                        Rs. <?= number_format((float)$product['price'], 2) ?>
                                    </span>
                                <?php endif; ?>

                                <span class="text-[14px] md:text-[16px] font-medium text-black">
                                    Rs. <?= number_format($displayPrice, 2) ?>
                                </span>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="rounded-[1.75rem] border border-slate-200 bg-white p-8 text-center text-slate-600 shadow-sm">Best seller products not found.</div>
        <?php endif; ?>

        <!-- BUTTON -->
        <div class="flex justify-center mt-12 md:mt-10">
            <a href="<?= BASE_URL ?>/collection"
                class="bg-[#D3D3D3] text-black px-10 md:px-12 py-4 text-[18px] font-serif hover:bg-[#A9A9A9] transition duration-300">
                View all
            </a>
        </div>
    </div>
</section>

<!-- CATEGORY PRODUCTS SECTIONS -->
<?php if ($categoryProductSections): ?>
    <?php foreach ($categoryProductSections as $section): ?>
        <section class="w-full pb-10 md:pb-14 overflow-hidden ">
            <div class="max-w-[1920px] mx-auto px-4 md:px-10">
                <!-- HEADING -->
                <div class="text-center mb-10 md:mb-16 px-4">
                    <h2 class="text-[32px] md:text-[42px] leading-none font-serif text-[#303030] animate-slide-bottom">
                        <?= sanitize($section['category']['name']) ?>
                    </h2>
                </div>
                <?php if ($section['products']): ?>
                    <!-- SLIDER -->
                    <div
                        class="flex gap-3 md:gap-6 overflow-x-auto snap-x snap-mandatory [scrollbar-width:none] [-ms-overflow-style:none] [&::-webkit-scrollbar]:hidden md:-mt-[25px] scroll-animate-bottom">
                        <?php foreach ($section['products'] as $product): ?>
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
                                $badgeText = $hasOffer ? 'Sale' : 'In Stock';
                                $badgeClass = 'bg-black text-white';
                            }
                            ?>
                            <!-- CARD -->
                            <a href="<?= BASE_URL ?>/product.php?id=<?= (int)$product['id'] ?>" class="group flex-shrink-0 w-[145px] md:flex-1 snap-start">
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
                                        <?= sanitize($badgeText) ?>
                                    </span>
                                </div>

                                <!-- CONTENT -->
                                <div class="pt-3 md:pt-5">
                                    <h3 class="text-[14px] md:text-[16px] leading-[1.3] md:leading-[1.4] text-[#222] mb-2 md:mb-3">
                                        <?= sanitize($product['name']) ?>
                                    </h3>

                                    <div class="flex flex-col md:flex-row md:items-center gap-1 md:gap-4 flex-wrap">
                                        <?php if ($hasOffer): ?>
                                            <span class="text-[12px] md:text-[14px] text-[#666] line-through">
                                                Rs. <?= number_format((float)$product['price'], 2) ?>
                                            </span>
                                        <?php endif; ?>

                                        <span class="text-[14px] md:text-[16px] font-medium text-black">
                                            Rs. <?= number_format($displayPrice, 2) ?>
                                        </span>
                                    </div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="rounded-[1.75rem] border border-slate-200 bg-white p-8 text-center text-slate-600 shadow-sm">Products not found.</div>
                <?php endif; ?>

                <!-- BUTTON -->
                <div class="flex justify-center mt-12 md:mt-10">
                    <a href="<?= BASE_URL ?>/collection/<?= sanitize($section['slug']) ?>"
                        class="bg-[#D3D3D3] text-black px-10 md:px-12 py-4 text-[18px] font-serif hover:bg-[#A9A9A9] transition duration-300">
                        View all
                    </a>
                </div>
            </div>
        </section>
    <?php endforeach; ?>
<?php else: ?>
    <section class="w-full py-10 md:py-14 overflow-hidden">
        <div class="max-w-[1920px] mx-auto px-4 md:px-10">
            <div class="rounded-[1.75rem] border border-slate-200 bg-white p-8 text-center text-slate-600 shadow-sm">Categories not found.</div>
        </div>
    </section>
<?php endif; ?>

<!-- Banner Section -->
<section class="w-full">
    <a href="<?= BASE_URL ?>/collection" class="block overflow-hidden">
        <img src="<?= BASE_URL ?>/assets/images/public/banner.png" alt="Banner" class="w-full md:h-[580px] object-cover" />
    </a>
</section>

<!-- WATCH & BUY SECTION -->
<section class="overflow-hidden bg-white py-10 md:py-14">
    <div class="mx-auto max-w-[1400px] px-4">
        <h1 class="mb-9 text-center font-serif text-[30px] leading-none text-[#303030] md:text-[35px]">
            Watch and Buy
        </h1>

        <div
            class="flex gap-[30px] overflow-x-auto pb-3 [scrollbar-width:none] [-ms-overflow-style:none] [&::-webkit-scrollbar]:hidden">
            <?php if ($watchBuyProducts): ?>
                <?php foreach ($watchBuyProducts as $product): ?>
                    <?php
                    $gallery = json_decode($product['gallery'] ?? '[]', true) ?: [];
                    $videoUrl = resolveAssetUrl($product['video_path'] ?? '');
                    $thumbUrl = resolveAssetUrl($product['images'] ?: ($gallery[0] ?? ''));
                    $hasOffer = (float)$product['offer_price'] > 0 && (float)$product['offer_price'] < (float)$product['price'];
                    $displayPrice = $hasOffer ? (float)$product['offer_price'] : (float)$product['price'];
                    $oldPrice = $hasOffer ? 'Rs. ' . number_format((float)$product['price'], 2) : '';
                    $priceText = 'Rs. ' . number_format($displayPrice, 2);
                    $productUrl = BASE_URL . '/product.php?id=' . (int)$product['id'];
                    $discount = $hasOffer ? max(1, round((1 - ($displayPrice / (float)$product['price'])) * 100)) : 0;
                    ?>
                    <article class="watch-card w-[270px] shrink-0 cursor-pointer"
                        data-title="<?= sanitize($product['name']) ?>"
                        data-price="<?= sanitize($priceText) ?>"
                        data-old-price="<?= sanitize($oldPrice) ?>"
                        data-thumb="<?= sanitize($thumbUrl) ?>"
                        data-product-url="<?= sanitize($productUrl) ?>">
                        <div class="relative h-[400px] overflow-hidden rounded-[12px] bg-neutral-100">
                            <video class="h-[calc(100%+76px)] w-full object-cover object-top"
                                src="<?= sanitize($videoUrl) ?>"
                                autoplay muted loop playsinline></video>
                        </div>

                        <div class="pt-[14px]">
                            <h2 class="truncate text-[14px] md:text-[16px] leading-[20px] text-black">
                                <?= sanitize($product['name']) ?>
                            </h2>
                            <div class="mt-[10px] flex items-center gap-3">
                                <span class="text-[14px] md:text-[16px] font-bold text-black"><?= sanitize($priceText) ?></span>
                                <?php if ($hasOffer): ?>
                                    <span class="text-[12px] md:text-[14px] text-gray-500 line-through"><?= sanitize($oldPrice) ?></span>
                                <?php endif; ?>
                            </div>
                            <?php if ($hasOffer): ?>
                                <div class="mt-[10px]">
                                    <span
                                        class="inline-flex rounded-[4px] bg-[#008000] px-[9px] py-[6px] text-[14px] font-bold leading-none text-white">
                                        <?= (int)$discount ?>% off
                                    </span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="w-full rounded-[12px] border border-slate-200 bg-slate-50 p-8 text-center text-slate-600">
                    Product videos not found.
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<div id="watchModal" class="fixed inset-0 z-[9999] hidden overflow-hidden bg-[#303030]">
    <!-- Close -->
    <button id="closeModal"
        class="absolute right-6 top-5 z-50 text-white md:right-[24%] md:top-[62px]"
        type="button"
        aria-label="Close">
        <i data-lucide="x" class="h-10 w-10 stroke-[1]"></i>
    </button>

    <!-- Previous -->
    <button id="prevModal"
        class="absolute left-5 top-1/2 z-40 flex h-[30px] w-[30px] md:h-[42px] md:w-[42px] -translate-y-1/2 items-center justify-center rounded-full bg-white text-black shadow-lg md:left-[25%]"
        type="button"
        aria-label="Previous">
        <i data-lucide="chevron-left" class="h-5 w-5 md:h-7 md:w-7 stroke-[1]"></i>
    </button>

    <!-- Next -->
    <button id="nextModal"
        class="absolute right-5 top-1/2 z-40 flex h-[30px] w-[30px] md:h-[42px] md:w-[42px] -translate-y-1/2 items-center justify-center rounded-full bg-white text-black shadow-lg md:right-[25%]"
        type="button"
        aria-label="Next">
        <i data-lucide="chevron-right" class="h-5 w-5 md:h-7 md:w-7 stroke-[1]"></i>
    </button>

    <div class="flex h-full items-center justify-center gap-[24px] px-4">
        <div class="hidden h-[452px] w-[254px] overflow-hidden rounded-[8px] bg-black opacity-45 md:block">
            <video id="prevVideo" class="h-[calc(100%+76px)] w-full object-cover object-top" muted loop playsinline></video>
        </div>

        <div
            class="relative h-[min(78vh,660px)] w-[min(84vw,372px)] overflow-hidden rounded-[12px] border border-white/20 bg-black shadow-2xl md:h-[660px] md:w-[372px]">
            <video id="mainVideo" class="h-[calc(100%+76px)] w-full object-cover object-top" muted loop playsinline></video>


            <!-- Sound Button -->
            <button
                id="soundToggle"
                class="absolute right-3 top-5 flex h-11 w-11 items-center justify-center rounded-full bg-black/35 text-white backdrop-blur-sm"
                type="button">
                <i data-lucide="volume-x"
                    class="h-6 w-6 stroke-[1]"></i>
            </button>

            <!-- Like & Share -->
            <div class="absolute bottom-[152px] right-3 flex flex-col items-center gap-2 text-white">

                <button
                    class="flex h-11 w-11 items-center justify-center rounded-full bg-black/35 backdrop-blur-sm"
                    type="button"
                    aria-label="Like">
                    <i data-lucide="heart" class="h-7 w-7 stroke-[1]"></i>
                </button>
                <span class="text-[12px] font-bold leading-none">Like</span>

                <!-- Share Button -->
                <button
                    id="shareProduct"
                    class="mt-2 flex h-11 w-11 items-center justify-center rounded-full bg-black/35 backdrop-blur-sm"
                    type="button">

                    <i data-lucide="share-2" class="h-6 w-6 stroke-[1]"></i>

                </button>
                <span class="text-[12px] font-bold leading-none">Share</span>

            </div>

            <div class="absolute bottom-[10px] left-[18px] right-[18px] md:left-[52px] md:right-[52px]">
                <h3 id="modalTitleTop"
                    class="mb-2 truncate text-center font-serif text-[20px] leading-tight text-white drop-shadow"></h3>

                <div class="overflow-hidden rounded-[7px] bg-white/95 shadow-xl">
                    <div class="flex">
                        <div class="h-[88px] w-[94px] shrink-0 bg-white">
                            <img id="productThumb" class="h-full w-full object-contain object-center" ₹
                                src="https://i.ibb.co/jvmWzcf0/Invicta-Men-s-Pro-Diver-Collection-Coin-Edge-Automatic-Watch.jpg"
                                alt="Invicta Men's Pro Diver Watch" />
                        </div>

                        <div class="relative min-w-0 flex-1 px-3 py-3">
                            <a id="productViewLink" href="#" class="absolute right-2 top-2 text-[18px] text-gray-700"
                                aria-label="View product">
                                &#8599;
                            </a>

                            <h4 id="modalTitle" class="truncate pr-6 text-[14px] font-semibold leading-[18px] text-black"></h4>

                            <div class="mt-2 flex items-center gap-2">
                                <span id="modalPrice" class="rounded bg-[#f4f4f4] text-[12px] font-bold text-black"></span>
                                <span id="modalOldPrice" class="text-[12px] text-gray-500 line-through"></span>
                            </div>
                        </div>
                    </div>

                    <form id="watchBuyForm" method="post" action="">
                        <input type="hidden" name="quantity" value="1">
                        <input type="hidden" name="action" value="add_cart">
                        <button class="w-full bg-[#2b2b2b] py-[13px] text-[17px] font-bold uppercase leading-none text-white"
                            type="submit">
                            ADD TO CART
                        </button>
                    </form>
                </div>

                <p class="mt-3 text-center text-[12px] font-semibold text-white">
                    Powered By
                    <a href="https://websolvit.com" target="_blank" class="underline">
                        <img src="https://i.ibb.co/TMMLkPmj/websolvit-logo.png" alt="Websolvit" class="inline h-4">
                    </a>
                </p>
            </div>
        </div>

        <div class="hidden h-[452px] w-[254px] overflow-hidden rounded-[8px] bg-black opacity-45 md:block">
            <video id="nextVideo" class="h-[calc(100%+76px)] w-full object-cover object-top" muted loop playsinline></video>
        </div>
    </div>
</div>

<!-- Running strip -->
<section class="w-full bg-black py-6 overflow-hidden">
    <div class="flex w-max animate-marquee">

        <!-- First Set -->
        <div class="flex items-center gap-7 md:gap-20 px-6 md:px-8 shrink-0">
            <img src="<?= BASE_URL ?>/assets/images/brands/arma.png" class="h-25 md:h-24 w-auto py-4" alt="">
            <img src="<?= BASE_URL ?>/assets/images/brands/rado.png" class="h-25 md:h-24 w-auto py-4" alt="">
            <img src="<?= BASE_URL ?>/assets/images/brands/2.png" class="h-25 md:h-24 w-auto py-4" alt="">
            <img src="<?= BASE_URL ?>/assets/images/brands/3.png" class="h-25 md:h-24 w-auto py-4" alt="">
            <img src="<?= BASE_URL ?>/assets/images/brands/4.png" class="h-25 md:h-24 w-auto py-4" alt="">
            <img src="<?= BASE_URL ?>/assets/images/brands/5.png" class="h-25 md:h-24 w-auto py-4" alt="">
            <img src="<?= BASE_URL ?>/assets/images/brands/6.png" class="h-25 md:h-24 w-auto py-4" alt="">

        </div>

        <!-- Duplicate Set -->
        <div class="flex items-center gap-7 md:gap-20  px-6 md:px-8 shrink-0">
            <img src="<?= BASE_URL ?>/assets/images/brands/arma.png" class="h-25 md:h-24 w-auto py-4" alt="">
            <img src="<?= BASE_URL ?>/assets/images/brands/rado.png" class="h-25 md:h-24 w-auto py-4" alt="">
            <img src="<?= BASE_URL ?>/assets/images/brands/2.png" class="h-25 md:h-24 w-auto py-4" alt="">
            <img src="<?= BASE_URL ?>/assets/images/brands/ca.png" class="h-25 md:h-24 w-auto py-4" alt="">
            <img src="<?= BASE_URL ?>/assets/images/brands/4.png" class="h-25 md:h-24 w-auto py-4" alt="">
            <img src="<?= BASE_URL ?>/assets/images/brands/5.png" class="h-25 md:h-24 w-auto py-4" alt="">
            <img src="<?= BASE_URL ?>/assets/images/brands/6.png" class="h-25 md:h-24 w-auto py-4" alt="">

        </div>

    </div>
</section>

<!-- CUSTOMER REVIEWS -->
<section class="w-full bg-[#f5f5f3] py-10 md:py-14 overflow-hidden">
    <div class="max-w-[1400px] mx-auto px-4">

        <!-- HEADING -->
        <div class="text-center mb-10 md:mb-14">
            <h2 class="text-[32px] md:text-[42px] font-serif text-black">
                Customer Reviews
            </h2>
        </div>

        <!-- REVIEWS -->
        <div id="reviewSlider"
            class="flex gap-5 md:gap-8 overflow-x-auto snap-x snap-mandatory scroll-smooth [scrollbar-width:none] [-ms-overflow-style:none] [&::-webkit-scrollbar]:hidden">

            <!-- REVIEW CARD -->
            <div
                class="bg-white rounded-[16px] p-6 md:p-8 flex-shrink-0 w-[320px] md:w-[380px] snap-start text-center shadow-sm">

                <h3 class="text-[20px] text-[#222]">
                    Nirav
                </h3>

                <div class="flex justify-center gap-1 text-[#f4c400] text-[20px] mb-4">
                    ★ ★ ★ ★ ★
                </div>

                <div class="flex justify-center mb-4">
                    <img
                        src="https://i.ibb.co/yD4bp6k/Gemini-Generated-Image-2nnke92nnke92nnk-400x400.png"
                        alt="Nirav"
                        class="w-[120px] h-[120px] md:w-[180px] md:h-[180px] rounded-full object-cover" />
                </div>

                <p class="text-[14px] text-[#333] line-clamp-2">
                    Got gifted this by my girl, loved it! Checked out the website and
                    ordered the Apex kada as well and my god this is superb! Thanks!
                </p>
            </div>

            <!-- REVIEW CARD -->
            <div
                class="bg-white rounded-[16px] p-6 md:p-8 flex-shrink-0 w-[320px] md:w-[380px] snap-start text-center shadow-sm">

                <h3 class="text-[20px] text-[#222]">
                    Mihir
                </h3>

                <div class="flex justify-center gap-1 text-[#f4c400] text-[20px] mb-4">
                    ★ ★ ★ ★ ★
                </div>

                <div class="flex justify-center mb-4">
                    <img
                        src="https://i.ibb.co/yD4bp6k/Gemini-Generated-Image-2nnke92nnke92nnk-400x400.png"
                        alt="Mihir"
                        class="w-[120px] h-[120px] md:w-[180px] md:h-[180px] rounded-full object-cover" />
                </div>

                <p class="text-[14px] text-[#333] line-clamp-2">
                    Even though I have a real gold chain, I prefer wearing this daily.
                    Stylish, lightweight, and comfortable for everyday use.
                </p>
            </div>

            <!-- REVIEW CARD -->
            <div
                class="bg-white rounded-[16px] p-6 md:p-8 flex-shrink-0 w-[320px] md:w-[380px] snap-start text-center shadow-sm">

                <h3 class="text-[20px] text-[#222]">
                    Mihir
                </h3>

                <div class="flex justify-center gap-1 text-[#f4c400] text-[20px] mb-4">
                    ★ ★ ★ ★ ★
                </div>

                <div class="flex justify-center mb-4">
                    <img
                        src="https://i.ibb.co/yD4bp6k/Gemini-Generated-Image-2nnke92nnke92nnk-400x400.png"
                        alt="Mihir"
                        class="w-[120px] h-[120px] md:w-[180px] md:h-[180px] rounded-full object-cover" />
                </div>

                <p class="text-[14px] text-[#333] line-clamp-2">
                    Even though I have a real gold chain, I prefer wearing this daily.
                    Stylish, lightweight, and comfortable for everyday use.
                </p>
            </div>

            <!-- REVIEW CARD -->
            <div
                class="bg-white rounded-[16px] p-6 md:p-8 flex-shrink-0 w-[320px] md:w-[380px] snap-start text-center shadow-sm">

                <h3 class="text-[20px] text-[#222]">
                    Priti
                </h3>

                <div class="flex justify-center gap-1 text-[#f4c400] text-[20px] mb-4">
                    ★ ★ ★ ★ ★
                </div>

                <div class="flex justify-center mb-4">
                    <img
                        src="https://i.ibb.co/yD4bp6k/Gemini-Generated-Image-2nnke92nnke92nnk-400x400.png"
                        alt="Priti"
                        class="w-[120px] h-[120px] md:w-[180px] md:h-[180px] rounded-full object-cover" />
                </div>

                <p class="text-[14px] text-[#333] line-clamp-2">
                    Beautiful craftsmanship and premium finish. The quality exceeded
                    my expectations and delivery was very quick.
                </p>
            </div>

        </div>
    </div>
</section>


<?php include __DIR__ . '/includes/footer.php'; ?>