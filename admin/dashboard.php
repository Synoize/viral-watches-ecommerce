<?php
require_once __DIR__ . '/_header.php';
ensureBoxOptionsTableExists();
ensureSlidesTableExists();
seedDefaultPageMeta();

$totalUsers = (int)$pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
$totalOrders = (int)$pdo->query('SELECT COUNT(*) FROM orders')->fetchColumn();
$totalProducts = (int)$pdo->query('SELECT COUNT(*) FROM products')->fetchColumn();
$totalBoxes = (int)$pdo->query('SELECT COUNT(*) FROM box_options')->fetchColumn();
$totalSlides = (int)$pdo->query('SELECT COUNT(*) FROM slides')->fetchColumn();
$totalRevenue = (float)$pdo->query('SELECT COALESCE(SUM(total_amount),0) FROM orders WHERE payment_status = "Paid"')->fetchColumn();
$paidOrders = (int)$pdo->query('SELECT COUNT(*) FROM orders WHERE payment_status = "Paid"')->fetchColumn();
$pendingOrders = (int)$pdo->query('SELECT COUNT(*) FROM orders WHERE status = "Pending"')->fetchColumn();
$lowStockProducts = (int)$pdo->query('SELECT COUNT(*) FROM products WHERE stock > 0 AND stock < 10')->fetchColumn();
$activeCoupons = (int)$pdo->query('SELECT COUNT(*) FROM coupons WHERE status = 1')->fetchColumn();

$paidPercent = $totalOrders > 0 ? min(100, round(($paidOrders / $totalOrders) * 100)) : 0;
$pendingPercent = $totalOrders > 0 ? min(100, round(($pendingOrders / $totalOrders) * 100)) : 0;
$lowStockPercent = $totalProducts > 0 ? min(100, round(($lowStockProducts / $totalProducts) * 100)) : 0;
$couponPercent = $activeCoupons > 0 ? 100 : 0;

$recentOrders = $pdo->query('SELECT id, total_amount, status, payment_status, created_at FROM orders ORDER BY id DESC LIMIT 5')->fetchAll();
?>
<div class="space-y-8">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <p class="text-sm font-bold text-emerald-600">Welcome back, <?= $adminName ?></p>
            <h1 class="mt-2 text-3xl font-black tracking-tight text-slate-950">Dashboard</h1>
            <p class="mt-1 text-sm text-slate-500">Monitor store activity and manage daily ecommerce work.</p>
        </div>
        <a href="<?= BASE_URL ?>/admin/orders.php" class="inline-flex items-center gap-2 rounded-2xl bg-white px-5 py-3 text-sm font-bold text-slate-700 shadow-sm hover:text-slate-950">
            <i class="fa-solid fa-filter text-emerald-500"></i>
            Filter Orders
        </a>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-3xl bg-white p-6 shadow-soft">
            <div class="flex items-center justify-between">
                <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600"><i class="fa-solid fa-box"></i></span>
                <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-600">Products</span>
            </div>
            <p class="mt-5 text-3xl font-black text-slate-950"><?= number_format($totalProducts) ?></p>
            <p class="mt-1 text-sm font-medium text-slate-400">Total products</p>
        </div>

        <div class="rounded-3xl bg-white p-6 shadow-soft">
            <div class="flex items-center justify-between">
                <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-cyan-50 text-cyan-600"><i class="fa-solid fa-receipt"></i></span>
                <span class="rounded-full bg-cyan-50 px-3 py-1 text-xs font-bold text-cyan-600">Orders</span>
            </div>
            <p class="mt-5 text-3xl font-black text-slate-950"><?= number_format($totalOrders) ?></p>
            <p class="mt-1 text-sm font-medium text-slate-400">Total orders</p>
        </div>

        <div class="rounded-3xl bg-white p-6 shadow-soft">
            <div class="flex items-center justify-between">
                <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-violet-50 text-violet-600"><i class="fa-solid fa-user-group"></i></span>
                <span class="rounded-full bg-violet-50 px-3 py-1 text-xs font-bold text-violet-600">Users</span>
            </div>
            <p class="mt-5 text-3xl font-black text-slate-950"><?= number_format($totalUsers) ?></p>
            <p class="mt-1 text-sm font-medium text-slate-400">Registered users</p>
        </div>

        <div class="rounded-3xl bg-white p-6 shadow-soft">
            <div class="flex items-center justify-between">
                <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-rose-50 text-rose-600"><i class="fa-solid fa-indian-rupee-sign"></i></span>
                <span class="rounded-full bg-rose-50 px-3 py-1 text-xs font-bold text-rose-600">Revenue</span>
            </div>
            <p class="mt-5 text-3xl font-black text-slate-950">&#8377;<?= number_format($totalRevenue, 2) ?></p>
            <p class="mt-1 text-sm font-medium text-slate-400">Paid orders value</p>
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-[1.05fr_1.25fr]">
        <section class="rounded-3xl bg-white p-6 shadow-soft">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-black text-slate-950">Store Health</h2>
                    <p class="mt-1 text-sm text-slate-400">Live ratios from your current data.</p>
                </div>
                <a href="<?= BASE_URL ?>/admin/products.php" class="rounded-2xl border border-slate-100 px-4 py-2 text-sm font-bold text-slate-500 hover:text-slate-950">Manage</a>
            </div>

            <div class="mt-8 grid gap-5 sm:grid-cols-2">
                <?php
                $healthCards = [
                    ['Paid Orders', $paidPercent, 'text-emerald-600', 'border-emerald-100'],
                    ['Pending Orders', $pendingPercent, 'text-amber-600', 'border-amber-100'],
                    ['Low Stock', $lowStockPercent, 'text-rose-600', 'border-rose-100'],
                    ['Active Coupons', $couponPercent, 'text-cyan-600', 'border-cyan-100'],
                ];
                ?>
                <?php foreach ($healthCards as $card): ?>
                    <div class="rounded-3xl border <?= $card[3] ?> bg-slate-50/60 p-5 text-center">
                        <div class="mx-auto grid h-28 w-28 place-items-center rounded-full border-[14px] border-slate-100 <?= $card[2] ?>">
                            <span class="text-2xl font-black"><?= (int)$card[1] ?>%</span>
                        </div>
                        <p class="mt-4 text-sm font-bold text-slate-700"><?= sanitize($card[0]) ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="rounded-3xl bg-white p-6 shadow-soft">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-black text-slate-950">Order Overview</h2>
                    <p class="mt-1 text-sm text-slate-400">A compact visual summary for scanning.</p>
                </div>
                <a href="<?= BASE_URL ?>/admin/orders.php" class="rounded-2xl border border-slate-100 px-4 py-2 text-sm font-bold text-slate-500 hover:text-slate-950">See orders</a>
            </div>
            <div class="mt-8 h-64">
                <svg viewBox="0 0 640 240" class="h-full w-full">
                    <defs>
                        <linearGradient id="dashboardLine" x1="0" x2="1" y1="0" y2="0">
                            <stop offset="0%" stop-color="#10b981" />
                            <stop offset="100%" stop-color="#38bdf8" />
                        </linearGradient>
                    </defs>
                    <path d="M20 190 C 95 70, 145 205, 220 115 S 340 40, 410 130 S 520 215, 620 75" fill="none" stroke="url(#dashboardLine)" stroke-width="8" stroke-linecap="round" />
                    <path d="M20 205 C 95 125, 145 220, 220 150 S 340 80, 410 160 S 520 230, 620 115" fill="none" stroke="#fb7185" stroke-width="5" stroke-linecap="round" opacity=".7" />
                    <?php foreach ([60, 120, 180] as $gridY): ?>
                        <line x1="20" y1="<?= $gridY ?>" x2="620" y2="<?= $gridY ?>" stroke="#e2e8f0" stroke-dasharray="8 10" />
                    <?php endforeach; ?>
                </svg>
            </div>
        </section>
    </div>

    <div class="grid gap-6 xl:grid-cols-[1.2fr_.8fr]">
        <section class="rounded-3xl bg-white p-6 shadow-soft">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-black text-slate-950">Recent Orders</h2>
                <a href="<?= BASE_URL ?>/admin/orders.php" class="text-sm font-bold text-emerald-600">View all</a>
            </div>
            <div class="mt-5 overflow-hidden rounded-2xl border border-slate-100">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 text-slate-400">
                        <tr>
                            <th class="px-5 py-4">Order</th>
                            <th class="px-5 py-4">Amount</th>
                            <th class="px-5 py-4">Status</th>
                            <th class="px-5 py-4">Payment</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php foreach ($recentOrders as $order): ?>
                            <tr>
                                <td class="px-5 py-4 font-bold text-slate-800">#<?= (int)$order['id'] ?></td>
                                <td class="px-5 py-4 text-slate-600">&#8377;<?= number_format((float)$order['total_amount'], 2) ?></td>
                                <td class="px-5 py-4 text-slate-600"><?= sanitize($order['status']) ?></td>
                                <td class="px-5 py-4 text-slate-600"><?= sanitize($order['payment_status']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (!$recentOrders): ?>
                            <tr><td colspan="4" class="px-5 py-8 text-center text-slate-400">Orders not found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="rounded-3xl bg-white p-6 shadow-soft">
            <h2 class="text-lg font-black text-slate-950">Quick Actions</h2>
            <div class="mt-5 grid gap-3">
                <a href="<?= BASE_URL ?>/admin/products.php" class="flex items-center justify-between rounded-2xl bg-slate-50 px-5 py-4 text-sm font-bold text-slate-700 hover:bg-emerald-50 hover:text-emerald-700"><span>Manage Products</span><i class="fa-solid fa-arrow-right"></i></a>
                <a href="<?= BASE_URL ?>/admin/slides.php" class="flex items-center justify-between rounded-2xl bg-slate-50 px-5 py-4 text-sm font-bold text-slate-700 hover:bg-emerald-50 hover:text-emerald-700"><span>Hero Slides</span><i class="fa-solid fa-arrow-right"></i></a>
                <a href="<?= BASE_URL ?>/admin/orders.php" class="flex items-center justify-between rounded-2xl bg-slate-50 px-5 py-4 text-sm font-bold text-slate-700 hover:bg-emerald-50 hover:text-emerald-700"><span>Manage Orders</span><i class="fa-solid fa-arrow-right"></i></a>
                <a href="<?= BASE_URL ?>/admin/coupons.php" class="flex items-center justify-between rounded-2xl bg-slate-50 px-5 py-4 text-sm font-bold text-slate-700 hover:bg-emerald-50 hover:text-emerald-700"><span>Coupons</span><i class="fa-solid fa-arrow-right"></i></a>
            </div>
            <div class="mt-5 grid grid-cols-2 gap-3">
                <div class="rounded-2xl bg-emerald-50 p-4">
                    <p class="text-2xl font-black text-emerald-700"><?= number_format($totalBoxes) ?></p>
                    <p class="mt-1 text-xs font-bold text-emerald-600">Box options</p>
                </div>
                <div class="rounded-2xl bg-cyan-50 p-4">
                    <p class="text-2xl font-black text-cyan-700"><?= number_format($totalSlides) ?></p>
                    <p class="mt-1 text-xs font-bold text-cyan-600">Hero slides</p>
                </div>
            </div>
        </section>
    </div>
</div>
<?php require_once __DIR__ . '/_footer.php'; ?>
