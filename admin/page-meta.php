<?php
require_once __DIR__ . '/../includes/functions.php';
if (!isAdmin()) {
    redirect('/admin/login.php');
}

seedDefaultPageMeta();

$meta = null;
if (!empty($_GET['edit'])) {
    $stmt = $pdo->prepare('SELECT * FROM page_meta WHERE id = ?');
    $stmt->execute([(int)$_GET['edit']]);
    $meta = $stmt->fetch();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_POST['delete_id'])) {
        $stmt = $pdo->prepare('UPDATE page_meta SET is_active = 0 WHERE id = ?');
        $stmt->execute([(int)$_POST['delete_id']]);
        flash('success', 'Page metadata disabled.');
        redirect('/admin/page-meta.php');
    }

    $pageKey = strtolower(preg_replace('/[^a-z0-9_-]+/i', '-', sanitize($_POST['page_key'] ?? '')));
    $pageName = sanitize($_POST['page_name'] ?? '');
    $path = normalizePageMetaPath($_POST['path'] ?? '/');
    $title = sanitize($_POST['title'] ?? '');
    $description = sanitize($_POST['description'] ?? '');
    $keywords = sanitize($_POST['keywords'] ?? '');
    $isActive = isset($_POST['is_active']) ? 1 : 0;

    if ($pageKey === '' || $pageName === '' || $path === '' || $title === '') {
        $error = 'Page key, name, path, and title are required.';
    } else {
        try {
            if (!empty($_POST['meta_id'])) {
                $stmt = $pdo->prepare('UPDATE page_meta SET page_key = ?, page_name = ?, path = ?, title = ?, description = ?, keywords = ?, is_active = ? WHERE id = ?');
                $stmt->execute([$pageKey, $pageName, $path, $title, $description, $keywords, $isActive, (int)$_POST['meta_id']]);
                flash('success', 'Page metadata updated.');
            } else {
                $stmt = $pdo->prepare('INSERT INTO page_meta (page_key, page_name, path, title, description, keywords, is_active) VALUES (?, ?, ?, ?, ?, ?, ?)');
                $stmt->execute([$pageKey, $pageName, $path, $title, $description, $keywords, $isActive]);
                flash('success', 'Page metadata added.');
            }
            redirect('/admin/page-meta.php');
        } catch (PDOException $e) {
            $error = 'Page key or path already exists.';
        }
    }
}

$items = $pdo->query('SELECT * FROM page_meta ORDER BY path')->fetchAll();
require_once __DIR__ . '/_header.php';
?>
<div class="grid gap-6 xl:grid-cols-[68%_30%]">
    <div>
        <h2 class="text-2xl font-semibold text-slate-900">Page Titles & Descriptions</h2>
        <div class="mt-6 overflow-hidden rounded-[2rem] border border-slate-200">
            <table class="w-full border-separate border-spacing-0 text-left text-sm">
                <thead class="bg-slate-100 text-slate-600">
                    <tr>
                        <th class="px-6 py-4">Page</th>
                        <th class="px-6 py-4">Title</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                        <tr class="border-t border-slate-200 bg-white">
                            <td class="px-6 py-4">
                                <p class="font-semibold text-slate-900"><?= sanitize($item['page_name']) ?></p>
                                <p class="mt-1 text-xs text-slate-500"><?= sanitize($item['path']) ?> / <?= sanitize($item['page_key']) ?></p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="max-w-[280px] truncate font-medium text-slate-900"><?= sanitize($item['title']) ?></p>
                                <p class="mt-1 max-w-[280px] truncate text-xs text-slate-500"><?= sanitize($item['description'] ?? '') ?></p>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold <?= $item['is_active'] ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600' ?>">
                                    <?= $item['is_active'] ? 'Active' : 'Inactive' ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 space-x-2 flex">
                                <a class="inline-flex rounded-full border border-slate-200 bg-slate-100 px-4 py-2 text-sm text-slate-900 hover:bg-slate-50" href="<?= publicUrl('/admin/page-meta?edit=' . $item['id']) ?>">Edit</a>
                                <form class="inline" method="post" onsubmit="return confirm('Disable this page metadata?');">
                                    <input type="hidden" name="delete_id" value="<?= $item['id'] ?>">
                                    <button class="inline-flex rounded-full bg-rose-50 px-4 py-2 text-sm font-semibold text-rose-700 hover:bg-rose-100">Disable</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <aside class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="text-2xl font-semibold text-slate-900"><?= $meta ? 'Edit Page Meta' : 'Add Page Meta' ?></h2>
        <form method="post" class="mt-6 space-y-4">
            <input type="hidden" name="meta_id" value="<?= sanitize($meta['id'] ?? '') ?>">
            <label class="block text-sm font-medium text-slate-700">Page Key<input name="page_key" value="<?= sanitize($meta['page_key'] ?? '') ?>" placeholder="about" required class="mt-2 w-full rounded-3xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 outline-none focus:border-slate-900" /></label>
            <label class="block text-sm font-medium text-slate-700">Page Name<input name="page_name" value="<?= sanitize($meta['page_name'] ?? '') ?>" placeholder="About" required class="mt-2 w-full rounded-3xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 outline-none focus:border-slate-900" /></label>
            <label class="block text-sm font-medium text-slate-700">Path<input name="path" value="<?= sanitize($meta['path'] ?? '') ?>" placeholder="/about" required class="mt-2 w-full rounded-3xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 outline-none focus:border-slate-900" /></label>
            <label class="block text-sm font-medium text-slate-700">Title<input name="title" value="<?= sanitize($meta['title'] ?? '') ?>" required class="mt-2 w-full rounded-3xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 outline-none focus:border-slate-900" /></label>
            <label class="block text-sm font-medium text-slate-700">Description<textarea name="description" rows="4" class="mt-2 w-full rounded-3xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 outline-none focus:border-slate-900"><?= sanitize($meta['description'] ?? '') ?></textarea></label>
            <label class="block text-sm font-medium text-slate-700">Keywords<input name="keywords" value="<?= sanitize($meta['keywords'] ?? '') ?>" placeholder="watch, box, checkout" class="mt-2 w-full rounded-3xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 outline-none focus:border-slate-900" /></label>
            <div class="rounded-3xl border border-slate-200 bg-slate-50 p-4 text-xs leading-6 text-slate-600">
                Product template tokens: <strong>{product_name}</strong>, <strong>{product_price}</strong>, <strong>{category_name}</strong>.
            </div>
            <label class="flex items-center gap-3 text-sm font-medium text-slate-700"><input type="checkbox" name="is_active" class="h-5 w-5 rounded border-slate-300 text-brand focus:ring-brand" <?= !isset($meta['is_active']) || $meta['is_active'] ? 'checked' : '' ?> /> Active</label>
            <button class="inline-flex w-full items-center justify-center rounded-3xl bg-slate-900 px-6 py-3 text-sm font-semibold text-white hover:bg-slate-800">Save Metadata</button>
            <?php if ($meta): ?>
                <a href="<?= publicUrl('/admin/page-meta') ?>" class="inline-flex w-full items-center justify-center rounded-3xl border border-slate-200 bg-white px-6 py-3 text-sm font-semibold text-slate-900 hover:bg-slate-50">Cancel Edit</a>
            <?php endif; ?>
        </form>
    </aside>
</div>
<?php require_once __DIR__ . '/_footer.php'; ?>
