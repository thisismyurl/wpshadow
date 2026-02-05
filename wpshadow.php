<?php
/**
 * Plugin Name: WPShadow
 * Description: WordPress health monitoring, security diagnostics, and performance optimization.
 * Version: 1.6035.2150
 * Author: thisismyurl
 * Text Domain: wpshadow
 * Requires PHP: 8.1
 * Requires at least: 6.4
 *
 * @package WPShadow
 * @since   1.0000.0000
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugin Constants
 */
define( 'WPSHADOW_VERSION', '1.6035.2150' );
define( 'WPSHADOW_FILE', __FILE__ );
define( 'WPSHADOW_BASENAME', plugin_basename( __FILE__ ) );
define( 'WPSHADOW_PATH', plugin_dir_path( __FILE__ ) );
define( 'WPSHADOW_URL', plugin_dir_url( __FILE__ ) );

/**
 * Composer Autoloader (PSR-4)
 *
 * Enables automatic class loading for PSR-4 compliant files.
 */
if ( file_exists( WPSHADOW_PATH . 'vendor/autoload.php' ) ) {
	require_once WPSHADOW_PATH . 'vendor/autoload.php';
}

/**
 * Bootstrap Autoloader
 *
 * Automatically loads all WPShadow classes in dependency order.
 * Replaces 130+ manual require_once calls.
 *
 * Phase 4: Bootstrap Consolidation - eliminates manual loading.
 * Phase 6: Final Polish - clean implementation.
 */
require_once WPSHADOW_PATH . 'includes/systems/core/class-bootstrap-autoloader.php';

/**
 * Hook Registry
 *
 * Auto-discovers and subscribes all Hook_Subscriber_Base classes.
 * Phase 2: Perfect Hooks Pattern - eliminates manual ::init() calls.
 */
require_once WPSHADOW_PATH . 'includes/systems/core/class-hook-registry.php';

/**
 * Initialize Bootstrap
 *
 * Load all classes and initialize error handling.
 * This runs at priority 1 to ensure classes are available early.
 */
add_action(
	'plugins_loaded',
	function () {
		\WPShadow\Core\Bootstrap_Autoloader::init();
	},
	1
);

/**
 * Initialize Hook Registry
 *
 * Auto-discover and subscribe all Hook_Subscriber_Base classes.
 * This runs at priority 5, after classes are loaded.
 * 
 * Phase 6: All 45+ Hook_Subscriber_Base classes are auto-subscribed.
 * Zero manual ::init() calls required!
 */
add_action(
	'plugins_loaded',
	function () {
		\WPShadow\Core\Hook_Registry::init();
	},
	5
);

/**
 * Load Translations
 *
 * WordPress 6.7.0+ requires translations on init or later.
 */
add_action(
	'init',
	function () {
		load_plugin_textdomain( 'wpshadow', false, dirname( WPSHADOW_BASENAME ) . '/languages' );
	},
	1
);

/**
 * Register Settings
 *
 * Register all plugin settings after translations are loaded.
 */
add_action(
	'init',
	function () {
		\WPShadow\Core\Settings_Registry::register();
	},
	5
);

/**
 * Initialize Core Systems
 *
 * Bootstrap the plugin after all classes are loaded.
 */
add_action(
	'init',
	function () {
		\WPShadow\Core\Plugin_Bootstrap::init();
	},
	20
);

/**
 * Disable Admin Page Caching
 *
 * Prevents stale admin UI during development/testing.
 */
add_action(
	'admin_init',
	function () {
		$page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		if ( empty( $page ) || 0 !== strpos( $page, 'wpshadow' ) ) {
			return;
		}

		if ( ! defined( 'DONOTCACHEPAGE' ) ) {
			define( 'DONOTCACHEPAGE', true );
		}

		if ( ! defined( 'DONOTCACHEOBJECT' ) ) {
			define( 'DONOTCACHEOBJECT', true );
		}

		if ( ! defined( 'DONOTCACHEDB' ) ) {
			define( 'DONOTCACHEDB', true );
		}

		if ( function_exists( 'nocache_headers' ) ) {
			nocache_headers();
		}
	},
	1
);
