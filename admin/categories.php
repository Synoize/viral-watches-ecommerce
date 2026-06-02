<?php
require_once __DIR__ . '/_header.php';
$category = null;
if (!empty($_GET['edit'])) {
    $stmt = $pdo->prepare('SELECT * FROM categories WHERE id = ?');
    $stmt->execute([(int)$_GET['edit']]);
    $category = $stmt->fetch();
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $image = sanitize($_POST['category_image'] ?? '');
    if (empty($name)) {
        $error = 'Category name is required.';
    } else {
        if (!empty($_POST['category_id'])) {
            $stmt = $pdo->prepare('UPDATE categories SET name = ?, category_image = ? WHERE id = ?');
            $stmt->execute([$name, $image, (int)$_POST['category_id']]);
            flash('success', 'Category updated.');
        } else {
            $stmt = $pdo->prepare('INSERT INTO categories (name, category_image) VALUES (?, ?)');
            $stmt->execute([$name, $image]);
            flash('success', 'Category added.');
        }
        redirect('/admin/categories.php');
    }
}
if (!empty($_POST['delete_id'])) {
    $stmt = $pdo->prepare('DELETE FROM categories WHERE id = ?');
    $stmt->execute([(int)$_POST['delete_id']]);
    flash('success', 'Category deleted.');
    redirect('/admin/categories.php');
}
$categories = $pdo->query('SELECT * FROM categories ORDER BY name')->fetchAll();
?>
<div class="row g-4">
    <div class="col-lg-7">
        <div class="card p-4 shadow-sm">
            <h4>Categories</h4>
            <?php if ($msg = flash('success')): ?><div class="alert alert-success"><?= sanitize($msg) ?></div><?php endif; ?>
            <table class="table mt-3">
                <thead><tr><th>Name</th><th>Image</th><th></th></tr></thead>
                <tbody>
                    <?php foreach ($categories as $cat): ?>
                        <tr>
                            <td><?= sanitize($cat['name']) ?></td>
                            <td><?= sanitize($cat['category_image']) ? '<a href="' . sanitize($cat['category_image']) . '" target="_blank">View</a>' : '—' ?></td>
                            <td>
                                <a class="btn btn-sm btn-outline-primary" href="<?= BASE_URL ?>/admin/categories.php?edit=<?= $cat['id'] ?>">Edit</a>
                                <form class="d-inline" method="post" onsubmit="return confirm('Delete category?');">
                                    <input type="hidden" name="delete_id" value="<?= $cat['id'] ?>">
                                    <button class="btn btn-sm btn-outline-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card p-4 shadow-sm">
            <h4><?= $category ? 'Edit Category' : 'Add Category' ?></h4>
            <?php if (!empty($error)): ?><div class="alert alert-danger"><?= sanitize($error) ?></div><?php endif; ?>
            <form method="post">
                <input type="hidden" name="category_id" value="<?= sanitize($category['id'] ?? '') ?>">
                <div class="mb-3"><label class="form-label">Name</label><input name="name" class="form-control" value="<?= sanitize($category['name'] ?? '') ?>" required></div>
                <div class="mb-3"><label class="form-label">Image URL</label><input name="category_image" class="form-control" value="<?= sanitize($category['category_image'] ?? '') ?>"></div>
                <button class="btn btn-primary w-100">Save Category</button>
            </form>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/_footer.php'; ?>
