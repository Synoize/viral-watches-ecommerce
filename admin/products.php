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
<div class="row g-4">
    <div class="col-lg-8">
        <div class="card shadow-sm p-4">
            <h4>Products</h4>
            <?php if ($msg = flash('success')): ?><div class="alert alert-success"><?= sanitize($msg) ?></div><?php endif; ?>
            <table class="table table-hover mt-3">
                <thead><tr><th>Name</th><th>Category</th><th>Price</th><th>Stock</th><th>Actions</th></tr></thead>
                <tbody>
                    <?php foreach ($products as $item): ?>
                        <tr>
                            <td><?= sanitize($item['name']) ?></td>
                            <td><?= sanitize($item['category_name'] ?? 'Uncategorized') ?></td>
                            <td>₹<?= number_format($item['price'], 2) ?></td>
                            <td><?= (int)$item['stock'] ?></td>
                            <td>
                                <a class="btn btn-sm btn-outline-primary" href="<?= BASE_URL ?>/admin/products.php?action=edit&id=<?= $item['id'] ?>">Edit</a>
                                <form class="d-inline" method="post" onsubmit="return confirm('Delete this product?');">
                                    <input type="hidden" name="delete_id" value="<?= $item['id'] ?>">
                                    <button class="btn btn-sm btn-outline-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card shadow-sm p-4">
            <h4><?= $product ? 'Edit Product' : 'Add Product' ?></h4>
            <?php if (!empty($error)): ?><div class="alert alert-danger"><?= sanitize($error) ?></div><?php endif; ?>
            <form method="post">
                <input type="hidden" name="product_id" value="<?= sanitize($product['id'] ?? '') ?>">
                <div class="mb-3"><label class="form-label">Name</label><input name="name" class="form-control" value="<?= sanitize($product['name'] ?? '') ?>" required></div>
                <div class="mb-3"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="3"><?= sanitize($product['description'] ?? '') ?></textarea></div>
                <div class="mb-3"><label class="form-label">Category</label><select name="category" class="form-select">
                    <option value="0">Select category</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= isset($product['category']) && $product['category']==$cat['id'] ? 'selected' : '' ?>><?= sanitize($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select></div>
                <div class="mb-3"><label class="form-label">Price</label><input type="number" step="0.01" name="price" class="form-control" value="<?= sanitize($product['price'] ?? '') ?>" required></div>
                <div class="mb-3"><label class="form-label">Stock</label><input type="number" name="stock" class="form-control" value="<?= sanitize($product['stock'] ?? '0') ?>" required></div>
                <div class="mb-3"><label class="form-label">Main Image URL</label><input name="images" class="form-control" value="<?= sanitize($product['images'] ?? '') ?>"></div>
                <div class="mb-3"><label class="form-label">Gallery URLs (comma separated)</label><textarea name="gallery" class="form-control" rows="2"><?= sanitize(implode(', ', json_decode($product['gallery'] ?? '[]', true) ?: [])) ?></textarea></div>
                <button class="btn btn-primary w-100">Save Product</button>
            </form>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/_footer.php'; ?>
