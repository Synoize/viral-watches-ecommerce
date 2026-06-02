<?php
require_once __DIR__ . '/includes/functions.php';
$categoryId = !empty($_GET['category']) ? (int)$_GET['category'] : null;
$search = !empty($_GET['search']) ? sanitize($_GET['search']) : null;
sort($_GET); // no-op compatibility
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
<div class="container mt-5">
    <div class="row">
        <aside class="col-lg-3 mb-4">
            <div class="card p-3 mb-4">
                <h5 class="mb-3">Filters</h5>
                <form method="get" action="<?= BASE_URL ?>/shop.php">
                    <div class="mb-3">
                        <label class="form-label">Category</label>
                        <select class="form-select" name="category">
                            <option value="">All categories</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>" <?= $categoryId === (int)$cat['id'] ? 'selected' : '' ?>><?= sanitize($cat['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Price range</label>
                        <div class="input-group mb-2">
                            <span class="input-group-text">₹</span>
                            <input type="number" class="form-control" name="min_price" value="<?= $priceMin ?>" placeholder="Min">
                        </div>
                        <div class="input-group">
                            <span class="input-group-text">₹</span>
                            <input type="number" class="form-control" name="max_price" value="<?= $priceMax ?>" placeholder="Max">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Sort by</label>
                        <select class="form-select" name="sort">
                            <option value="">Newest first</option>
                            <option value="price_asc" <?= ($_GET['sort'] ?? '') === 'price_asc' ? 'selected' : '' ?>>Price low → high</option>
                            <option value="price_desc" <?= ($_GET['sort'] ?? '') === 'price_desc' ? 'selected' : '' ?>>Price high → low</option>
                        </select>
                    </div>
                    <button class="btn btn-primary w-100">Apply</button>
                </form>
            </div>
            <div class="card p-3">
                <h5>Search</h5>
                <form action="<?= BASE_URL ?>/shop.php" method="get">
                    <div class="input-group">
                        <input type="text" name="search" class="form-control" placeholder="Keyword" value="<?= $search ?>">
                        <button class="btn btn-primary"><i class="fas fa-search"></i></button>
                    </div>
                </form>
            </div>
        </aside>

        <section class="col-lg-9">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h2 class="h4 mb-0">Shop Products</h2>
                    <p class="text-muted small mb-0"><?= $total ?> products found</p>
                </div>
            </div>
            <div class="row g-4">
                <?php if ($products): foreach ($products as $product): $gallery = json_decode($product['gallery'], true) ?: []; ?>
                    <div class="col-md-6 col-xl-4">
                        <div class="card h-100 shadow-sm">
                            <img src="<?= sanitize($gallery[0] ?? $product['images']) ?>" class="card-img-top" style="height:220px; object-fit:cover;" alt="<?= sanitize($product['name']) ?>">
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title"><?= sanitize($product['name']) ?></h5>
                                <p class="text-muted mb-2">₹<?= number_format($product['price'], 2) ?></p>
                                <p class="small text-secondary">Stock: <?= (int)$product['stock'] ?></p>
                                <div class="mt-auto d-flex gap-2">
                                    <a class="btn btn-outline-primary btn-sm flex-grow-1" href="<?= BASE_URL ?>/product.php?id=<?= $product['id'] ?>">View</a>
                                    <a class="btn btn-primary btn-sm" href="<?= BASE_URL ?>/cart.php?action=add&id=<?= $product['id'] ?>">Add</a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; else: ?>
                    <div class="col-12"><div class="alert alert-warning">No products match your filters.</div></div>
                <?php endif; ?>
            </div>
            <?php if ($totalPages > 1): ?>
                <nav class="mt-4">
                    <ul class="pagination">
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                <a class="page-link" href="<?= BASE_URL ?>/shop.php?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        </section>
    </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
