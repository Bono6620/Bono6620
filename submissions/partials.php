<?php
/** الهيدر والفوتر المشتركين بين صفحات الوحدة. */

require_once __DIR__ . '/lib.php';

/**
 * @param string $title  عنوان الصفحة
 * @param array  $opts   subtitle / back_url / back_label / admin (bool)
 */
function s_header($title, $opts = array())
{
    $subtitle   = isset($opts['subtitle']) ? $opts['subtitle'] : 'تسليم الورق المطلوب';
    $backUrl    = isset($opts['back_url']) ? $opts['back_url'] : SUBMIT_LOGIN_URL;
    $backLabel  = isset($opts['back_label']) ? $opts['back_label'] : 'رجوع';
    ?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?php echo s_e($title); ?> — <?php echo s_e(SUBMIT_SITE_TITLE); ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?php echo s_e(SUBMIT_SITE_CSS); ?>">
<link rel="stylesheet" href="assets/submit.css?v=1">
</head>
<body>
<header class="site-header">
    <div class="header-inner">
        <div class="brand">
            <div class="brand-emblem">خ</div>
            <div>
                <h1><?php echo s_e(SUBMIT_SITE_TITLE); ?></h1>
                <p><?php echo s_e($subtitle); ?></p>
            </div>
        </div>
        <div class="header-actions">
            <a class="btn-logout" href="<?php echo s_e($backUrl); ?>"><?php echo s_e($backLabel); ?></a>
        </div>
    </div>
</header>
<main>
    <?php
}

function s_footer()
{
    ?>
</main>
<footer class="site-footer">
    <p>شجرة عائلة الخربوطلي — قسم تسليم الورق المطلوب</p>
</footer>
</body>
</html>
    <?php
}

/** رسالة نجاح / خطأ. */
function s_flash($type, $message)
{
    $class = ($type === 'error') ? 'flash-error' : 'flash-success';
    echo '<div class="' . $class . '">' . s_e($message) . '</div>';
}
