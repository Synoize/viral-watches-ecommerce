<?php
require_once __DIR__ . '/functions.php';
$categories = getCategories();
$cartCount = getCartCount();
$user = getCurrentUser();
$csrfToken = generateCsrfToken();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ShopMaster</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        heading: ['Cormorant Garamond', 'serif'],
                        body: ['Inter', 'sans-serif'],
                    },
                    colors: {
                        brand: '#1d4ed8',
                    },
                },
            },
        }
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />
</head>

<body class="font-body bg-slate-50 text-slate-900">
      <!-- SUMMER SALE BAR -->
  <!-- ULTRA SLIM SUMMER SALE BAR -->
  <section class="relative w-full bg-cover bg-center" style="
        background-image: url(&quot;https://images.unsplash.com/photo-1506744038136-46273834b3fb?q=80&w=1920&quot;);
      ">
    <div class="bg-[#ead8c7]/80">
      <div class="max-w-[1920px] mx-auto px-2 py-[4px]">
        <div class="flex flex-col md:flex-row items-center justify-center gap-1 md:gap-4">
          <!-- TEXT -->
          <div class="text-center">
            <h2 class="text-[14px] md:text-[18px] font-serif text-[#222] leading-none">
              Summer Sale
            </h2>

            <p class="text-[9px] md:text-[11px] text-[#333] leading-none mt-[1px]">
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

  <!-- TOP BAR -->
  <div class="bg-black text-white relative">
    <div class="max-w-[1920px] mx-auto py-1 text-center">
      <!-- LEFT ARROW -->
      <!-- <button id="prevBtn" class="absolute left-5 top-1/2 -translate-y-1/2 text-[12px]">
      <i class="fa-solid fa-chevron-left"></i>
    </button> -->

      <!-- TEXT -->
      <p id="announcementText" class="text-[16px] font-heading font-semibold tracking-wide"
        >
        Prepaid Orders Deliver Faster!
      </p>

      <!-- RIGHT ARROW -->
      <!-- <button id="nextBtn" class="absolute right-5 top-1/2 -translate-y-1/2 text-[12px]">
      <i class="fa-solid fa-chevron-right"></i>
    </button> -->
    </div>
  </div>


    <header class="sticky top-0 z-50 bg-white shadow-sm">
        <div class="mx-auto flex max-w-7xl flex-col gap-4 px-4 py-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between gap-4">
                <a href="<?= BASE_URL ?>/index.php" class="text-2xl font-semibold tracking-tight text-slate-900">ShopMaster</a>
                <button id="mobile-menu-btn" class="inline-flex h-11 w-11 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-700 shadow-sm md:hidden" aria-label="Open menu">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="hidden flex-1 items-center justify-between gap-6 md:flex">
                    <div class="flex items-center gap-2 text-sm font-medium text-slate-700">
                        <details class="relative">
                            <summary class="flex cursor-pointer items-center gap-2 rounded-full border border-slate-200 bg-slate-100 px-4 py-2 hover:bg-slate-200">Categories <i class="fas fa-chevron-down text-xs"></i></summary>
                            <div class="absolute left-0 top-full z-20 mt-2 w-56 rounded-2xl border border-slate-200 bg-white p-3 shadow-lg">
                                <?php foreach ($categories as $category): ?>
                                    <a class="block rounded-xl px-3 py-2 text-sm text-slate-700 hover:bg-slate-50" href="<?= BASE_URL ?>/shop.php?category=<?= $category['id'] ?>"><?= sanitize($category['name']) ?></a>
                                <?php endforeach; ?>
                            </div>
                        </details>
                        <a href="<?= BASE_URL ?>/shop.php" class="rounded-full px-4 py-2 text-sm text-slate-700 hover:bg-slate-100">Shop</a>
                        <a href="<?= BASE_URL ?>/about.php" class="rounded-full px-4 py-2 text-sm text-slate-700 hover:bg-slate-100">About</a>
                        <a href="<?= BASE_URL ?>/contact.php" class="rounded-full px-4 py-2 text-sm text-slate-700 hover:bg-slate-100">Help</a>
                    </div>
                    <form class="flex w-full max-w-md items-center gap-2 rounded-full border border-slate-200 bg-slate-100 px-3 py-2" action="<?= BASE_URL ?>/shop.php" method="get">
                        <input type="search" name="search" placeholder="Search products" class="w-full bg-transparent text-sm text-slate-900 outline-none placeholder:text-slate-500" aria-label="Search products">
                        <button type="submit" class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-slate-900 text-white hover:bg-slate-800"><i class="fas fa-search"></i></button>
                    </form>
                    <div class="flex items-center gap-3">
                        <a href="<?= BASE_URL ?>/cart.php" class="relative inline-flex items-center justify-center rounded-full border border-slate-200 bg-white p-3 text-slate-700 shadow-sm hover:bg-slate-50">
                            <i class="fas fa-shopping-cart"></i>
                            <span class="absolute -right-2 -top-2 inline-flex h-5 min-w-[1.25rem] items-center justify-center rounded-full bg-rose-500 px-1.5 text-[0.65rem] font-semibold text-white"><?= $cartCount ?></span>
                        </a>
                        <?php if ($user): ?>
                            <details class="relative">
                                <summary class="flex cursor-pointer items-center gap-2 rounded-full border border-slate-200 bg-slate-100 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-200"><?= sanitize($user['name']) ?> <i class="fas fa-chevron-down text-xs"></i></summary>
                                <div class="absolute right-0 top-full z-20 mt-2 w-44 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-lg">
                                    <a class="block px-4 py-3 text-sm text-slate-700 hover:bg-slate-50" href="<?= BASE_URL ?>/user/profile.php">Profile</a>
                                    <a class="block px-4 py-3 text-sm text-slate-700 hover:bg-slate-50" href="<?= BASE_URL ?>/user/orders.php">Orders</a>
                                    <div class="border-t border-slate-200"></div>
                                    <a class="block px-4 py-3 text-sm text-rose-600 hover:bg-slate-50" href="<?= BASE_URL ?>/logout.php">Logout</a>
                                </div>
                            </details>
                        <?php else: ?>
                            <a href="<?= BASE_URL ?>/login.php" class="rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Login</a>
                            <a href="<?= BASE_URL ?>/register.php" class="rounded-full bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800">Register</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div id="mobile-menu" class="hidden flex-col gap-3 rounded-3xl border border-slate-200 bg-white p-4 shadow-sm md:hidden">
                <a class="block rounded-2xl px-4 py-3 text-sm text-slate-700 hover:bg-slate-50" href="<?= BASE_URL ?>/shop.php">Shop</a>
                <a class="block rounded-2xl px-4 py-3 text-sm text-slate-700 hover:bg-slate-50" href="<?= BASE_URL ?>/about.php">About</a>
                <a class="block rounded-2xl px-4 py-3 text-sm text-slate-700 hover:bg-slate-50" href="<?= BASE_URL ?>/contact.php">Help</a>
                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-3">
                    <p class="mb-2 text-sm font-medium text-slate-700">Categories</p>
                    <div class="space-y-1">
                        <?php foreach ($categories as $category): ?>
                            <a class="block rounded-xl px-3 py-2 text-sm text-slate-700 hover:bg-slate-100" href="<?= BASE_URL ?>/shop.php?category=<?= $category['id'] ?>"><?= sanitize($category['name']) ?></a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <form class="flex items-center gap-2 rounded-full border border-slate-200 bg-slate-100 px-3 py-2" action="<?= BASE_URL ?>/shop.php" method="get">
                    <input type="search" name="search" placeholder="Search products" class="w-full bg-transparent text-sm text-slate-900 outline-none placeholder:text-slate-500">
                    <button type="submit" class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-slate-900 text-white"><i class="fas fa-search"></i></button>
                </form>
                <?php if ($user): ?>
                    <a class="block rounded-full bg-slate-900 px-4 py-3 text-center text-sm font-medium text-white" href="<?= BASE_URL ?>/user/profile.php">Profile</a>
                <?php else: ?>
                    <a class="block rounded-full border border-slate-200 bg-white px-4 py-3 text-center text-sm font-medium text-slate-700" href="<?= BASE_URL ?>/login.php">Login</a>
                    <a class="block rounded-full bg-slate-900 px-4 py-3 text-center text-sm font-medium text-white" href="<?= BASE_URL ?>/register.php">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </header>
    <main class="py-6">