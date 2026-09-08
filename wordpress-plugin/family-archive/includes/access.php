<?php
if (!defined('ABSPATH')) {
    exit;
}

function fa_current_url(): string
{
    return home_url(add_query_arg(null, null));
}

function fa_is_download_request(): bool
{
    return isset($_GET['fa_download_type'], $_GET['fa_download_id']);
}

add_action('template_redirect', 'fa_handle_download', 5);

function fa_handle_download(): void
{
    if (!fa_is_download_request()) {
        return;
    }

    if (!is_user_logged_in()) {
        wp_safe_redirect(wp_login_url(fa_current_url()));
        exit;
    }

    $type = sanitize_key($_GET['fa_download_type']);
    $id = absint($_GET['fa_download_id']);
    $sub = $type === 'document' ? 'documents' : ($type === 'file' ? 'files' : '');
    $postType = $type === 'document' ? 'fa_document' : ($type === 'file' ? 'fa_file' : '');

    if ($sub === '' || $postType === '') {
        status_header(404);
        exit('غير موجود.');
    }

    $post = get_post($id);
    if (!$post || $post->post_type !== $postType) {
        status_header(404);
        exit('غير موجود.');
    }

    if (!current_user_can('read_' . $postType, $id)) {
        status_header(403);
        exit('مالكش صلاحية تشوف الملف ده.');
    }

    $filename = get_post_meta($id, '_fa_file', true);
    if (!$filename) {
        status_header(404);
        exit('لا يوجد ملف مرفق.');
    }

    $path = fa_private_dir($sub) . '/' . $filename;
    if (!is_file($path)) {
        status_header(404);
        exit('الملف مش موجود على السيرفر.');
    }

    $original = get_post_meta($id, '_fa_file_original', true) ?: $post->post_title;
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    if ($ext && !str_contains($original, '.')) {
        $original .= '.' . $ext;
    }

    header('Content-Type: ' . (mime_content_type($path) ?: 'application/octet-stream'));
    header('Content-Disposition: inline; filename="' . rawurlencode($original) . '"');
    header('Content-Length: ' . filesize($path));
    header('X-Content-Type-Options: nosniff');
    readfile($path);
    exit;
}

add_action('template_redirect', 'fa_require_login_site_wide', 6);

function fa_require_login_site_wide(): void
{
    if (is_user_logged_in()) {
        return;
    }
    wp_safe_redirect(wp_login_url(fa_current_url()));
    exit;
}

add_action('admin_init', 'fa_redirect_non_editors_from_admin');

function fa_redirect_non_editors_from_admin(): void
{
    if (wp_doing_ajax()) {
        return;
    }
    if (fa_user_can_edit()) {
        return;
    }
    global $pagenow;
    if (in_array($pagenow, ['profile.php', 'admin-ajax.php', 'async-upload.php'], true)) {
        return;
    }
    wp_safe_redirect(home_url('/family-tree/'));
    exit;
}

add_filter('login_redirect', 'fa_login_redirect', 10, 3);

function fa_login_redirect(string $redirectTo, string $requested, $user): string
{
    if ($redirectTo && $redirectTo !== admin_url()) {
        return $redirectTo;
    }
    if (is_object($user) && !is_wp_error($user)) {
        if (in_array('family_viewer', (array) $user->roles, true)) {
            return home_url('/family-tree/');
        }
    }
    return $redirectTo ?: home_url('/family-tree/');
}
