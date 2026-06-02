<?php
require_once __DIR__ . '/includes/functions.php';
?>
<?php include __DIR__ . '/includes/header.php'; ?>
<div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
    <div class="grid gap-10 lg:grid-cols-2 lg:items-center">
        <div>
            <h1 class="text-4xl font-semibold tracking-tight text-slate-900">About ShopMaster</h1>
            <p class="mt-4 max-w-2xl text-lg leading-8 text-slate-600">Built to deliver a clean, scalable, and secure eCommerce experience with a fully Tailwind-powered storefront.</p>
            <ul class="mt-8 space-y-4 text-slate-600">
                <li class="flex gap-3"><span class="mt-1 inline-flex h-8 w-8 items-center justify-center rounded-full bg-slate-900 text-white">✓</span> Modern responsive layout with Tailwind CSS.</li>
                <li class="flex gap-3"><span class="mt-1 inline-flex h-8 w-8 items-center justify-center rounded-full bg-slate-900 text-white">✓</span> Secure authentication and session-based cart.</li>
                <li class="flex gap-3"><span class="mt-1 inline-flex h-8 w-8 items-center justify-center rounded-full bg-slate-900 text-white">✓</span> Product filtering, coupon engine, and admin controls.</li>
            </ul>
        </div>
        <div class="overflow-hidden rounded-3xl bg-slate-100 shadow-sm">
            <img src="https://images.unsplash.com/photo-1512436991641-6745cdb1723f?auto=format&fit=crop&w=900&q=80" alt="About ShopMaster" class="h-full w-full object-cover" />
        </div>
    </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
