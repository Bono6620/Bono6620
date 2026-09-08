<?php
/**
 * Template Name: ملفات العائلة
 */
if (!defined('ABSPATH')) {
    exit;
}
get_header();
echo fa_render_files_page();
get_footer();
