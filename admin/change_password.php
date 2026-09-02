<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $current = $_POST['current_password'] ?? '';
    $new = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    $stmt = get_db()->prepare('SELECT * FROM admins WHERE id = ?');
    $stmt->execute([$_SESSION['admin_id']]);
    $admin = $stmt->fetch();

    if (!$admin || !password_verify($current, $admin['password_hash'])) {
        $error = 'كلمة السر الحالية غلط.';
    } elseif (strlen($new) < 8) {
        $error = 'كلمة السر الجديدة لازم تكون 8 حروف على الأقل.';
    } elseif ($new !== $confirm) {
        $error = 'كلمة السر الجديدة والتأكيد مش متطابقين.';
    } else {
        $hash = password_hash($new, PASSWORD_BCRYPT);
        get_db()->prepare('UPDATE admins SET password_hash = ? WHERE id = ?')->execute([$hash, $admin['id']]);
        $success = 'تم تغيير كلمة السر بنجاح.';
    }
}

$pageTitle = 'تغيير كلمة السر';
$assetPrefix = '../';
require __DIR__ . '/../includes/header.php';
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
        <a href="index.php" class="button secondary">رجوع</a>
    </form>
</section>
<?php require __DIR__ . '/../includes/footer.php'; ?>
