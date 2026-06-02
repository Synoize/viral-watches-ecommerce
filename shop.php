<?php
require_once __DIR__ . '/includes/functions.php';
$categoryId = !empty($_GET['category']) ? (int)$_GET['category'] : null;
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
?>
<?php include __DIR__ . '/includes/header.php'; ?>
<div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
    <div class="grid gap-8 lg:grid-cols-[280px_1fr]">
        <aside class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">Filters</h2>
            <form method="get" class="space-y-4 mt-6">
                <div>
                    <label class="block text-sm font-medium text-slate-700">Category</label>
                    <select name="category" class="mt-2 w-full rounded-3xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 outline-none focus:border-slate-900">
                        <option value="">All categories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= $categoryId === (int)$cat['id'] ? 'selected' : '' ?>><?= sanitize($cat['name']) ?></option>
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
                <form method="get" action="<?= BASE_URL ?>/shop.php" class="mt-4 flex items-center gap-2">
                    <input type="text" name="search" value="<?= $search ?>" placeholder="Keyword" class="w-full rounded-3xl border border-slate-200 bg-white px-4 py-3 text-slate-900 outline-none focus:border-slate-900" />
                    <button class="inline-flex h-12 w-12 items-center justify-center rounded-full bg-slate-900 text-white hover:bg-slate-800"><i class="fas fa-search"></i></button>
                </form>
            </div>
        </aside>
        <section>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h1 class="text-2xl font-semibold text-slate-900">Shop Products</h1>
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
                    <div class="col-span-full rounded-[1.75rem] border border-rose-200 bg-rose-50 p-6 text-rose-700">No products match your filters.</div>
                <?php endif; ?>
            </div>
            <?php if ($totalPages > 1): ?>
                <nav class="mt-8 flex flex-wrap items-center gap-2">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <a href="<?= BASE_URL ?>/shop.php?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>" class="inline-flex items-center justify-center rounded-full border border-slate-200 px-4 py-2 text-sm <?= $i === $page ? 'bg-slate-900 text-white' : 'bg-white text-slate-700 hover:bg-slate-50' ?>"><?= $i ?></a>
                    <?php endfor; ?>
                </nav>
            <?php endif; ?>
        </section>
    </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
