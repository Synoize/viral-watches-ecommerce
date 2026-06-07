<?php
require_once __DIR__ . '/../includes/functions.php';
if (!isLoggedIn()) {
    redirect('/login.php');
}
$stmt = $pdo->prepare('SELECT id, name, email, phone, password FROM users WHERE id = ?');
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();
$success = null;
$error = null;

// Parse stored phone to extract country code and number
$phoneCountryCode = '91';
$phoneNumber = '';
if (!empty($user['phone'])) {
    if (preg_match('/^\+(\d{1,3})(\d+)$/', $user['phone'], $matches)) {
        $phoneCountryCode = $matches[1];
        $phoneNumber = $matches[2];
    } else {
        $phoneNumber = preg_replace('/\D/', '', $user['phone']);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $countryCode = $_POST['country_code'] ?? '91';
    $phone = $_POST['phone'] ?? '';
    $current = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    if (!$name || !$phone) {
        $error = 'Name and phone are required.';
    } else {
        $phoneValidation = validateInternationalPhone($countryCode, $phone);
        if (!empty($phoneValidation['error'])) {
            $error = $phoneValidation['error'];
        } elseif ($newPassword) {
            if (!password_verify($current, $user['password'])) {
                $error = 'Current password is incorrect.';
            } elseif ($newPassword !== $confirmPassword) {
                $error = 'New passwords do not match.';
            }
        }
        if (!$error) {
            $formattedPhone = $phoneValidation['formatted'];
            if ($newPassword) {
                $hash = password_hash($newPassword, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare('UPDATE users SET name = ?, phone = ?, password = ? WHERE id = ?');
                $stmt->execute([$name, $formattedPhone, $hash, $_SESSION['user_id']]);
            } else {
                $stmt = $pdo->prepare('UPDATE users SET name = ?, phone = ? WHERE id = ?');
                $stmt->execute([$name, $formattedPhone, $_SESSION['user_id']]);
            }
            $success = 'Profile updated successfully.';
            // Refresh user data
            $stmt = $pdo->prepare('SELECT id, name, email, phone, password FROM users WHERE id = ?');
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch();
            // Re-parse phone
            if (preg_match('/^\+(\d{1,3})(\d+)$/', $user['phone'], $matches)) {
                $phoneCountryCode = $matches[1];
                $phoneNumber = $matches[2];
            }
        }
    }
}
?>
<?php include __DIR__ . '/../includes/header.php'; ?>
<div class="mx-auto max-w-4xl px-4 py-10 sm:px-6 lg:px-8">
    <div class="rounded-[2rem] bg-white p-8 shadow-sm">
        <h1 class="text-3xl font-semibold text-slate-900">My Profile</h1>
        <form method="post" class="mt-8 grid gap-6">
            <div class="grid gap-6 sm:grid-cols-2">
                <label class="space-y-2 text-sm font-medium text-slate-700">Email<input name="email_display" readonly value="<?= sanitize($user['email']) ?>" class="mt-2 w-full rounded-3xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 outline-none" /></label>
                <label class="space-y-2 text-sm font-medium text-slate-700">Phone<input name="phone_display" readonly value="<?= sanitize(preg_replace('/^\+(\d{1,3})(\d+)$/', '+$1 $2', $user['phone'] ?? ('+' . $phoneCountryCode . $phoneNumber)) ) ?>" class="mt-2 w-full rounded-3xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 outline-none" /></label>
            </div>
            <div class="grid gap-6 sm:grid-cols-2">
                <label class="space-y-2 text-sm font-medium text-slate-700">Name<input name="name" value="<?= sanitize($user['name']) ?>" required class="mt-2 w-full rounded-3xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 outline-none focus:border-slate-900" /></label>
                <div class="space-y-2">
                    <label class="block text-sm font-medium text-slate-700">Phone</label>
                    <div class="flex gap-2">
                        <select name="country_code" class="rounded-3xl border border-slate-200 bg-slate-50 px-3 py-3 text-slate-900 outline-none focus:border-slate-900 w-24">
                                <?php foreach (getCountryCodes() as $code => $label): ?>
                                    <option value="<?= $code ?>" <?= $phoneCountryCode == $code ? 'selected' : '' ?>><?= sanitize($label) ?></option>
                                <?php endforeach; ?>
                        </select>
                        <input type="text" name="phone" value="<?= sanitize($phoneNumber) ?>" required placeholder="Phone number" class="flex-1 rounded-3xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 outline-none focus:border-slate-900" />
                    </div>
                </div>
            </div>
            <div class="pt-6 text-sm font-semibold text-slate-900">Change Password</div>
            <div class="grid gap-6 sm:grid-cols-2">
                <label class="space-y-2 text-sm font-medium text-slate-700">Current Password<input type="password" name="current_password" class="w-full rounded-3xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 outline-none focus:border-slate-900" /></label>
                <label class="space-y-2 text-sm font-medium text-slate-700">New Password<input type="password" name="new_password" class="w-full rounded-3xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 outline-none focus:border-slate-900" /></label>
            </div>
            <label class="space-y-2 text-sm font-medium text-slate-700">Confirm Password<input type="password" name="confirm_password" class="w-full rounded-3xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 outline-none focus:border-slate-900" /></label>
            <button class="inline-flex items-center justify-center rounded-3xl bg-slate-900 px-6 py-3 text-sm font-semibold text-white hover:bg-slate-800">Save Changes</button>
        </form>
    </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
