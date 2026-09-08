<?php
if (!defined('ABSPATH')) {
    exit;
}

add_action('init', 'fa_register_post_types');

function fa_register_post_types(): void
{
    register_post_type('fa_member', [
        'labels' => [
            'name' => 'أفراد العائلة',
            'singular_name' => 'فرد',
            'add_new' => 'إضافة فرد',
            'add_new_item' => 'إضافة فرد',
            'edit_item' => 'تعديل فرد',
            'new_item' => 'فرد جديد',
            'view_item' => 'عرض الفرد',
            'all_items' => 'كل الأفراد',
            'search_items' => 'بحث عن فرد',
            'not_found' => 'لا يوجد أفراد',
            'menu_name' => 'شجرة العائلة',
        ],
        'public' => false,
        'show_ui' => true,
        'show_in_menu' => true,
        'menu_icon' => 'dashicons-groups',
        'supports' => ['title', 'editor', 'thumbnail'],
        'capability_type' => ['fa_member', 'fa_members'],
        'map_meta_cap' => true,
        'has_archive' => false,
        'rewrite' => false,
        'show_in_rest' => false,
    ]);

    register_post_type('fa_event', [
        'labels' => [
            'name' => 'الأحداث الزمنية',
            'singular_name' => 'حدث',
            'add_new' => 'إضافة حدث',
            'add_new_item' => 'إضافة حدث',
            'edit_item' => 'تعديل حدث',
            'new_item' => 'حدث جديد',
            'view_item' => 'عرض الحدث',
            'all_items' => 'كل الأحداث',
            'search_items' => 'بحث عن حدث',
            'not_found' => 'لا يوجد أحداث',
            'menu_name' => 'الأحداث الزمنية',
        ],
        'public' => false,
        'show_ui' => true,
        'show_in_menu' => true,
        'menu_icon' => 'dashicons-clock',
        'supports' => ['title', 'editor'],
        'capability_type' => ['fa_event', 'fa_events'],
        'map_meta_cap' => true,
        'has_archive' => false,
        'rewrite' => false,
        'show_in_rest' => false,
    ]);

    register_post_type('fa_document', [
        'labels' => [
            'name' => 'الوثائق',
            'singular_name' => 'وثيقة',
            'add_new' => 'إضافة وثيقة',
            'add_new_item' => 'إضافة وثيقة',
            'edit_item' => 'تعديل وثيقة',
            'new_item' => 'وثيقة جديدة',
            'view_item' => 'عرض الوثيقة',
            'all_items' => 'كل الوثائق',
            'search_items' => 'بحث عن وثيقة',
            'not_found' => 'لا يوجد وثائق',
            'menu_name' => 'الوثائق',
        ],
        'public' => false,
        'show_ui' => true,
        'show_in_menu' => true,
        'menu_icon' => 'dashicons-media-document',
        'supports' => ['title', 'editor'],
        'capability_type' => ['fa_document', 'fa_documents'],
        'map_meta_cap' => true,
        'has_archive' => false,
        'rewrite' => false,
        'show_in_rest' => false,
    ]);

    register_post_type('fa_file', [
        'labels' => [
            'name' => 'الملفات',
            'singular_name' => 'ملف',
            'add_new' => 'إضافة ملف',
            'add_new_item' => 'إضافة ملف',
            'edit_item' => 'تعديل ملف',
            'new_item' => 'ملف جديد',
            'view_item' => 'عرض الملف',
            'all_items' => 'كل الملفات',
            'search_items' => 'بحث عن ملف',
            'not_found' => 'لا يوجد ملفات',
            'menu_name' => 'الملفات',
        ],
        'public' => false,
        'show_ui' => true,
        'show_in_menu' => true,
        'menu_icon' => 'dashicons-media-default',
        'supports' => ['title', 'editor'],
        'capability_type' => ['fa_file', 'fa_files'],
        'map_meta_cap' => true,
        'has_archive' => false,
        'rewrite' => false,
        'show_in_rest' => false,
    ]);
}
