<?php
/**
 * Plugin Name: WPShadow
 * Description: WordPress health monitoring, security diagnostics, and performance optimization.
 * Version: 0.6093.1200
 * Author: thisismyurl
 * Text Domain: wpshadow
 * Requires PHP: 8.1
 * Requires at least: 6.4
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 *
 * @package WPShadow
 * @since 0.6093.1200
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugin Constants
 */
define( 'WPSHADOW_VERSION', '0.6093.1200' );
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
require_once WPSHADOW_PATH . 'includes/systems/core/class-hooks-initializer.php';

if ( ! function_exists( 'wpshadow_activate_plugin' ) ) {
	/**
	 * Run plugin activation tasks.
	 *
	 * @return void
	 */
	function wpshadow_activate_plugin() {
		\WPShadow\Core\Bootstrap_Autoloader::init();
		\WPShadow\Core\Hooks_Initializer::on_activate();
	}
}

if ( ! function_exists( 'wpshadow_deactivate_plugin' ) ) {
	/**
	 * Run plugin deactivation tasks.
	 *
	 * @return void
	 */
	function wpshadow_deactivate_plugin() {
		\WPShadow\Core\Bootstrap_Autoloader::init();
		\WPShadow\Core\Hooks_Initializer::on_deactivate();
	}
}

register_activation_hook( __FILE__, 'wpshadow_activate_plugin' );
register_deactivation_hook( __FILE__, 'wpshadow_deactivate_plugin' );

/**
 * Load UI callback functions used by admin menus.
 *
 * These files define global callbacks like wpshadow_render_dashboard()
 * which are referenced by add_menu_page()/add_submenu_page().
 */
$wpshadow_ui_view_files = array(
	'includes/ui/views/functions-page-layout.php',
	'includes/ui/views/menu-stubs.php',
	'includes/ui/views/dashboard-page.php',
	'includes/systems/core/functions-treatment.php',
);

foreach ( $wpshadow_ui_view_files as $wpshadow_ui_view_file ) {
	$wpshadow_ui_view_path = WPSHADOW_PATH . $wpshadow_ui_view_file;
	if ( file_exists( $wpshadow_ui_view_path ) ) {
		require_once $wpshadow_ui_view_path;
	}
}

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
