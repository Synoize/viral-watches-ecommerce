<?php
require_once __DIR__ . '/includes/functions.php';

if (!empty($_GET['action']) && $_GET['action'] === 'add' && !empty($_GET['id'])) {
    $id = (int)$_GET['id'];
    $quantity = 1;
    if (!isset($_SESSION['cart'][$id])) {
        $_SESSION['cart'][$id] = ['quantity' => 0];
    }
    $_SESSION['cart'][$id]['quantity'] += $quantity;
    flash('success', 'Product added to cart.');
    redirect('/cart.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_POST['remove'])) {
        $removeId = (int)$_POST['remove'];
        unset($_SESSION['cart'][$removeId]);
        flash('success', 'Product removed from cart.');
        redirect('/cart.php');
    }

    if (!empty($_POST['cart_action']) && !empty($_POST['product_id'])) {
        $productId = (int)$_POST['product_id'];
        $action = $_POST['cart_action'];

        if (isset($_SESSION['cart'][$productId])) {
            $currentQuantity = max(1, (int)($_SESSION['cart'][$productId]['quantity'] ?? 1));
            $maxQuantity = 20;
            $stmt = $pdo->prepare('SELECT stock FROM products WHERE id = ?');
            $stmt->execute([$productId]);
            $stock = (int)$stmt->fetchColumn();
            if ($stock > 0) {
                $maxQuantity = min($maxQuantity, $stock);
            }

            if ($action === 'increase') {
                $_SESSION['cart'][$productId]['quantity'] = min($maxQuantity, $currentQuantity + 1);
            } elseif ($action === 'decrease') {
                $_SESSION['cart'][$productId]['quantity'] = max(1, $currentQuantity - 1);
            }
        }

        redirect('/cart.php');
    }

    redirect('/cart.php');
}

$items = getCartItems();
$subtotal = calculateCartTotal();
$regularProductSubtotal = 0;
$productSubtotal = 0;
$discountAmount = 0;
$boxSubtotal = 0;
$totalQuantity = 0;

foreach ($items as $item) {
    $regularProductSubtotal += (float)$item['price'] * (int)$item['quantity'];
    $productSubtotal += (float)$item['effective_price'] * (int)$item['quantity'];
    $boxSubtotal += (float)$item['box_total'];
    $totalQuantity += (int)$item['quantity'];
}
$discountAmount = max(0, $regularProductSubtotal - $productSubtotal);
?>
<?php include __DIR__ . '/includes/header.php'; ?>
<div class="mx-auto max-w-[1920px] px-4 py-10 md:px-10">
    <div>
        <h2 class="text-[32px] md:text-[42px] font-serif text-black">
            Shopping Cart
        </h2>
        <?php if ($items): ?>
            <p class="mt-2 text-sm text-slate-500"><?= count($items) ?> item<?= count($items) === 1 ? '' : 's' ?> in your bag</p>
        <?php endif; ?>
    </div>

    <?php if ($items): ?>
        <div class="mt-8 grid gap-8 lg:grid-cols-[minmax(0,1fr)_420px]">
            <div class="space-y-4">
                <?php foreach ($items as $item): ?>
                    <?php
                    $gallery = json_decode($item['gallery'] ?? '[]', true) ?: [];
                    $image = resolveAssetUrl($item['images'] ?? $gallery[0]);
                    $itemProductTotal = (float)$item['effective_price'] * (int)$item['quantity'];
                    $maxQuantity = min(20, max(1, (int)$item['stock']));
                    ?>
                    <article class="grid gap-5 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm grid-cols-[60px_1fr] sm:grid-cols-[140px_1fr] sm:p-5">
                        <a href="<?= publicUrl('/product?id=' . (int)$item['id']) ?>" class="flex aspect-square items-center justify-center overflow-hidden rounded-xl bg-white">
                            <?php if ($image): ?>
                                <img src="<?= sanitize($image) ?>" alt="<?= sanitize($item['name']) ?>" class="h-full w-full object-contain" />
                            <?php else: ?>
                                <span class="px-4 text-center text-sm text-slate-400">Image not found</span>
                            <?php endif; ?>
                        </a>

                        <div class="grid gap-5 md:grid-cols-[minmax(0,1fr)_180px] relative">
                            <div class="min-w-0">
                                <div class="flex items-center">
                                    <a href="<?= publicUrl('/product?id=' . (int)$item['id']) ?>" class="text-lg font-semibold leading-snug text-slate-950 hover:text-brand">
                                        <?= sanitize($item['name']) ?>
                                    </a>
                                    <?php if (!empty($item['has_offer'])): ?>
                                        <span class="ml-2 rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">Offer</span>
                                    <?php endif; ?>
                                </div>
                                <p class="mt-2 text-sm text-slate-500">
                                    Unit price:
                                    <span class="font-medium text-slate-800">&#8377;<?= number_format((float)$item['effective_price'], 2) ?></span>
                                    <?php if (!empty($item['has_offer'])): ?>
                                        <span class="ml-1 text-xs text-slate-400 line-through">&#8377;<?= number_format((float)$item['price'], 2) ?></span>
                                    <?php endif; ?>
                                </p>

                                <?php if (!empty($item['box_id'])): ?>
                                    <div class="mt-4 flex items-center gap-3 rounded-xl border border-slate-100 bg-slate-50 p-2">
                                        <img src="<?= sanitize(resolveAssetUrl($item['box_image'] ?: 'assets/images/cartier-box.svg')) ?>" alt="<?= sanitize($item['box_name']) ?>" class="h-14 w-14 rounded-lg bg-white object-contain">
                                        <div class="w-full space-y-2">
                                            <p class="truncate text-sm font-semibold text-slate-900"><?= sanitize($item['box_name']) ?></p>
                                            <div class="w-full flex gap-2 justify-between text-slate-500">
                                                <p class="text-[11px] sm:text-xs text-nowrap">&#8377;<?= number_format((float)$item['box_price'], 2) ?> x <?= (int)$item['box_quantity'] ?></p>
                                                <p class="text-xs sm:text-sm mr-0 sm:mr-2">Total: &#8377;<?= number_format((float)$item['box_total'], 2) ?></p>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="flex flex-row items-end justify-between gap-4 sm:flex-col sm:items-end">

                                <!-- REMOVE BUTTON -->
                                <form method="post" class="absolute top-0 right-0 sm:relative sm:top-auto sm:right-auto">
                                    <button
                                        type="submit"
                                        name="remove"
                                        value="<?= (int)$item['id'] ?>"
                                        class="text-rose-500 transition-all duration-200 hover:scale-105">

                                        <i data-lucide="x" class="h-6 w-6 stroke-[1.5]"></i>

                                    </button>
                                </form>

                                <!-- QUANTITY STEPPER -->
                                <div
                                    class="flex h-12 items-center overflow-hidden rounded-xl border border-slate-200 bg-white">

                                    <form method="post">
                                        <input type="hidden" name="product_id" value="<?= (int)$item['id'] ?>">

                                        <button
                                            type="submit"
                                            name="cart_action"
                                            value="decrease"
                                            class="flex h-12 w-12 items-center justify-center text-slate-700 transition hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-40"
                                            <?= (int)$item['quantity'] <= 1 ? 'disabled' : '' ?>>

                                            <i data-lucide="minus" class="h-4 w-4"></i>

                                        </button>
                                    </form>

                                    <span
                                        class="flex h-12 min-w-[52px] items-center justify-center border-x border-slate-200 bg-slate-50 px-4 text-base font-semibold text-slate-900">

                                        <?= (int)$item['quantity'] ?>

                                    </span>

                                    <form method="post">
                                        <input type="hidden" name="product_id" value="<?= (int)$item['id'] ?>">

                                        <button
                                            type="submit"
                                            name="cart_action"
                                            value="increase"
                                            class="flex h-12 w-12 items-center justify-center text-slate-700 transition hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-40"
                                            <?= (int)$item['quantity'] >= $maxQuantity ? 'disabled' : '' ?>>

                                            <i data-lucide="plus" class="h-4 w-4"></i>

                                        </button>
                                    </form>

                                </div>

                                <!-- PRICE -->
                                <div class="text-right">

                                    <p class="text-[10px] font-medium uppercase tracking-[0.2em] text-slate-400">
                                        Total
                                    </p>

                                    <div class="mt-1 flex items-center justify-end gap-1 text-xl tracking-tight text-slate-900">

                                        ₹<?= number_format((float)$item['line_total'], 2) ?>

                                    </div>

                                </div>

                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>

            <aside class="h-fit rounded-2xl border border-slate-200 bg-white p-6 shadow-sm lg:sticky lg:top-28">
                <h3 class="text-2xl font-semibold text-slate-950">Order Summary</h3>
                <div class="mt-6 space-y-4">
                    <div class="flex items-center justify-between text-sm text-slate-600">
                        <span>Items</span>
                        <span><?= (int)$totalQuantity ?></span>
                    </div>
                    <div class="flex items-center justify-between text-sm text-slate-600">
                        <span>Product MRP</span>
                        <span>&#8377;<?= number_format($regularProductSubtotal, 2) ?></span>
                    </div>
                    <div class="flex items-center justify-between text-sm <?= $discountAmount > 0 ? 'text-emerald-600' : 'text-slate-600' ?>">
                        <span>Discount</span>
                        <span><?= $discountAmount > 0 ? '-' : '' ?>&#8377;<?= number_format($discountAmount, 2) ?></span>
                    </div>
                    <div class="flex items-center justify-between text-sm text-slate-600">
                        <span>Subtotal</span>
                        <span>&#8377;<?= number_format($productSubtotal, 2) ?></span>
                    </div>
                    <div class="flex items-center justify-between text-sm text-slate-600">
                        <span>Box charges</span>
                        <span>&#8377;<?= number_format($boxSubtotal, 2) ?></span>
                    </div>
                    <div class="border-t border-slate-200 pt-4 flex items-center justify-between text-lg font-semibold text-slate-950">
                        <span>Total</span>
                        <span>&#8377;<?= number_format($subtotal, 2) ?></span>
                    </div>
                </div>
                <a href="<?= publicUrl('/checkout') ?>" class="mt-6 inline-flex w-full items-center justify-center rounded-xl bg-slate-900 px-6 py-4 text-sm font-semibold text-white transition hover:bg-slate-800">Proceed to Checkout</a>
            </aside>
        </div>
    <?php else: ?>
        <div class="col-span-full flex h-[68vh] flex-col items-center justify-center text-center md:h-[56vh]">

            <i class="fa-solid fa-shopping-cart text-5xl text-slate-300"></i>

            <h3 class="mt-6 text-2xl font-serif text-[#222]">
                Your cart is empty.
            </h3>

            <p class="mt-3 text-slate-500">
                Save products you want to buy later.
            </p>

            <a href="<?= BASE_URL ?>/collection"
                class="mt-8 inline-block border border-black px-8 py-3 text-sm uppercase tracking-widest transition hover:bg-black hover:text-white">
                Browse products
            </a>

        </div>
    <?php endif; ?>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>