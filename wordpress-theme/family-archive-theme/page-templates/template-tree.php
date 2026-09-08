<?php
/**
 * Template Name: شجرة العائلة
 */
if (!defined('ABSPATH')) {
    exit;
}
get_header();
echo fa_render_tree_page();
get_footer();
