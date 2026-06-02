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
    flash('success', 'Cart updated successfully.');
    redirect('/cart.php');
}
$items = getCartItems();
$subtotal = calculateCartTotal();
?>
<?php include __DIR__ . '/includes/header.php'; ?>
<div class="container mt-5">
    <h2 class="h4 mb-4">Shopping Cart</h2>
    <?php if ($message = flash('success')): ?><div class="alert alert-success"><?= $message ?></div><?php endif; ?>
    <?php if ($items): ?>
        <form method="post">
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Total</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): $itemTotal = $item['price'] * $item['quantity']; ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <img src="<?= sanitize(json_decode($item['gallery'], true)[0] ?? $item['images']) ?>" width="80" class="rounded-3" alt="<?= sanitize($item['name']) ?>">
                                        <div>
                                            <a href="<?= BASE_URL ?>/product.php?id=<?= $item['id'] ?>" class="fw-semibold text-dark"><?= sanitize($item['name']) ?></a>
                                            <p class="small text-muted mb-0">Stock: <?= (int)$item['stock'] ?></p>
                                        </div>
                                    </div>
                                </td>
                                <td>₹<?= number_format($item['price'], 2) ?></td>
                                <td><input type="number" name="quantities[<?= $item['id'] ?>]" value="<?= $item['quantity'] ?>" min="1" max="<?= $item['stock'] ?>" class="form-control w-50"></td>
                                <td>₹<?= number_format($itemTotal, 2) ?></td>
                                <td>
                                    <button type="submit" name="remove" value="<?= $item['id'] ?>" class="btn btn-sm btn-outline-danger">Remove</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-between align-items-center">
                <button type="submit" class="btn btn-outline-primary">Update Cart</button>
                <div class="text-end">
                    <p class="mb-1">Subtotal: <strong>₹<?= number_format($subtotal, 2) ?></strong></p>
                    <a href="<?= BASE_URL ?>/checkout.php" class="btn btn-primary">Proceed to Checkout</a>
                </div>
            </div>
        </form>
    <?php else: ?>
        <div class="alert alert-info">Your cart is empty. <a href="<?= BASE_URL ?>/shop.php">Browse products</a> to add items.</div>
    <?php endif; ?>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
