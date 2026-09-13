<?php
/**
 * عرض / تحميل ملف مرفوع — للأدمن بس.
 * الملفات نفسها محميّة بـ .htaccess، فمحدش يقدر يوصلها غير من هنا.
 */

require_once __DIR__ . '/lib.php';

if (!s_is_logged_in() || !s_is_admin()) {
    http_response_code(403);
    header('Content-Type: text/plain; charset=UTF-8');
    echo 'غير مسموح';
    exit;
}

$slug = isset($_GET['b']) ? preg_replace('/[^a-z0-9_-]/', '', strtolower($_GET['b'])) : '';
$file = isset($_GET['f']) ? basename((string) $_GET['f']) : '';

if (!s_branch($slug) || $file === '' || !preg_match('/^[0-9]{8}-[0-9]{6}-[a-f0-9]{16}\.[a-z0-9]{2,5}$/', $file)) {
    http_response_code(404);
    echo 'الملف مش موجود';
    exit;
}

$path = SUBMIT_FILES_DIR . '/' . $slug . '/' . $file;
$real = realpath($path);
$base = realpath(SUBMIT_FILES_DIR);

// التأكد إن الملف فعلاً جوه فولدر التخزين.
if (!$real || !$base || strpos($real, $base . DIRECTORY_SEPARATOR) !== 0 || !is_file($real)) {
    http_response_code(404);
    echo 'الملف مش موجود';
    exit;
}

// الاسم الأصلي عشان يبان وقت التحميل.
$original = $file;
foreach (s_load_submissions() as $item) {
    if (!isset($item['branch']) || $item['branch'] !== $slug) {
        continue;
    }
    foreach ($item['files'] as $f) {
        if ($f['stored'] === $file) {
            $original = $f['original'] !== '' ? $f['original'] : $file;
            break 2;
        }
    }
}

$ext = strtolower(pathinfo($real, PATHINFO_EXTENSION));
$types = array(
    'jpg'  => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'png'  => 'image/png',
    'webp' => 'image/webp',
    'gif'  => 'image/gif',
    'pdf'  => 'application/pdf',
);
$mime = isset($types[$ext]) ? $types[$ext] : 'application/octet-stream';

$download = isset($_GET['dl']) && $_GET['dl'] === '1';
$disposition = $download ? 'attachment' : 'inline';

// اسم ASCII آمن للهيدر + الاسم العربي الأصلي بصيغة UTF-8.
$asciiName = preg_replace('/[^A-Za-z0-9._-]/', '_', $original);
if ($asciiName === '' || $asciiName === '.' ) {
    $asciiName = 'document.' . $ext;
}

header('Content-Type: ' . $mime);
header('Content-Length: ' . filesize($real));
header('Content-Disposition: ' . $disposition . '; filename="' . $asciiName . '"; filename*=UTF-8\'\'' . rawurlencode($original));
header('X-Content-Type-Options: nosniff');
header('Content-Security-Policy: default-src \'none\'; img-src \'self\'; object-src \'none\'');
header('Cache-Control: private, max-age=0, no-store');

readfile($real);
