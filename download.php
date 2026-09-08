<?php
require_once __DIR__ . '/includes/auth.php';
require_login();

$type = $_GET['type'] ?? '';
$id = (int) ($_GET['id'] ?? 0);

if ($type === 'document') {
    $record = get_document($id);
    $dir = UPLOAD_DIR . '/documents';
    $storedName = $record['file'] ?? null;
    $displayName = $record['title'] ?? 'document';
} elseif ($type === 'file') {
    $record = get_file_record($id);
    $dir = UPLOAD_DIR . '/files';
    $storedName = $record['file'] ?? null;
    $displayName = $record['original_name'] ?? ($record['title'] ?? 'file');
} else {
    http_response_code(404);
    exit('غير موجود.');
}

if (!$record || !$storedName) {
    http_response_code(404);
    exit('الملف غير موجود.');
}

$path = $dir . '/' . $storedName;
if (!is_file($path)) {
    http_response_code(404);
    exit('الملف غير موجود على السيرفر.');
}

$ext = strtolower(pathinfo($storedName, PATHINFO_EXTENSION));
if ($ext && !str_contains($displayName, '.')) {
    $displayName .= '.' . $ext;
}

header('Content-Type: ' . (mime_content_type($path) ?: 'application/octet-stream'));
header('Content-Disposition: inline; filename="' . rawurlencode($displayName) . '"');
header('Content-Length: ' . filesize($path));
header('X-Content-Type-Options: nosniff');
readfile($path);
exit;
