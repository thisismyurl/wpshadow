<?php
/**
 * Author:              Christopher Ross
 * Author URI:          https://thisismyurl.com/?source=core-support-thisismyurl
 * Plugin Name:         Core Support (thisismyurl)
 * Plugin URI:          https://thisismyurl.com/core-support-thisismyurl/?source=core-support-thisismyurl
 * Donate link:         https://thisismyurl.com/core-support-thisismyurl/#register?source=core-support-thisismyurl
 * Description:         The Hub of the thisismyurl Media Suite. Provides Multi-Engine Fallback, Encryption, Cloud Bridge, and Killer Features (Pixel-Sovereign, Smart Focus-Point, The Vault, Surgical Scrubbing, Broken Link Guardian).
 * Tags:                media, core, hub, architecture, images, encryption, vault
 * Version:             1.2601.71900
 * Requires at least:   6.4
 * Requires PHP:        8.1.29
 * Update URI:          https://github.com/thisismyurl/core-support-thisismyurl
 * GitHub Plugin URI:   https://github.com/thisismyurl/core-support-thisismyurl
 * Primary Branch:      main
 * Text Domain:         core-support-thisismyurl
 * License:             GPL2
 * License URI:         https://www.gnu.org/licenses/gpl-2.0.html
 *
 * @package TIMU_CORE_SUPPORT
 */

declare(strict_types=1);

namespace TIMU\CoreSupport;

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Plugin constants.
define( 'TIMU_CORE_VERSION', '1.2601.71900' );
define( 'TIMU_CORE_FILE', __FILE__ );
define( 'TIMU_CORE_PATH', plugin_dir_path( __FILE__ ) );
define( 'TIMU_CORE_URL', plugin_dir_url( __FILE__ ) );
define( 'TIMU_CORE_BASENAME', plugin_basename( __FILE__ ) );
define( 'TIMU_CORE_TEXT_DOMAIN', 'core-support-thisismyurl' );

// Suite Identifier for Hub & Spoke handshake.
define( 'TIMU_SUITE_ID', 'thisismyurl-media-suite-2026' );

// Minimum requirements.
define( 'TIMU_CORE_MIN_PHP', '8.1.29' );
define( 'TIMU_CORE_MIN_WP', '6.4.0' );

/**
 * Plugin activation hook.
 *
 * @return void
 */
function timu_core_activate(): void {
	// Check PHP version.
	if ( version_compare( PHP_VERSION, TIMU_CORE_MIN_PHP, '<' ) ) {
		deactivate_plugins( TIMU_CORE_BASENAME );
		wp_die(
			sprintf(
				/* translators: 1: Required PHP version, 2: Current PHP version */
				esc_html__( 'Core Support requires PHP %1$s or higher. You are running PHP %2$s.', 'core-support-thisismyurl' ),
				esc_html( TIMU_CORE_MIN_PHP ),
				esc_html( PHP_VERSION )
			),
			esc_html__( 'Plugin Activation Error', 'core-support-thisismyurl' ),
			array( 'back_link' => true )
		);
	}

	// Check WordPress version.
	global $wp_version;
	if ( version_compare( $wp_version, TIMU_CORE_MIN_WP, '<' ) ) {
		deactivate_plugins( TIMU_CORE_BASENAME );
		wp_die(
			sprintf(
				/* translators: 1: Required WordPress version, 2: Current WordPress version */
				esc_html__( 'Core Support requires WordPress %1$s or higher. You are running WordPress %2$s.', 'core-support-thisismyurl' ),
				esc_html( TIMU_CORE_MIN_WP ),
				esc_html( $wp_version )
			),
			esc_html__( 'Plugin Activation Error', 'core-support-thisismyurl' ),
			array( 'back_link' => true )
		);
	}

	// Create vault directory with proper permissions.
	timu_core_setup_vault();

	// Flush rewrite rules.
	flush_rewrite_rules();
}

/**
 * Plugin deactivation hook.
 *
 * @return void
 */
function timu_core_deactivate(): void {
	// Flush rewrite rules.
	flush_rewrite_rules();
}

/**
 * Setup the vault directory for secure original storage.
 *
 * @return bool True on success, false on failure.
 */
function timu_core_setup_vault(): bool {
	$upload_dir = wp_upload_dir();
	$vault_path = $upload_dir['basedir'] . '/vault';

	// Create vault directory if it doesn't exist.
	if ( ! file_exists( $vault_path ) ) {
		if ( ! wp_mkdir_p( $vault_path ) ) {
			error_log( 'TIMU Core: Failed to create vault directory at ' . $vault_path );
			return false;
		}
	}

	// Create .htaccess for Apache protection.
	$htaccess_file = $vault_path . '/.htaccess';
	if ( ! file_exists( $htaccess_file ) ) {
		$htaccess_content = "# Protect vault directory\n";
		$htaccess_content .= "Options -Indexes\n";
		$htaccess_content .= "<FilesMatch \"\\.(zip|jpg|jpeg|png|gif|webp|avif|heic|bmp|tiff|svg|raw)$\">\n";
		$htaccess_content .= "    Require all denied\n";
		$htaccess_content .= "</FilesMatch>\n";

		file_put_contents( $htaccess_file, $htaccess_content );
	}

	// Create index.php to prevent directory listing.
	$index_file = $vault_path . '/index.php';
	if ( ! file_exists( $index_file ) ) {
		file_put_contents( $index_file, "<?php\n// Silence is golden.\n" );
	}

	return true;
}

/**
 * Initialize the plugin.
 *
 * @return void
 */
function timu_core_init(): void {
	// Load text domain for translations.
	load_plugin_textdomain(
		'core-support-thisismyurl',
		false,
		dirname( TIMU_CORE_BASENAME ) . '/languages'
	);

	// Load module registry.
	require_once TIMU_CORE_PATH . 'includes/class-timu-module-registry.php';
	TIMU_Module_Registry::init();

	// Initialize multisite support if applicable.
	if ( is_multisite() ) {
		add_action( 'network_admin_menu', __NAMESPACE__ . '\\timu_core_network_admin_menu' );
	}

	// Register admin menu.
	add_action( 'admin_menu', __NAMESPACE__ . '\\timu_core_admin_menu' );

	// Handle AJAX actions.
	add_action( 'wp_ajax_timu_toggle_module', __NAMESPACE__ . '\\timu_ajax_toggle_module' );

	// Enqueue admin scripts and styles.
	add_action( 'admin_enqueue_scripts', __NAMESPACE__ . '\\timu_core_admin_enqueue' );
}

/**
 * Register network admin menu for multisite.
 *
 * @return void
 */
function timu_core_network_admin_menu(): void {
	add_menu_page(
		__( 'Support', 'core-support-thisismyurl' ),
		__( 'Support', 'core-support-thisismyurl' ),
		'manage_network_options',
		'timu-core-support',
		__NAMESPACE__ . '\\timu_core_render_dashboard',
		'dashicons-admin-generic',
		30
	);

	add_submenu_page(
		'timu-core-support',
		__( 'Modules', 'core-support-thisismyurl' ),
		__( 'Modules', 'core-support-thisismyurl' ),
		'manage_network_options',
		'timu-core-support',
		__NAMESPACE__ . '\\timu_core_render_dashboard'
	);

	add_submenu_page(
		'timu-core-support',
		__( 'Network Settings', 'core-support-thisismyurl' ),
		__( 'Network Settings', 'core-support-thisismyurl' ),
		'manage_network_options',
		'timu-core-network-settings',
		__NAMESPACE__ . '\\timu_core_render_network_settings'
	);
}

/**
 * Register admin menu.
 *
 * @return void
 */
function timu_core_admin_menu(): void {
	add_menu_page(
		__( 'Support', 'core-support-thisismyurl' ),
		__( 'Support', 'core-support-thisismyurl' ),
		'manage_options',
		'timu-core-support',
		__NAMESPACE__ . '\\timu_core_render_dashboard',
		'dashicons-admin-generic',
		30
	);

	add_submenu_page(
		'timu-core-support',
		__( 'Modules', 'core-support-thisismyurl' ),
		__( 'Modules', 'core-support-thisismyurl' ),
		'manage_options',
		'timu-core-support',
		__NAMESPACE__ . '\\timu_core_render_dashboard'
	);

	add_submenu_page(
		'timu-core-support',
		__( 'Settings', 'core-support-thisismyurl' ),
		__( 'Settings', 'core-support-thisismyurl' ),
		'manage_options',
		'timu-core-settings',
		__NAMESPACE__ . '\\timu_core_render_settings_page'
	);
}

/**
 * Render modules dashboard.
 *
 * @return void
 */
function timu_core_render_dashboard(): void {
	if ( ! current_user_can( 'manage_options' ) && ! current_user_can( 'manage_network_options' ) ) {
		wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'core-support-thisismyurl' ) );
	}

	$installed_modules = TIMU_Module_Registry::get_modules();
	$catalog_modules   = TIMU_Module_Registry::get_catalog_with_status();

	$total_installed = count( $installed_modules );
	$enabled         = count( array_filter( $installed_modules, fn( $m ) => TIMU_Module_Registry::is_enabled( $m['slug'] ) ) );
	$hubs            = count( TIMU_Module_Registry::get_modules( 'hub' ) );
	$spokes          = count( TIMU_Module_Registry::get_modules( 'spoke' ) );
	$available       = count( array_filter( $catalog_modules, fn( $m ) => empty( $m['installed'] ) ) );
	$updates         = count( array_filter( $catalog_modules, fn( $m ) => ! empty( $m['update_available'] ) ) );
	$modules         = $catalog_modules;

	require_once TIMU_CORE_PATH . 'includes/views/dashboard.php';
}

/**
 * Render network settings page.
 *
 * @return void
 */
function timu_core_render_network_settings(): void {
	if ( ! current_user_can( 'manage_network_options' ) ) {
		wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'core-support-thisismyurl' ) );
	}

	echo '<div class="wrap">';
	echo '<h1>' . esc_html__( 'Network Settings', 'core-support-thisismyurl' ) . '</h1>';
	echo '<p>' . esc_html__( 'Global Network Governance settings for the thisismyurl Media Suite.', 'core-support-thisismyurl' ) . '</p>';
	echo '</div>';
}

/**
 * Render settings page.
 *
 * @return void
 */
function timu_core_render_settings_page(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'core-support-thisismyurl' ) );
	}

	echo '<div class="wrap">';
	echo '<h1>' . esc_html__( 'Core Support - Settings', 'core-support-thisismyurl' ) . '</h1>';
	echo '<p>' . esc_html__( 'Configure the thisismyurl Media Suite settings.', 'core-support-thisismyurl' ) . '</p>';
	echo '</div>';
}

/**
 * Handle AJAX toggle module request.
 *
 * @return void
 */
function timu_ajax_toggle_module(): void {
	check_ajax_referer( 'timu_toggle_module', 'nonce' );

	if ( ! current_user_can( 'manage_options' ) && ! current_user_can( 'manage_network_options' ) ) {
		wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'core-support-thisismyurl' ) ) );
	}

	$slug    = sanitize_text_field( wp_unslash( $_POST['slug'] ?? '' ) );
	$enabled = isset( $_POST['enabled'] ) && 'true' === $_POST['enabled'];
	$network = isset( $_POST['network'] ) && 'true' === $_POST['network'] && is_multisite();

	if ( empty( $slug ) ) {
		wp_send_json_error( array( 'message' => __( 'Invalid module slug.', 'core-support-thisismyurl' ) ) );
	}

	$settings = array( 'enabled' => $enabled );
	$success  = TIMU_Module_Registry::update_module_settings( $slug, $settings, $network );

	if ( $success ) {
		wp_send_json_success( array( 'message' => __( 'Module settings updated.', 'core-support-thisismyurl' ) ) );
	} else {
		wp_send_json_error( array( 'message' => __( 'Failed to update settings.', 'core-support-thisismyurl' ) ) );
	}
}

/**
 * Enqueue admin scripts and styles.
 *
 * @param string $hook The current admin page hook.
 * @return void
 */
function timu_core_admin_enqueue( string $hook ): void {
	// Only load on our plugin pages.
	if ( strpos( $hook, 'timu-core' ) === false ) {
		return;
	}

	wp_enqueue_style(
		'timu-core-admin',
		TIMU_CORE_URL . 'assets/css/admin.css',
		array(),
		TIMU_CORE_VERSION
	);

	wp_enqueue_script(
		'timu-core-admin',
		TIMU_CORE_URL . 'assets/js/admin.js',
		array( 'jquery' ),
		TIMU_CORE_VERSION,
		true
	);

	// Localize script for AJAX and i18n.
	wp_localize_script(
		'timu-core-admin',
		'timuAdminData',
		array(
			'toggleNonce' => wp_create_nonce( 'timu_toggle_module' ),
			'i18n'        => array(
				'enabled'    => __( 'Enabled', 'core-support-thisismyurl' ),
				'disabled'   => __( 'Disabled', 'core-support-thisismyurl' ),
				'ajaxError'  => __( 'An error occurred. Please try again.', 'core-support-thisismyurl' ),
				'noResults'  => __( 'No modules match this filter.', 'core-support-thisismyurl' ),
				'installFirst' => __( 'Install the module before enabling it.', 'core-support-thisismyurl' ),
			),
		)
	);
}

// Register activation and deactivation hooks.
register_activation_hook( __FILE__, __NAMESPACE__ . '\\timu_core_activate' );
register_deactivation_hook( __FILE__, __NAMESPACE__ . '\\timu_core_deactivate' );

// Initialize the plugin.
add_action( 'plugins_loaded', __NAMESPACE__ . '\\timu_core_init' );

/* @changelog
 * [1.2601.71900] - 2026-01-07 18:45
 * - Added bundled catalog for hubs/spokes with optional remote override
 * - Catalog drives dashboard with available/update states and GitHub release links
 * - Dashboard shows Available/Updates stats and status badges per module
 * - Prevent enable toggles for non-installed modules; added notices
 * - Catalog merged with installed modules (even if missing from catalog)
 * - Issue #31: Bundled JSON catalog + updater integration groundwork
 *
 * [1.2601.71818] - 2026-01-07 18:18
 * - Completed Issue #1: Support Menu & Modules Dashboard
 * - Created TIMU_Module_Registry class for action-based module discovery
 * - Implemented Support top-level menu in both site and network admin
 * - Built responsive modules dashboard with stats, filters, and toggle controls
 * - Added AJAX handler for module enable/disable with multisite support
 * - Created dashboard view template with module cards (hub/spoke differentiation)
 * - Implemented JavaScript dashboard controller with filter persistence
 * - Added toggle switch UI with loading states and notifications
 * - Module settings persist at site-level and network-level via options
 * - Module registry includes 5-minute transient caching
 * - Dashboard shows: Total Modules, Enabled, Hubs, Spokes
 * - Acceptance Criteria Met:
 *   ✓ Support menu appears once Core is active (site + network)
 *   ✓ Modules list shows locally installed add-ons (via action hook)
 *   ✓ Module status and toggles persist correctly at site and network scope
 *   ✓ Feature toggles work via AJAX with visual feedback
 * - Files Modified:
 *   - core-support-thisismyurl.php: Added menu structure, AJAX handlers
 *   - includes/class-timu-module-registry.php: Full registry implementation
 *   - includes/views/dashboard.php: Dashboard template with module cards
 *   - assets/css/admin.css: Extended with toggle, grid, and loading styles
 *   - assets/js/admin.js: Dashboard controller with AJAX and filtering
 * 
 * - Completed Issue #24: Internationalization Baseline
 * - Created languages/ directory with placeholder POT file
 * - Verified all user-facing strings use gettext functions (__(), _e(), esc_html__())
 * - Confirmed text domain 'core-support-thisismyurl' loads via load_plugin_textdomain()
 * - Ready for WP-CLI i18n make-pot to generate complete translation template
 * - Minimum PHP version updated to 8.1.29
 *
 * [1.2601.71701] - 2026-01-07 17:17
 * - Initial plugin structure created
 * - Implemented Hub architecture with Suite ID handshake
 * - Added multisite support with network admin menu
 * - Created vault directory setup with security measures
 * - Enforced PHP 8.4+ and WordPress 6.4+ requirements
 * - Implemented strict typing and proper SVE protocol
 * - Added i18n support with proper text domain
 */
