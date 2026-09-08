<?php
if (!defined('ABSPATH')) {
    exit;
}
get_header();
?>
<div class="fa-app">
<?php if (is_user_logged_in()): ?>
    <?php echo fa_render_nav('tree'); ?>
    <main class="fa-main">
        <?php if (have_posts()): ?>
            <?php while (have_posts()): the_post(); ?>
                <article class="fa-card">
                    <h1><?php the_title(); ?></h1>
                    <div><?php the_content(); ?></div>
                </article>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="fa-empty-state"><?php echo fa_icon('search'); ?><p>مفيش حاجة هنا.</p></div>
        <?php endif; ?>
    </main>
<?php endif; ?>
</div>
<?php
get_footer();
