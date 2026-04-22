<?php
/**
 * Plugin Name: WPShadow
 * Description: WordPress health monitoring, security diagnostics, and performance optimization.
 * Version: 0.Yddd
 * Author: thisismyurl
 * Text Domain: wpshadow
 * Requires PHP: 8.1
 * Requires at least: 6.4
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 *
 * @package WPShadow
 * @since 0.Yddd
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Define the plugin-wide constants used during bootstrap.
 *
 * These are intentionally declared in the main plugin file because they are
 * needed before the autoloader and service bootstrap can resolve any classes.
 * In practice they form the contract between WordPress' plugin loader and the
 * rest of the WPShadow codebase.
 */
define( 'WPSHADOW_VERSION', '0.Yddd' );
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
	 * Activation is intentionally kept thin here. The main file only ensures the
	 * autoloader exists, then delegates real setup work to Hooks_Initializer so
	 * lifecycle behavior stays centralized in one place.
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
	 * Deactivation mirrors activation: this file boots the minimum runtime and
	 * then hands off to the centralized lifecycle coordinator.
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
 * These files define global callbacks and helper functions referenced by
 * add_menu_page()/add_submenu_page().
 */
$wpshadow_ui_view_files = array(
	'includes/ui/views/functions-page-layout.php',
	'includes/ui/views/menu-stubs.php',
	'includes/ui/views/dashboard-shared.php',
	'includes/ui/views/dashboard-page-v2.php',
	'includes/systems/core/functions-runtime.php',
	'includes/systems/core/functions-treatment.php',
);

foreach ( $wpshadow_ui_view_files as $wpshadow_ui_view_file ) {
	$wpshadow_ui_view_path = WPSHADOW_PATH . $wpshadow_ui_view_file;
	if ( file_exists( $wpshadow_ui_view_path ) ) {
		require_once $wpshadow_ui_view_path;
	}
}

/**
 * Prime the autoloader as early as possible once plugins are loaded.
 *
 * This first bootstrap step does not initialize subsystems yet. Its only job
 * is to make classes resolvable before later init hooks begin wiring menus,
 * settings, diagnostics, and treatments.
 */
add_action(
	'plugins_loaded',
	function () {
		\WPShadow\Core\Bootstrap_Autoloader::init();
	},
	1
);

/**
 * Register plugin settings after translations are available.
 *
 * This ordering keeps translated labels available to the Settings API while
 * still running early enough that downstream systems can rely on defaults.
 */
add_action(
	'init',
	function () {
		\WPShadow\Core\Settings_Registry::register();
	},
	5
);

/**
 * Hand off to the service bootstrap once WordPress init is underway.
 *
 * Plugin_Bootstrap owns the dependency order for the rest of the plugin, so
 * the main file stops making decisions here and delegates orchestration.
 */
add_action(
	'init',
	function () {
		\WPShadow\Core\Plugin_Bootstrap::init();
	},
	20
);

/**
 * Disable caching on WPShadow admin pages.
 *
 * The dashboard is highly stateful and responds to scans, AJAX actions, and
 * treatment results. Preventing page/object/db caching here reduces confusing
 * stale states while people learn from or operate the plugin.
 */
add_action(
	'admin_init',
	function () {
		$page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		if ( empty( $page ) || 0 !== strpos( $page, 'wpshadow' ) ) {
			return;
		}

		if ( ! defined( 'DONOTCACHEPAGE' ) ) {
			// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedConstantFound
			define( 'DONOTCACHEPAGE', true );
		}

		if ( ! defined( 'DONOTCACHEOBJECT' ) ) {
			// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedConstantFound
			define( 'DONOTCACHEOBJECT', true );
		}

		if ( ! defined( 'DONOTCACHEDB' ) ) {
			// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedConstantFound
			define( 'DONOTCACHEDB', true );
		}

		if ( function_exists( 'nocache_headers' ) ) {
			nocache_headers();
		}
	},
	1
);
