<?php
require_once __DIR__ . '/../includes/functions.php';

if (is_admin_logged_in()) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = get_db()->prepare('SELECT * FROM admins WHERE username = ?');
    $stmt->execute([$username]);
    $admin = $stmt->fetch();

    if ($admin && password_verify($password, $admin['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_username'] = $admin['username'];
        header('Location: index.php');
        exit;
    }

    $error = 'اسم المستخدم أو كلمة السر غلط.';
}

$pageTitle = 'تسجيل دخول الأدمن';
$assetPrefix = '../';
require __DIR__ . '/../includes/header.php';
?>
<section class="auth-box">
    <h1>تسجيل دخول الأدمن</h1>
    <?php if ($error): ?>
        <p class="alert"><?= e($error) ?></p>
    <?php endif; ?>
    <form method="post" class="stacked-form">
        <?= csrf_field() ?>
        <label>اسم المستخدم
            <input type="text" name="username" required autofocus>
        </label>
        <label>كلمة السر
            <input type="password" name="password" required>
        </label>
        <button type="submit">دخول</button>
    </form>
</section>
<?php require __DIR__ . '/../includes/footer.php'; ?>
