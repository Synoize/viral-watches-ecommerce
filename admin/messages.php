<?php
require_once __DIR__ . '/_header.php';
$messages = $pdo->query('SELECT * FROM contact_messages ORDER BY created_at DESC')->fetchAll();
?>
<div class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
    <h2 class="text-2xl font-semibold text-slate-900">Contact Messages</h2>
    <div class="mt-6 overflow-hidden rounded-[2rem] border border-slate-200">
        <table class="w-full border-separate border-spacing-0 text-left text-sm">
            <thead class="bg-slate-100 text-slate-600"><tr><th class="px-6 py-4">Name</th><th class="px-6 py-4">Email</th><th class="px-6 py-4">Phone</th><th class="px-6 py-4">Message</th><th class="px-6 py-4">Received</th></tr></thead>
            <tbody>
                <?php foreach ($messages as $message): ?>
                    <tr class="border-t border-slate-200 bg-white">
                        <td class="px-6 py-4"><?= sanitize($message['name']) ?></td>
                        <td class="px-6 py-4"><?= sanitize($message['email']) ?></td>
                        <td class="px-6 py-4"><?= sanitize($message['phone']) ?></td>
                        <td class="px-6 py-4"><?= nl2br(sanitize($message['message'])) ?></td>
                        <td class="px-6 py-4"><?= date('j M Y', strtotime($message['created_at'])) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require_once __DIR__ . '/_footer.php'; ?>
