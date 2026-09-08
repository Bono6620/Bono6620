<?php
if (!defined('ABSPATH')) {
    exit;
}
?><!DOCTYPE html>
<html <?php language_attributes(); ?> dir="rtl">
<head>
<meta charset="<?php bloginfo('charset'); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?php echo esc_html(get_the_title() . ' - ' . get_bloginfo('name')); ?></title>
<?php
remove_action('wp_head', '_wp_render_title_tag', 1);
remove_action('wp_head', '_block_template_render_title_tag', 1);
wp_head();
?>
</head>
<body <?php body_class('fa-body'); ?>>
<?php
while (have_posts()) {
    the_post();
    the_content();
}
?>
<?php wp_footer(); ?>
</body>
</html>
