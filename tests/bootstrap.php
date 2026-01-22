<?php
/**
 * PHPUnit bootstrap file for WPShadow tests
 *
 * @package WPShadow
 */

// Load Composer autoloader
require_once dirname(__DIR__) . '/vendor/autoload.php';

// Initialize Brain Monkey for WordPress function mocking
\Brain\Monkey\setUp();

// Define WordPress constants that might be used in diagnostics
if (!defined('ABSPATH')) {
    define('ABSPATH', '/tmp/wordpress/');
}

if (!defined('WP_CONTENT_DIR')) {
    define('WP_CONTENT_DIR', ABSPATH . 'wp-content');
}

if (!defined('WP_PLUGIN_DIR')) {
    define('WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins');
}

if (!defined('WPINC')) {
    define('WPINC', 'wp-includes');
}
