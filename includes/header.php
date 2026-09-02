<?php require_once __DIR__ . '/functions.php'; ?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= isset($pageTitle) ? e($pageTitle) . ' - ' . e(SITE_NAME) : e(SITE_NAME) ?></title>
<link rel="stylesheet" href="<?= isset($assetPrefix) ? $assetPrefix : '' ?>assets/css/style.css">
</head>
<body>
<header class="site-header">
    <div class="container header-inner">
        <a href="<?= isset($assetPrefix) ? $assetPrefix : '' ?>index.php" class="site-title"><?= e(SITE_NAME) ?></a>
        <nav>
            <a href="<?= isset($assetPrefix) ? $assetPrefix : '' ?>index.php">الشجرة</a>
        </nav>
    </div>
</header>
<main class="container">
