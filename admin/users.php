<?php
require_once __DIR__ . '/_header.php';
$users = $pdo->query('SELECT id, name, email, phone, role FROM users ORDER BY id DESC')->fetchAll();
?>
<div class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
    <h2 class="text-2xl font-semibold text-slate-900">Users</h2>
    <div class="mt-6 overflow-hidden rounded-[2rem] border border-slate-200">
        <table class="w-full border-separate border-spacing-0 text-left text-sm">
            <thead class="bg-slate-100 text-slate-600"><tr><th class="px-6 py-4">ID</th><th class="px-6 py-4">Name</th><th class="px-6 py-4">Email</th><th class="px-6 py-4">Phone</th><th class="px-6 py-4">Role</th></tr></thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr class="border-t border-slate-200 bg-white">
                        <td class="px-6 py-4"><?= $user['id'] ?></td>
                        <td class="px-6 py-4"><?= sanitize($user['name']) ?></td>
                        <td class="px-6 py-4"><?= sanitize($user['email']) ?></td>
                        <td class="px-6 py-4"><?= sanitize($user['phone']) ?></td>
                        <td class="px-6 py-4"><?= sanitize($user['role']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require_once __DIR__ . '/_footer.php'; ?>
