<?php
require_once __DIR__ . '/includes/functions.php';
// Accept category slug from rewrite or query
$categorySlug = !empty($_GET['category_slug']) ? sanitize($_GET['category_slug']) : null;
$categoryId = null;
if ($categorySlug) {
    $cat = getCategoryBySlug($categorySlug);
    if ($cat) $categoryId = (int)$cat['id'];
}
// Backwards compatibility: allow ?category=id
if (!$categoryId && !empty($_GET['category'])) {
    $categoryId = (int)$_GET['category'];
}
$search = !empty($_GET['search']) ? sanitize($_GET['search']) : null;
$orderBy = 'p.id DESC';
if (!empty($_GET['sort']) && $_GET['sort'] === 'price_asc') {
    $orderBy = 'p.price ASC';
} elseif (!empty($_GET['sort']) && $_GET['sort'] === 'price_desc') {
    $orderBy = 'p.price DESC';
}
$priceMin = isset($_GET['min_price']) ? (float)$_GET['min_price'] : 0;
$priceMax = isset($_GET['max_price']) ? (float)$_GET['max_price'] : 0;
$page = max(1, (int)($_GET['page'] ?? 1));
$pageSize = 12;
$offset = ($page - 1) * $pageSize;
$query = 'SELECT SQL_CALC_FOUND_ROWS p.* FROM products p WHERE p.stock > 0';
$params = [];
if ($categoryId) {
    $query .= ' AND p.category = ?';
    $params[] = $categoryId;
}
if ($search) {
    $query .= ' AND (p.name LIKE ? OR p.description LIKE ?)';
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if ($priceMin > 0) {
    $query .= ' AND p.price >= ?';
    $params[] = $priceMin;
}
if ($priceMax > 0) {
    $query .= ' AND p.price <= ?';
    $params[] = $priceMax;
}
$query .= " ORDER BY $orderBy LIMIT $pageSize OFFSET $offset";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$products = $stmt->fetchAll();
$total = $pdo->query('SELECT FOUND_ROWS()')->fetchColumn();
$totalPages = ceil($total / $pageSize);
$categories = getCategories();
$activeCategoryName = $categoryId ? ($cat['name'] ?? '') : '';
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
<div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
    <?php if (!$categoryId && empty($_GET['search']) && empty($_GET['min_price']) && empty($_GET['max_price']) && empty($_GET['sort']) && empty($_GET['page'])): ?>
        <section>
            <div class="flex items-center justify-between gap-4">
                <h1 class="text-3xl font-semibold text-slate-900">Collections</h1>
                <p class="text-sm text-slate-500">Browse by category</p>
            </div>
            <div class="mt-6 grid gap-6 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4">
                <?php if ($categories): foreach ($categories as $catItem): $slugItem = strtolower(preg_replace('/[^a-z0-9]+/i', '-', trim($catItem['name']))); ?>
                    <a href="<?= BASE_URL ?>/collection/<?= $slugItem ?>" class="group overflow-hidden rounded-[1.5rem] border border-slate-200 bg-white p-6 text-center transition hover:-translate-y-1 hover:shadow-lg">
                        <div class="mx-auto mb-4 flex h-20 w-20 items-center justify-center rounded-full bg-slate-100 text-brand transition group-hover:bg-brand group-hover:text-white">
                            <i class="fas fa-tag text-2xl"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-slate-900"><?= sanitize($catItem['name']) ?></h3>
                    </a>
                <?php endforeach; else: ?>
                    <div class="col-span-full rounded-[1.75rem] border border-slate-200 bg-white p-8 text-center text-slate-600 shadow-sm">Categories not found.</div>
                <?php endif; ?>
            </div>
        </section>
    <?php else: ?>
    <div class="grid gap-8 lg:grid-cols-[280px_1fr]">
        <aside class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">Filters</h2>
            <form method="get" class="space-y-4 mt-6">
                <div>
                    <label class="block text-sm font-medium text-slate-700">Category</label>
                    <select name="category" class="mt-2 w-full rounded-3xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 outline-none focus:border-slate-900">
                        <option value="">All categories</option>
                        <?php foreach ($categories as $catOption): ?>
                            <option value="<?= $catOption['id'] ?>" <?= $categoryId === (int)$catOption['id'] ? 'selected' : '' ?>><?= sanitize($catOption['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Price range</label>
                    <div class="mt-2 grid gap-3 sm:grid-cols-2">
                        <input type="number" class="w-full rounded-3xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 outline-none focus:border-slate-900" name="min_price" value="<?= $priceMin ?>" placeholder="Min">
                        <input type="number" class="w-full rounded-3xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 outline-none focus:border-slate-900" name="max_price" value="<?= $priceMax ?>" placeholder="Max">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Sort by</label>
                    <select name="sort" class="mt-2 w-full rounded-3xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 outline-none focus:border-slate-900">
                        <option value="">Newest first</option>
                        <option value="price_asc" <?= ($_GET['sort'] ?? '') === 'price_asc' ? 'selected' : '' ?>>Price low → high</option>
                        <option value="price_desc" <?= ($_GET['sort'] ?? '') === 'price_desc' ? 'selected' : '' ?>>Price high → low</option>
                    </select>
                </div>
                <button type="submit" class="inline-flex w-full items-center justify-center rounded-3xl bg-slate-900 px-4 py-3 text-sm font-semibold text-white hover:bg-slate-800">Apply filters</button>
            </form>
            <div class="mt-8 rounded-[1.75rem] border border-slate-200 bg-slate-50 p-4">
                <h3 class="text-sm font-semibold text-slate-900">Quick Search</h3>
                <form method="get" action="<?= BASE_URL ?>/collection/<?= $categorySlug ?? '' ?>" class="mt-4 flex items-center gap-2">
                    <input type="text" name="search" value="<?= $search ?>" placeholder="Keyword" class="w-full rounded-3xl border border-slate-200 bg-white px-4 py-3 text-slate-900 outline-none focus:border-slate-900" />
                    <button class="inline-flex h-12 w-12 items-center justify-center rounded-full bg-slate-900 text-white hover:bg-slate-800"><i class="fas fa-search"></i></button>
                </form>
            </div>
        </aside>
        <section>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h1 class="text-2xl font-semibold text-slate-900"><?= $activeCategoryName ? sanitize($activeCategoryName) : 'Products' ?></h1>
                    <p class="mt-1 text-sm text-slate-500"><?= $total ?> products found</p>
                </div>
            </div>
            <div class="mt-6 grid gap-6 sm:grid-cols-2 xl:grid-cols-3">
                <?php if ($products): foreach ($products as $product): $gallery = json_decode($product['gallery'], true) ?: []; ?>
                    <article class="overflow-hidden rounded-[1.75rem] border border-slate-200 bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-md">
                        <img src="<?= sanitize($gallery[0] ?? $product['images']) ?>" alt="<?= sanitize($product['name']) ?>" class="h-60 w-full object-cover" />
                        <div class="space-y-3 p-5">
                            <h3 class="text-lg font-semibold text-slate-900"><?= sanitize($product['name']) ?></h3>
                            <div class="flex items-center justify-between gap-3 text-slate-700">
                                <span class="text-brand text-lg font-semibold">₹<?= number_format($product['price'], 2) ?></span>
                                <span class="text-sm">Stock <?= (int)$product['stock'] ?></span>
                            </div>
                            <div class="flex gap-3">
                                <a href="<?= BASE_URL ?>/product.php?id=<?= $product['id'] ?>" class="inline-flex flex-1 items-center justify-center rounded-3xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-900 hover:bg-slate-50">View</a>
                                <a href="<?= BASE_URL ?>/cart.php?action=add&id=<?= $product['id'] ?>" class="inline-flex flex-1 items-center justify-center rounded-3xl bg-slate-900 px-4 py-3 text-sm font-semibold text-white hover:bg-slate-800">Add</a>
                            </div>
                        </div>
                    </article>
                <?php endforeach; else: ?>
                    <div class="col-span-full rounded-[1.75rem] border border-slate-200 bg-white p-8 text-center text-slate-600 shadow-sm">Products not found.</div>
                <?php endif; ?>
            </div>
            <?php if ($totalPages > 1): ?>
                <nav class="mt-8 flex flex-wrap items-center gap-2">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <?php $queryParams = array_merge($_GET, ['page' => $i]); ?>
                        <a href="<?= BASE_URL ?>/collection/<?= $categorySlug ? $categorySlug . '?' . http_build_query($queryParams) : '?' . http_build_query($queryParams) ?>" class="inline-flex items-center justify-center rounded-full border border-slate-200 px-4 py-2 text-sm <?= $i === $page ? 'bg-slate-900 text-white' : 'bg-white text-slate-700 hover:bg-slate-50' ?>"><?= $i ?></a>
                    <?php endfor; ?>
                </nav>
            <?php endif; ?>
        </section>
    </div>
</div>
<?php endif; ?>
<?php include __DIR__ . '/includes/footer.php'; ?>
