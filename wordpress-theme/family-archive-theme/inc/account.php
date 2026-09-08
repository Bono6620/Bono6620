<?php
if (!defined('ABSPATH')) {
    exit;
}

function fa_role_label(string $role): string
{
    return match ($role) {
        'administrator' => 'مدير',
        'family_editor' => 'محرر الأرشيف',
        'family_viewer' => 'مشاهدة فقط',
        default => $role,
    };
}

function fa_role_description(string $role): string
{
    return match ($role) {
        'administrator' => 'كل الصلاحيات، بما فيها إدارة المستخدمين ولوحة التحكم.',
        'family_editor' => 'يقدر يضيف ويعدل ويمسح أفراد وملفات وأحداث ووثائق.',
        'family_viewer' => 'يشوف المحتوى بس، من غير أي تعديل.',
        default => '',
    };
}

function fa_primary_role(WP_User $user): string
{
    if (in_array('administrator', $user->roles, true)) {
        return 'administrator';
    }
    if (in_array('family_editor', $user->roles, true)) {
        return 'family_editor';
    }
    if (in_array('family_viewer', $user->roles, true)) {
        return 'family_viewer';
    }
    return $user->roles[0] ?? '';
}

function fa_account_message(string $code): array
{
    return match ($code) {
        'pw_ok' => ['success', 'تم تغيير كلمة السر بنجاح.'],
        'pw_mismatch' => ['error', 'كلمة السر الجديدة والتأكيد مش متطابقين.'],
        'pw_wrong' => ['error', 'كلمة السر الحالية غلط.'],
        'pw_short' => ['error', 'كلمة السر الجديدة لازم تكون 6 حروف على الأقل.'],
        default => ['', ''],
    };
}

function fa_render_account_page(): string
{
    if (!is_user_logged_in()) {
        return '';
    }

    $user = wp_get_current_user();
    $role = fa_primary_role($user);
    $isAdmin = current_user_can('manage_options');
    $displayName = $user->display_name ?: $user->user_login;
    $initial = function_exists('mb_substr') ? mb_substr($displayName, 0, 1) : substr($displayName, 0, 1);
    $joined = $user->user_registered ? date_i18n('j F Y', strtotime($user->user_registered)) : '';

    [$msgType, $msgText] = isset($_GET['fa_msg']) ? fa_account_message(sanitize_key($_GET['fa_msg'])) : ['', ''];

    ob_start();
    echo '<div class="fa-app">';
    echo fa_render_nav('account');
    echo '<main class="fa-main">';
    echo '<section class="fa-tab-toolbar"><h1>' . fa_icon('user') . 'حسابي</h1></section>';

    if ($msgText) {
        echo '<div class="fa-alert fa-alert-' . esc_attr($msgType) . '">' . esc_html($msgText) . '</div>';
    }
    ?>
    <div class="fa-account-grid">
        <div class="fa-card fa-account-card">
            <div class="fa-account-avatar-lg"><?php echo esc_html($initial); ?></div>
            <h2><?php echo esc_html($displayName); ?></h2>
            <span class="fa-role-badge fa-role-<?php echo esc_attr($role); ?>"><?php echo esc_html(fa_role_label($role)); ?></span>
            <p class="fa-muted fa-small"><?php echo esc_html(fa_role_description($role)); ?></p>
            <dl class="fa-relations">
                <dt>اسم المستخدم</dt><dd><?php echo esc_html($user->user_login); ?></dd>
                <dt>البريد الإلكتروني</dt><dd><?php echo esc_html($user->user_email); ?></dd>
                <?php if ($joined): ?><dt>عضو منذ</dt><dd><?php echo esc_html($joined); ?></dd><?php endif; ?>
            </dl>
            <?php if ($isAdmin): ?>
                <a class="fa-button" href="<?php echo esc_url(home_url('/family-admin/')); ?>"><?php echo fa_icon('sliders'); ?>لوحة التحكم</a>
            <?php endif; ?>
        </div>

        <div class="fa-card fa-password-card">
            <h2><?php echo fa_icon('key'); ?>تغيير كلمة السر</h2>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="fa-form">
                <input type="hidden" name="action" value="fa_change_password">
                <?php wp_nonce_field('fa_change_password'); ?>
                <label><span>كلمة السر الحالية</span><input type="password" name="fa_current_password" required autocomplete="current-password"></label>
                <label><span>كلمة السر الجديدة</span><input type="password" name="fa_new_password" required minlength="6" autocomplete="new-password"></label>
                <label><span>تأكيد كلمة السر الجديدة</span><input type="password" name="fa_new_password_confirm" required minlength="6" autocomplete="new-password"></label>
                <button type="submit" class="fa-button">حفظ كلمة السر الجديدة</button>
            </form>
        </div>
    </div>
    <?php
    echo '</main></div>';
    return ob_get_clean();
}

add_action('admin_post_fa_change_password', 'fa_handle_change_password');

function fa_handle_change_password(): void
{
    if (!is_user_logged_in()) {
        wp_die('لازم تسجل دخول.');
    }
    check_admin_referer('fa_change_password');

    $user = wp_get_current_user();
    $current = (string) ($_POST['fa_current_password'] ?? '');
    $new = (string) ($_POST['fa_new_password'] ?? '');
    $confirm = (string) ($_POST['fa_new_password_confirm'] ?? '');
    $redirect = home_url('/family-account/');

    if (!wp_check_password($current, $user->user_pass, $user->ID)) {
        wp_safe_redirect(add_query_arg('fa_msg', 'pw_wrong', $redirect));
        exit;
    }
    if (strlen($new) < 6) {
        wp_safe_redirect(add_query_arg('fa_msg', 'pw_short', $redirect));
        exit;
    }
    if ($new !== $confirm) {
        wp_safe_redirect(add_query_arg('fa_msg', 'pw_mismatch', $redirect));
        exit;
    }

    wp_set_password($new, $user->ID);
    wp_set_auth_cookie($user->ID, true);

    wp_safe_redirect(add_query_arg('fa_msg', 'pw_ok', $redirect));
    exit;
}
