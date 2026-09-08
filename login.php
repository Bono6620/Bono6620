<?php
require_once __DIR__ . '/includes/auth.php';

if (current_user()) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $user = attempt_login($username, $password);
    if ($user) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        header('Location: index.php');
        exit;
    }

    $error = 'اسم المستخدم أو كلمة السر غلط.';
}

$pageTitle = 'تسجيل الدخول';
require __DIR__ . '/includes/header.php';
?>
<section class="auth-box centered-box">
    <h1><?= e(SITE_NAME) ?></h1>
    <p class="muted">سجّل دخولك عشان تشوف شجرة العائلة.</p>
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
<?php require __DIR__ . '/includes/footer.php'; ?>
