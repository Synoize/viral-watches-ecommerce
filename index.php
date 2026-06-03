<?php
require_once __DIR__ . '/includes/functions.php';
$featuredCategories = getCategories();
$stmt = $pdo->query('SELECT * FROM products WHERE stock > 0 ORDER BY id DESC LIMIT 8');
$trending = $stmt->fetchAll();
?>
<?php include __DIR__ . '/includes/header.php'; ?>

<!-- HERO SECTION -->
<section class="relative w-full overflow-hidden">
    <!-- SLIDER -->
    <div class="swiper heroSlider">
        <div class="swiper-wrapper">
            <!-- SLIDE 1 -->
            <div class="swiper-slide relative">
                <!-- BACKGROUND IMAGE -->

                <picture>
                    <!-- Mobile -->
                    <source media="(max-width: 767px)"
                        srcset="https://i.ibb.co/WpqS6MVp/Chat-GPT-Image-Jun-1-2026-03-47-29-PM.png" />

                    <!-- Desktop -->
                    <img src="https://i.ibb.co/fVV6MSGV/Untitled-design-36.png" alt="Banner"
                        class="w-full h-[650px] md:h-[750px] lg:h-[550px] object-cover" />
                </picture>
                <!-- OVERLAY -->
                <div class="absolute inset-0 bg-black/25"></div>

                <!-- CONTENT -->

                <!-- <div class="absolute inset-0 flex items-end md:items-center pb-12 md:pb-0">
                    <div class="max-w-[1800px] mx-auto w-full px-5 md:px-10">
                        <div class="flex justify-center lg:justify-end">
                            <div class="max-w-[620px] text-white text-center lg:text-left">
                                <p class="text-[18px] md:text-[22px] lg:text-[26px] font-light tracking-wide mb-2">
                                    Pre-Summer Sale
                                </p>

                                <h1
                                    class="text-[65px] md:text-[100px] lg:text-[140px] leading-[0.9] font-light">
                                    Sparkle
                                </h1>

                                <div class="flex items-center gap-3 md:gap-4 mt-4 mb-5">
                                    <div class="h-[1px] bg-white flex-1"></div>

                                    <div class="flex items-center gap-1">
                                        <div class="w-2 h-2 bg-white rotate-45"></div>
                                        <div class="w-2 h-2 bg-white rotate-45"></div>
                                    </div>

                                    <div class="h-[1px] bg-white flex-1"></div>
                                </div>

                                <h2
                                    class="text-[24px] md:text-[32px] lg:text-[38px] leading-tight font-light">
                                    Flat
                                    <span class="font-semibold"> 30% off </span>

                                    on prepaid order
                                </h2>

                                <div class="mt-10 md:mt-12 lg:mt-14">
                                    <a href="#"
                                        class="inline-flex items-center justify-center border border-white px-8 md:px-10 lg:px-12 py-4 md:py-5 text-[16px] md:text-[20px] lg:text-[22px] tracking-wide hover:bg-white hover:text-black transition duration-300">
                                        SHOP NOW
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div> -->
            </div>

            <!-- SLIDE 2 -->
            <div class="swiper-slide relative">
                <picture>
                    <!-- Mobile -->
                    <source media="(max-width: 767px)" srcset="https://i.ibb.co/fdvH4XyM/pposter-2.webp" />

                    <!-- Desktop -->
                    <img src="https://i.ibb.co/4Z4P7hL4/Untitled-design-37.png" alt="Banner"
                        class="w-full h-[650px] md:h-[750px] lg:h-[550px] object-cover" />
                </picture>
                <!-- OVERLAY -->
                <div class="absolute inset-0 bg-black/20"></div>
            </div>
        </div>
        <!-- LEFT ARROW -->
        <div class="swiper-button-prev !text-black !w-[35px] !h-[35px] !bg-white rounded-full [&::after]:!text-[14px]">
        </div>
        <!-- RIGHT ARROW -->
        <div class="swiper-button-next !text-black !w-[35px] !h-[35px] !bg-white rounded-full [&::after]:!text-[14px]">
        </div>
    </div>
</section>


<div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
    <section class="grid gap-10 lg:grid-cols-[1.2fr,0.8fr] lg:items-center">
        <div class="space-y-6">
            <p class="text-sm font-semibold uppercase tracking-[0.24em] text-brand">New arrivals</p>
            <h1 class="text-5xl font-semibold tracking-tight text-slate-900">Shop the latest styles with ShopMaster.</h1>
            <p class="max-w-xl text-lg leading-8 text-slate-600">Premium products, fast shipping, and a secure checkout experience for modern online shopping.</p>
            <div class="flex flex-wrap gap-3">
                <a href="<?= BASE_URL ?>/collection" class="inline-flex items-center justify-center rounded-3xl bg-slate-900 px-6 py-3 text-sm font-semibold text-white hover:bg-slate-800">Shop Now</a>
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
            <a href="<?= BASE_URL ?>/collection" class="text-sm font-medium text-brand hover:text-slate-900">View all</a>
        </div>
        <div class="mt-6 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
            <?php if ($featuredCategories): foreach ($featuredCategories as $category): ?>
                <?php $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', trim($category['name']))); ?>
                <a href="<?= BASE_URL ?>/collection/<?= $slug ?>" class="group overflow-hidden rounded-[1.75rem] border border-slate-200 bg-white p-6 text-center transition hover:-translate-y-1 hover:shadow-lg">
                    <div class="mx-auto mb-4 flex h-20 w-20 items-center justify-center rounded-full bg-slate-100 text-brand transition group-hover:bg-brand group-hover:text-white">
                        <i class="fas fa-tag text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-slate-900"><?= sanitize($category['name']) ?></h3>
                </a>
            <?php endforeach; else: ?>
                <div class="col-span-full rounded-[1.75rem] border border-slate-200 bg-white p-8 text-center text-slate-600 shadow-sm">Categories not found.</div>
            <?php endif; ?>
        </div>
    </section>
    <section class="mt-16">
        <div class="flex items-center justify-between gap-4">
            <h2 class="text-2xl font-semibold text-slate-900">Trending Products</h2>
            <a href="<?= BASE_URL ?>/collection" class="text-sm font-medium text-brand hover:text-slate-900">See all products</a>
        </div>
        <div class="mt-6 grid gap-6 md:grid-cols-2 xl:grid-cols-4">
            <?php if ($trending): foreach ($trending as $product): ?>
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
            <?php endforeach; else: ?>
                <div class="col-span-full rounded-[1.75rem] border border-slate-200 bg-white p-8 text-center text-slate-600 shadow-sm">Products not found.</div>
            <?php endif; ?>
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
