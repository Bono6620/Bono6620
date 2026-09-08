<?php
require_once __DIR__ . '/auth.php';
$__user = current_user();
$__activeTab = $activeTab ?? '';
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= isset($pageTitle) ? e($pageTitle) . ' - ' . e(SITE_NAME) : e(SITE_NAME) ?></title>
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<header class="site-header">
    <div class="container header-inner">
        <a href="index.php" class="site-title"><?= e(SITE_NAME) ?></a>
        <?php if ($__user): ?>
        <nav class="main-tabs">
            <a href="tree.php" class="<?= $__activeTab === 'tree' ? 'is-active' : '' ?>">الشجرة</a>
            <a href="files.php" class="<?= $__activeTab === 'files' ? 'is-active' : '' ?>">الملفات</a>
            <a href="timeline.php" class="<?= $__activeTab === 'timeline' ? 'is-active' : '' ?>">الأحداث الزمنية</a>
            <a href="documents.php" class="<?= $__activeTab === 'documents' ? 'is-active' : '' ?>">الوثائق</a>
        </nav>
        <div class="user-menu">
            <span class="user-name"><?= e($__user['display_name'] ?: $__user['username']) ?></span>
            <span class="role-badge role-<?= e($__user['role']) ?>"><?= e(role_label($__user['role'])) ?></span>
            <div class="user-menu-dropdown">
                <a href="change_password.php">تغيير كلمة السر</a>
                <?php if (is_admin()): ?>
                    <a href="users.php">إدارة المستخدمين</a>
                <?php endif; ?>
                <a href="logout.php">تسجيل خروج</a>
            </div>
        </div>
        <?php endif; ?>
    </div>
</header>
<main class="container">
