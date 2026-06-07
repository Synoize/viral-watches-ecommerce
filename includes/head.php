<?php
$pageMeta = getPageMeta($_SERVER['REQUEST_URI'] ?? '/', $pageMetaOverrides ?? []);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= sanitize($pageMeta['title']) ?></title>
    <meta name="description" content="<?= sanitize($pageMeta['description']) ?>">
    <?php if (!empty($pageMeta['keywords'])): ?>
        <meta name="keywords" content="<?= sanitize($pageMeta['keywords']) ?>">
    <?php endif; ?>
    <meta property="og:title" content="<?= sanitize($pageMeta['title']) ?>">
    <meta property="og:description" content="<?= sanitize($pageMeta['description']) ?>">

    <link rel="stylesheet" href="<?= (defined('BASE_URL') ? BASE_URL : '') ?>/assets/css/style.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=PT+Serif:wght@400;700&display=swap" rel="stylesheet" />

    <link
        href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500;600;700&family=Inter:wght@300;400;500;600&display=swap"
        rel="stylesheet" />

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />

    <!-- SWIPER JS -->
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

    <!-- Lucide Icons CDN -->
    <script src="https://unpkg.com/lucide@latest"></script>
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
                        primary: '#fff',
                        secondary: '#f5f5f3',
                    },
                    keyframes: {
                        marquee: {
                            '0%': {
                                transform: 'translateX(0)'
                            },
                            '100%': {
                                transform: 'translateX(-50%)'
                            },
                        }
                    },
                    animation: {
                        marquee: 'marquee 20s linear infinite',
                    }
                },
            },
        }
    </script>
</head>
