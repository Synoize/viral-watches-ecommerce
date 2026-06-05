<?php
require_once __DIR__ . '/includes/functions.php';
$categories = getCategories();
$categoriesById = [];
foreach ($categories as $categoryOption) {
    $categoriesById[(int)$categoryOption['id']] = $categoryOption;
}

$categorySlug = !empty($_GET['category_slug']) ? trim((string)$_GET['category_slug']) : null;
$categoryId = 0;
$cat = null;

if (array_key_exists('category', $_GET)) {
    $requestedCategoryId = (int)($_GET['category'] ?? 0);
    if ($requestedCategoryId > 0 && isset($categoriesById[$requestedCategoryId])) {
        $categoryId = $requestedCategoryId;
        $cat = $categoriesById[$categoryId];
    }
} elseif ($categorySlug) {
    $cat = getCategoryBySlug($categorySlug);
    if ($cat) {
        $categoryId = (int)$cat['id'];
    }
}

$search = trim((string)($_GET['search'] ?? ''));
$sort = $_GET['sort'] ?? '';
$effectivePriceSql = 'CASE WHEN p.offer_price > 0 AND p.offer_price < p.price THEN p.offer_price ELSE p.price END';
$orderBy = 'p.id DESC';
if ($sort === 'price_asc') {
    $orderBy = "$effectivePriceSql ASC, p.id DESC";
} elseif ($sort === 'price_desc') {
    $orderBy = "$effectivePriceSql DESC, p.id DESC";
} else {
    $sort = '';
}

$priceMin = isset($_GET['min_price']) && $_GET['min_price'] !== '' ? max(0, (float)$_GET['min_price']) : 0;
$priceMax = isset($_GET['max_price']) && $_GET['max_price'] !== '' ? max(0, (float)$_GET['max_price']) : 0;
if ($priceMin > 0 && $priceMax > 0 && $priceMin > $priceMax) {
    [$priceMin, $priceMax] = [$priceMax, $priceMin];
}
$page = max(1, (int)($_GET['page'] ?? 1));
$pageSize = 12;
$offset = ($page - 1) * $pageSize;
$where = ['p.stock > 0'];
$params = [];
if ($categoryId) {
    $where[] = 'p.category = ?';
    $params[] = $categoryId;
}
if ($search !== '') {
    $where[] = '(p.name LIKE ? OR p.description LIKE ?)';
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if ($priceMin > 0) {
    $where[] = "$effectivePriceSql >= ?";
    $params[] = $priceMin;
}
if ($priceMax > 0) {
    $where[] = "$effectivePriceSql <= ?";
    $params[] = $priceMax;
}

$stmt = $pdo->query('SELECT * FROM products WHERE stock > 0 AND is_best_seller = 1 ORDER BY id DESC LIMIT 8');
$bestSellers = $stmt->fetchAll();

$whereSql = implode(' AND ', $where);
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM products p WHERE $whereSql");
$countStmt->execute($params);
$total = (int)$countStmt->fetchColumn();
$totalPages = max(1, (int)ceil($total / $pageSize));
if ($page > $totalPages) {
    $page = $totalPages;
    $offset = ($page - 1) * $pageSize;
}

$query = "SELECT p.* FROM products p WHERE $whereSql ORDER BY $orderBy LIMIT ? OFFSET ?";
$stmt = $pdo->prepare($query);
foreach ($params as $index => $value) {
    $stmt->bindValue($index + 1, $value);
}
$stmt->bindValue(count($params) + 1, $pageSize, PDO::PARAM_INT);
$stmt->bindValue(count($params) + 2, $offset, PDO::PARAM_INT);
$stmt->execute();
$products = $stmt->fetchAll();
$activeCategoryName = $categoryId ? ($cat['name'] ?? '') : '';
$activeCategorySlug = $activeCategoryName ? strtolower(preg_replace('/[^a-z0-9]+/i', '-', trim($activeCategoryName))) : '';
$clearFiltersUrl = BASE_URL . ($activeCategorySlug ? '/collection/' . $activeCategorySlug : '/collection');
$hasActiveFilters = $categoryId || $search !== '' || $priceMin > 0 || $priceMax > 0 || $sort !== '';
$filterParams = [];
if ($categoryId) $filterParams['category'] = $categoryId;
if ($search !== '') $filterParams['search'] = $search;
if ($priceMin > 0) $filterParams['min_price'] = $priceMin;
if ($priceMax > 0) $filterParams['max_price'] = $priceMax;
if ($sort !== '') $filterParams['sort'] = $sort;
$pageMetaOverrides = [];
if ($activeCategoryName) {
    $pageMetaOverrides = [
        'title' => $activeCategoryName . ' | ShopMaster Collections',
        'description' => 'Browse ' . $activeCategoryName . ' products at ShopMaster with latest prices and secure checkout.',
        'keywords' => strtolower($activeCategoryName) . ', collection, shopmaster',
    ];
} elseif ($search) {
    $pageMetaOverrides = [
        'title' => 'Search results for ' . $search . ' | ShopMaster',
        'description' => 'Search ShopMaster products for ' . $search . ' and compare available items.',
        'keywords' => $search . ', search, products',
    ];
}
?>
<?php include __DIR__ . '/includes/header.php'; ?>

<section class="mx-auto max-w-[1920px] px-4 md:px-10 py-10 md:py-14">
    <?php if (!$hasActiveFilters && empty($_GET['page'])): ?>
        <div>
            <div class="flex items-center justify-between">
                <h2 class="text-[32px] md:text-[42px] font-serif text-black">
                    Collections
                </h2>
            </div>

            <div class="mt-8 grid grid-cols-2 gap-3 md:gap-6 lg:grid-cols-4">

                <?php if ($categories): ?>
                    <?php foreach ($categories as $catItem):
                        $slugItem = strtolower(preg_replace('/[^a-z0-9]+/i', '-', trim($catItem['name'])));
                    ?>

                        <a href="<?= BASE_URL ?>/collection/<?= $slugItem ?>"
                            class="group block">

                            <!-- IMAGE -->
                            <div class="overflow-hidden rounded-[16px] bg-white h-[180px] md:h-[320px]">
                                <!-- <img
                            src="<?= BASE_URL . 'assets/images/categories/' . $catItem['category_image'] ?>"
                            alt="<?= sanitize($catItem['name']) ?>"
                            class="h-full w-full object-cover transition duration-500 group-hover:scale-105 " /> -->
                            </div>

                            <!-- TITLE -->
                            <div class="mt-3 text-center">
                                <h3 class="font-serif text-[18px] md:text-[24px] text-[#222]">
                                    <?= sanitize($catItem['name']) ?>
                                    <span class="ml-1 transition group-hover:translate-x-1 inline-block">
                                        →
                                    </span>
                                </h3>
                            </div>

                        </a>

                    <?php endforeach; ?>
                <?php else: ?>

                    <div class="col-span-full text-center py-12">
                        No collections found.
                    </div>

                <?php endif; ?>

            </div>
        </div>

        <!-- BEST SELLER SECTION -->
        <div class="mt-10 md:mt-14">
            <!-- HEADING -->
            <div class="text-center mb-10 md:mb-16 px-4">
                <h2 class="text-[32px] md:text-[42px] leading-none font-serif text-[#303030] animate-slide-bottom">
                    Best Seller
                </h2>
            </div>
            <?php if ($bestSellers): ?>
                <!-- SLIDER -->
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 md:gap-6 scroll-animate-bottom">
                    <?php foreach ($bestSellers as $product): ?>
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
                        <a href="<?= BASE_URL ?>/product.php?id=<?= (int)$product['id'] ?>" class="group flex-shrink-0 w-auto snap-start">
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
        </div>

    <?php else: ?>
        <div>
            <!-- PAGE TITLE -->
            <div class="mb-10 md:mb-14">
                <h1 class="text-[32px] md:text-[42px] leading-none font-serif text-[#222]">
                    <?= $activeCategoryName ? sanitize($activeCategoryName) : 'Products' ?>
                </h1>
            </div>

            <!-- FILTER SECTION -->
            <div class="flex items-center justify-between mb-8 md:mb-12">

                <!-- MOBILE FILTER BUTTON -->
                <button
                    type="button"
                    id="openFilter"
                    class="flex md:hidden items-center gap-2 text-sm font-medium text-[#222]">

                    <i class="fa-solid fa-sliders"></i>
                    <span>Filter & Sort</span>

                </button>

                <!-- DESKTOP FILTERS -->
                <form
                    method="get"
                    action="<?= BASE_URL ?>/collection"
                    class="hidden md:flex items-center gap-10">
                    <?php if ($search !== ''): ?>
                        <input type="hidden" name="search" value="<?= sanitize($search) ?>">
                    <?php endif; ?>

                    <!-- CATEGORY -->
                    <div class="flex items-center gap-3">

                        <span class="text-[15px] text-[#222]">
                            Category
                        </span>

                        <select
                            name="category"
                            onchange="this.form.submit()"
                            class="bg-transparent border-none outline-none text-[15px] text-[#444] cursor-pointer">

                            <option value="">
                                All Categories
                            </option>

                            <?php foreach ($categories as $catOption): ?>

                                <option
                                    value="<?= $catOption['id'] ?>"
                                    <?= $categoryId === (int)$catOption['id'] ? 'selected' : '' ?>>

                                    <?= sanitize($catOption['name']) ?>

                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>

                    <!-- SORT -->
                    <div class="flex items-center gap-3">

                        <span class="text-[15px] text-[#222]">
                            Sort By
                        </span>

                        <select
                            name="sort"
                            onchange="this.form.submit()"
                            class="bg-transparent border-none outline-none text-[15px] text-[#444] cursor-pointer">

                            <option value="">
                                Featured
                            </option>

                            <option
                                value="price_asc"
                                <?= $sort === 'price_asc' ? 'selected' : '' ?>>

                                Price Low → High

                            </option>

                            <option
                                value="price_desc"
                                <?= $sort === 'price_desc' ? 'selected' : '' ?>>

                                Price High → Low

                            </option>

                        </select>

                    </div>

                    <!-- PRICE -->
                    <div class="flex items-center gap-3">
                        <span class="text-[15px] text-[#222]">
                            Price
                        </span>

                        <input
                            type="number"
                            min="0"
                            name="min_price"
                            value="<?= $priceMin > 0 ? sanitize($priceMin) : '' ?>"
                            placeholder="Min"
                            class="w-24 bg-transparent border-b border-slate-300 px-1 py-1 text-[15px] text-[#444] outline-none">

                        <span class="text-slate-400">-</span>

                        <input
                            type="number"
                            min="0"
                            name="max_price"
                            value="<?= $priceMax > 0 ? sanitize($priceMax) : '' ?>"
                            placeholder="Max"
                            class="w-24 bg-transparent border-b border-slate-300 px-1 py-1 text-[15px] text-[#444] outline-none">

                        <button
                            type="submit"
                            class="h-10 px-5 rounded-lg bg-black text-white text-sm font-medium hover:bg-slate-800 transition">

                            Apply

                        </button>
                    </div>

                    <?php if ($hasActiveFilters): ?>
                        <a
                            href="<?= $clearFiltersUrl ?>"
                            class="text-slate-500 hover:text-black">

                            <i data-lucide="x" class="w-8 h-8 stroke-[1]"></i>

                        </a>
                    <?php endif; ?>

                </form>

                <!-- PRODUCT COUNT -->
                <span class="text-sm text-slate-500">
                    <?= $total ?> Products
                </span>

            </div>

            <!-- FILTER OVERLAY -->
            <div
                id="filterOverlay"
                class="fixed inset-0 bg-black/40 backdrop-blur-[2px] opacity-0 invisible transition-all duration-300 z-40">
            </div>

            <!-- FILTER DRAWER -->
            <div
                id="filterDrawer"
                class="fixed top-0 right-[-100%] w-[80%] sm:w-[420px] max-w-full h-screen bg-white z-50 transition-all duration-300 ease-out flex flex-col shadow-2xl">

                <form method="get" action="<?= BASE_URL ?>/collection" class="flex flex-col h-full">
                    <?php if ($search !== ''): ?>
                        <input type="hidden" name="search" value="<?= sanitize($search) ?>">
                    <?php endif; ?>

                    <!-- HEADER -->
                    <div class="relative border-b border-slate-100 px-6 py-5">

                        <button
                            type="button"
                            id="closeFilter"
                            class="absolute right-6 top-5 flex items-center justify-center w-10 h-10 rounded-full text-slate-400 hover:text-black transition">

                            <i data-lucide="x" class="w-8 h-8 stroke-[1]"></i>

                        </button>

                        <h2 class="text-[32px] font-serif text-[#222]">
                            Filter and sort
                        </h2>

                        <p class="text-sm text-slate-500">
                            <?= $total ?> products
                        </p>

                    </div>

                    <!-- BODY -->
                    <div class="flex-1 overflow-y-auto overscroll-contain">

                        <!-- CATEGORY -->
                        <details class="border-b border-slate-100" open>

                            <summary class="flex items-center justify-between px-6 py-5 cursor-pointer list-none">

                                <span class="text-base font-medium text-slate-900">
                                    Category
                                </span>

                                <i
                                    data-lucide="plus"
                                    class="w-4 h-4 stroke-[1.5] text-slate-500">
                                </i>

                            </summary>

                            <div class="px-6 pb-5 space-y-4">

                                <label class="flex items-center justify-between cursor-pointer">

                                    <span class="text-sm text-slate-700">
                                        All Categories
                                    </span>

                                    <input
                                        type="radio"
                                        name="category"
                                        value=""
                                        <?= empty($categoryId) ? 'checked' : '' ?>
                                        class="w-4 h-4">

                                </label>

                                <?php foreach ($categories as $catOption): ?>

                                    <label class="flex items-center justify-between cursor-pointer">

                                        <span class="text-sm text-slate-700">
                                            <?= sanitize($catOption['name']) ?>
                                        </span>

                                        <input
                                            type="radio"
                                            name="category"
                                            value="<?= $catOption['id'] ?>"
                                            <?= $categoryId === (int)$catOption['id'] ? 'checked' : '' ?>
                                            class="w-4 h-4">

                                    </label>

                                <?php endforeach; ?>

                            </div>

                        </details>

                        <!-- SORT -->
                        <details class="border-b border-slate-100">

                            <summary class="flex items-center justify-between px-6 py-5 cursor-pointer list-none">

                                <span class="text-base font-medium text-slate-900">
                                    Sort By
                                </span>

                                <i
                                    data-lucide="plus"
                                    class="w-4 h-4 stroke-[1.5] text-slate-500">
                                </i>

                            </summary>

                            <div class="px-6 pb-5 space-y-4">

                                <label class="flex items-center justify-between cursor-pointer">

                                    <span class="text-sm text-slate-700">
                                        Featured
                                    </span>

                                    <input
                                        type="radio"
                                        name="sort"
                                        value=""
                                        <?= $sort === '' ? 'checked' : '' ?>
                                        class="w-4 h-4">

                                </label>

                                <label class="flex items-center justify-between cursor-pointer">

                                    <span class="text-sm text-slate-700">
                                        Price Low → High
                                    </span>

                                    <input
                                        type="radio"
                                        name="sort"
                                        value="price_asc"
                                        <?= $sort === 'price_asc' ? 'checked' : '' ?>
                                        class="w-4 h-4">

                                </label>

                                <label class="flex items-center justify-between cursor-pointer">

                                    <span class="text-sm text-slate-700">
                                        Price High → Low
                                    </span>

                                    <input
                                        type="radio"
                                        name="sort"
                                        value="price_desc"
                                        <?= $sort === 'price_desc' ? 'checked' : '' ?>
                                        class="w-4 h-4">

                                </label>

                            </div>

                        </details>

                        <!-- PRICE -->
                        <details class="border-b border-slate-100">

                            <summary class="flex items-center justify-between px-6 py-5 cursor-pointer list-none">

                                <span class="text-base font-medium text-slate-900">
                                    Price
                                </span>

                                <i
                                    data-lucide="plus"
                                    class="w-4 h-4 stroke-[1.5] text-slate-500">
                                </i>

                            </summary>

                            <div class="grid grid-cols-2 gap-3 px-6 pb-5">

                                <label class="block text-sm text-slate-700">
                                    Min
                                    <input
                                        type="number"
                                        min="0"
                                        name="min_price"
                                        value="<?= $priceMin > 0 ? sanitize($priceMin) : '' ?>"
                                        class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 outline-none focus:border-black">
                                </label>

                                <label class="block text-sm text-slate-700">
                                    Max
                                    <input
                                        type="number"
                                        min="0"
                                        name="max_price"
                                        value="<?= $priceMax > 0 ? sanitize($priceMax) : '' ?>"
                                        class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 outline-none focus:border-black">
                                </label>

                            </div>

                        </details>

                    </div>

                    <!-- FOOTER -->
                    <div class="border-t border-slate-100 p-5 bg-white">

                        <div class="flex items-center gap-4">

                            <a
                                href="<?= $clearFiltersUrl ?>"
                                class="flex-1 text-center text-[15px] underline text-slate-600 hover:text-black transition">

                                Remove all

                            </a>

                            <button
                                type="submit"
                                class="flex-1 bg-black text-white py-4 text-[15px] font-medium hover:bg-[#111] transition">

                                Apply

                            </button>

                        </div>

                    </div>

                </form>

            </div>

            <script>
                document.addEventListener('DOMContentLoaded', () => {

                    const openFilter = document.getElementById('openFilter');
                    const closeFilter = document.getElementById('closeFilter');
                    const filterDrawer = document.getElementById('filterDrawer');
                    const filterOverlay = document.getElementById('filterOverlay');

                    function openDrawer() {
                        filterDrawer.style.right = '0';

                        filterOverlay.classList.remove(
                            'opacity-0',
                            'invisible'
                        );

                        document.body.classList.add('overflow-hidden');
                    }

                    function closeDrawer() {
                        filterDrawer.style.right = '-100%';

                        filterOverlay.classList.add(
                            'opacity-0',
                            'invisible'
                        );

                        document.body.classList.remove('overflow-hidden');
                    }

                    openFilter?.addEventListener('click', openDrawer);
                    closeFilter?.addEventListener('click', closeDrawer);
                    filterOverlay?.addEventListener('click', closeDrawer);

                    document.addEventListener('keydown', (e) => {
                        if (e.key === 'Escape') {
                            closeDrawer();
                        }
                    });

                });
            </script>

            <!-- PRODUCT GRID -->
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 md:gap-6 scroll-animate-bottom">

                <?php if ($products): ?>

                    <?php foreach ($products as $product):

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
                        <a href="<?= BASE_URL ?>/product.php?id=<?= (int)$product['id'] ?>" class="group flex-shrink-0 w-auto snap-start">
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

                <?php else: ?>

                    <div class="col-span-full text-center py-28 md:py-10">

                        <i class="fa-regular fa-gem text-5xl text-slate-300"></i>

                        <h3 class="mt-6 text-2xl font-serif text-[#222]">
                            No Products Available
                        </h3>

                        <p class="mt-3 text-slate-500">
                            Please check back later or explore other collections.
                        </p>

                        <a href="<?= BASE_URL ?>/collection"
                            class="inline-block mt-8 border border-black px-8 py-3 text-sm tracking-widest uppercase hover:bg-black hover:text-white transition">
                            View Collection
                        </a>

                    </div>

                <?php endif; ?>

            </div>

            <!-- PAGINATION -->
            <?php if ($totalPages > 1): ?>

                <div class="flex justify-center gap-2 mt-16">

                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>

                        <?php
                        $queryParams = array_merge($filterParams, ['page' => $i]);
                        ?>

                        <a
                            href="<?= BASE_URL ?>/collection?<?= http_build_query($queryParams) ?>"
                            class="w-10 h-10 rounded flex items-center justify-center <?= $i === $page ? 'bg-black text-white' : 'bg-white text-black' ?>">

                            <?= $i ?>

                        </a>

                    <?php endfor; ?>

                </div>

            <?php endif; ?>

        </div>
    <?php endif; ?>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>