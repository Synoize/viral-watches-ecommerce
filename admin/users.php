<?php
require_once __DIR__ . '/_header.php';
$users = $pdo->query('SELECT id, name, email, phone, role FROM users ORDER BY id DESC')->fetchAll();
?>
<div class="card p-4 shadow-sm">
    <h4>Users</h4>
    <table class="table table-hover mt-3">
        <thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Phone</th><th>Role</th></tr></thead>
        <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= $user['id'] ?></td>
                    <td><?= sanitize($user['name']) ?></td>
                    <td><?= sanitize($user['email']) ?></td>
                    <td><?= sanitize($user['phone']) ?></td>
                    <td><?= sanitize($user['role']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php require_once __DIR__ . '/_footer.php'; ?>
