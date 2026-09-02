<?php
if (!defined('ABSPATH')) {
    exit;
}

function fa_activate(): void
{
    fa_register_post_types();
    fa_register_roles();

    fa_private_dir('documents');
    fa_private_dir('files');

    $pages = [
        'family-tree' => ['title' => 'الشجرة', 'shortcode' => '[fa_tree]'],
        'family-files' => ['title' => 'الملفات', 'shortcode' => '[fa_files]'],
        'family-timeline' => ['title' => 'الأحداث الزمنية', 'shortcode' => '[fa_timeline]'],
        'family-documents' => ['title' => 'الوثائق', 'shortcode' => '[fa_documents]'],
    ];

    $treePageId = 0;
    foreach ($pages as $slug => $data) {
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

    if ($treePageId) {
        update_option('show_on_front', 'page');
        update_option('page_on_front', $treePageId);
    }

    if (!get_option('permalink_structure')) {
        update_option('permalink_structure', '/%postname%/');
    }

    flush_rewrite_rules();
}

function fa_deactivate(): void
{
    flush_rewrite_rules();
}
