<?php
require_once __DIR__ . '/_header.php';
$action = $_GET['action'] ?? '';
$product = null;
$categories = $pdo->query('SELECT id, name FROM categories')->fetchAll();
if ($action === 'edit' && !empty($_GET['id'])) {
    $stmt = $pdo->prepare('SELECT * FROM products WHERE id = ?');
    $stmt->execute([(int)$_GET['id']]);
    $product = $stmt->fetch();
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $description = sanitize($_POST['description'] ?? '');
    $category = (int)($_POST['category'] ?? 0);
    $price = (float)($_POST['price'] ?? 0);
    $stock = (int)($_POST['stock'] ?? 0);
    $images = sanitize($_POST['images'] ?? '');
    $gallery = array_filter(array_map('trim', explode(',', $_POST['gallery'] ?? '')));
    $galleryJson = json_encode($gallery);
    if (empty($name) || $price <= 0) {
        $error = 'Product must have a name and positive price.';
    } else {
        if (!empty($_POST['product_id'])) {
            $stmt = $pdo->prepare('UPDATE products SET name = ?, description = ?, category = ?, price = ?, stock = ?, images = ?, gallery = ? WHERE id = ?');
            $stmt->execute([$name, $description, $category, $price, $stock, $images, $galleryJson, (int)$_POST['product_id']]);
            flash('success', 'Product updated.');
        } else {
            $stmt = $pdo->prepare('INSERT INTO products (name, description, category, price, stock, images, gallery) VALUES (?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute([$name, $description, $category, $price, $stock, $images, $galleryJson]);
            flash('success', 'Product added.');
        }
        redirect('/admin/products.php');
    }
}
if (!empty($_POST['delete_id'])) {
    $stmt = $pdo->prepare('DELETE FROM products WHERE id = ?');
    $stmt->execute([(int)$_POST['delete_id']]);
    flash('success', 'Product deleted.');
    redirect('/admin/products.php');
}
$products = $pdo->query('SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON p.category = c.id ORDER BY p.id DESC')->fetchAll();
?>
<div class="grid gap-6 xl:grid-cols-[2fr_1fr]">
    <div class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="text-2xl font-semibold text-slate-900">Products</h2>
        <?php if ($msg = flash('success')): ?><div class="mt-6 rounded-3xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-700"><?= sanitize($msg) ?></div><?php endif; ?>
        <div class="mt-6 overflow-hidden rounded-[2rem] border border-slate-200">
            <table class="w-full border-separate border-spacing-0 text-left text-sm">
                <thead class="bg-slate-100 text-slate-600">
                    <tr>
                        <th class="px-6 py-4">Name</th>
                        <th class="px-6 py-4">Category</th>
                        <th class="px-6 py-4">Price</th>
                        <th class="px-6 py-4">Stock</th>
                        <th class="px-6 py-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $item): ?>
                        <tr class="border-t border-slate-200 bg-white">
                            <td class="px-6 py-4 text-slate-900"><?= sanitize($item['name']) ?></td>
                            <td class="px-6 py-4 text-slate-700"><?= sanitize($item['category_name'] ?? 'Uncategorized') ?></td>
                            <td class="px-6 py-4 text-slate-900">₹<?= number_format($item['price'], 2) ?></td>
                            <td class="px-6 py-4 text-slate-700"><?= (int)$item['stock'] ?></td>
                            <td class="px-6 py-4 space-x-2">
                                <a class="inline-flex rounded-full border border-slate-200 bg-slate-100 px-4 py-2 text-sm text-slate-900 hover:bg-slate-50" href="<?= BASE_URL ?>/admin/products.php?action=edit&id=<?= $item['id'] ?>">Edit</a>
                                <form class="inline" method="post" onsubmit="return confirm('Delete this product?');">
                                    <input type="hidden" name="delete_id" value="<?= $item['id'] ?>">
                                    <button class="inline-flex rounded-full bg-rose-50 px-4 py-2 text-sm font-semibold text-rose-700 hover:bg-rose-100">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$products): ?>
                        <tr class="bg-white"><td colspan="5" class="px-6 py-8 text-center text-slate-500">Products not found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <aside class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="text-2xl font-semibold text-slate-900"><?= $product ? 'Edit Product' : 'Add Product' ?></h2>
        <?php if (!empty($error)): ?><div class="mt-6 rounded-3xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-700"><?= sanitize($error) ?></div><?php endif; ?>
        <form method="post" class="mt-6 space-y-4">
            <input type="hidden" name="product_id" value="<?= sanitize($product['id'] ?? '') ?>">
            <label class="block text-sm font-medium text-slate-700">Name<input name="name" value="<?= sanitize($product['name'] ?? '') ?>" required class="mt-2 w-full rounded-3xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 outline-none focus:border-slate-900" /></label>
            <label class="block text-sm font-medium text-slate-700">Description<textarea name="description" rows="3" class="mt-2 w-full rounded-3xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 outline-none focus:border-slate-900"><?= sanitize($product['description'] ?? '') ?></textarea></label>
            <label class="block text-sm font-medium text-slate-700">Category<select name="category" class="mt-2 w-full rounded-3xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 outline-none focus:border-slate-900">
                <option value="0">Select category</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>" <?= isset($product['category']) && $product['category'] == $cat['id'] ? 'selected' : '' ?>><?= sanitize($cat['name']) ?></option>
                <?php endforeach; ?>
                <?php if (!$categories): ?>
                    <option value="0" disabled>Categories not found</option>
                <?php endif; ?>
            </select></label>
            <label class="block text-sm font-medium text-slate-700">Price<input type="number" step="0.01" name="price" value="<?= sanitize($product['price'] ?? '') ?>" required class="mt-2 w-full rounded-3xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 outline-none focus:border-slate-900" /></label>
            <label class="block text-sm font-medium text-slate-700">Stock<input type="number" name="stock" value="<?= sanitize($product['stock'] ?? '0') ?>" required class="mt-2 w-full rounded-3xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 outline-none focus:border-slate-900" /></label>
            <label class="block text-sm font-medium text-slate-700">Main Image URL<input name="images" value="<?= sanitize($product['images'] ?? '') ?>" class="mt-2 w-full rounded-3xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 outline-none focus:border-slate-900" /></label>
            <label class="block text-sm font-medium text-slate-700">Gallery URLs (comma separated)<textarea name="gallery" rows="2" class="mt-2 w-full rounded-3xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 outline-none focus:border-slate-900"><?= sanitize(implode(', ', json_decode($product['gallery'] ?? '[]', true) ?: [])) ?></textarea></label>
            <button class="inline-flex w-full items-center justify-center rounded-3xl bg-slate-900 px-6 py-3 text-sm font-semibold text-white hover:bg-slate-800">Save Product</button>
        </form>
    </aside>
</div>
<?php require_once __DIR__ . '/_footer.php'; ?>
