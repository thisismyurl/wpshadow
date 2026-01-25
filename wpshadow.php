<?php

/**
 * Plugin Name: WPShadow
 * Description: Minimal bootstrap to show WPShadow menu and Settings link.
 * Version: 1.2601.2148
 * Author: thisismyurl
 */

if (! defined('ABSPATH')) {
	exit;
}

define('WPSHADOW_VERSION', '1.2601.2148');
define('WPSHADOW_BASENAME', plugin_basename(__FILE__));
define('WPSHADOW_PATH', plugin_dir_path(__FILE__));
define('WPSHADOW_URL', plugin_dir_url(__FILE__));

/**
 * Load essential base classes (required by all other systems)
 *
 * These are loaded here rather than in Plugin_Bootstrap because they're
 * dependencies for other classes that might be loaded before plugins_loaded.
 */
require_once WPSHADOW_PATH . 'includes/core/class-ajax-handler-base.php';
require_once WPSHADOW_PATH . 'includes/core/class-treatment-base.php';
require_once WPSHADOW_PATH . 'includes/core/class-diagnostic-base.php';
require_once WPSHADOW_PATH . 'includes/core/class-activity-logger.php';
require_once WPSHADOW_PATH . 'includes/core/class-error-handler.php';
require_once WPSHADOW_PATH . 'includes/core/class-settings-registry.php';
require_once WPSHADOW_PATH . 'includes/core/class-database-migrator.php';
require_once WPSHADOW_PATH . 'includes/core/functions-treatment.php';

/**
 * Initialize error handler early
 *
 * This must run before plugins_loaded so fatal errors during bootstrap are handled.
 */

/**
 * Initialize Settings Registry
 *
 * Register all settings with WordPress Settings API for proper validation,
 * sanitization, and WordPress integration.
 */
\WPShadow\Core\Settings_Registry::register();
\WPShadow\Core\Error_Handler::init();

/**
 * Load bootstrap and initialize all systems on plugins_loaded
 *
 * This gives all plugins a chance to load their hooks before WPShadow initializes.
 */
require_once WPSHADOW_PATH . 'includes/core/class-menu-manager.php';
require_once WPSHADOW_PATH . 'includes/core/class-ajax-router.php';
require_once WPSHADOW_PATH . 'includes/core/class-hooks-initializer.php';
require_once WPSHADOW_PATH . 'includes/core/class-plugin-bootstrap.php';

add_action('plugins_loaded', function () {
	\WPShadow\Core\Plugin_Bootstrap::init();
});
