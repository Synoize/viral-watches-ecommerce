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
    }
    if (!empty($_POST['quantities'])) {
        foreach ($_POST['quantities'] as $productId => $quantity) {
            $productId = (int)$productId;
            $quantity = max(1, min(20, (int)$quantity));
            if (isset($_SESSION['cart'][$productId])) {
                $_SESSION['cart'][$productId]['quantity'] = $quantity;
            }
        }
    }
    if (!empty($_POST['box_quantities'])) {
        foreach ($_POST['box_quantities'] as $productId => $quantity) {
            $productId = (int)$productId;
            $quantity = max(1, min(10, (int)$quantity));
            if (isset($_SESSION['cart'][$productId]) && !empty($_SESSION['cart'][$productId]['box_id'])) {
                $_SESSION['cart'][$productId]['box_quantity'] = $quantity;
            }
        }
    }
    flash('success', 'Cart updated successfully.');
    redirect('/cart.php');
}
$items = getCartItems();
$subtotal = calculateCartTotal();
?>
<?php include __DIR__ . '/includes/header.php'; ?>
<div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
    <h1 class="text-3xl font-semibold text-slate-900">Shopping Cart</h1>
    <?php if ($message = flash('success')): ?><div class="mt-6 rounded-3xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-700"><?= sanitize($message) ?></div><?php endif; ?>
    <?php if ($items): ?>
        <form method="post" class="mt-8 space-y-6">
            <div class="overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-sm">
                <div class="grid gap-px bg-slate-200 text-sm sm:grid-cols-[3fr_1fr_1fr_1fr_100px]">
                    <div class="bg-white px-6 py-4 text-slate-500">Product</div>
                    <div class="bg-white px-6 py-4 text-slate-500">Price</div>
                    <div class="bg-white px-6 py-4 text-slate-500">Quantity</div>
                    <div class="bg-white px-6 py-4 text-slate-500">Total</div>
                    <div class="bg-white px-6 py-4"></div>
                </div>
                <?php foreach ($items as $item): ?>
                    <div class="grid gap-px bg-slate-200 text-sm sm:grid-cols-[3fr_1fr_1fr_1fr_100px]">
                        <div class="bg-white px-6 py-5">
                            <div class="flex items-start gap-4">
                                <img src="<?= sanitize(resolveAssetUrl(json_decode($item['gallery'], true)[0] ?? $item['images'])) ?>" alt="<?= sanitize($item['name']) ?>" class="h-20 w-20 rounded-3xl object-cover" />
                                <div>
                                    <a href="<?= BASE_URL ?>/product.php?id=<?= $item['id'] ?>" class="font-semibold text-slate-900 hover:text-brand"><?= sanitize($item['name']) ?></a>
                                    <p class="mt-2 text-sm text-slate-500">Stock: <?= (int)$item['stock'] ?></p>
                                    <?php if (!empty($item['box_id'])): ?>
                                        <div class="mt-3 flex items-center gap-3 rounded-2xl bg-slate-50 p-3">
                                            <img src="<?= sanitize(resolveAssetUrl($item['box_image'] ?: 'assets/images/cartier-box.svg')) ?>" alt="<?= sanitize($item['box_name']) ?>" class="h-12 w-12 object-contain">
                                            <div>
                                                <p class="font-medium text-slate-900"><?= sanitize($item['box_name']) ?></p>
                                                <p class="text-xs text-slate-500">Box: &#8377;<?= number_format($item['box_price'], 2) ?> x <?= (int)$item['box_quantity'] ?></p>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <div class="bg-white px-6 py-5 text-slate-900">&#8377;<?= number_format($item['price'], 2) ?></div>
                        <div class="bg-white px-6 py-5">
                            <input type="number" name="quantities[<?= $item['id'] ?>]" value="<?= $item['quantity'] ?>" min="1" max="<?= $item['stock'] ?>" class="w-full rounded-3xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 outline-none focus:border-slate-900" />
                            <?php if (!empty($item['box_id'])): ?>
                                <label class="mt-3 block text-xs font-medium text-slate-500">Box quantity</label>
                                <input type="number" name="box_quantities[<?= $item['id'] ?>]" value="<?= $item['box_quantity'] ?>" min="1" max="10" class="mt-1 w-full rounded-3xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 outline-none focus:border-slate-900" />
                            <?php endif; ?>
                        </div>
                        <div class="bg-white px-6 py-5 text-slate-900">&#8377;<?= number_format($item['line_total'], 2) ?></div>
                        <div class="bg-white px-6 py-5">
                            <button type="submit" name="remove" value="<?= $item['id'] ?>" class="inline-flex items-center justify-center rounded-3xl bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-700 hover:bg-rose-100">Remove</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <button type="submit" class="inline-flex items-center justify-center rounded-3xl border border-slate-200 bg-white px-6 py-3 text-sm font-semibold text-slate-900 hover:bg-slate-50">Update Cart</button>
                <div class="rounded-[2rem] border border-slate-200 bg-slate-50 p-6 text-right">
                    <p class="text-sm text-slate-600">Subtotal</p>
                    <p class="mt-2 text-3xl font-semibold text-slate-900">&#8377;<?= number_format($subtotal, 2) ?></p>
                    <a href="<?= BASE_URL ?>/checkout.php" class="mt-4 inline-flex w-full items-center justify-center rounded-3xl bg-slate-900 px-6 py-3 text-sm font-semibold text-white hover:bg-slate-800 lg:w-auto">Proceed to Checkout</a>
                </div>
            </div>
        </form>
    <?php else: ?>
        <div class="mt-8 rounded-[2rem] border border-slate-200 bg-white p-8 text-center text-slate-600 shadow-sm">Your cart is empty. <a href="<?= BASE_URL ?>/collection" class="font-semibold text-brand hover:text-brand/80">Browse products</a> to add items.</div>
    <?php endif; ?>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
