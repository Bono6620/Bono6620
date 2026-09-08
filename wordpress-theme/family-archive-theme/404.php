<?php
if (!defined('ABSPATH')) {
    exit;
}
get_header();
?>
<div class="fa-app">
    <?php if (is_user_logged_in()): ?>
        <?php echo fa_render_nav(''); ?>
        <main class="fa-main">
            <div class="fa-empty-state">
                <?php echo fa_icon('search'); ?>
                <p>الصفحة دي مش موجودة.</p>
                <p><a class="fa-button" href="<?php echo esc_url(home_url('/family-tree/')); ?>"><?php echo fa_icon('arrow-back'); ?>رجوع للشجرة</a></p>
            </div>
        </main>
    <?php endif; ?>
</div>
<?php
get_footer();
