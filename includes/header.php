<?php
require_once __DIR__ . '/functions.php';
$categories = getCategories();
$cartCount = getCartCount();
$wishlistCount = getWishlistCount();
$user = getCurrentUser();
$csrfToken = generateCsrfToken();
// Determine normalized current path (remove BASE_URL prefix)
$currentRequestPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$basePrefix = defined('BASE_URL') ? rtrim(BASE_URL, '/') : '';
if ($basePrefix !== '' && strpos($currentRequestPath, $basePrefix) === 0) {
    $normalizedPath = substr($currentRequestPath, strlen($basePrefix));
} else {
    $normalizedPath = $currentRequestPath;
}
function normalize_nav_path($path)
{
    $path = parse_url($path, PHP_URL_PATH) ?: '/';
    $path = '/' . trim($path, '/');

    if ($path === '/index.php' || $path === '/index' || $path === '') {
        return '/';
    }

    if (substr($path, -4) === '.php') {
        $path = substr($path, 0, -4);
    }

    return rtrim($path, '/') ?: '/';
}

$normalizedPath = normalize_nav_path($normalizedPath);

function is_active($match)
{
    global $normalizedPath;

    $activeClass = 'bg-gray-50 md:bg-transparent text-slate-900 md:underline md:decoration-[1.5px] md:underline-offset-8 md:decoration-slate-900';

    if (substr($match, -1) === '*') {
        $prefix = normalize_nav_path(substr($match, 0, -1));

        return strpos($normalizedPath . '/', rtrim($prefix, '/') . '/') === 0
            ? $activeClass
            : '';
    }

    return $normalizedPath === normalize_nav_path($match)
        ? $activeClass
        : '';
}
?>
<?php include __DIR__ . '/head.php'; ?>

<body class="font-body bg-gray-50">
    <!-- SUMMER SALE BAR -->
    <section class="relative w-full bg-cover bg-center" style="
        background-image: url('https://images.unsplash.com/photo-1506744038136-46273834b3fb?q=80&w=1920');
      ">
        <div class="bg-[#ead8c7]/80">
            <div class="max-w-[1920px] mx-auto px-2 py-[4px]">
                <div class="flex flex-col md:flex-row items-center justify-center gap-1 md:gap-4">
                    <!-- TEXT -->
                    <div class="text-center">
                        <h2 class="text-[14px] md:text-[18px] font-serif text-slate-700 leading-none">
                            Summer Sale
                        </h2>

                        <p class="text-[9px] md:text-[11px] text-slate-500 leading-none mt-[1px]">
                            Up to 30% OFF ON Prepaid
                        </p>
                    </div>

                    <!-- TIMER -->
                    <div id="countdown" class="flex items-center gap-[2px] md:gap-2">
                        <!-- DAYS -->
                        <div class="text-center">
                            <div class="text-[7px] md:text-[9px] font-semibold leading-none mb-[1px]">
                                Days
                            </div>

                            <div class="flex gap-[1px]">
                                <span class="bg-[#79a9c5] text-white text-[11px] md:text-[16px] font-bold px-1 py-0 leading-none">
                                    0
                                </span>

                                <span class="bg-[#79a9c5] text-white text-[11px] md:text-[16px] font-bold px-1 py-0 leading-none">
                                    0
                                </span>
                            </div>
                        </div>

                        <!-- HOURS -->
                        <div class="text-center">
                            <div class="text-[7px] md:text-[9px] font-semibold leading-none mb-[1px]">
                                Hours
                            </div>

                            <div class="flex gap-[1px]">
                                <span id="h1"
                                    class="bg-[#79a9c5] text-white text-[11px] md:text-[16px] font-bold px-1 py-0 leading-none">
                                    0
                                </span>

                                <span id="h2"
                                    class="bg-[#79a9c5] text-white text-[11px] md:text-[16px] font-bold px-1 py-0 leading-none">
                                    0
                                </span>
                            </div>
                        </div>

                        <div class="text-[12px] md:text-[18px] font-bold text-[#444]">
                            :
                        </div>

                        <!-- MINUTES -->
                        <div class="text-center">
                            <div class="text-[7px] md:text-[9px] font-semibold leading-none mb-[1px]">
                                Minutes
                            </div>

                            <div class="flex gap-[1px]">
                                <span id="m1"
                                    class="bg-[#79a9c5] text-white text-[11px] md:text-[16px] font-bold px-1 py-0 leading-none">
                                    0
                                </span>

                                <span id="m2"
                                    class="bg-[#79a9c5] text-white text-[11px] md:text-[16px] font-bold px-1 py-0 leading-none">
                                    0
                                </span>
                            </div>
                        </div>

                        <div class="text-[12px] md:text-[18px] font-bold text-[#444]">
                            :
                        </div>

                        <!-- SECONDS -->
                        <div class="text-center">
                            <div class="text-[7px] md:text-[9px] font-semibold leading-none mb-[1px]">
                                Seconds
                            </div>

                            <div class="flex gap-[1px]">
                                <span id="s1"
                                    class="bg-[#79a9c5] text-white text-[11px] md:text-[16px] font-bold px-1 py-0 leading-none">
                                    0
                                </span>

                                <span id="s2"
                                    class="bg-[#79a9c5] text-white text-[11px] md:text-[16px] font-bold px-1 py-0 leading-none">
                                    0
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script>
        /* SUMMER SALE COUNTDOWN TIMER */
        function updateCountdown() {
            const now = new Date();

            // Next Midnight
            const tomorrow = new Date();
            tomorrow.setDate(now.getDate() + 1);
            tomorrow.setHours(0, 0, 0, 0);

            const diff = tomorrow - now;

            const hours = Math.floor(diff / (1000 * 60 * 60));
            const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((diff % (1000 * 60)) / 1000);

            const hh = String(hours).padStart(2, "0");
            const mm = String(minutes).padStart(2, "0");
            const ss = String(seconds).padStart(2, "0");

            const h1 = document.getElementById("h1");
            const h2 = document.getElementById("h2");
            const m1 = document.getElementById("m1");
            const m2 = document.getElementById("m2");
            const s1 = document.getElementById("s1");
            const s2 = document.getElementById("s2");
            if (!h1 || !h2 || !m1 || !m2 || !s1 || !s2) return;

            // Hours
            h1.innerText = hh[0];
            h2.innerText = hh[1];

            // Minutes
            m1.innerText = mm[0];
            m2.innerText = mm[1];

            // Seconds
            s1.innerText = ss[0];
            s2.innerText = ss[1];
        }

        // Start Countdown
        updateCountdown();
        setInterval(updateCountdown, 1000);
    </script>

    <!-- TOP BAR -->
    <div class="bg-black text-white relative">
        <div class="max-w-[1920px] mx-auto py-2 text-center">
            <!-- LEFT ARROW -->
            <!-- <button id="prevBtn" class="absolute left-5 top-1/2 -translate-y-1/2 text-[12px]">
      <i class="fa-solid fa-chevron-left"></i>
    </button> -->

            <!-- TEXT -->
            <p id="announcementText" class="text-[14px] font-serif font-semibold tracking-wide">
                Prepaid Orders Deliver Faster!
            </p>

            <!-- RIGHT ARROW -->
            <!-- <button id="nextBtn" class="absolute right-5 top-1/2 -translate-y-1/2 text-[12px]">
      <i class="fa-solid fa-chevron-right"></i>
    </button> -->
        </div>
    </div>

    <!-- HEADER -->
    <header class="sticky top-0 z-50 bg-white">
        <div class="max-w-[1920px] mx-auto px-4 lg:px-10 py-5">
            <div class="flex items-center justify-between">
                <!-- LEFT -->
                <div class="flex items-center">
                    <!-- MOBILE HAMBURGER -->
                    <button id="menuBtn" class="lg:hidden">
                        <i data-lucide="menu" class="w-6 h-6 stroke-[1]"></i>
                    </button>

                    <!-- DESKTOP MENU -->
                    <nav class="hidden lg:flex items-center gap-6 text-sm text-slate-700 ">
                        <a href="<?= BASE_URL ?>" class="hover:underline hover:decoration-[1.5px] hover:underline-offset-8 <?= is_active('/') ?>"> Home </a>
                        <a href="<?= BASE_URL ?>/collection" class="hover:underline hover:decoration-[1.5px] hover:underline-offset-8 <?= is_active('/collection') ?>"> Collections </a>

                        <?php foreach (array_slice($categories, 0, 3) as $category):
                            $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', trim($category['name'])));
                        ?>
                            <a class="block hover:underline hover:decoration-[1.5px] hover:underline-offset-8 <?= is_active('/collection/' . $slug) ?>"
                                href="<?= BASE_URL ?>/collection/<?= $slug ?>">
                                <?= sanitize($category['name']) ?>
                            </a>
                        <?php endforeach; ?>
                    </nav>
                </div>

                <!-- LOGO -->
                <a href="<?= BASE_URL ?>" class="absolute left-1/2 -translate-x-1/2 lg:static lg:translate-x-0">
                    <img src="<?= BASE_URL ?>/assets/images/public/logo.png" alt="Logo"
                        class="h-[40px] lg:h-[52px] lg:mr-[220px] object-contain" />
                </a>

                <!-- RIGHT -->
                <div class="flex items-center gap-5 lg:gap-7">

                    <!-- SEARCH BUTTON -->
                    <button id="openSearch"
                        class="text-slate-700  hover:opacity-70 transition">
                        <i data-lucide="search" class="w-6 h-6 stroke-[1]"></i>
                    </button>

                    <!-- SEARCH OVERLAY -->
                    <div id="searchOverlay"
                        class="fixed inset-0 z-[9999] invisible bg-black/20 transition-opacity duration-300 opacity-0">

                        <!-- SEARCH PANEL -->
                        <div id="searchPanel"
                            class="bg-white shadow-xl -translate-y-full transition-transform duration-500 ease-in-out">

                            <div class="max-w-5xl mx-auto p-4 md:py-6 md:pt-16">

                                <div class="flex items-center gap-2 md:gap-4">

                                    <!-- FORM -->
                                    <form
                                        action="<?= BASE_URL ?>/collection"
                                        method="GET"
                                        class="flex-1">

                                        <div class="flex items-center border border-gray-300">

                                            <input
                                                type="search"
                                                name="search"
                                                placeholder="Search products..."
                                                class="w-full px-5 py-3 md:py-4 md:text-lg text-slate-900 outline-none placeholder:text-slate-400 placeholder:font-light"
                                                autocomplete="off">

                                            <button
                                                type="submit"
                                                class="pr-4 text-gray-700 hover:text-black transition">

                                                <i data-lucide="search"
                                                    class="w-6 h-6 stroke-[1]"></i>

                                            </button>

                                        </div>

                                    </form>

                                    <!-- CLOSE BUTTON -->
                                    <button
                                        id="closeSearch"
                                        class="text-black hover:opacity-70 transition">

                                        <i data-lucide="x"
                                            class="md:w-8 w-6 md:h-8 h-6 stroke-[1]"></i>

                                    </button>

                                </div>

                            </div>

                        </div>

                    </div>

                    <!-- WISHLIST -->
                    <a href="<?= BASE_URL ?>/wishlist"
                        class="relative text-slate-700 hover:opacity-70 transition <?= is_active('/wishlist') ?>">

                        <i data-lucide="heart"
                            class="w-6 h-6 stroke-[1]"></i>

                        <?php if ($wishlistCount > 0): ?>
                            <span
                                class="absolute -right-2 -bottom-1 flex h-5 w-5 items-center justify-center rounded-full bg-rose-600 text-[10px] font-semibold text-white">
                                <?= $wishlistCount ?>
                            </span>
                        <?php endif; ?>

                    </a>

                    <!-- CART -->
                    <a href="<?= BASE_URL ?>/cart"
                        class="relative text-slate-700 hover:opacity-70 transition <?= is_active('/cart') ?>">

                        <i data-lucide="shopping-bag"
                            class="w-6 h-6 stroke-[1]"></i>

                        <?php if ($cartCount > 0): ?>
                            <span
                                class="absolute -right-2 -bottom-1 flex h-5 w-5 items-center justify-center rounded-full bg-black text-[10px] font-semibold text-white">
                                <?= $cartCount ?>
                            </span>
                        <?php endif; ?>

                    </a>

                    <!-- User -->
                    <?php if ($user): ?>
                        <details class="relative hidden lg:block" id="profileMenu">

                            <!-- Profile Icon -->
                            <summary class="list-none cursor-pointer text-slate-700 hover:opacity-70 transition">
                                <i data-lucide="circle-user-round" class="w-6 h-6 stroke-[1]"></i>
                            </summary>

                            <!-- Dropdown -->
                            <div
                                class="dropdown absolute right-0 top-full mt-3 w-52 overflow-hidden bg-white rounded-xl border border z-50
        max-h-0 opacity-0 -translate-y-3
        transition-all duration-300 ease-out">

                                <!-- User Info -->
                                <div class="px-5 py-3 bg-slate-50 border-b">

                                    <div class="flex items-center gap-3">

                                        <!-- Avatar -->
                                        <div class="w-8 h-8 rounded-full bg-slate-900 text-white flex items-center justify-center text-xs font-semibold uppercase">
                                            <?= strtoupper(substr($user['name'], 0, 1)) ?>
                                        </div>

                                        <!-- User Name -->
                                        <div class="min-w-0">
                                            <p class="text-xs font-semibold text-slate-900 truncate">
                                                <?= sanitize($user['name']) ?>
                                            </p>
                                            <p class="text-[10px] text-slate-500">
                                                Welcome back
                                            </p>
                                        </div>

                                    </div>

                                </div>

                                <!-- Menu Items -->
                                <div class="py-1">

                                    <a href="<?= BASE_URL ?>/user/profile"
                                        class="flex items-center gap-3 px-5 py-3 text-sm text-slate-700 hover:bg-slate-50 transition">
                                        <i data-lucide="user" class="w-4 h-4 stroke-[1.5]"></i>
                                        My Profile
                                    </a>

                                    <a href="<?= BASE_URL ?>/user/orders"
                                        class="flex items-center gap-3 px-5 py-3 text-sm text-slate-700 hover:bg-slate-50 transition">
                                        <i data-lucide="package" class="w-4 h-4 stroke-[1.5]"></i>
                                        My Orders
                                    </a>

                                </div>

                                <!-- Logout -->
                                <div class="border-t border-slate-100">
                                    <a href="<?= publicUrl('/logout') ?>"
                                        class="flex items-center gap-3 px-5 py-3 text-sm text-red-500 hover:bg-red-50 transition">
                                        <i data-lucide="log-out" class="w-4 h-4 stroke-[1.5]"></i>
                                        Logout
                                    </a>
                                </div>

                            </div>

                        </details>
                    <?php else: ?>

                        <a href="<?= BASE_URL ?>/login"
                            class="hidden lg:block text-slate-700 hover:opacity-70 transition">

                            <i data-lucide="user"
                                class="w-6 h-6 stroke-[1]"></i>

                        </a>

                    <?php endif; ?>

                </div>
            </div>
        </div>
    </header>

    <!-- OVERLAY -->
    <div id="overlay" class="fixed inset-0 bg-black/50 z-[99] hidden"></div>

    <!-- SIDEBAR -->
    <div id="sidebar"
        class="fixed top-0 -left-[320px] w-[300px] h-screen bg-white z-[100]
    flex flex-col shadow-2xl transition-all duration-300 ease-out">

        <!-- HEADER -->
        <div class="flex items-center justify-between px-5 h-[72px] border-b border-slate-100 shrink-0">
            <img src="<?= BASE_URL ?>/assets/images/public/logo.png"
                class="h-8 object-contain"
                alt="Logo">

            <button id="closeMenu"
                class="flex items-center justify-center transition">
                <i data-lucide="x" class="w-6 h-6 stroke-[1]"></i>
            </button>
        </div>

        <!-- SCROLLABLE CONTENT -->
        <div class="flex-1 overflow-y-auto">

            <!-- USER -->
            <?php if ($user): ?>
                <div class="p-4 border-b border-slate-100">

                    <div class="flex items-center gap-3">

                        <div class="w-10 h-10 rounded-full bg-slate-900 text-white flex items-center justify-center text-sm font-semibold uppercase">
                            <?= strtoupper(substr($user['name'], 0, 1)) ?>
                        </div>

                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-slate-900 truncate">
                                <?= sanitize($user['name']) ?>
                            </p>

                            <p class="text-xs text-slate-500">
                                Welcome back
                            </p>
                        </div>

                    </div>

                </div>
            <?php endif; ?>

            <!-- NAVIGATION -->
            <nav class="py-2">

                <a href="<?= BASE_URL ?>"
                    class="flex items-center px-6 py-3 text-sm text-slate-700 hover:bg-slate-50 transition <?= is_active('/') ?>">
                    Home
                </a>

                <a href="<?= BASE_URL ?>/collection"
                    class="flex items-center px-6 py-3 text-sm text-slate-700 hover:bg-slate-50 transition <?= is_active('/collection') ?>">
                    Collections
                </a>

                <?php foreach (array_slice($categories, 0, 3) as $category):
                    $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', trim($category['name'])));
                ?>
                    <a href="<?= BASE_URL ?>/collection/<?= $slug ?>"
                        class="flex items-center px-6 py-3 text-sm text-slate-700 hover:bg-slate-50 transition <?= is_active('/collection/' . $slug) ?>">
                        <?= sanitize($category['name']) ?>
                    </a>
                <?php endforeach; ?>

                <a href="<?= BASE_URL ?>/about"
                    class="flex items-center px-6 py-3 text-sm text-slate-700 hover:bg-slate-50 transition <?= is_active('/about') ?>">
                    About
                </a>

                <a href="<?= BASE_URL ?>/contact"
                    class="flex items-center px-6 py-3 text-sm text-slate-700 hover:bg-slate-50 transition <?= is_active('/contact') ?>">
                    Contact
                </a>

            </nav>

            <!-- ACCOUNT ACTIONS -->
            <?php if ($user): ?>
                <div class="py-2 border-t border-slate-100">

                    <a href="<?= BASE_URL ?>/user/profile"
                        class="flex items-center gap-3 px-6 py-3 text-sm text-slate-700 hover:bg-slate-50 transition <?= is_active('/user/profile') ?>">
                        <i data-lucide="user" class="w-4 h-4 stroke-[1]"></i>
                        My Profile
                    </a>

                    <a href="<?= BASE_URL ?>/user/orders"
                        class="flex items-center gap-3 px-6 py-3 text-sm text-slate-700 hover:bg-slate-50 transition <?= is_active('/user/orders') ?>">
                        <i data-lucide="package" class="w-4 h-4 stroke-[1]"></i>
                        My Orders
                    </a>

                    <a href="<?= BASE_URL ?>/wishlist"
                        class="flex items-center gap-3 px-6 py-3 text-sm text-slate-700 hover:bg-slate-50 transition <?= is_active('/wishlist') ?>">
                        <i data-lucide="heart" class="w-4 h-4 stroke-[1]"></i>
                        Wishlist
                    </a>

                    <a href="<?= publicUrl('/logout') ?>"
                        class="flex items-center gap-3 px-6 py-3 mt-2 text-sm text-red-500 hover:bg-red-50 transition">
                        <i data-lucide="log-out" class="w-4 h-4 stroke-[1]"></i>
                        Logout
                    </a>

                </div>
            <?php endif; ?>

        </div>

        <!-- FOOTER -->
        <div class="border-t border-slate-100 p-5 shrink-0">

            <div class="flex justify-center gap-4">

                <a href="#"
                    class="w-10 h-10 rounded-full border border-slate-200 flex items-center justify-center hover:bg-slate-50 transition">
                    <i class="fa-brands fa-facebook-f text-sm"></i>
                </a>

                <a href="#"
                    class="w-10 h-10 rounded-full border border-slate-200 flex items-center justify-center hover:bg-slate-50 transition">
                    <i class="fa-brands fa-instagram text-sm"></i>
                </a>

                <a href="#"
                    class="w-10 h-10 rounded-full border border-slate-200 flex items-center justify-center hover:bg-slate-50 transition">
                    <i class="fa-brands fa-pinterest-p text-sm"></i>
                </a>

            </div>

        </div>

    </div>

    <!-- MAIN CONTENT -->
    <main>