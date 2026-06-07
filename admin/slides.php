<?php
require_once __DIR__ . '/../includes/functions.php';
if (!isAdmin()) {
    redirect('/admin/login.php');
}

ensureSlidesTableExists();

$slide = null;
if (!empty($_GET['edit'])) {
    $stmt = $pdo->prepare('SELECT * FROM slides WHERE id = ?');
    $stmt->execute([(int)$_GET['edit']]);
    $slide = $stmt->fetch();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_POST['delete_id'])) {
        $deleteId = (int)$_POST['delete_id'];
        $stmt = $pdo->prepare('SELECT file_path, mobile_file_path FROM slides WHERE id = ?');
        $stmt->execute([$deleteId]);
        $slideAssets = $stmt->fetch() ?: [];

        $stmt = $pdo->prepare('DELETE FROM slides WHERE id = ?');
        $stmt->execute([$deleteId]);
        deleteLocalAssetsIfUnused([$slideAssets['file_path'] ?? '', $slideAssets['mobile_file_path'] ?? '']);
        flash('success', 'Slide deleted.');
        redirect('/admin/slides.php');
    }

    $desktopImage = sanitize($_POST['file_path'] ?? '');
    $mobileImage = sanitize($_POST['mobile_file_path'] ?? '');
    $sortOrder = max(0, (int)($_POST['sort_order'] ?? 0));
    $isActive = isset($_POST['is_active']) ? 1 : 0;

    $desktopUpload = saveAdminImageUpload($_FILES['desktop_image_file'] ?? [], 'slides', 'hero-desktop');
    if (!empty($desktopUpload['error'])) {
        $error = $desktopUpload['error'];
    } elseif (!empty($desktopUpload['path'])) {
        $desktopImage = $desktopUpload['path'];
    }

    if (empty($error)) {
        $mobileUpload = saveAdminImageUpload($_FILES['mobile_image_file'] ?? [], 'slides', 'hero-mobile');
        if (!empty($mobileUpload['error'])) {
            $error = $mobileUpload['error'];
        } elseif (!empty($mobileUpload['path'])) {
            $mobileImage = $mobileUpload['path'];
        }
    }

    if (empty($error) && $desktopImage === '') {
        $error = 'Desktop image URL/path or upload is required.';
    }

    if (empty($error)) {
        if (!empty($_POST['slide_id'])) {
            $slideId = (int)$_POST['slide_id'];
            $stmt = $pdo->prepare('SELECT file_path, mobile_file_path FROM slides WHERE id = ?');
            $stmt->execute([$slideId]);
            $oldSlideAssets = $stmt->fetch() ?: [];

            $stmt = $pdo->prepare('UPDATE slides SET type = ?, file_path = ?, mobile_file_path = ?, sort_order = ?, is_active = ? WHERE id = ?');
            $stmt->execute(['hero', $desktopImage, $mobileImage, $sortOrder, $isActive, $slideId]);
            deleteLocalAssetsIfUnused([$oldSlideAssets['file_path'] ?? '', $oldSlideAssets['mobile_file_path'] ?? '']);
            flash('success', 'Slide updated.');
        } else {
            $stmt = $pdo->prepare('INSERT INTO slides (type, file_path, mobile_file_path, sort_order, is_active) VALUES (?, ?, ?, ?, ?)');
            $stmt->execute(['hero', $desktopImage, $mobileImage, $sortOrder, $isActive]);
            flash('success', 'Slide added.');
        }
        redirect('/admin/slides.php');
    }
}

$slides = $pdo->query("SELECT * FROM slides WHERE type = 'hero' ORDER BY sort_order ASC, id ASC")->fetchAll();
require_once __DIR__ . '/_header.php';
?>
<div class="grid gap-6 xl:grid-cols-[68%_30%]">

    <!-- LEFT SIDE -->
    <div>

        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="text-2xl font-semibold text-slate-900">
                Hero Slides
            </h2>

            <a href="<?= BASE_URL ?>/"
                target="_blank"
                class="inline-flex items-center justify-center rounded-full border border-slate-200 bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-900 hover:bg-slate-50">
                Preview Home
            </a>
        </div>

        <?php if ($msg = flash('success')): ?>
            <div class="mt-6 rounded-3xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-700">
                <?= sanitize($msg) ?>
            </div>
        <?php endif; ?>

        <div class="mt-6 overflow-hidden rounded-[2rem] border border-slate-200 bg-white">
            <div class="overflow-x-auto">

                <table class="w-full text-left text-sm">

                    <thead class="bg-slate-100 text-slate-600">
                        <tr>
                            <th class="px-2 py-4">Desktop</th>
                            <th class="px-2 py-4">Mobile</th>
                            <th class="px-2 py-4">Order</th>
                            <th class="px-2 py-4">Status</th>
                            <th class="px-2 py-4 text-center">Actions</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php foreach ($slides as $item): ?>

                            <tr class="border-t border-slate-200 bg-white">

                                <td class="px-2 py-4">
                                    <div class="flex items-center gap-3">

                                        <div class="flex h-16 w-24 items-center justify-center rounded-2xl border border-slate-200 bg-slate-50">
                                            <img
                                                src="<?= sanitize(resolveAssetUrl($item['file_path'])) ?>"
                                                alt="Desktop slide"
                                                class="h-14 w-20 rounded-lg object-cover">
                                        </div>

                                        <p class="max-w-[140px] truncate text-xs text-slate-500">
                                            <?= sanitize($item['file_path']) ?>
                                        </p>

                                    </div>
                                </td>

                                <td class="px-2 py-4">
                                    <?php if (!empty($item['mobile_file_path'])): ?>

                                        <div class="flex items-center gap-3">

                                            <div class="flex h-16 w-12 items-center justify-center rounded-2xl border border-slate-200 bg-slate-50">
                                                <img
                                                    src="<?= sanitize(resolveAssetUrl($item['mobile_file_path'])) ?>"
                                                    alt="Mobile slide"
                                                    class="h-14 w-10 rounded-lg object-cover">
                                            </div>

                                            <p class="max-w-[140px] truncate text-xs text-slate-500">
                                                <?= sanitize($item['mobile_file_path']) ?>
                                            </p>

                                        </div>

                                    <?php else: ?>

                                        <span class="text-slate-500">
                                            Uses desktop
                                        </span>

                                    <?php endif; ?>
                                </td>

                                <td class="px-2 py-4 font-medium text-slate-700">
                                    <?= (int)$item['sort_order'] ?>
                                </td>

                                <td class="px-2 py-4">
                                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold <?= $item['is_active'] ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600' ?>">
                                        <?= $item['is_active'] ? 'Active' : 'Inactive' ?>
                                    </span>
                                </td>

                                <td class="px-6 py-4">
                                    <div class="flex justify-end gap-2">

                                        <a
                                            href="<?= publicUrl('/admin/slides?edit=' . $item['id']) ?>"
                                            class="inline-flex rounded-full border border-slate-200 bg-slate-100 px-4 py-2 text-sm text-slate-900 hover:bg-slate-50">
                                            Edit
                                        </a>

                                        <form
                                            method="post"
                                            onsubmit="return confirm('Delete this slide?');">

                                            <input
                                                type="hidden"
                                                name="delete_id"
                                                value="<?= $item['id'] ?>">

                                            <button
                                                class="inline-flex rounded-full bg-rose-50 px-4 py-2 text-sm font-semibold text-rose-700 hover:bg-rose-100">
                                                Delete
                                            </button>

                                        </form>

                                    </div>
                                </td>

                            </tr>

                        <?php endforeach; ?>

                        <?php if (!$slides): ?>

                            <tr>
                                <td colspan="5"
                                    class="px-6 py-10 text-center text-slate-500">
                                    Slides not found.
                                </td>
                            </tr>

                        <?php endif; ?>

                    </tbody>

                </table>

            </div>
        </div>

    </div>

    <aside class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="text-2xl font-semibold text-slate-900"><?= $slide ? 'Edit Slide' : 'Add Slide' ?></h2> <?php if (!empty($error)): ?><div class="mt-6 rounded-3xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-700"><?= sanitize($error) ?></div><?php endif; ?> <form method="post" enctype="multipart/form-data" class="mt-6 space-y-4"> <input type="hidden" name="slide_id" value="<?= sanitize($slide['id'] ?? '') ?>"> <label class="block text-sm font-medium text-slate-700">Desktop Image URL or Path<input name="file_path" value="<?= sanitize($slide['file_path'] ?? '') ?>" placeholder="assets/images/slides/desktop.jpg or https://..." class="mt-2 w-full rounded-3xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 outline-none focus:border-slate-900" /></label> <label class="block text-sm font-medium text-slate-700">Upload Desktop Image<input type="file" name="desktop_image_file" accept="image/png,image/jpeg,image/webp" class="mt-2 w-full rounded-3xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none focus:border-slate-900" /></label> <label class="block text-sm font-medium text-slate-700">Mobile Image URL or Path<input name="mobile_file_path" value="<?= sanitize($slide['mobile_file_path'] ?? '') ?>" placeholder="assets/images/slides/mobile.jpg or https://..." class="mt-2 w-full rounded-3xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 outline-none focus:border-slate-900" /></label> <label class="block text-sm font-medium text-slate-700">Upload Mobile Image<input type="file" name="mobile_image_file" accept="image/png,image/jpeg,image/webp" class="mt-2 w-full rounded-3xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none focus:border-slate-900" /></label> <label class="block text-sm font-medium text-slate-700">Sort Order<input type="number" min="0" name="sort_order" value="<?= sanitize($slide['sort_order'] ?? '0') ?>" class="mt-2 w-full rounded-3xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 outline-none focus:border-slate-900" /></label> <?php if (!empty($slide['file_path']) || !empty($slide['mobile_file_path'])): ?> <div class="rounded-3xl border border-slate-200 bg-slate-50 p-4">
                    <p class="text-sm font-medium text-slate-700">Current images</p>
                    <div class="mt-3 grid gap-3 sm:grid-cols-2"> <?php if (!empty($slide['file_path'])): ?><img src="<?= sanitize(resolveAssetUrl($slide['file_path'])) ?>" alt="Desktop slide" class="h-28 w-full rounded-2xl object-cover"><?php endif; ?> <?php if (!empty($slide['mobile_file_path'])): ?><img src="<?= sanitize(resolveAssetUrl($slide['mobile_file_path'])) ?>" alt="Mobile slide" class="h-28 w-full rounded-2xl object-cover"><?php endif; ?> </div>
                </div> <?php endif; ?> <label class="flex items-center gap-3 text-sm font-medium text-slate-700"><input type="checkbox" name="is_active" class="h-5 w-5 rounded border-slate-300 text-brand focus:ring-brand" <?= !isset($slide['is_active']) || $slide['is_active'] ? 'checked' : '' ?> /> Active</label> <button class="inline-flex w-full items-center justify-center rounded-3xl bg-slate-900 px-6 py-3 text-sm font-semibold text-white hover:bg-slate-800">Save Slide</button> <?php if ($slide): ?> <a href="<?= publicUrl('/admin/slides') ?>" class="inline-flex w-full items-center justify-center rounded-3xl border border-slate-200 bg-white px-6 py-3 text-sm font-semibold text-slate-900 hover:bg-slate-50">Cancel Edit</a> <?php endif; ?> </form>
    </aside>

</div>
<?php require_once __DIR__ . '/_footer.php'; ?>
