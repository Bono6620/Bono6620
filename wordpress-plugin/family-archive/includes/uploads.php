<?php
if (!defined('ABSPATH')) {
    exit;
}

const FA_DOCUMENT_MIME_MAP = [
    'image/jpeg' => 'jpg',
    'image/png' => 'png',
    'image/webp' => 'webp',
    'application/pdf' => 'pdf',
];

const FA_FILE_MIME_MAP = [
    'image/jpeg' => 'jpg',
    'image/png' => 'png',
    'image/webp' => 'webp',
    'image/gif' => 'gif',
    'application/pdf' => 'pdf',
    'application/msword' => 'doc',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
    'application/vnd.ms-excel' => 'xls',
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
    'application/zip' => 'zip',
    'text/plain' => 'txt',
];

function fa_private_dir(string $sub): string
{
    $dir = FA_PRIVATE_DIR . '/' . $sub;
    if (!is_dir($dir)) {
        wp_mkdir_p($dir);
    }
    fa_protect_dir($dir);
    return $dir;
}

function fa_protect_dir(string $dir): void
{
    $htaccess = $dir . '/.htaccess';
    if (!file_exists($htaccess)) {
        file_put_contents(
            $htaccess,
            "<IfModule mod_authz_core.c>\n    Require all denied\n</IfModule>\n<IfModule !mod_authz_core.c>\n    Order deny,allow\n    Deny from all\n</IfModule>\n"
        );
    }
    $indexFile = $dir . '/index.php';
    if (!file_exists($indexFile)) {
        file_put_contents($indexFile, "<?php\n// Silence is golden.\n");
    }
}

function fa_handle_secure_upload(int $postId, string $sub, array $mimeMap, string $fieldName = 'fa_upload_file'): void
{
    if (empty($_FILES[$fieldName]) || $_FILES[$fieldName]['error'] === UPLOAD_ERR_NO_FILE) {
        return;
    }

    $file = $_FILES[$fieldName];
    if ($file['error'] !== UPLOAD_ERR_OK || $file['size'] > FA_MAX_UPLOAD_BYTES) {
        return;
    }

    $mime = mime_content_type($file['tmp_name']);
    if (!isset($mimeMap[$mime])) {
        return;
    }

    $dir = fa_private_dir($sub);
    $filename = bin2hex(random_bytes(16)) . '.' . $mimeMap[$mime];
    $destination = $dir . '/' . $filename;
    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        return;
    }

    $old = get_post_meta($postId, '_fa_file', true);
    if ($old) {
        $oldPath = $dir . '/' . $old;
        if (is_file($oldPath)) {
            @unlink($oldPath);
        }
    }

    update_post_meta($postId, '_fa_file', $filename);
    update_post_meta($postId, '_fa_file_original', sanitize_file_name($file['name']));
    update_post_meta($postId, '_fa_file_size', (int) $file['size']);
}

function fa_delete_secure_upload(int $postId, string $sub): void
{
    $filename = get_post_meta($postId, '_fa_file', true);
    if ($filename) {
        $path = fa_private_dir($sub) . '/' . $filename;
        if (is_file($path)) {
            @unlink($path);
        }
    }
}

function fa_format_bytes(int $bytes): string
{
    if ($bytes >= 1024 * 1024) {
        return round($bytes / (1024 * 1024), 1) . ' ميجا';
    }
    return round($bytes / 1024, 1) . ' كيلو';
}
