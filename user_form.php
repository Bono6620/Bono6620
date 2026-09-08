<?php
require_once __DIR__ . '/includes/auth.php';
require_admin();

$id = (int) ($_GET['id'] ?? 0);
$user = null;
if ($id) {
    $stmt = get_db()->prepare('SELECT * FROM users WHERE id = ?');
    $stmt->execute([$id]);
    $user = $stmt->fetch() ?: null;
    if (!$user) {
        http_response_code(404);
        exit('المستخدم ده مش موجود.');
    }
}

$pageTitle = $user ? 'تعديل مستخدم' : 'إضافة مستخدم';
require __DIR__ . '/includes/header.php';
?>
<h1><?= $user ? 'تعديل ' . e($user['username']) : 'إضافة مستخدم جديد' ?></h1>

<form method="post" action="user_save.php" class="stacked-form">
    <?= csrf_field() ?>
    <input type="hidden" name="id" value="<?= (int) ($user['id'] ?? 0) ?>">

    <label>الاسم اللي هيظهر
        <input type="text" name="display_name" value="<?= e($user['display_name'] ?? '') ?>">
    </label>

    <label>اسم المستخدم (للدخول)
        <input type="text" name="username" required value="<?= e($user['username'] ?? '') ?>" <?= $user ? 'readonly' : '' ?>>
    </label>

    <label><?= $user ? 'كلمة سر جديدة (سيبها فاضية لو مش عايز تغيّرها)' : 'كلمة السر' ?>
        <input type="password" name="password" <?= $user ? '' : 'required' ?> minlength="8">
    </label>

    <label>الصلاحية
        <select name="role">
            <option value="viewer" <?= ($user['role'] ?? '') === 'viewer' ? 'selected' : '' ?>>مشاهدة فقط</option>
            <option value="editor" <?= ($user['role'] ?? '') === 'editor' ? 'selected' : '' ?>>محرر</option>
            <option value="admin" <?= ($user['role'] ?? '') === 'admin' ? 'selected' : '' ?>>مدير</option>
        </select>
    </label>

    <div class="form-actions">
        <button type="submit">حفظ</button>
        <a href="users.php" class="button secondary">إلغاء</a>
    </div>
</form>
<?php require __DIR__ . '/includes/footer.php'; ?>
