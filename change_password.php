<?php
require_once __DIR__ . '/includes/auth.php';
require_login();

$error = '';
$success = '';
$user = current_user();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $current = $_POST['current_password'] ?? '';
    $new = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if (!password_verify($current, $user['password_hash'])) {
        $error = 'كلمة السر الحالية غلط.';
    } elseif (strlen($new) < 8) {
        $error = 'كلمة السر الجديدة لازم تكون 8 حروف على الأقل.';
    } elseif ($new !== $confirm) {
        $error = 'كلمة السر الجديدة والتأكيد مش متطابقين.';
    } else {
        $hash = password_hash($new, PASSWORD_BCRYPT);
        get_db()->prepare('UPDATE users SET password_hash = ? WHERE id = ?')->execute([$hash, $user['id']]);
        $success = 'تم تغيير كلمة السر بنجاح.';
    }
}

$pageTitle = 'تغيير كلمة السر';
require __DIR__ . '/includes/header.php';
?>
<section class="auth-box">
    <h1>تغيير كلمة السر</h1>
    <?php if ($error): ?><p class="alert"><?= e($error) ?></p><?php endif; ?>
    <?php if ($success): ?><p class="alert alert-success"><?= e($success) ?></p><?php endif; ?>
    <form method="post" class="stacked-form">
        <?= csrf_field() ?>
        <label>كلمة السر الحالية
            <input type="password" name="current_password" required>
        </label>
        <label>كلمة السر الجديدة
            <input type="password" name="new_password" required minlength="8">
        </label>
        <label>تأكيد كلمة السر الجديدة
            <input type="password" name="confirm_password" required minlength="8">
        </label>
        <button type="submit">حفظ</button>
        <a href="tree.php" class="button secondary">رجوع</a>
    </form>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
