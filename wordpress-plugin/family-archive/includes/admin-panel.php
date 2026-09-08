<?php
if (!defined('ABSPATH')) {
    exit;
}

add_action('init', function () {
    add_shortcode('fa_admin', 'fa_shortcode_admin');
});

function fa_admin_count(): int
{
    return count(get_users(['role' => 'administrator', 'fields' => 'ID']));
}

function fa_admin_message(string $code): array
{
    return match ($code) {
        'user_added' => ['success', 'تم إضافة المستخدم بنجاح.'],
        'user_exists' => ['error', 'اسم المستخدم أو البريد الإلكتروني مستخدم قبل كده.'],
        'user_invalid' => ['error', 'البيانات ناقصة أو غلط.'],
        'user_deleted' => ['success', 'تم حذف المستخدم.'],
        'user_role_updated' => ['success', 'تم تحديث صلاحية المستخدم.'],
        'cannot_delete_self' => ['error', 'مينفعش تمسح حسابك انت من هنا.'],
        'cannot_change_own_role' => ['error', 'مينفعش تغير صلاحيتك انت من هنا.'],
        'cannot_delete_last_admin' => ['error', 'لازم يفضل مدير واحد على الأقل في الموقع.'],
        'member_added' => ['success', 'تم إضافة الفرد للشجرة.'],
        'event_added' => ['success', 'تم إضافة الحدث.'],
        'document_added' => ['success', 'تم إضافة الوثيقة.'],
        'file_added' => ['success', 'تم إضافة الملف.'],
        default => ['', ''],
    };
}

function fa_shortcode_admin(): string
{
    if (!is_user_logged_in()) {
        return '';
    }

    ob_start();
    echo '<div class="fa-app">';
    echo fa_render_nav('admin');
    echo '<main class="fa-main">';

    if (!current_user_can('manage_options')) {
        echo '<div class="fa-empty-state"><span class="fa-empty-icon">🔒</span><p>الصفحة دي للمدير بس.</p></div>';
        echo '</main></div>';
        return ob_get_clean();
    }

    $section = (isset($_GET['fa_section']) && $_GET['fa_section'] === 'content') ? 'content' : 'users';

    echo '<section class="fa-tab-toolbar"><h1>لوحة التحكم</h1></section>';

    [$msgType, $msgText] = isset($_GET['fa_msg']) ? fa_admin_message(sanitize_key($_GET['fa_msg'])) : ['', ''];
    if ($msgText) {
        echo '<div class="fa-alert fa-alert-' . esc_attr($msgType) . '">' . esc_html($msgText) . '</div>';
        if (isset($_GET['fa_pw'])) {
            echo '<div class="fa-alert fa-alert-success">كلمة السر المبدئية للمستخدم الجديد: <code>' . esc_html(wp_unslash($_GET['fa_pw'])) . '</code> - ابعتها له وقوله يغيّرها من صفحة "حسابي".</div>';
        }
    }

    echo '<div class="fa-filter-tabs">';
    echo '<a href="' . esc_url(home_url('/family-admin/')) . '" class="' . ($section === 'users' ? 'is-active' : '') . '">👥 المستخدمون</a>';
    echo '<a href="' . esc_url(add_query_arg('fa_section', 'content', home_url('/family-admin/'))) . '" class="' . ($section === 'content' ? 'is-active' : '') . '">➕ إضافة محتوى</a>';
    echo '</div>';

    echo $section === 'users' ? fa_render_admin_users_section() : fa_render_admin_content_section();

    echo '</main></div>';
    return ob_get_clean();
}

function fa_render_admin_users_section(): string
{
    $users = get_users(['orderby' => 'registered', 'order' => 'ASC']);
    $adminCount = 0;
    $editorCount = 0;
    $viewerCount = 0;
    foreach ($users as $u) {
        if (in_array('administrator', $u->roles, true)) {
            $adminCount++;
        } elseif (in_array('family_editor', $u->roles, true)) {
            $editorCount++;
        } elseif (in_array('family_viewer', $u->roles, true)) {
            $viewerCount++;
        }
    }

    ob_start();
    ?>
    <div class="fa-stat-row">
        <div class="fa-stat-card"><span class="fa-stat-number"><?php echo count($users); ?></span><span class="fa-stat-label">إجمالي المستخدمين</span></div>
        <div class="fa-stat-card"><span class="fa-stat-number"><?php echo (int) $adminCount; ?></span><span class="fa-stat-label">مدراء</span></div>
        <div class="fa-stat-card"><span class="fa-stat-number"><?php echo (int) $editorCount; ?></span><span class="fa-stat-label">محررين</span></div>
        <div class="fa-stat-card"><span class="fa-stat-number"><?php echo (int) $viewerCount; ?></span><span class="fa-stat-label">مشاهدين فقط</span></div>
    </div>

    <div class="fa-card fa-add-user-card">
        <h2>إضافة مستخدم جديد</h2>
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="fa-form fa-form-grid">
            <input type="hidden" name="action" value="fa_admin_add_user">
            <?php wp_nonce_field('fa_admin_add_user'); ?>
            <label><span>الاسم بالكامل</span><input type="text" name="fa_name" required></label>
            <label><span>اسم المستخدم</span><input type="text" name="fa_username" required pattern="[A-Za-z0-9_\.\-]+"></label>
            <label><span>البريد الإلكتروني</span><input type="email" name="fa_email" required></label>
            <label><span>كلمة السر <span class="fa-muted fa-small">(سيبها فاضية عشان تتولد أوتوماتيك)</span></span><input type="password" name="fa_password" minlength="6" autocomplete="new-password"></label>
            <label><span>الصلاحية</span>
                <select name="fa_role">
                    <option value="family_viewer">مشاهدة فقط</option>
                    <option value="family_editor">محرر الأرشيف</option>
                    <option value="administrator">مدير</option>
                </select>
            </label>
            <button type="submit" class="fa-button">+ إضافة المستخدم</button>
        </form>
    </div>

    <div class="fa-card fa-users-table-card">
        <h2>كل المستخدمين</h2>
        <div class="fa-table-scroll">
        <table class="fa-data-table">
            <thead><tr><th>الاسم</th><th>اسم المستخدم</th><th>البريد الإلكتروني</th><th>الصلاحية</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($users as $u): $r = fa_primary_role($u); $isSelf = $u->ID === get_current_user_id(); ?>
                <tr>
                    <td><?php echo esc_html($u->display_name ?: $u->user_login); ?><?php if ($isSelf) { echo ' <span class="fa-muted fa-small">(انت)</span>'; } ?></td>
                    <td><?php echo esc_html($u->user_login); ?></td>
                    <td><?php echo esc_html($u->user_email); ?></td>
                    <td>
                        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="fa-inline-form">
                            <input type="hidden" name="action" value="fa_admin_update_role">
                            <input type="hidden" name="fa_user_id" value="<?php echo (int) $u->ID; ?>">
                            <?php wp_nonce_field('fa_admin_update_role_' . $u->ID); ?>
                            <select name="fa_role" onchange="this.form.submit()" <?php disabled($isSelf); ?>>
                                <option value="family_viewer" <?php selected($r, 'family_viewer'); ?>>مشاهدة فقط</option>
                                <option value="family_editor" <?php selected($r, 'family_editor'); ?>>محرر الأرشيف</option>
                                <option value="administrator" <?php selected($r, 'administrator'); ?>>مدير</option>
                            </select>
                        </form>
                    </td>
                    <td>
                        <?php if (!$isSelf): ?>
                        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="fa-inline-form" onsubmit="return confirm('متأكد إنك عايز تمسح <?php echo esc_js($u->user_login); ?>؟');">
                            <input type="hidden" name="action" value="fa_admin_delete_user">
                            <input type="hidden" name="fa_user_id" value="<?php echo (int) $u->ID; ?>">
                            <?php wp_nonce_field('fa_admin_delete_user_' . $u->ID); ?>
                            <button type="submit" class="fa-link-danger fa-as-link">حذف</button>
                        </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

function fa_render_admin_content_section(): string
{
    ob_start();
    ?>
    <div class="fa-quickadd-grid">
        <details class="fa-card fa-details" open>
            <summary>🌳 إضافة فرد للشجرة</summary>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" enctype="multipart/form-data" class="fa-form fa-form-grid">
                <input type="hidden" name="action" value="fa_admin_quickadd_member">
                <?php wp_nonce_field('fa_admin_quickadd_member'); ?>
                <?php wp_nonce_field('fa_save_member', 'fa_member_nonce'); ?>
                <label><span>الاسم</span><input type="text" name="post_title" required></label>
                <label><span>النوع</span>
                    <select name="fa_gender"><option value="male">ذكر</option><option value="female">أنثى</option></select>
                </label>
                <label><span>سنة الميلاد</span><input type="number" name="fa_birth_year" min="1500" max="2100"></label>
                <label><span>سنة الوفاة (لو متوفى)</span><input type="number" name="fa_death_year" min="1500" max="2100"></label>
                <label><span>الأب</span><?php fa_dropdown_members('fa_father_id', 0, 0, 'male'); ?></label>
                <label><span>الأم</span><?php fa_dropdown_members('fa_mother_id', 0, 0, 'female'); ?></label>
                <label><span>الزوج/الزوجة</span><?php fa_dropdown_members('fa_spouse_id', 0, 0); ?></label>
                <label><span>صورة شخصية</span><input type="file" name="fa_photo" accept=".jpg,.jpeg,.png,.webp"></label>
                <label class="fa-form-full"><span>نبذة (اختياري)</span><textarea name="post_content" rows="3"></textarea></label>
                <button type="submit" class="fa-button">+ إضافة الفرد</button>
            </form>
        </details>

        <details class="fa-card fa-details">
            <summary>🕰️ إضافة حدث زمني</summary>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="fa-form fa-form-grid">
                <input type="hidden" name="action" value="fa_admin_quickadd_event">
                <?php wp_nonce_field('fa_admin_quickadd_event'); ?>
                <?php wp_nonce_field('fa_save_event', 'fa_event_nonce'); ?>
                <label><span>عنوان الحدث</span><input type="text" name="post_title" required></label>
                <label><span>التاريخ (بأي صيغة)</span><input type="text" name="fa_date_label" placeholder="مثال: 1970 أو 3 مارس 1970"></label>
                <label><span>السنة (للترتيب)</span><input type="number" name="fa_sort_year" min="1" max="2100"></label>
                <label><span>مرتبط بفرد (اختياري)</span><?php fa_dropdown_members('fa_related_member_id', 0, 0); ?></label>
                <label class="fa-form-full"><span>وصف الحدث</span><textarea name="post_content" rows="3"></textarea></label>
                <button type="submit" class="fa-button">+ إضافة الحدث</button>
            </form>
        </details>

        <details class="fa-card fa-details">
            <summary>📜 إضافة وثيقة</summary>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" enctype="multipart/form-data" class="fa-form fa-form-grid">
                <input type="hidden" name="action" value="fa_admin_quickadd_document">
                <?php wp_nonce_field('fa_admin_quickadd_document'); ?>
                <?php wp_nonce_field('fa_save_document', 'fa_document_nonce'); ?>
                <label><span>عنوان الوثيقة</span><input type="text" name="post_title" required></label>
                <label><span>النوع</span>
                    <select name="fa_category">
                        <option value="waqf">وقف</option>
                        <option value="inheritance">ورث</option>
                        <option value="other">أخرى</option>
                    </select>
                </label>
                <label><span>التاريخ (بأي صيغة)</span><input type="text" name="fa_date_label"></label>
                <label><span>السنة (للترتيب)</span><input type="number" name="fa_sort_year" min="1" max="2100"></label>
                <label><span>مرتبطة بفرد (اختياري)</span><?php fa_dropdown_members('fa_related_member_id', 0, 0); ?></label>
                <label><span>صورة أو PDF</span><input type="file" name="fa_upload_file" accept=".jpg,.jpeg,.png,.webp,.pdf"></label>
                <label class="fa-form-full"><span>وصف الوثيقة</span><textarea name="post_content" rows="3"></textarea></label>
                <button type="submit" class="fa-button">+ إضافة الوثيقة</button>
            </form>
        </details>

        <details class="fa-card fa-details">
            <summary>📁 إضافة ملف</summary>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" enctype="multipart/form-data" class="fa-form fa-form-grid">
                <input type="hidden" name="action" value="fa_admin_quickadd_file">
                <?php wp_nonce_field('fa_admin_quickadd_file'); ?>
                <?php wp_nonce_field('fa_save_file', 'fa_file_nonce'); ?>
                <label><span>عنوان الملف</span><input type="text" name="post_title" required></label>
                <label><span>الملف</span><input type="file" name="fa_upload_file" required accept=".jpg,.jpeg,.png,.webp,.gif,.pdf,.doc,.docx,.xls,.xlsx,.zip,.txt"></label>
                <label class="fa-form-full"><span>وصف الملف (اختياري)</span><textarea name="post_content" rows="3"></textarea></label>
                <button type="submit" class="fa-button">+ إضافة الملف</button>
            </form>
        </details>
    </div>
    <?php
    return ob_get_clean();
}

add_action('admin_post_fa_admin_add_user', 'fa_handle_admin_add_user');

function fa_handle_admin_add_user(): void
{
    if (!current_user_can('manage_options')) {
        wp_die('غير مصرح.');
    }
    check_admin_referer('fa_admin_add_user');

    $redirect = home_url('/family-admin/');
    $name = sanitize_text_field($_POST['fa_name'] ?? '');
    $username = sanitize_user($_POST['fa_username'] ?? '', true);
    $email = sanitize_email($_POST['fa_email'] ?? '');
    $password = (string) ($_POST['fa_password'] ?? '');
    $role = in_array($_POST['fa_role'] ?? '', ['administrator', 'family_editor', 'family_viewer'], true) ? $_POST['fa_role'] : 'family_viewer';

    if (!$name || !$username || !is_email($email)) {
        wp_safe_redirect(add_query_arg('fa_msg', 'user_invalid', $redirect));
        exit;
    }
    if (username_exists($username) || email_exists($email)) {
        wp_safe_redirect(add_query_arg('fa_msg', 'user_exists', $redirect));
        exit;
    }

    $autoGenerated = false;
    if (strlen($password) < 6) {
        $password = wp_generate_password(12, false);
        $autoGenerated = true;
    }

    $userId = wp_insert_user([
        'user_login' => $username,
        'user_pass' => $password,
        'user_email' => $email,
        'display_name' => $name,
        'role' => $role,
    ]);

    if (is_wp_error($userId)) {
        wp_safe_redirect(add_query_arg('fa_msg', 'user_invalid', $redirect));
        exit;
    }

    wp_new_user_notification($userId, null, 'user');

    $args = ['fa_msg' => 'user_added'];
    if ($autoGenerated) {
        $args['fa_pw'] = rawurlencode($password);
    }
    wp_safe_redirect(add_query_arg($args, $redirect));
    exit;
}

add_action('admin_post_fa_admin_update_role', 'fa_handle_admin_update_role');

function fa_handle_admin_update_role(): void
{
    if (!current_user_can('manage_options')) {
        wp_die('غير مصرح.');
    }
    $userId = absint($_POST['fa_user_id'] ?? 0);
    check_admin_referer('fa_admin_update_role_' . $userId);

    $redirect = home_url('/family-admin/');

    if ($userId === get_current_user_id()) {
        wp_safe_redirect(add_query_arg('fa_msg', 'cannot_change_own_role', $redirect));
        exit;
    }

    $user = get_user_by('id', $userId);
    if (!$user) {
        wp_safe_redirect(add_query_arg('fa_msg', 'user_invalid', $redirect));
        exit;
    }

    $role = in_array($_POST['fa_role'] ?? '', ['administrator', 'family_editor', 'family_viewer'], true) ? $_POST['fa_role'] : 'family_viewer';

    if ($role !== 'administrator' && in_array('administrator', $user->roles, true) && fa_admin_count() <= 1) {
        wp_safe_redirect(add_query_arg('fa_msg', 'cannot_delete_last_admin', $redirect));
        exit;
    }

    $user->set_role($role);
    wp_safe_redirect(add_query_arg('fa_msg', 'user_role_updated', $redirect));
    exit;
}

add_action('admin_post_fa_admin_delete_user', 'fa_handle_admin_delete_user');

function fa_handle_admin_delete_user(): void
{
    if (!current_user_can('manage_options')) {
        wp_die('غير مصرح.');
    }
    $userId = absint($_POST['fa_user_id'] ?? 0);
    check_admin_referer('fa_admin_delete_user_' . $userId);

    $redirect = home_url('/family-admin/');

    if ($userId === get_current_user_id()) {
        wp_safe_redirect(add_query_arg('fa_msg', 'cannot_delete_self', $redirect));
        exit;
    }

    $user = get_user_by('id', $userId);
    if (!$user) {
        wp_safe_redirect(add_query_arg('fa_msg', 'user_invalid', $redirect));
        exit;
    }

    if (in_array('administrator', $user->roles, true) && fa_admin_count() <= 1) {
        wp_safe_redirect(add_query_arg('fa_msg', 'cannot_delete_last_admin', $redirect));
        exit;
    }

    require_once ABSPATH . 'wp-admin/includes/user.php';
    wp_delete_user($userId, get_current_user_id());

    wp_safe_redirect(add_query_arg('fa_msg', 'user_deleted', $redirect));
    exit;
}

function fa_admin_quickadd_redirect(): string
{
    return add_query_arg('fa_section', 'content', home_url('/family-admin/'));
}

function fa_admin_quickadd_insert(string $postType): int|WP_Error
{
    $title = sanitize_text_field($_POST['post_title'] ?? '');
    if (!$title) {
        return new WP_Error('fa_missing_title', 'العنوان مطلوب.');
    }
    return wp_insert_post([
        'post_type' => $postType,
        'post_status' => 'publish',
        'post_title' => $title,
        'post_content' => wp_kses_post($_POST['post_content'] ?? ''),
    ], true);
}

add_action('admin_post_fa_admin_quickadd_member', 'fa_handle_admin_quickadd_member');

function fa_handle_admin_quickadd_member(): void
{
    if (!current_user_can('manage_options')) {
        wp_die('غير مصرح.');
    }
    check_admin_referer('fa_admin_quickadd_member');

    $redirect = fa_admin_quickadd_redirect();
    $postId = fa_admin_quickadd_insert('fa_member');
    if (is_wp_error($postId)) {
        wp_safe_redirect(add_query_arg('fa_msg', 'user_invalid', $redirect));
        exit;
    }

    fa_set_member_photo_from_upload($postId);

    wp_safe_redirect(add_query_arg('fa_msg', 'member_added', $redirect));
    exit;
}

add_action('admin_post_fa_admin_quickadd_event', 'fa_handle_admin_quickadd_event');

function fa_handle_admin_quickadd_event(): void
{
    if (!current_user_can('manage_options')) {
        wp_die('غير مصرح.');
    }
    check_admin_referer('fa_admin_quickadd_event');

    $redirect = fa_admin_quickadd_redirect();
    $postId = fa_admin_quickadd_insert('fa_event');
    if (is_wp_error($postId)) {
        wp_safe_redirect(add_query_arg('fa_msg', 'user_invalid', $redirect));
        exit;
    }

    wp_safe_redirect(add_query_arg('fa_msg', 'event_added', $redirect));
    exit;
}

add_action('admin_post_fa_admin_quickadd_document', 'fa_handle_admin_quickadd_document');

function fa_handle_admin_quickadd_document(): void
{
    if (!current_user_can('manage_options')) {
        wp_die('غير مصرح.');
    }
    check_admin_referer('fa_admin_quickadd_document');

    $redirect = fa_admin_quickadd_redirect();
    $postId = fa_admin_quickadd_insert('fa_document');
    if (is_wp_error($postId)) {
        wp_safe_redirect(add_query_arg('fa_msg', 'user_invalid', $redirect));
        exit;
    }

    wp_safe_redirect(add_query_arg('fa_msg', 'document_added', $redirect));
    exit;
}

add_action('admin_post_fa_admin_quickadd_file', 'fa_handle_admin_quickadd_file');

function fa_handle_admin_quickadd_file(): void
{
    if (!current_user_can('manage_options')) {
        wp_die('غير مصرح.');
    }
    check_admin_referer('fa_admin_quickadd_file');

    $redirect = fa_admin_quickadd_redirect();
    $postId = fa_admin_quickadd_insert('fa_file');
    if (is_wp_error($postId)) {
        wp_safe_redirect(add_query_arg('fa_msg', 'user_invalid', $redirect));
        exit;
    }

    wp_safe_redirect(add_query_arg('fa_msg', 'file_added', $redirect));
    exit;
}
