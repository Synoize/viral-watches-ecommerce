<?php
require_once __DIR__ . '/includes/functions.php';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$stmt = $pdo->prepare('SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON p.category = c.id WHERE p.id = ?');
$stmt->execute([$id]);
$product = $stmt->fetch();
if (!$product) {
    $pageMetaOverrides = [
        'title' => 'Products not found | ShopMaster',
        'description' => 'The requested product is not available.',
    ];
    include __DIR__ . '/includes/header.php';
    ?>
    <div class="mx-auto max-w-7xl px-4 py-16 text-center sm:px-6 lg:px-8">
        <div class="rounded-[2rem] border border-slate-200 bg-white p-10 shadow-sm">
            <h1 class="text-3xl font-semibold text-slate-900">Products not found.</h1>
            <p class="mt-3 text-slate-600">This product is not available right now.</p>
            <a href="<?= BASE_URL ?>/collection" class="mt-6 inline-flex items-center justify-center rounded-3xl bg-slate-900 px-6 py-3 text-sm font-semibold text-white hover:bg-slate-800">Browse Products</a>
        </div>
    </div>
    <?php
    include __DIR__ . '/includes/footer.php';
    exit;
}

$gallery = json_decode($product['gallery'], true) ?: [];
$boxOptions = getActiveBoxOptions();
$defaultBox = $boxOptions[0] ?? null;
$productMaxQuantity = max(1, min(10, (int)$product['stock']));

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['quantity'])) {
    if ((int)$product['stock'] <= 0) {
        flash('error', 'This product is currently out of stock.');
        redirect('/product.php?id=' . $id);
    }

    $quantity = max(1, min($productMaxQuantity, (int)$_POST['quantity']));
    $buyWithBox = ($_POST['buy_with_box'] ?? '0') === '1';
    $boxQuantity = max(1, min(10, (int)($_POST['box_quantity'] ?? 1)));
    $boxId = null;

    if ($buyWithBox) {
        foreach ($boxOptions as $boxOption) {
            if ((int)$boxOption['id'] === (int)($_POST['box_option_id'] ?? 0)) {
                $boxId = (int)$boxOption['id'];
                break;
            }
        }

        if (!$boxId && $defaultBox) {
            $boxId = (int)$defaultBox['id'];
        }
    }

    if (!isset($_SESSION['cart'][$id])) {
        $_SESSION['cart'][$id] = ['quantity' => 0];
    }

    $_SESSION['cart'][$id]['quantity'] += $quantity;
    $_SESSION['cart'][$id]['quantity'] = min($productMaxQuantity, $_SESSION['cart'][$id]['quantity']);
    $_SESSION['cart'][$id]['box_id'] = $boxId;
    $_SESSION['cart'][$id]['box_quantity'] = $boxId ? $boxQuantity : 0;

    $action = $_POST['action'] ?? 'add_cart';
    if ($action === 'buy_now') {
        redirect('/checkout.php');
    }

    flash('success', 'Product added to cart.');
    redirect('/cart.php');
}

$initialBox = $defaultBox ?: ['id' => '', 'name' => 'No box selected', 'image' => '', 'price' => 0];
$boxOptionsForJs = array_map(function ($box) {
    return [
        'id' => (int)$box['id'],
        'name' => $box['name'],
        'image' => resolveAssetUrl($box['image'] ?: 'assets/images/cartier-box.svg'),
        'price' => (float)$box['price'],
    ];
}, $boxOptions);
$pageMetaOverrides = [
    'tokens' => [
        'product_name' => $product['name'],
        'product_price' => 'Rs. ' . number_format($product['price'], 2),
        'category_name' => $product['category_name'] ?? 'General',
    ],
];
?>
<?php include __DIR__ . '/includes/header.php'; ?>
<div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
    <?php if ($message = flash('success')): ?><div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-700"><?= sanitize($message) ?></div><?php endif; ?>
    <?php if ($message = flash('error')): ?><div class="rounded-2xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-700"><?= sanitize($message) ?></div><?php endif; ?>

    <div class="mt-8 grid gap-10 lg:grid-cols-[1fr_460px] lg:items-start">
        <div class="space-y-6">
            <div class="overflow-hidden rounded-2xl bg-white shadow-sm">
                <img src="<?= sanitize(resolveAssetUrl($gallery[0] ?? $product['images'])) ?>" alt="<?= sanitize($product['name']) ?>" class="product-main-image h-[520px] w-full object-cover" />
            </div>
            <?php if ($gallery): ?>
                <div class="grid grid-cols-4 gap-3">
                    <?php foreach ($gallery as $src): ?>
                        <button type="button" class="product-thumb overflow-hidden rounded-xl border border-slate-200 bg-slate-50 p-1" data-src="<?= sanitize(resolveAssetUrl($src)) ?>">
                            <img src="<?= sanitize(resolveAssetUrl($src)) ?>" alt="<?= sanitize($product['name']) ?>" class="h-24 w-full object-cover" />
                        </button>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="space-y-6">
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-sm text-slate-500">Category: <?= sanitize($product['category_name'] ?? 'General') ?></p>
                <h1 class="mt-3 text-4xl font-semibold text-slate-900"><?= sanitize($product['name']) ?></h1>
                <p class="mt-4 text-3xl font-semibold text-slate-900">&#8377;<?= number_format($product['price'], 2) ?></p>
                <p class="mt-4 leading-7 text-slate-600"><?= nl2br(sanitize($product['description'])) ?></p>
                <p class="mt-6 text-sm font-medium text-slate-700">Status: <span class="font-semibold text-slate-900"><?= $product['stock'] > 0 ? 'In stock' : 'Out of stock' ?></span></p>
            </div>

            <form method="post" id="product-purchase-form" class="space-y-7" data-product-price="<?= (float)$product['price'] ?>">
                <section class="space-y-4">
                    <h2 class="text-3xl font-semibold text-slate-900">Buy with Box <span class="text-rose-500">*</span></h2>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <label class="flex h-14 items-center gap-3 rounded-2xl border border-slate-200 bg-white px-5 text-base text-slate-900">
                            <input type="radio" name="buy_with_box" value="1" class="h-4 w-4 accent-red-500" <?= $boxOptions ? '' : 'disabled' ?>>
                            Yes
                        </label>
                        <label class="flex h-14 items-center gap-3 rounded-2xl border border-slate-200 bg-white px-5 text-base text-slate-900">
                            <input type="radio" name="buy_with_box" value="0" class="h-4 w-4 accent-red-500" checked>
                            No
                        </label>
                    </div>
                </section>

                <section id="box-panel" class="hidden rounded-2xl border border-slate-200 bg-white p-5 sm:p-6">
                    <div class="grid gap-6 md:grid-cols-[250px_1fr]">
                        <div>
                            <label for="box-option-select" class="text-base font-medium text-slate-600">Choose box</label>
                            <select id="box-option-select" name="box_option_id" class="mt-3 h-14 w-full rounded-xl border border-slate-200 bg-white px-5 text-base text-slate-700 outline-none focus:border-slate-900" disabled>
                                <?php foreach ($boxOptions as $box): ?>
                                    <option value="<?= (int)$box['id'] ?>"><?= sanitize($box['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="grid gap-6 sm:grid-cols-[150px_1fr]">
                            <div class="flex aspect-square items-center justify-center border border-slate-200 bg-white p-3">
                                <img id="box-preview-image" src="<?= sanitize(resolveAssetUrl($initialBox['image'] ?: 'assets/images/cartier-box.svg')) ?>" alt="<?= sanitize($initialBox['name']) ?>" class="h-full w-full object-contain">
                            </div>

                            <div class="space-y-5">
                                <div>
                                    <h3 id="box-preview-name" class="text-2xl font-semibold text-slate-900"><?= sanitize($initialBox['name']) ?></h3>
                                    <p id="box-preview-price" class="mt-3 text-xl font-medium text-slate-900">&#8377;<?= number_format($initialBox['price'], 2) ?></p>
                                </div>

                                <div class="grid gap-5 sm:grid-cols-[1fr_144px] sm:items-end">
                                    <div>
                                        <p class="text-sm uppercase tracking-wide text-slate-500">Amount</p>
                                        <p id="box-amount" class="mt-2 text-xl font-semibold text-slate-900">&#8377;<?= number_format($initialBox['price'], 2) ?></p>
                                    </div>
                                    <div>
                                        <p class="mb-3 text-center text-sm uppercase tracking-wide text-slate-500">Box Quantity</p>
                                        <div class="grid h-12 grid-cols-3 overflow-hidden rounded-xl border border-slate-200 bg-white">
                                            <button type="button" class="box-step text-xl text-slate-500 hover:bg-slate-50" data-target="box_quantity" data-step="-1">-</button>
                                            <input id="box-quantity" name="box_quantity" value="1" min="1" max="10" class="w-full border-x border-slate-200 text-center text-lg outline-none" readonly disabled>
                                            <button type="button" class="box-step text-xl text-slate-500 hover:bg-slate-50" data-target="box_quantity" data-step="1">+</button>
                                        </div>
                                        <p class="mt-2 text-right text-sm text-slate-500">Max: 10</p>
                                    </div>
                                </div>

                                <div class="border-t border-slate-200 pt-4">
                                    <div class="flex items-center justify-between gap-4">
                                        <span class="text-lg text-slate-600">Final total</span>
                                        <span id="final-total" class="text-2xl font-semibold text-slate-900">&#8377;<?= number_format($product['price'], 2) ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="grid gap-5 sm:grid-cols-[170px_1fr] sm:items-end">
                    <div>
                        <label class="text-lg font-medium text-slate-700">Quantity</label>
                        <div class="mt-3 grid h-14 grid-cols-3 overflow-hidden rounded-xl border border-slate-200 bg-white">
                            <button type="button" class="quantity-step text-2xl text-slate-500 hover:bg-slate-50" data-target="quantity" data-step="-1">-</button>
                            <input id="product-quantity" type="number" name="quantity" value="1" min="1" max="<?= $productMaxQuantity ?>" class="w-full border-x border-slate-200 text-center text-lg outline-none" readonly>
                            <button type="button" class="quantity-step text-2xl text-slate-500 hover:bg-slate-50" data-target="quantity" data-step="1">+</button>
                        </div>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <button type="submit" name="action" value="add_cart" class="inline-flex h-14 items-center justify-center bg-slate-100 px-6 text-sm font-semibold uppercase text-slate-900 hover:bg-slate-200" <?= $product['stock'] ? '' : 'disabled' ?>>Add to Cart</button>
                        <button type="submit" name="action" value="buy_now" class="inline-flex h-14 items-center justify-center bg-sky-700 px-6 text-sm font-semibold uppercase text-white hover:bg-sky-800" <?= $product['stock'] ? '' : 'disabled' ?>>Buy Now</button>
                    </div>
                </section>

                <p class="text-sm uppercase tracking-[0.35em] text-sky-700">Shipping days 4 to 7 days</p>
            </form>
        </div>
    </div>

    <section class="mt-12 rounded-2xl border border-slate-200 bg-white p-8 shadow-sm">
        <h2 class="text-2xl font-semibold text-slate-900">Related Products</h2>
        <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <?php
            $relStmt = $pdo->prepare('SELECT id, name, price, images, gallery FROM products WHERE category = ? AND id != ? LIMIT 4');
            $relStmt->execute([$product['category'], $product['id']]);
            $related = $relStmt->fetchAll();
            if ($related): foreach ($related as $item): $itemGallery = json_decode($item['gallery'] ?? '[]', true) ?: []; ?>
                <a href="<?= BASE_URL ?>/product.php?id=<?= $item['id'] ?>" class="group overflow-hidden rounded-xl border border-slate-200 bg-slate-50 transition hover:-translate-y-0.5 hover:shadow-sm">
                    <img src="<?= sanitize(resolveAssetUrl($itemGallery[0] ?? $item['images'])) ?>" alt="<?= sanitize($item['name']) ?>" class="h-40 w-full object-cover" />
                    <div class="p-4">
                        <h3 class="text-base font-semibold text-slate-900"><?= sanitize($item['name']) ?></h3>
                        <p class="mt-2 text-sm text-slate-700">&#8377;<?= number_format($item['price'], 2) ?></p>
                    </div>
                </a>
            <?php endforeach; else: ?>
                <div class="col-span-full rounded-[1.75rem] border border-slate-200 bg-slate-50 p-6 text-center text-slate-600">Related products not found.</div>
            <?php endif; ?>
        </div>
    </section>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('product-purchase-form');
    if (!form) return;

    const boxes = <?= json_encode($boxOptionsForJs, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
    const productPrice = Number(form.dataset.productPrice || 0);
    const productQty = document.getElementById('product-quantity');
    const boxQty = document.getElementById('box-quantity');
    const select = document.getElementById('box-option-select');
    const panel = document.getElementById('box-panel');
    const image = document.getElementById('box-preview-image');
    const name = document.getElementById('box-preview-name');
    const price = document.getElementById('box-preview-price');
    const amount = document.getElementById('box-amount');
    const total = document.getElementById('final-total');
    const radios = form.querySelectorAll('input[name="buy_with_box"]');
    const money = new Intl.NumberFormat('en-IN', { style: 'currency', currency: 'INR' });

    function selectedBox() {
        return boxes.find((box) => String(box.id) === String(select?.value)) || boxes[0] || { name: 'No box selected', image: '', price: 0 };
    }

    function boxEnabled() {
        const checked = form.querySelector('input[name="buy_with_box"]:checked');
        return checked && checked.value === '1' && boxes.length > 0;
    }

    function renderTotals() {
        const box = selectedBox();
        const productQuantity = Number(productQty.value || 1);
        const boxQuantity = Number(boxQty.value || 1);
        const hasBox = boxEnabled();
        const boxAmount = hasBox ? Number(box.price) * boxQuantity : 0;

        panel.classList.toggle('hidden', !hasBox);
        if (select) select.disabled = !hasBox;
        boxQty.disabled = !hasBox;

        image.src = box.image || '<?= BASE_URL ?>/assets/images/cartier-box.svg';
        image.alt = box.name;
        name.textContent = hasBox ? box.name : 'No box selected';
        price.textContent = hasBox ? money.format(Number(box.price)) : money.format(0);
        amount.textContent = money.format(boxAmount);
        total.textContent = money.format((productPrice * productQuantity) + boxAmount);
    }

    form.querySelectorAll('.quantity-step, .box-step').forEach((button) => {
        button.addEventListener('click', function () {
            const input = this.dataset.target === 'box_quantity' ? boxQty : productQty;
            const step = Number(this.dataset.step || 0);
            const min = Number(input.min || 1);
            const max = Number(input.max || 10);
            input.value = Math.max(min, Math.min(max, Number(input.value || min) + step));
            renderTotals();
        });
    });

    radios.forEach((radio) => radio.addEventListener('change', renderTotals));
    select?.addEventListener('change', renderTotals);
    renderTotals();
});
</script>
<?php include __DIR__ . '/includes/footer.php'; ?>
