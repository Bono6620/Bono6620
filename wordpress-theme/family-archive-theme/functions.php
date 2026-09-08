<?php
if (!defined('ABSPATH')) {
    exit;
}

define('FA_VERSION', '1.0.0');
define('FA_DIR', get_template_directory() . '/');
define('FA_URL', get_template_directory_uri() . '/');
define('FA_PRIVATE_DIR', WP_CONTENT_DIR . '/family-archive-private');
define('FA_MAX_UPLOAD_BYTES', 8 * 1024 * 1024);

require_once FA_DIR . 'inc/icons.php';
require_once FA_DIR . 'inc/post-types.php';
require_once FA_DIR . 'inc/roles.php';
require_once FA_DIR . 'inc/uploads.php';
require_once FA_DIR . 'inc/meta-boxes.php';
require_once FA_DIR . 'inc/tree-data.php';
require_once FA_DIR . 'inc/template-functions.php';
require_once FA_DIR . 'inc/account.php';
require_once FA_DIR . 'inc/admin-panel.php';
require_once FA_DIR . 'inc/access.php';
require_once FA_DIR . 'inc/remote-api.php';
require_once FA_DIR . 'inc/setup.php';

add_action('after_setup_theme', 'fa_theme_setup');

function fa_theme_setup(): void
{
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('automatic-feed-links');
}

add_action('wp_enqueue_scripts', 'fa_enqueue_assets');

function fa_enqueue_assets(): void
{
    wp_enqueue_style('fa-google-font', 'https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800&display=swap', [], null);
    wp_enqueue_style('fa-style', get_stylesheet_uri(), ['fa-google-font'], FA_VERSION);
    wp_enqueue_script('fa-script', FA_URL . 'assets/js/main.js', [], FA_VERSION, true);
}
