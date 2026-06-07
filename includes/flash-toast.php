<?php
$toastMessages = [];
$toastTypes = [
    'success' => [
        'title' => 'Success',
        'icon' => 'check',
        'border' => 'border-emerald-200',
        'iconBg' => 'bg-emerald-100',
        'iconText' => 'text-emerald-600',
        'bar' => 'bg-emerald-500',
    ],
    'error' => [
        'title' => 'Error',
        'icon' => 'circle-x',
        'border' => 'border-red-200',
        'iconBg' => 'bg-red-100',
        'iconText' => 'text-red-600',
        'bar' => 'bg-red-500',
    ],
    'warning' => [
        'title' => 'Warning',
        'icon' => 'triangle-alert',
        'border' => 'border-amber-200',
        'iconBg' => 'bg-amber-100',
        'iconText' => 'text-amber-600',
        'bar' => 'bg-amber-500',
    ],
];

foreach (array_keys($toastTypes) as $type) {
    $message = flash($type);
    if ($message) {
        $toastMessages[] = ['type' => $type, 'message' => $message];
    }
}

if (!empty($error)) {
    $toastMessages[] = ['type' => 'error', 'message' => $error];
}

if (!empty($warning)) {
    $toastMessages[] = ['type' => 'warning', 'message' => $warning];
}

if (!empty($success)) {
    $toastMessages[] = ['type' => 'success', 'message' => $success];
}
?>

<?php if ($toastMessages): ?>
    <div class="fixed right-4 top-4 z-[9999] flex w-[calc(100%-2rem)] max-w-sm flex-col gap-3 sm:right-5 sm:top-5 sm:w-full">
        <?php foreach ($toastMessages as $index => $toast): ?>
            <?php $style = $toastTypes[$toast['type']] ?? $toastTypes['success']; ?>
            <div
                data-toast
                class="relative flex items-start gap-3 overflow-hidden rounded-2xl border <?= $style['border'] ?> bg-white px-5 py-4 shadow-2xl transition duration-300"
                style="animation: toast-in 220ms ease-out both; animation-delay: <?= (int)$index * 80 ?>ms;">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full <?= $style['iconBg'] ?>">
                    <i data-lucide="<?= sanitize($style['icon']) ?>" class="h-5 w-5 <?= $style['iconText'] ?> stroke-[1]"></i>
                </div>
                <div class="min-w-0 flex-1 pr-2">
                    <p class="text-sm font-semibold text-slate-900"><?= sanitize($style['title']) ?></p>
                    <p class="mt-0.5 break-words text-sm leading-5 text-slate-600"><?= sanitize($toast['message']) ?></p>
                </div>
                <button type="button" data-toast-close class="shrink-0 text-slate-400 transition hover:text-slate-700" aria-label="Close notification">
                    <i data-lucide="x" class="h-4 w-4 stroke-[1]"></i>
                </button>
            </div>
        <?php endforeach; ?>
    </div>
    <style>
        @keyframes toast-in {
            from {
                opacity: 0;
                transform: translateY(-8px) scale(0.98);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }
    </style>
    <script>
        function closeToast(toast) {
            if (!toast) return;
            toast.classList.add('opacity-0', 'translate-y-[-8px]');
            setTimeout(function () {
                toast.remove();
            }, 250);
        }

        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('[data-toast]').forEach(function (toast) {
                toast.querySelector('[data-toast-close]')?.addEventListener('click', function () {
                    closeToast(toast);
                });
                setTimeout(function () {
                    closeToast(toast);
                }, 5000);
            });
        });
    </script>
<?php endif; ?>
