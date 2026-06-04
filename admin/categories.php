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
    $upload = saveAdminImageUpload($_FILES['category_image_file'] ?? [], 'categories', 'category');
    if (!empty($upload['error'])) {
        $error = $upload['error'];
    } elseif (!empty($upload['path'])) {
        $image = $upload['path'];
    }

    if (empty($error) && empty($name)) {
        $error = 'Category name is required.';
    } elseif (empty($error)) {
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
<div class="grid gap-6 xl:grid-cols-[2fr_1fr]">
    <div>
        <h2 class="text-2xl font-semibold text-slate-900">Categories</h2>
        <?php if ($msg = flash('success')): ?><div class="mt-6 rounded-3xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-700"><?= sanitize($msg) ?></div><?php endif; ?>
        <div class="mt-6 overflow-hidden rounded-[2rem] border border-slate-200">
            <table class="w-full border-separate border-spacing-0 text-left text-sm">
                <thead class="bg-slate-100 text-slate-600"><tr><th class="px-6 py-4">Name</th><th class="px-6 py-4">Image</th><th class="px-6 py-4"></th></tr></thead>
                <tbody>
                    <?php foreach ($categories as $cat): ?>
                        <tr class="border-t border-slate-200 bg-white">
                            <td class="px-6 py-4 text-slate-900"><?= sanitize($cat['name']) ?></td>
                            <td class="px-6 py-4 text-slate-700"><?= sanitize($cat['category_image']) ? '<a href="' . sanitize($cat['category_image']) . '" target="_blank" class="text-brand underline">View</a>' : '—' ?></td>
                            <td class="px-6 py-4 space-x-2 flex ">
                                <a class="inline-flex rounded-full border border-slate-200 bg-slate-100 px-4 py-2 text-sm text-slate-900 hover:bg-slate-50" href="<?= BASE_URL ?>/admin/categories.php?edit=<?= $cat['id'] ?>">Edit</a>
                                <form class="inline" method="post" onsubmit="return confirm('Delete category?');"><input type="hidden" name="delete_id" value="<?= $cat['id'] ?>"><button class="inline-flex rounded-full bg-rose-50 px-4 py-2 text-sm font-semibold text-rose-700 hover:bg-rose-100">Delete</button></form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$categories): ?>
                        <tr class="bg-white"><td colspan="3" class="px-6 py-8 text-center text-slate-500">Categories not found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <aside class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="text-2xl font-semibold text-slate-900"><?= $category ? 'Edit Category' : 'Add Category' ?></h2>
        <?php if (!empty($error)): ?><div class="mt-6 rounded-3xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-700"><?= sanitize($error) ?></div><?php endif; ?>
        <form method="post" enctype="multipart/form-data" class="mt-6 space-y-4">
            <input type="hidden" name="category_id" value="<?= sanitize($category['id'] ?? '') ?>">
            <label class="block text-sm font-medium text-slate-700">Name<input name="name" value="<?= sanitize($category['name'] ?? '') ?>" required class="mt-2 w-full rounded-3xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 outline-none focus:border-slate-900" /></label>
            <label class="block text-sm font-medium text-slate-700">Image URL or Path<input name="category_image" value="<?= sanitize($category['category_image'] ?? '') ?>" placeholder="assets/images/categories/example.jpg or https://..." class="mt-2 w-full rounded-3xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 outline-none focus:border-slate-900" /></label>
            <label class="block text-sm font-medium text-slate-700">Upload Image<input type="file" name="category_image_file" accept="image/png,image/jpeg,image/webp" class="mt-2 w-full rounded-3xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none focus:border-slate-900" /></label>
            <?php if (!empty($category['category_image'])): ?>
                <div class="rounded-3xl border border-slate-200 bg-slate-50 p-4">
                    <p class="text-sm font-medium text-slate-700">Current image</p>
                    <img src="<?= sanitize(resolveAssetUrl($category['category_image'])) ?>" alt="<?= sanitize($category['name'] ?? 'Category image') ?>" class="mt-3 h-28 w-full object-contain">
                </div>
            <?php endif; ?>
            <button class="inline-flex w-full items-center justify-center rounded-3xl bg-slate-900 px-6 py-3 text-sm font-semibold text-white hover:bg-slate-800">Save Category</button>
        </form>
    </aside>
</div>
<?php require_once __DIR__ . '/_footer.php'; ?>
