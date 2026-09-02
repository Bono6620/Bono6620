<?php
if (!defined('ABSPATH')) {
    exit;
}

add_action('init', function () {
    add_shortcode('fa_tree', 'fa_shortcode_tree');
    add_shortcode('fa_files', 'fa_shortcode_files');
    add_shortcode('fa_timeline', 'fa_shortcode_timeline');
    add_shortcode('fa_documents', 'fa_shortcode_documents');
});

const FA_TAB_SLUGS = ['family-tree', 'family-files', 'family-timeline', 'family-documents'];

add_action('wp_enqueue_scripts', 'fa_maybe_enqueue_assets');

function fa_maybe_enqueue_assets(): void
{
    if (!is_page(FA_TAB_SLUGS)) {
        return;
    }
    wp_enqueue_style('fa-style', FA_URL . 'assets/css/style.css', [], FA_VERSION);
    wp_enqueue_script('fa-script', FA_URL . 'assets/js/main.js', [], FA_VERSION, true);
}

add_filter('template_include', 'fa_template_override');

function fa_template_override(string $template): string
{
    if (is_page(FA_TAB_SLUGS)) {
        $override = FA_DIR . 'templates/page-full.php';
        if (file_exists($override)) {
            return $override;
        }
    }
    return $template;
}

function fa_document_category_label(string $category): string
{
    return match ($category) {
        'waqf' => 'وقف',
        'inheritance' => 'ورث',
        default => 'أخرى',
    };
}

function fa_render_nav(string $active): string
{
    $user = wp_get_current_user();
    $canEdit = fa_user_can_edit();
    $isAdmin = current_user_can('manage_options');

    $tabs = [
        'tree' => ['url' => home_url('/family-tree/'), 'label' => 'الشجرة'],
        'files' => ['url' => home_url('/family-files/'), 'label' => 'الملفات'],
        'timeline' => ['url' => home_url('/family-timeline/'), 'label' => 'الأحداث الزمنية'],
        'documents' => ['url' => home_url('/family-documents/'), 'label' => 'الوثائق'],
    ];

    $html = '<header class="fa-header"><div class="fa-header-inner">';
    $html .= '<a href="' . esc_url(home_url('/family-tree/')) . '" class="fa-site-title">' . esc_html(get_bloginfo('name')) . '</a>';
    $html .= '<nav class="fa-main-tabs">';
    foreach ($tabs as $key => $tab) {
        $class = $key === $active ? 'is-active' : '';
        $html .= '<a href="' . esc_url($tab['url']) . '" class="' . $class . '">' . esc_html($tab['label']) . '</a>';
    }
    $html .= '</nav>';

    $html .= '<div class="fa-user-menu">';
    $html .= '<span class="fa-user-name">' . esc_html($user->display_name ?: $user->user_login) . '</span>';
    $html .= '<div class="fa-user-menu-dropdown">';
    $html .= '<a href="' . esc_url(admin_url('profile.php')) . '">تغيير كلمة السر</a>';
    if ($isAdmin) {
        $html .= '<a href="' . esc_url(admin_url('users.php')) . '">إدارة المستخدمين</a>';
    }
    $html .= '<a href="' . esc_url(wp_logout_url(home_url('/'))) . '">تسجيل خروج</a>';
    $html .= '</div></div>';

    $html .= '</div></header>';

    return $html;
}

function fa_shortcode_tree(): string
{
    if (!is_user_logged_in()) {
        return '';
    }

    $index = fa_build_member_index();
    $roots = fa_get_root_ids($index);
    $canEdit = fa_user_can_edit();

    ob_start();
    echo '<div class="fa-app">';
    echo fa_render_nav('tree');
    echo '<main class="fa-main">';
    echo '<section class="fa-tab-toolbar">';
    echo '<input type="search" id="fa-member-search" placeholder="ابحث عن اسم فرد من العائلة...">';
    if ($canEdit) {
        echo '<a class="fa-button" href="' . esc_url(admin_url('post-new.php?post_type=fa_member')) . '">+ إضافة فرد</a>';
    }
    echo '</section>';

    if (!$roots) {
        echo '<div class="fa-empty-state"><p>لسه مفيش أفراد مضافين في الشجرة.</p>';
        if ($canEdit) {
            echo '<p><a href="' . esc_url(admin_url('post-new.php?post_type=fa_member')) . '">ابدأ بإضافة أول فرد</a></p>';
        }
        echo '</div>';
    } else {
        echo '<div class="fa-tree-wrapper"><div class="fa-tree" id="fa-family-tree"><ul>';
        foreach ($roots as $rootId) {
            echo fa_render_tree_node($index, $rootId);
        }
        echo '</ul></div></div>';
    }

    echo '</main></div>';
    return ob_get_clean();
}

function fa_render_tree_node(array $index, int $id): string
{
    $data = $index['byId'][$id];
    $post = $data['post'];
    $meta = $data['meta'];
    $name = esc_html(fa_member_full_name($post));
    $years = esc_html(fa_member_years_label($meta));
    $photo = esc_url(fa_member_photo_url($post));
    $genderClass = $meta['gender'] === 'female' ? 'is-female' : 'is-male';

    $html = '<li data-name="' . esc_attr($name) . '">';
    $html .= '<div class="fa-person-card ' . $genderClass . '">';
    $html .= '<a href="' . esc_url(home_url('/family-tree/?fa_member=' . $id)) . '">';
    $html .= '<img src="' . $photo . '" alt="' . $name . '" loading="lazy">';
    $html .= '<span class="fa-person-name">' . $name . '</span>';
    if ($years) {
        $html .= '<span class="fa-person-years">' . $years . '</span>';
    }
    $html .= '</a>';

    if (!empty($meta['spouse_id']) && isset($index['byId'][$meta['spouse_id']])) {
        $spouse = $index['byId'][$meta['spouse_id']]['post'];
        $html .= '<div class="fa-spouse-link"><span class="fa-spouse-icon">&#128141;</span>';
        $html .= '<a href="' . esc_url(home_url('/family-tree/?fa_member=' . $meta['spouse_id'])) . '">' . esc_html(fa_member_full_name($spouse)) . '</a></div>';
    }

    $html .= '</div>';

    $children = $index['childrenOf'][$id] ?? [];
    if ($children) {
        $html .= '<ul>';
        foreach ($children as $childId) {
            $html .= fa_render_tree_node($index, $childId);
        }
        $html .= '</ul>';
    }

    $html .= '</li>';
    return $html;
}

function fa_render_member_profile(int $memberId): string
{
    $member = fa_get_member($memberId);
    if (!$member) {
        return '<div class="fa-empty-state"><p>الشخص ده مش موجود.</p><p><a href="' . esc_url(home_url('/family-tree/')) . '">رجوع للشجرة</a></p></div>';
    }

    $meta = fa_member_meta($memberId);
    $canEdit = fa_user_can_edit();
    $father = $meta['father_id'] ? fa_get_member($meta['father_id']) : null;
    $mother = $meta['mother_id'] ? fa_get_member($meta['mother_id']) : null;
    $spouse = $meta['spouse_id'] ? fa_get_member($meta['spouse_id']) : null;

    $index = fa_build_member_index();
    $childrenIds = $index['childrenOf'][$memberId] ?? [];

    ob_start();
    ?>
    <article class="fa-member-profile">
        <div class="fa-member-profile-photo">
            <img src="<?php echo esc_url(fa_member_photo_url($member)); ?>" alt="<?php echo esc_attr(fa_member_full_name($member)); ?>">
        </div>
        <div class="fa-member-profile-info">
            <div class="fa-profile-heading">
                <h1><?php echo esc_html(fa_member_full_name($member)); ?></h1>
                <?php if ($canEdit): ?>
                    <div class="fa-profile-actions">
                        <a class="fa-button fa-secondary" href="<?php echo esc_url(get_edit_post_link($memberId)); ?>">تعديل</a>
                        <a class="fa-button fa-danger" href="<?php echo esc_url(get_delete_post_link($memberId)); ?>" onclick="return confirm('متأكد إنك عايز تمسح <?php echo esc_js(fa_member_full_name($member)); ?>؟');">حذف</a>
                    </div>
                <?php endif; ?>
            </div>
            <?php if (fa_member_years_label($meta)): ?>
                <p class="fa-years"><?php echo esc_html(fa_member_years_label($meta)); ?></p>
            <?php endif; ?>
            <?php if ($member->post_content): ?>
                <div class="fa-bio"><?php echo wp_kses_post(wpautop($member->post_content)); ?></div>
            <?php endif; ?>
            <dl class="fa-relations">
                <?php if ($father): ?>
                    <dt>الأب</dt><dd><a href="<?php echo esc_url(home_url('/family-tree/?fa_member=' . $father->ID)); ?>"><?php echo esc_html(fa_member_full_name($father)); ?></a></dd>
                <?php endif; ?>
                <?php if ($mother): ?>
                    <dt>الأم</dt><dd><a href="<?php echo esc_url(home_url('/family-tree/?fa_member=' . $mother->ID)); ?>"><?php echo esc_html(fa_member_full_name($mother)); ?></a></dd>
                <?php endif; ?>
                <?php if ($spouse): ?>
                    <dt>الزوج/الزوجة</dt><dd><a href="<?php echo esc_url(home_url('/family-tree/?fa_member=' . $spouse->ID)); ?>"><?php echo esc_html(fa_member_full_name($spouse)); ?></a></dd>
                <?php endif; ?>
            </dl>
            <?php if ($childrenIds): ?>
                <h2>الأبناء</h2>
                <ul class="fa-children-list">
                    <?php foreach ($childrenIds as $childId): $child = $index['byId'][$childId]['post']; ?>
                        <li><a href="<?php echo esc_url(home_url('/family-tree/?fa_member=' . $childId)); ?>"><?php echo esc_html(fa_member_full_name($child)); ?></a></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
            <p><a href="<?php echo esc_url(home_url('/family-tree/')); ?>">&larr; رجوع للشجرة</a></p>
        </div>
    </article>
    <?php
    return ob_get_clean();
}

add_filter('the_content', 'fa_maybe_show_member_profile');

function fa_maybe_show_member_profile(string $content): string
{
    if (!is_singular('page') || !in_the_loop() || !is_main_query()) {
        return $content;
    }
    if (empty($_GET['fa_member']) || !has_shortcode($content, 'fa_tree')) {
        return $content;
    }
    return fa_render_member_profile(absint($_GET['fa_member']));
}

function fa_shortcode_files(): string
{
    if (!is_user_logged_in()) {
        return '';
    }
    $canEdit = fa_user_can_edit();
    $files = get_posts(['post_type' => 'fa_file', 'numberposts' => -1, 'orderby' => 'date', 'order' => 'DESC']);

    ob_start();
    echo '<div class="fa-app">';
    echo fa_render_nav('files');
    echo '<main class="fa-main">';
    echo '<section class="fa-tab-toolbar"><h1>ملفات العائلة</h1>';
    if ($canEdit) {
        echo '<a class="fa-button" href="' . esc_url(admin_url('post-new.php?post_type=fa_file')) . '">+ إضافة ملف</a>';
    }
    echo '</section>';

    if (!$files) {
        echo '<div class="fa-empty-state"><p>لسه مفيش ملفات مرفوعة.</p></div>';
    } else {
        echo '<div class="fa-card-grid">';
        foreach ($files as $file) {
            $original = get_post_meta($file->ID, '_fa_file_original', true);
            $size = (int) get_post_meta($file->ID, '_fa_file_size', true);
            echo '<div class="fa-item-card">';
            echo '<h3>' . esc_html(get_the_title($file)) . '</h3>';
            if ($file->post_content) {
                echo '<p>' . esc_html(wp_strip_all_tags($file->post_content)) . '</p>';
            }
            if ($original) {
                echo '<p class="fa-muted fa-small">' . esc_html($original) . ($size ? ' &middot; ' . esc_html(fa_format_bytes($size)) : '') . '</p>';
            }
            echo '<div class="fa-item-card-actions">';
            echo '<a class="fa-button fa-secondary" href="' . esc_url(home_url('/?fa_download_type=file&fa_download_id=' . $file->ID)) . '">تحميل</a>';
            if ($canEdit) {
                echo '<a href="' . esc_url(get_edit_post_link($file->ID)) . '">تعديل</a>';
                echo '<a href="' . esc_url(get_delete_post_link($file->ID)) . '" onclick="return confirm(\'متأكد؟\');" class="fa-link-danger">حذف</a>';
            }
            echo '</div></div>';
        }
        echo '</div>';
    }

    echo '</main></div>';
    return ob_get_clean();
}

function fa_shortcode_timeline(): string
{
    if (!is_user_logged_in()) {
        return '';
    }
    $canEdit = fa_user_can_edit();
    $events = get_posts(['post_type' => 'fa_event', 'numberposts' => -1, 'orderby' => 'meta_value_num', 'meta_key' => '_fa_sort_year', 'order' => 'ASC']);

    ob_start();
    echo '<div class="fa-app">';
    echo fa_render_nav('timeline');
    echo '<main class="fa-main">';
    echo '<section class="fa-tab-toolbar"><h1>الأحداث الزمنية</h1>';
    if ($canEdit) {
        echo '<a class="fa-button" href="' . esc_url(admin_url('post-new.php?post_type=fa_event')) . '">+ إضافة حدث</a>';
    }
    echo '</section>';

    if (!$events) {
        echo '<div class="fa-empty-state"><p>لسه مفيش أحداث مسجلة.</p></div>';
    } else {
        echo '<ol class="fa-timeline">';
        foreach ($events as $event) {
            $dateLabel = get_post_meta($event->ID, '_fa_date_label', true);
            $relatedId = (int) get_post_meta($event->ID, '_fa_related_member_id', true);
            echo '<li class="fa-timeline-item"><div class="fa-timeline-date">' . esc_html($dateLabel ?: '؟') . '</div>';
            echo '<div class="fa-timeline-content"><h3>' . esc_html(get_the_title($event)) . '</h3>';
            if ($relatedId && ($relatedMember = fa_get_member($relatedId))) {
                echo '<p class="fa-muted fa-small">متعلق بـ <a href="' . esc_url(home_url('/family-tree/?fa_member=' . $relatedId)) . '">' . esc_html(fa_member_full_name($relatedMember)) . '</a></p>';
            }
            if ($event->post_content) {
                echo '<p>' . esc_html(wp_strip_all_tags($event->post_content)) . '</p>';
            }
            if ($canEdit) {
                echo '<div class="fa-item-card-actions">';
                echo '<a href="' . esc_url(get_edit_post_link($event->ID)) . '">تعديل</a>';
                echo '<a href="' . esc_url(get_delete_post_link($event->ID)) . '" onclick="return confirm(\'متأكد؟\');" class="fa-link-danger">حذف</a>';
                echo '</div>';
            }
            echo '</div></li>';
        }
        echo '</ol>';
    }

    echo '</main></div>';
    return ob_get_clean();
}

function fa_shortcode_documents(): string
{
    if (!is_user_logged_in()) {
        return '';
    }
    $canEdit = fa_user_can_edit();
    $categoryFilter = isset($_GET['fa_cat']) && in_array($_GET['fa_cat'], ['waqf', 'inheritance', 'other'], true) ? $_GET['fa_cat'] : '';

    $args = ['post_type' => 'fa_document', 'numberposts' => -1, 'orderby' => 'meta_value_num', 'meta_key' => '_fa_sort_year', 'order' => 'ASC'];
    if ($categoryFilter) {
        $args['meta_query'] = [['key' => '_fa_category', 'value' => $categoryFilter]];
    }
    $documents = get_posts($args);

    ob_start();
    echo '<div class="fa-app">';
    echo fa_render_nav('documents');
    echo '<main class="fa-main">';
    echo '<section class="fa-tab-toolbar"><h1>الوثائق (حجج الوقف والورث وغيرها)</h1>';
    if ($canEdit) {
        echo '<a class="fa-button" href="' . esc_url(admin_url('post-new.php?post_type=fa_document')) . '">+ إضافة وثيقة</a>';
    }
    echo '</section>';

    echo '<div class="fa-filter-tabs">';
    $catUrl = home_url('/family-documents/');
    echo '<a href="' . esc_url($catUrl) . '" class="' . ($categoryFilter === '' ? 'is-active' : '') . '">الكل</a>';
    foreach (['waqf' => 'وقف', 'inheritance' => 'ورث', 'other' => 'أخرى'] as $key => $label) {
        echo '<a href="' . esc_url(add_query_arg('fa_cat', $key, $catUrl)) . '" class="' . ($categoryFilter === $key ? 'is-active' : '') . '">' . esc_html($label) . '</a>';
    }
    echo '</div>';

    if (!$documents) {
        echo '<div class="fa-empty-state"><p>لسه مفيش وثائق مسجلة في القسم ده.</p></div>';
    } else {
        echo '<div class="fa-card-grid">';
        foreach ($documents as $doc) {
            $category = get_post_meta($doc->ID, '_fa_category', true) ?: 'other';
            $dateLabel = get_post_meta($doc->ID, '_fa_date_label', true);
            $relatedId = (int) get_post_meta($doc->ID, '_fa_related_member_id', true);
            $hasFile = (bool) get_post_meta($doc->ID, '_fa_file', true);

            echo '<div class="fa-item-card">';
            echo '<span class="fa-category-badge fa-category-' . esc_attr($category) . '">' . esc_html(fa_document_category_label($category)) . '</span>';
            echo '<h3>' . esc_html(get_the_title($doc)) . '</h3>';
            if ($dateLabel) {
                echo '<p class="fa-muted fa-small">' . esc_html($dateLabel) . '</p>';
            }
            if ($relatedId && ($relatedMember = fa_get_member($relatedId))) {
                echo '<p class="fa-muted fa-small">متعلقة بـ <a href="' . esc_url(home_url('/family-tree/?fa_member=' . $relatedId)) . '">' . esc_html(fa_member_full_name($relatedMember)) . '</a></p>';
            }
            if ($doc->post_content) {
                echo '<p>' . esc_html(wp_strip_all_tags($doc->post_content)) . '</p>';
            }
            echo '<div class="fa-item-card-actions">';
            if ($hasFile) {
                echo '<a class="fa-button fa-secondary" href="' . esc_url(home_url('/?fa_download_type=document&fa_download_id=' . $doc->ID)) . '">فتح المستند</a>';
            }
            if ($canEdit) {
                echo '<a href="' . esc_url(get_edit_post_link($doc->ID)) . '">تعديل</a>';
                echo '<a href="' . esc_url(get_delete_post_link($doc->ID)) . '" onclick="return confirm(\'متأكد؟\');" class="fa-link-danger">حذف</a>';
            }
            echo '</div></div>';
        }
        echo '</div>';
    }

    echo '</main></div>';
    return ob_get_clean();
}
