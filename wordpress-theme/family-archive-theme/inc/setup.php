<?php
if (!defined('ABSPATH')) {
    exit;
}

function fa_page_definitions(): array
{
    return [
        'family-tree' => ['title' => 'الشجرة', 'template' => 'page-templates/template-tree.php'],
        'family-files' => ['title' => 'الملفات', 'template' => 'page-templates/template-files.php'],
        'family-timeline' => ['title' => 'الأحداث الزمنية', 'template' => 'page-templates/template-timeline.php'],
        'family-documents' => ['title' => 'الوثائق', 'template' => 'page-templates/template-documents.php'],
        'family-account' => ['title' => 'حسابي', 'template' => 'page-templates/template-account.php'],
        'family-admin' => ['title' => 'لوحة التحكم', 'template' => 'page-templates/template-admin.php'],
    ];
}

function fa_ensure_pages_exist(): int
{
    $treePageId = 0;
    foreach (fa_page_definitions() as $slug => $data) {
        $existing = get_page_by_path($slug);
        if ($existing) {
            update_post_meta($existing->ID, '_wp_page_template', $data['template']);
            if ($slug === 'family-tree') {
                $treePageId = $existing->ID;
            }
            continue;
        }
        $pageId = wp_insert_post([
            'post_title' => $data['title'],
            'post_name' => $slug,
            'post_status' => 'publish',
            'post_type' => 'page',
            'comment_status' => 'closed',
            'ping_status' => 'closed',
        ]);
        if ($pageId && !is_wp_error($pageId)) {
            update_post_meta($pageId, '_wp_page_template', $data['template']);
            if ($slug === 'family-tree') {
                $treePageId = $pageId;
            }
        }
    }
    return $treePageId;
}

add_action('after_switch_theme', 'fa_theme_activate');

function fa_theme_activate(): void
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
