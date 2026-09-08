<?php
if (!defined('ABSPATH')) {
    exit;
}

function fa_page_definitions(): array
{
    return [
        'family-tree' => ['title' => 'الشجرة', 'shortcode' => '[fa_tree]'],
        'family-files' => ['title' => 'الملفات', 'shortcode' => '[fa_files]'],
        'family-timeline' => ['title' => 'الأحداث الزمنية', 'shortcode' => '[fa_timeline]'],
        'family-documents' => ['title' => 'الوثائق', 'shortcode' => '[fa_documents]'],
        'family-account' => ['title' => 'حسابي', 'shortcode' => '[fa_account]'],
        'family-admin' => ['title' => 'لوحة التحكم', 'shortcode' => '[fa_admin]'],
    ];
}

function fa_ensure_pages_exist(): int
{
    $treePageId = 0;
    foreach (fa_page_definitions() as $slug => $data) {
        $existing = get_page_by_path($slug);
        if ($existing) {
            if ($slug === 'family-tree') {
                $treePageId = $existing->ID;
            }
            continue;
        }
        $pageId = wp_insert_post([
            'post_title' => $data['title'],
            'post_name' => $slug,
            'post_content' => $data['shortcode'],
            'post_status' => 'publish',
            'post_type' => 'page',
            'comment_status' => 'closed',
            'ping_status' => 'closed',
        ]);
        if ($slug === 'family-tree' && $pageId && !is_wp_error($pageId)) {
            $treePageId = $pageId;
        }
    }
    return $treePageId;
}

function fa_activate(): void
{
    fa_register_post_types();
    fa_register_roles();

    fa_private_dir('documents');
    fa_private_dir('files');

    $treePageId = fa_ensure_pages_exist();

    if ($treePageId) {
        update_option('show_on_front', 'page');
        update_option('page_on_front', $treePageId);
    }

    if (!get_option('permalink_structure')) {
        update_option('permalink_structure', '/%postname%/');
    }

    update_option('fa_pages_version', FA_VERSION);

    flush_rewrite_rules();
}

add_action('init', 'fa_maybe_upgrade_pages', 2);

function fa_maybe_upgrade_pages(): void
{
    if (get_option('fa_pages_version') === FA_VERSION) {
        return;
    }
    fa_ensure_pages_exist();
    update_option('fa_pages_version', FA_VERSION);
    flush_rewrite_rules();
}

function fa_deactivate(): void
{
    flush_rewrite_rules();
}
