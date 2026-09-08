<?php
/**
 * Plugin Name: أرشيف العائلة (Family Archive)
 * Description: شجرة عائلة + ملفات + أحداث زمنية + وثائق (وقف/ورث)، بصلاحيات محرر/مشاهدة وموقع محمي بتسجيل دخول.
 * Version: 1.2.0
 * Author: Family Archive
 * Text Domain: family-archive
 */

if (!defined('ABSPATH')) {
    exit;
}

define('FA_VERSION', '1.2.0');
define('FA_DIR', plugin_dir_path(__FILE__));
define('FA_URL', plugin_dir_url(__FILE__));
define('FA_PRIVATE_DIR', WP_CONTENT_DIR . '/family-archive-private');
define('FA_MAX_UPLOAD_BYTES', 8 * 1024 * 1024);

require_once FA_DIR . 'includes/post-types.php';
require_once FA_DIR . 'includes/roles.php';
require_once FA_DIR . 'includes/uploads.php';
require_once FA_DIR . 'includes/meta-boxes.php';
require_once FA_DIR . 'includes/tree-data.php';
require_once FA_DIR . 'includes/shortcodes.php';
require_once FA_DIR . 'includes/account.php';
require_once FA_DIR . 'includes/admin-panel.php';
require_once FA_DIR . 'includes/access.php';
require_once FA_DIR . 'includes/remote-api.php';
require_once FA_DIR . 'includes/activation.php';

register_activation_hook(__FILE__, 'fa_activate');
register_deactivation_hook(__FILE__, 'fa_deactivate');
