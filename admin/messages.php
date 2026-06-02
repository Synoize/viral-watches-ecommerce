<?php
require_once __DIR__ . '/_header.php';
$messages = $pdo->query('SELECT * FROM contact_messages ORDER BY created_at DESC')->fetchAll();
?>
<div class="card p-4 shadow-sm">
    <h4>Contact Messages</h4>
    <table class="table table-hover mt-3">
        <thead><tr><th>Name</th><th>Email</th><th>Phone</th><th>Message</th><th>Received</th></tr></thead>
        <tbody>
            <?php foreach ($messages as $message): ?>
                <tr>
                    <td><?= sanitize($message['name']) ?></td>
                    <td><?= sanitize($message['email']) ?></td>
                    <td><?= sanitize($message['phone']) ?></td>
                    <td><?= nl2br(sanitize($message['message'])) ?></td>
                    <td><?= date('j M Y', strtotime($message['created_at'])) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php require_once __DIR__ . '/_footer.php'; ?>
