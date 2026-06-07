<?php
require_once __DIR__ . '/../includes/functions.php';
if (!isAdmin()) {
    redirect('/admin/login.php');
}

ensureProductReviewsTableExists();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reviewId = (int)($_POST['review_id'] ?? 0);
    $reviewAction = $_POST['review_action'] ?? '';

    if ($reviewId > 0) {
        if (in_array($reviewAction, ['pending', 'approved', 'rejected'], true)) {
            $stmt = $pdo->prepare('UPDATE product_reviews SET status = ? WHERE id = ?');
            $stmt->execute([$reviewAction, $reviewId]);
            flash('success', 'Review status updated.');
        } elseif ($reviewAction === 'delete') {
            $stmt = $pdo->prepare('SELECT image FROM product_reviews WHERE id = ?');
            $stmt->execute([$reviewId]);
            $reviewImage = $stmt->fetchColumn();

            $stmt = $pdo->prepare('DELETE FROM product_reviews WHERE id = ?');
            $stmt->execute([$reviewId]);
            if ($reviewImage) {
                deleteLocalAssetsIfUnused(getReviewImages($reviewImage));
            }
            flash('success', 'Review deleted.');
        }
    }

    $statusRedirect = in_array($_GET['status'] ?? '', ['pending', 'approved', 'rejected'], true)
        ? '?status=' . urlencode($_GET['status'])
        : '';
    redirect('/admin/reviews.php' . $statusRedirect);
}

$activeStatus = $_GET['status'] ?? 'all';
$allowedStatuses = ['all', 'pending', 'approved', 'rejected'];
if (!in_array($activeStatus, $allowedStatuses, true)) {
    $activeStatus = 'all';
}

$reviewCounts = [
    'all' => (int)$pdo->query('SELECT COUNT(*) FROM product_reviews')->fetchColumn(),
    'pending' => (int)$pdo->query('SELECT COUNT(*) FROM product_reviews WHERE status = "pending"')->fetchColumn(),
    'approved' => (int)$pdo->query('SELECT COUNT(*) FROM product_reviews WHERE status = "approved"')->fetchColumn(),
    'rejected' => (int)$pdo->query('SELECT COUNT(*) FROM product_reviews WHERE status = "rejected"')->fetchColumn(),
];

$sql = 'SELECT r.*, p.name AS product_name, p.images AS product_image
        FROM product_reviews r
        INNER JOIN products p ON p.id = r.product_id';
$params = [];
if ($activeStatus !== 'all') {
    $sql .= ' WHERE r.status = ?';
    $params[] = $activeStatus;
}
$sql .= ' ORDER BY r.created_at DESC, r.id DESC';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$reviews = $stmt->fetchAll();

function review_status_badge_class($status) {
    if ($status === 'approved') return 'bg-emerald-50 text-emerald-700';
    if ($status === 'rejected') return 'bg-rose-50 text-rose-700';
    return 'bg-amber-50 text-amber-700';
}

require_once __DIR__ . '/_header.php';
?>
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <p class="text-sm font-bold text-emerald-600">Moderation</p>
            <h1 class="mt-2 text-3xl font-black tracking-tight text-slate-950">Product Reviews</h1>
            <p class="mt-1 text-sm text-slate-500">Approve customer reviews before they become visible on product pages.</p>
        </div>
        <div class="grid grid-cols-2 gap-2 sm:flex">
            <?php foreach ($allowedStatuses as $status): ?>
                <?php $isActive = $activeStatus === $status; ?>
                <a href="<?= publicUrl('/admin/reviews' . ($status === 'all' ? '' : '?status=' . $status)) ?>" class="rounded-2xl px-4 py-2 text-sm font-bold <?= $isActive ? 'bg-slate-950 text-white' : 'bg-white text-slate-600 shadow-sm hover:text-slate-950' ?>">
                    <?= ucfirst($status) ?> (<?= (int)$reviewCounts[$status] ?>)
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <?php if ($msg = flash('success')): ?>
        <div class="rounded-3xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-700"><?= sanitize($msg) ?></div>
    <?php endif; ?>

    <div class="grid gap-5">
        <?php foreach ($reviews as $review): ?>
            <article class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="grid gap-5 xl:grid-cols-[180px_1fr_260px]">
                    <div class="overflow-hidden rounded-2xl border border-slate-100 bg-slate-50">
                        <?php
                        $reviewImages = getReviewImages($review['image'] ?? '');
                        $previewImage = $reviewImages[0] ?? ($review['product_image'] ?? '');
                        ?>
                        <?php if ($previewImage): ?>
                            <img src="<?= sanitize(resolveAssetUrl($previewImage)) ?>" alt="<?= sanitize($review['product_name']) ?>" class="aspect-square w-full object-cover">
                            <?php if (count($reviewImages) > 1): ?>
                                <div class="grid grid-cols-4 gap-1 bg-white p-2">
                                    <?php foreach (array_slice($reviewImages, 1, 2) as $reviewImage): ?>
                                        <img src="<?= sanitize(resolveAssetUrl($reviewImage)) ?>" alt="<?= sanitize($review['product_name']) ?>" class="aspect-square w-full rounded-lg object-cover">
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="flex aspect-square w-full items-center justify-center text-slate-300">
                                <i class="fa-regular fa-image text-3xl"></i>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="rounded-full px-3 py-1 text-xs font-bold <?= review_status_badge_class($review['status']) ?>"><?= ucfirst($review['status']) ?></span>
                            <?php if (!empty($review['is_verified_purchase'])): ?>
                                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-700">Verified purchase</span>
                            <?php endif; ?>
                            <span class="text-xs font-semibold uppercase tracking-wide text-slate-400"><?= date('j M Y', strtotime($review['created_at'])) ?></span>
                        </div>

                        <h2 class="mt-3 text-xl font-black text-slate-950"><?= sanitize($review['product_name']) ?></h2>
                        <div class="mt-3 flex gap-1 text-amber-500">
                            <?php for ($star = 1; $star <= 5; $star++): ?>
                                <i class="<?= $star <= (int)$review['rating'] ? 'fa-solid' : 'fa-regular' ?> fa-star text-sm"></i>
                            <?php endfor; ?>
                        </div>
                        <p class="mt-4 text-sm leading-6 text-slate-700"><?= nl2br(sanitize($review['comment'])) ?></p>
                        <div class="mt-4 text-sm text-slate-500">
                            <span class="font-bold text-slate-800"><?= sanitize($review['name']) ?></span>
                            <?php if (!empty($review['email'])): ?>
                                <span class="mx-2 text-slate-300">|</span><?= sanitize($review['email']) ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="grid content-start gap-3">
                        <?php if ($review['status'] !== 'approved'): ?>
                            <form method="post">
                                <input type="hidden" name="review_id" value="<?= (int)$review['id'] ?>">
                                <input type="hidden" name="review_action" value="approved">
                                <button class="inline-flex w-full items-center justify-center rounded-2xl bg-emerald-600 px-4 py-3 text-sm font-bold text-white hover:bg-emerald-700">Approve</button>
                            </form>
                        <?php endif; ?>

                        <?php if ($review['status'] !== 'pending'): ?>
                            <form method="post">
                                <input type="hidden" name="review_id" value="<?= (int)$review['id'] ?>">
                                <input type="hidden" name="review_action" value="pending">
                                <button class="inline-flex w-full items-center justify-center rounded-2xl bg-amber-50 px-4 py-3 text-sm font-bold text-amber-700 hover:bg-amber-100">Move to Pending</button>
                            </form>
                        <?php endif; ?>

                        <?php if ($review['status'] !== 'rejected'): ?>
                            <form method="post">
                                <input type="hidden" name="review_id" value="<?= (int)$review['id'] ?>">
                                <input type="hidden" name="review_action" value="rejected">
                                <button class="inline-flex w-full items-center justify-center rounded-2xl bg-rose-50 px-4 py-3 text-sm font-bold text-rose-700 hover:bg-rose-100">Reject</button>
                            </form>
                        <?php endif; ?>

                        <form method="post" onsubmit="return confirm('Delete this review?');">
                            <input type="hidden" name="review_id" value="<?= (int)$review['id'] ?>">
                            <input type="hidden" name="review_action" value="delete">
                            <button class="inline-flex w-full items-center justify-center rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-500 hover:text-rose-600">Delete</button>
                        </form>
                    </div>
                </div>
            </article>
        <?php endforeach; ?>

        <?php if (!$reviews): ?>
            <div class="rounded-3xl border border-dashed border-slate-300 bg-white p-10 text-center text-slate-500">
                Reviews not found.
            </div>
        <?php endif; ?>
    </div>
</div>
<?php require_once __DIR__ . '/_footer.php'; ?>
