<?php
require_once __DIR__ . '/includes/functions.php';
$featuredCategories = getCategories();
$stmt = $pdo->query('SELECT * FROM products WHERE stock > 0 ORDER BY id DESC LIMIT 8');
$trending = $stmt->fetchAll();
?>
<?php include __DIR__ . '/includes/header.php'; ?>
<div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
    <section class="grid gap-10 lg:grid-cols-[1.2fr,0.8fr] lg:items-center">
        <div class="space-y-6">
            <p class="text-sm font-semibold uppercase tracking-[0.24em] text-brand">New arrivals</p>
            <h1 class="text-5xl font-semibold tracking-tight text-slate-900">Shop the latest styles with ShopMaster.</h1>
            <p class="max-w-xl text-lg leading-8 text-slate-600">Premium products, fast shipping, and a secure checkout experience for modern online shopping.</p>
            <div class="flex flex-wrap gap-3">
                <a href="<?= BASE_URL ?>/shop.php" class="inline-flex items-center justify-center rounded-3xl bg-slate-900 px-6 py-3 text-sm font-semibold text-white hover:bg-slate-800">Shop Now</a>
                <a href="<?= BASE_URL ?>/contact.php" class="inline-flex items-center justify-center rounded-3xl border border-slate-300 bg-white px-6 py-3 text-sm font-semibold text-slate-900 hover:bg-slate-50">Contact Support</a>
            </div>
        </div>
        <div class="relative overflow-hidden rounded-[2rem] bg-slate-900 p-8 text-white shadow-xl">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top,_rgba(255,255,255,0.18),_transparent_45%)]"></div>
            <div class="relative h-[420px] overflow-hidden rounded-[2rem]">
                <img src="https://images.unsplash.com/photo-1523275335684-37898b6baf30?auto=format&fit=crop&w=1400&q=80" alt="Hero" class="h-full w-full object-cover" />
            </div>
        </div>
    </section>
    <section class="mt-16">
        <div class="flex items-center justify-between gap-4">
            <h2 class="text-2xl font-semibold text-slate-900">Featured Categories</h2>
            <a href="<?= BASE_URL ?>/shop.php" class="text-sm font-medium text-brand hover:text-slate-900">View all</a>
        </div>
        <div class="mt-6 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
            <?php foreach ($featuredCategories as $category): ?>
                <a href="<?= BASE_URL ?>/shop.php?category=<?= $category['id'] ?>" class="group overflow-hidden rounded-[1.75rem] border border-slate-200 bg-white p-6 text-center transition hover:-translate-y-1 hover:shadow-lg">
                    <div class="mx-auto mb-4 flex h-20 w-20 items-center justify-center rounded-full bg-slate-100 text-brand transition group-hover:bg-brand group-hover:text-white">
                        <i class="fas fa-tag text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-slate-900"><?= sanitize($category['name']) ?></h3>
                </a>
            <?php endforeach; ?>
        </div>
    </section>
    <section class="mt-16">
        <div class="flex items-center justify-between gap-4">
            <h2 class="text-2xl font-semibold text-slate-900">Trending Products</h2>
            <a href="<?= BASE_URL ?>/shop.php" class="text-sm font-medium text-brand hover:text-slate-900">See all products</a>
        </div>
        <div class="mt-6 grid gap-6 md:grid-cols-2 xl:grid-cols-4">
            <?php foreach ($trending as $product): ?>
                <?php $gallery = json_decode($product['gallery'], true) ?: []; ?>
                <article class="overflow-hidden rounded-[1.75rem] border border-slate-200 bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-md">
                    <img src="<?= sanitize($gallery[0] ?? $product['images']) ?>" alt="<?= sanitize($product['name']) ?>" class="h-52 w-full object-cover" />
                    <div class="space-y-3 p-5">
                        <div class="space-y-1">
                            <h3 class="text-lg font-semibold text-slate-900"><?= sanitize($product['name']) ?></h3>
                            <p class="text-brand text-lg font-semibold">₹<?= number_format($product['price'], 2) ?></p>
                        </div>
                        <p class="text-sm leading-6 text-slate-500"><?= substr(sanitize($product['description']), 0, 80) ?>...</p>
                        <div class="flex gap-3">
                            <a href="<?= BASE_URL ?>/product.php?id=<?= $product['id'] ?>" class="inline-flex flex-1 items-center justify-center rounded-3xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-900 hover:bg-slate-50">View</a>
                            <a href="<?= BASE_URL ?>/cart.php?action=add&id=<?= $product['id'] ?>" class="inline-flex flex-1 items-center justify-center rounded-3xl bg-slate-900 px-4 py-3 text-sm font-semibold text-white hover:bg-slate-800">Add</a>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </section>
    <section class="mt-16 rounded-[2rem] bg-white p-8 shadow-sm lg:p-10">
        <div class="grid gap-8 lg:grid-cols-2 lg:items-center">
            <div>
                <h2 class="text-3xl font-semibold text-slate-900">Why Shop With Us?</h2>
                <p class="mt-4 text-slate-600">High quality products, secure payments, and fast delivery with responsive support.</p>
                <div class="mt-8 grid gap-4 sm:grid-cols-2">
                    <div class="rounded-3xl bg-slate-50 p-6 text-slate-700">
                        <h3 class="font-semibold">Fast shipping</h3>
                        <p class="mt-2 text-sm text-slate-500">Reliable delivery across India.</p>
                    </div>
                    <div class="rounded-3xl bg-slate-50 p-6 text-slate-700">
                        <h3 class="font-semibold">Secure checkout</h3>
                        <p class="mt-2 text-sm text-slate-500">PCI-compliant payment flow.</p>
                    </div>
                    <div class="rounded-3xl bg-slate-50 p-6 text-slate-700">
                        <h3 class="font-semibold">Easy returns</h3>
                        <p class="mt-2 text-sm text-slate-500">Hassle-free order support.</p>
                    </div>
                    <div class="rounded-3xl bg-slate-50 p-6 text-slate-700">
                        <h3 class="font-semibold">24/7 support</h3>
                        <p class="mt-2 text-sm text-slate-500">Friendly customer service.</p>
                    </div>
                </div>
            </div>
            <div class="aspect-[16/9] overflow-hidden rounded-[2rem] bg-slate-900">
                <iframe class="h-full w-full" src="https://www.youtube.com/embed/dQw4w9WgXcQ" title="Company Video" allowfullscreen></iframe>
            </div>
        </div>
    </section>
    <section class="mt-16">
        <h2 class="text-2xl font-semibold text-slate-900">FAQ</h2>
        <div class="mt-6 space-y-4">
            <details class="rounded-[1.75rem] border border-slate-200 bg-white p-5">
                <summary class="cursor-pointer text-lg font-medium text-slate-900">How do I track my order?</summary>
                <p class="mt-4 text-slate-600">Your order details and status are available in your account dashboard under Orders.</p>
            </details>
            <details class="rounded-[1.75rem] border border-slate-200 bg-white p-5">
                <summary class="cursor-pointer text-lg font-medium text-slate-900">Can I return products?</summary>
                <p class="mt-4 text-slate-600">Yes, returns are accepted within the policy period. Contact support through the Help page.</p>
            </details>
            <details class="rounded-[1.75rem] border border-slate-200 bg-white p-5">
                <summary class="cursor-pointer text-lg font-medium text-slate-900">What payment options are available?</summary>
                <p class="mt-4 text-slate-600">We support Razorpay and Cash on Delivery with ₹50 advance for COD orders.</p>
            </details>
        </div>
    </section>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
