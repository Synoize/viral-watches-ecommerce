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
<div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
    <div class="grid gap-10 lg:grid-cols-2">
        <div class="rounded-3xl bg-white p-8 shadow-sm">
            <h2 class="text-3xl font-semibold text-slate-900">Contact Support</h2>
            <p class="mt-3 text-slate-600">Send us a message and we will respond within 24 hours.</p>
            <form method="post" class="mt-8 space-y-4">
                <div><label class="block text-sm font-medium text-slate-700">Name</label><input type="text" name="name" required class="mt-2 w-full rounded-3xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 outline-none focus:border-slate-900" /></div>
                <div><label class="block text-sm font-medium text-slate-700">Email</label><input type="email" name="email" required class="mt-2 w-full rounded-3xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 outline-none focus:border-slate-900" /></div>
                <div><label class="block text-sm font-medium text-slate-700">Phone</label><input type="text" name="phone" required pattern="\d{10}" class="mt-2 w-full rounded-3xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 outline-none focus:border-slate-900" /></div>
                <div><label class="block text-sm font-medium text-slate-700">Message</label><textarea name="message" rows="5" required class="mt-2 w-full rounded-3xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 outline-none focus:border-slate-900"></textarea></div>
                <button type="submit" class="inline-flex w-full items-center justify-center rounded-3xl bg-slate-900 px-6 py-3 text-sm font-semibold text-white hover:bg-slate-800">Send Message</button>
            </form>
        </div>
        <div class="space-y-6 rounded-3xl bg-white p-8 shadow-sm">
            <div><h3 class="text-2xl font-semibold text-slate-900">Help Center</h3><p class="mt-3 text-slate-600"><strong>Email:</strong> support@shopmaster.com<br /><strong>Phone:</strong> +91 98765 43210<br /><strong>Address:</strong> 123 Commerce Street, Mumbai, India</p></div>
            <div class="rounded-3xl bg-slate-50 p-6 text-slate-600">
                <h4 class="text-lg font-semibold text-slate-900">Support highlights</h4>
                <ul class="mt-4 space-y-3 text-sm">
                    <li>Fast response for order inquiries.</li>
                    <li>Secure payment and refund support.</li>
                    <li>Easy order tracking and returns.</li>
                </ul>
            </div>
        </div>
    </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
