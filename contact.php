<?php
require_once __DIR__ . '/includes/functions.php';
$success = null;
$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
    $phone = preg_replace('/\D/', '', $_POST['phone'] ?? '');
    $message = sanitize($_POST['message'] ?? '');
    if (!$name || !$email || !$phone || !$message) {
        $error = 'Please fill in all fields.';
    } else {
        $stmt = $pdo->prepare('INSERT INTO contact_messages (name, email, phone, message, created_at) VALUES (?, ?, ?, ?, NOW())');
        $stmt->execute([$name, $email, $phone, $message]);
        $success = 'Your message has been sent. Our support team will contact you soon.';
    }
}
?>
<?php include __DIR__ . '/includes/header.php'; ?>
<div class="container mt-5"><div class="row">
    <div class="col-lg-6 mb-4">
        <div class="card shadow-sm p-4">
            <h3>Contact Support</h3>
            <p>Still have questions? Send us a message and we will respond within 24 hours.</p>
            <?php if ($success): ?><div class="alert alert-success"><?= sanitize($success) ?></div><?php endif; ?>
            <?php if ($error): ?><div class="alert alert-danger"><?= sanitize($error) ?></div><?php endif; ?>
            <form method="post">
                <div class="mb-3"><label class="form-label">Name</label><input type="text" name="name" class="form-control" required></div>
                <div class="mb-3"><label class="form-label">Email</label><input type="email" name="email" class="form-control" required></div>
                <div class="mb-3"><label class="form-label">Phone</label><input type="text" name="phone" class="form-control" required pattern="\d{10}"></div>
                <div class="mb-3"><label class="form-label">Message</label><textarea name="message" class="form-control" rows="5" required></textarea></div>
                <button class="btn btn-primary">Send Message</button>
            </form>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card shadow-sm p-4">
            <h3>Help Center</h3>
            <p><strong>Email:</strong> support@shopmaster.com</p>
            <p><strong>Phone:</strong> +91 98765 43210</p>
            <p><strong>Address:</strong> 123 Commerce Street, Mumbai, India</p>
        </div>
    </div>
</div></div>
<?php include __DIR__ . '/includes/footer.php'; ?>
