<?php
require_once __DIR__ . '/../includes/functions.php';
if (!isAdmin()) {
    redirect('/admin/login.php');
}

ensureBoxOptionsTableExists();

$box = null;
$uploadDir = __DIR__ . '/../assets/images/boxes';
$uploadPathPrefix = 'assets/images/boxes/';

if (!empty($_GET['edit'])) {
    $stmt = $pdo->prepare('SELECT * FROM box_options WHERE id = ?');
    $stmt->execute([(int)$_GET['edit']]);
    $box = $stmt->fetch();
}

function saveBoxUpload($field, $uploadDir, $uploadPathPrefix) {
    if (empty($_FILES[$field]['name']) || !is_uploaded_file($_FILES[$field]['tmp_name'])) {
        return null;
    }

    if ($_FILES[$field]['error'] !== UPLOAD_ERR_OK) {
        return ['error' => 'Image upload failed.'];
    }

    $allowed = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];
    $mime = mime_content_type($_FILES[$field]['tmp_name']);
    if (!isset($allowed[$mime])) {
        return ['error' => 'Upload a JPG, PNG, or WEBP image.'];
    }

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0775, true);
    }

    $filename = 'box-' . date('YmdHis') . '-' . bin2hex(random_bytes(4)) . '.' . $allowed[$mime];
    $target = $uploadDir . DIRECTORY_SEPARATOR . $filename;
    if (!move_uploaded_file($_FILES[$field]['tmp_name'], $target)) {
        return ['error' => 'Could not save uploaded image.'];
    }

    return ['path' => $uploadPathPrefix . $filename];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_POST['delete_id'])) {
        $deleteId = (int)$_POST['delete_id'];
        try {
            $stmt = $pdo->prepare('DELETE FROM box_options WHERE id = ?');
            $stmt->execute([$deleteId]);
            flash('success', 'Box option deleted.');
        } catch (PDOException $e) {
            $stmt = $pdo->prepare('UPDATE box_options SET is_active = 0 WHERE id = ?');
            $stmt->execute([$deleteId]);
            flash('success', 'Box option is used in orders, so it was deactivated instead.');
        }
        redirect('/admin/box-options.php');
    }

    $name = sanitize($_POST['name'] ?? '');
    $price = (float)($_POST['price'] ?? 0);
    $image = sanitize($_POST['image'] ?? '');
    $isActive = isset($_POST['is_active']) ? 1 : 0;
    $upload = saveBoxUpload('image_file', $uploadDir, $uploadPathPrefix);

    if (!empty($upload['error'])) {
        $error = $upload['error'];
    } elseif (!empty($upload['path'])) {
        $image = $upload['path'];
    }

    if (empty($error)) {
        if ($name === '') {
            $error = 'Box name is required.';
        } elseif ($price < 0) {
            $error = 'Box price cannot be negative.';
        }
    }

    if (empty($error)) {
        try {
            if (!empty($_POST['box_id'])) {
                $stmt = $pdo->prepare('UPDATE box_options SET name = ?, image = ?, price = ?, is_active = ? WHERE id = ?');
                $stmt->execute([$name, $image, $price, $isActive, (int)$_POST['box_id']]);
                flash('success', 'Box option updated.');
            } else {
                $stmt = $pdo->prepare('INSERT INTO box_options (name, image, price, is_active) VALUES (?, ?, ?, ?)');
                $stmt->execute([$name, $image, $price, $isActive]);
                flash('success', 'Box option added.');
            }
            redirect('/admin/box-options.php');
        } catch (PDOException $e) {
            $error = 'A box option with this name already exists.';
        }
    }
}

$boxes = $pdo->query('SELECT * FROM box_options ORDER BY is_active DESC, name')->fetchAll();
require_once __DIR__ . '/_header.php';
?>
<div class="grid gap-6 xl:grid-cols-[2fr_1fr]">
    <div>
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="text-2xl font-semibold text-slate-900">Box Options</h2>
            <a href="<?= BASE_URL ?>/product.php?id=1" target="_blank" class="inline-flex items-center justify-center rounded-full border border-slate-200 bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-900 hover:bg-slate-50">Preview Product Page</a>
        </div>
        <?php if ($msg = flash('success')): ?><div class="mt-6 rounded-3xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-700"><?= sanitize($msg) ?></div><?php endif; ?>
        <div class="mt-6 overflow-hidden rounded-[2rem] border border-slate-200">
            <table class="w-full border-separate border-spacing-0 text-left text-sm">
                <thead class="bg-slate-100 text-slate-600">
                    <tr>
                        <th class="px-6 py-4">Box</th>
                        <th class="px-6 py-4">Price</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4">Created</th>
                        <th class="px-6 py-4"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($boxes as $item): ?>
                        <tr class="border-t border-slate-200 bg-white">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-14 w-14 items-center justify-center rounded-2xl border border-slate-200 bg-slate-50">
                                        <?php if (!empty($item['image'])): ?>
                                            <img src="<?= sanitize(resolveAssetUrl($item['image'])) ?>" alt="<?= sanitize($item['name']) ?>" class="h-12 w-12 object-contain">
                                        <?php else: ?>
                                            <span class="text-xs text-slate-400">No image</span>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <p class="font-semibold text-slate-900"><?= sanitize($item['name']) ?></p>
                                        <p class="mt-1 max-w-[260px] truncate text-xs text-slate-500"><?= sanitize($item['image'] ?: 'No image path') ?></p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-slate-900">&#8377;<?= number_format($item['price'], 2) ?></td>
                            <td class="px-6 py-4">
                                <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold <?= $item['is_active'] ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600' ?>">
                                    <?= $item['is_active'] ? 'Active' : 'Inactive' ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-slate-600"><?= date('j M Y', strtotime($item['created_at'])) ?></td>
                            <td class="px-6 py-4 space-x-2 flex">
                                <a class="inline-flex rounded-full border border-slate-200 bg-slate-100 px-4 py-2 text-sm text-slate-900 hover:bg-slate-50" href="<?= BASE_URL ?>/admin/box-options.php?edit=<?= $item['id'] ?>">Edit</a>
                                <form class="inline" method="post" onsubmit="return confirm('Delete this box option?');">
                                    <input type="hidden" name="delete_id" value="<?= $item['id'] ?>">
                                    <button class="inline-flex rounded-full bg-rose-50 px-4 py-2 text-sm font-semibold text-rose-700 hover:bg-rose-100">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$boxes): ?>
                        <tr class="bg-white"><td colspan="5" class="px-6 py-8 text-center text-slate-500">No box options yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <aside class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="text-2xl font-semibold text-slate-900"><?= $box ? 'Edit Box' : 'Add Box' ?></h2>
        <?php if (!empty($error)): ?><div class="mt-6 rounded-3xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-700"><?= sanitize($error) ?></div><?php endif; ?>
        <form method="post" enctype="multipart/form-data" class="mt-6 space-y-4">
            <input type="hidden" name="box_id" value="<?= sanitize($box['id'] ?? '') ?>">
            <label class="block text-sm font-medium text-slate-700">Name<input name="name" value="<?= sanitize($box['name'] ?? '') ?>" required class="mt-2 w-full rounded-3xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 outline-none focus:border-slate-900" /></label>
            <label class="block text-sm font-medium text-slate-700">Price<input type="number" step="0.01" min="0" name="price" value="<?= sanitize($box['price'] ?? '0.00') ?>" required class="mt-2 w-full rounded-3xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 outline-none focus:border-slate-900" /></label>
            <label class="block text-sm font-medium text-slate-700">Image URL or Path<input name="image" value="<?= sanitize($box['image'] ?? '') ?>" placeholder="assets/images/boxes/example.png" class="mt-2 w-full rounded-3xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 outline-none focus:border-slate-900" /></label>
            <label class="block text-sm font-medium text-slate-700">Upload Image<input type="file" name="image_file" accept="image/png,image/jpeg,image/webp" class="mt-2 w-full rounded-3xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none focus:border-slate-900" /></label>
            <?php if (!empty($box['image'])): ?>
                <div class="rounded-3xl border border-slate-200 bg-slate-50 p-4">
                    <p class="text-sm font-medium text-slate-700">Current image</p>
                    <img src="<?= sanitize(resolveAssetUrl($box['image'])) ?>" alt="<?= sanitize($box['name']) ?>" class="mt-3 h-28 w-full object-contain">
                </div>
            <?php endif; ?>
            <label class="flex items-center gap-3 text-sm font-medium text-slate-700"><input type="checkbox" name="is_active" class="h-5 w-5 rounded border-slate-300 text-brand focus:ring-brand" <?= !isset($box['is_active']) || $box['is_active'] ? 'checked' : '' ?> /> Active</label>
            <button class="inline-flex w-full items-center justify-center rounded-3xl bg-slate-900 px-6 py-3 text-sm font-semibold text-white hover:bg-slate-800">Save Box</button>
            <?php if ($box): ?>
                <a href="<?= BASE_URL ?>/admin/box-options.php" class="inline-flex w-full items-center justify-center rounded-3xl border border-slate-200 bg-white px-6 py-3 text-sm font-semibold text-slate-900 hover:bg-slate-50">Cancel Edit</a>
            <?php endif; ?>
        </form>
    </aside>
</div>
<?php require_once __DIR__ . '/_footer.php'; ?>
