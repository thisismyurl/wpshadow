<?php
/**
 * Author:              Christopher Ross
 * Author URI:          https://thisismyurl.com/?source=plugin-wp-support-thisismyurl
 * Plugin Name:         WP Support (thisismyurl)
 * Plugin URI:          https://thisismyurl.com/plugin-wp-support-thisismyurl/?source=plugin-wp-support-thisismyurl
 * Donate link:         https://thisismyurl.com/plugin-wp-support-thisismyurl/#register?source=plugin-wp-support-thisismyurl
 * Description:         The foundational plugin for all thisismyurl plugin-* repositories. Provides the backbone architecture for hub and spoke plugins, managing installations, updates, and features.
 * Tags:                wordpress, plugin, foundation, hub, architecture, management, suite
 * Version:             1.2601.73001
 * Requires at least:   6.4
 * Requires PHP:        8.1.29
 * Update URI:          https://github.com/thisismyurl/plugin-wp-support-thisismyurl
 * GitHub Plugin URI:   https://github.com/thisismyurl/plugin-wp-support-thisismyurl
 * Primary Branch:      main
 * Text Domain:         plugin-wp-support-thisismyurl
 * License:             GPL2
 * License URI:         https://www.gnu.org/licenses/gpl-2.0.html
 * @package TIMU_WP_SUPPORT_THISISMYURL
 */

declare(strict_types=1);

namespace TIMU\CoreSupport;

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Plugin constants.
define( 'TIMU_CORE_VERSION', '1.2601.73001' );
define( 'TIMU_CORE_FILE', __FILE__ );
define( 'TIMU_CORE_PATH', plugin_dir_path( __FILE__ ) );
define( 'TIMU_CORE_URL', plugin_dir_url( __FILE__ ) );
define( 'TIMU_CORE_BASENAME', plugin_basename( __FILE__ ) );
define( 'TIMU_CORE_TEXT_DOMAIN', 'plugin-wp-support-thisismyurl' );

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
				esc_html__( 'WordPress Support requires PHP %1$s or higher. You are running PHP %2$s.', 'plugin-wp-support-thisismyurl' ),
				esc_html( TIMU_CORE_MIN_PHP ),
				esc_html( PHP_VERSION )
			),
			esc_html__( 'Plugin Activation Error', 'plugin-wp-support-thisismyurl' ),
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
				esc_html__( 'WordPress Support requires WordPress %1$s or higher. You are running WordPress %2$s.', 'plugin-wp-support-thisismyurl' ),
				esc_html( TIMU_CORE_MIN_WP ),
				esc_html( $wp_version )
			),
			esc_html__( 'Plugin Activation Error', 'plugin-wp-support-thisismyurl' ),
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

	// Get or generate vault directory name (hidden with random suffix).
	$vault_dirname = get_option( 'timu_vault_dirname' );
	if ( empty( $vault_dirname ) ) {
		// Generate random directory name (e.g., .vault_a1b2c3d4e5f6).
		$random_suffix = bin2hex( random_bytes( 6 ) );
		$vault_dirname = '.vault_' . $random_suffix;
		update_option( 'timu_vault_dirname', $vault_dirname );
	}

	$vault_path = $upload_dir['basedir'] . '/' . $vault_dirname;

	// Create vault directory if it doesn't exist.
	if ( ! file_exists( $vault_path ) ) {
		if ( ! wp_mkdir_p( $vault_path ) ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			error_log( 'TIMU Core: Failed to create vault directory at ' . $vault_path );
			return false;
		}
	}

	// Create .htaccess for Apache protection.
	$htaccess_file = $vault_path . '/.htaccess';
	if ( ! file_exists( $htaccess_file ) ) {
		$htaccess_content  = "# Protect vault directory\n";
		$htaccess_content .= "Options -Indexes\n";
		$htaccess_content .= "# Block direct access to vault files\n";
		$htaccess_content .= "<FilesMatch \"\\.(zip|jpg|jpeg|png|gif|webp|avif|heic|bmp|tiff|svg|raw|enc)$\">\n";
		$htaccess_content .= "    Require all denied\n";
		$htaccess_content .= "</FilesMatch>\n";
		$htaccess_content .= "# Block directory listing and script execution\n";
		$htaccess_content .= "<IfModule mod_php7.c>\n";
		$htaccess_content .= "    php_flag engine off\n";
		$htaccess_content .= "</IfModule>\n";
		$htaccess_content .= "<IfModule mod_php.c>\n";
		$htaccess_content .= "    php_flag engine off\n";
		$htaccess_content .= "</IfModule>\n";

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
		file_put_contents( $htaccess_file, $htaccess_content );
	}

	// Create index.php to prevent directory listing.
	$index_file = $vault_path . '/index.php';
	if ( ! file_exists( $index_file ) ) {
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
		file_put_contents( $index_file, "<?php\n// Silence is golden.\n" );
	}

	// Initialize encryption keys if enabled.
	timu_core_setup_encryption_keys();

	return true;
}

/**
 * Setup encryption keys for vault files.
 * Checks wp-config for TIMU_VAULT_KEY; generates if missing.
 *
 * @return bool True if keys are available, false otherwise.
 */
function timu_core_setup_encryption_keys(): bool {
	// If wp-config defines the key, use it.
	if ( defined( 'TIMU_VAULT_KEY' ) && TIMU_VAULT_KEY ) {
		return true;
	}

	// If not in wp-config, check if stored in options (for backward compatibility).
	$stored_key = get_option( 'timu_vault_encryption_key' );
	if ( ! empty( $stored_key ) ) {
		return true;
	}

	// For production, keys MUST be in wp-config.
	// For development, auto-generate and store (with warning).
	if ( 'production' === wp_get_environment_type() ) {
		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
		error_log( 'TIMU Core: Encryption enabled but TIMU_VAULT_KEY not defined in wp-config.php. Define it for production use.' );
		return false;
	}

	// Auto-generate for development.
	$new_key = bin2hex( random_bytes( 32 ) ); // 256-bit key.
	update_option( 'timu_vault_encryption_key', $new_key );

	// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
	error_log( 'TIMU Core: Generated temporary encryption key. For production, add this to wp-config.php: define( "TIMU_VAULT_KEY", "' . $new_key . '" );' );

	return true;
}

/**
 * Get the encryption key for vault operations.
 *
 * @return string|null Encryption key, or null if not available.
 */
function timu_core_get_vault_key(): ?string {
	if ( defined( 'TIMU_VAULT_KEY' ) && TIMU_VAULT_KEY ) {
		return TIMU_VAULT_KEY;
	}

	$stored_key = get_option( 'timu_vault_encryption_key' );
	return ! empty( $stored_key ) ? $stored_key : null;
}

/**
 * Check if encryption is supported and enabled.
 *
 * @return bool True if openssl is available, false otherwise.
 */
function timu_core_encryption_supported(): bool {
	return extension_loaded( 'openssl' );
}

/**
 * Initialize the plugin.
 *
 * @return void
 */
function timu_core_init(): void {
	@file_put_contents( TIMU_CORE_PATH . 'timu_core_init_called.txt', date( 'Y-m-d H:i:s' ) );
	error_log( '=== TIMU_CORE_INIT CALLED ===' );
	
	// Load text domain for translations.
	load_plugin_textdomain(
		TIMU_CORE_TEXT_DOMAIN,
		false,
		dirname( TIMU_CORE_BASENAME ) . '/languages'
	);

	// Load module bootstrap for child plugin installation and activation.
	require_once TIMU_CORE_PATH . 'includes/class-timu-module-bootstrap.php';
	TIMU_Module_Bootstrap::init();

	// Load module toggles for feature flags.
	require_once TIMU_CORE_PATH . 'includes/class-timu-module-toggles.php';
	TIMU_Module_Toggles::init();

	// Load module registry.
	require_once TIMU_CORE_PATH . 'includes/class-timu-module-registry.php';
	TIMU_Module_Registry::init();

	// Load module loader (manages independent module repositories).
	require_once TIMU_CORE_PATH . 'includes/class-timu-module-loader.php';
	error_log( '=== ABOUT TO CALL MODULE_LOADER::INIT ===' );
	@file_put_contents( TIMU_CORE_PATH . 'about_to_call_module_loader_init.txt', date( 'Y-m-d H:i:s' ) );
	\TIMU\Core\Module_Loader::init();
	@file_put_contents( TIMU_CORE_PATH . 'module_loader_init_returned.txt', date( 'Y-m-d H:i:s' ) );
	error_log( '=== MODULE_LOADER::INIT RETURNED ===' );

	// Load settings API (network + site with overrides).
	require_once TIMU_CORE_PATH . 'includes/class-timu-settings.php';
	TIMU_Settings::init();
	require_once TIMU_CORE_PATH . 'includes/timu-settings-functions.php';

	// Load capability manager.
	require_once TIMU_CORE_PATH . 'includes/class-timu-capabilities.php';

	// Load Site Health integration.
	require_once TIMU_CORE_PATH . 'includes/class-timu-site-health.php';
	TIMU_Site_Health::init();

	// Load Activity Logger.
	require_once TIMU_CORE_PATH . 'includes/class-timu-activity-logger.php';
	TIMU_Activity_Logger::init();

	// Load license utilities.
	require_once TIMU_CORE_PATH . 'includes/class-timu-license.php';
	TIMU_License::init();

	// Load feature registry for flexible plugin dependencies.
	require_once TIMU_CORE_PATH . 'includes/class-timu-feature-registry.php';
	require_once TIMU_CORE_PATH . 'includes/timu-feature-functions.php';
	TIMU_Feature_Registry::init();

	// Load Spoke Base for spoke plugins (Image, Media, etc).
	require_once TIMU_CORE_PATH . 'includes/class-timu-spoke-base.php';

	// Load Vault service (canonical implementation in vault-support plugin).
	// Core aliases it for backward compatibility.
	if ( ! class_exists( '\\TIMU\\VaultSupport\\TIMU_Vault' ) ) {
		// Vault plugin not loaded yet; defer to vault-support plugin.
		// If vault-support is active, it will provide TIMU_Vault.
		// Core will alias it when available.
	}

	// Always load the alias file which will create the alias if vault-support is available.
	if ( file_exists( TIMU_CORE_PATH . 'includes/class-timu-vault.php' ) ) {
		require_once TIMU_CORE_PATH . 'includes/class-timu-vault.php';
	}

	// Initialize Vault if available (via vault-support's implementation).
	if ( class_exists( '\\TIMU\\CoreSupport\\TIMU_Vault' ) ) {
		TIMU_Vault::init();
	}

	// Load vault size monitoring (real-time alerts).
	require_once TIMU_CORE_PATH . 'includes/class-timu-vault-size-monitor.php';
	TIMU_Vault_Size_Monitor::init();

	// Load network license broadcaster for multisite (Super Admin push to all sites).
	require_once TIMU_CORE_PATH . 'includes/class-timu-network-license.php';
	TIMU_Network_License::init();

	// Load plugin upgrader for install/update flows.
	require_once TIMU_CORE_PATH . 'includes/class-timu-plugin-upgrader.php';

	// Load module action handlers for AJAX install/update/activate.
	require_once TIMU_CORE_PATH . 'includes/class-timu-module-actions.php';
	TIMU_Module_Actions::init();

	// Load tab navigation system.
	require_once TIMU_CORE_PATH . 'includes/class-timu-tab-navigation.php';
	require_once TIMU_CORE_PATH . 'includes/class-timu-dashboard-widgets.php';

	// Load CLI commands when WP-CLI present.
	if ( defined( 'WP_CLI' ) && WP_CLI ) {
		require_once TIMU_CORE_PATH . 'includes/class-timu-cli.php';
		\WP_CLI::add_command( 'timu modules', '\\TIMU\\CoreSupport\\TIMU_CLI_Modules' );
		\WP_CLI::add_command( 'timu settings', '\\TIMU\\CoreSupport\\TIMU_CLI_Settings' );
	}

	// Load notice manager for persistent dismissal.
	require_once TIMU_CORE_PATH . 'includes/class-timu-notice-manager.php';
	TIMU_Notice_Manager::init();

	// Initialize multisite support if applicable.
	if ( is_multisite() ) {
		add_action( 'network_admin_menu', __NAMESPACE__ . '\\timu_core_network_admin_menu' );
	}

	// Register admin menu.
	add_action( 'admin_menu', __NAMESPACE__ . '\\timu_core_admin_menu' );

	// Handle capability mapping submissions.
	add_action( 'admin_init', __NAMESPACE__ . '\\timu_core_handle_capabilities_post' );

	// Handle AJAX actions.
	add_action( 'wp_ajax_timu_toggle_module', __NAMESPACE__ . '\\timu_ajax_toggle_module' );
	add_action( 'wp_ajax_timu_install_module', __NAMESPACE__ . '\\timu_ajax_install_module' );
	add_action( 'wp_ajax_timu_update_module', __NAMESPACE__ . '\\timu_ajax_update_module' );
	add_action( 'wp_ajax_timu_broadcast_license', __NAMESPACE__ . '\\timu_ajax_broadcast_license' );

	// Admin-post action to force scheduled tasks to run immediately.
	add_action( 'admin_post_timu_run_task_now', __NAMESPACE__ . '\timu_run_task_now' );

	// Plugin page links and meta.
	add_filter( 'plugin_action_links_' . TIMU_CORE_BASENAME, __NAMESPACE__ . '\\timu_core_plugin_action_links' );
	add_filter( 'plugin_row_meta', __NAMESPACE__ . '\\timu_core_plugin_row_meta', 10, 2 );

	// Enqueue admin scripts and styles.
	add_action( 'admin_enqueue_scripts', __NAMESPACE__ . '\\timu_core_admin_enqueue' );

	// Register GDPR Personal Data Exporter and Eraser.
	add_filter( 'wp_privacy_personal_data_exporters', __NAMESPACE__ . '\\timu_core_register_privacy_exporters' );
	// Register GDPR Personal Data Eraser for Vault.
	add_filter( 'wp_privacy_personal_data_erasers', __NAMESPACE__ . '\\timu_core_register_privacy_erasers' );
}

/**
 * Register network admin menu for multisite.
 *
 * @return void
 */
function timu_core_network_admin_menu(): void {
	add_menu_page(
		__( 'Support Dashboard', 'plugin-wp-support-thisismyurl' ),
		__( 'Support', 'plugin-wp-support-thisismyurl' ),
		'manage_network_options',
		'wp-support',
		__NAMESPACE__ . '\\timu_core_render_tab_router',
		'dashicons-admin-generic',
		999
	);

	add_submenu_page(
		'wp-support',
		__( 'Capabilities', 'plugin-wp-support-thisismyurl' ),
		__( 'Capabilities', 'plugin-wp-support-thisismyurl' ),
		'manage_network_options',
		'timu-capabilities',
		__NAMESPACE__ . '\\timu_core_render_capabilities_page'
	);

	// Dynamically register hub submenu items.
	timu_core_register_hub_submenus( 'manage_network_options' );

	// Initialize dashboard screen extras (Screen Options, Help) and metaboxes.
	add_action( 'load-toplevel_page_wp-support', __NAMESPACE__ . '\\timu_core_setup_dashboard_screen' );
}

/**
 * Register admin menu.
 *
 * @return void
 */
function timu_core_admin_menu(): void {
	add_menu_page(
		__( 'Support Dashboard', 'plugin-wp-support-thisismyurl' ),
		__( 'Support', 'plugin-wp-support-thisismyurl' ),
		'manage_options',
		'wp-support',
		__NAMESPACE__ . '\\timu_core_render_tab_router',
		'dashicons-admin-generic',
		999
	);

	add_submenu_page(
		'wp-support',
		__( 'Capabilities', 'plugin-wp-support-thisismyurl' ),
		__( 'Capabilities', 'plugin-wp-support-thisismyurl' ),
		'manage_options',
		'timu-capabilities',
		__NAMESPACE__ . '\\timu_core_render_capabilities_page'
	);

	// Dynamically register hub submenu items.
	timu_core_register_hub_submenus( 'manage_options' );

	// Initialize dashboard screen extras (Screen Options, Help) and metaboxes.
	add_action( 'load-toplevel_page_wp-support', __NAMESPACE__ . '\\timu_core_setup_dashboard_screen' );
}

/**
 * Register submenu items for all active hubs.
 *
 * @param string $capability Required capability (manage_options or manage_network_options).
 * @return void
 */
function timu_core_register_hub_submenus( string $capability ): void {
	$catalog = TIMU_Module_Registry::get_catalog_with_status();
	$hubs    = array_filter(
		$catalog,
		fn( $m ) => 'hub' === ( $m['type'] ?? '' ) && ! empty( $m['status']['active'] )
	);

	foreach ( $hubs as $hub ) {
		$hub_id   = sanitize_key( str_replace( '-support-thisismyurl', '', $hub['id'] ?? '' ) );
		$hub_name = esc_html( $hub['name'] ?? ucfirst( $hub_id ) );
		$hub_url  = TIMU_Tab_Navigation::build_hub_url( $hub_id );

		add_submenu_page(
			'wp-support',
			$hub_name,
			$hub_name,
			$capability,
			'wp-support-hub-' . $hub_id, // Unique page slug.
			__NAMESPACE__ . '\\timu_core_render_tab_router' // Same router handles all.
		);
	}
}

/**
 * Render the capabilities management page.
 *
 * @return void
 */
function timu_core_render_capabilities_page(): void {
	$required_cap = is_network_admin() ? 'manage_network_options' : 'manage_options';

	if ( ! current_user_can( $required_cap ) ) {
		wp_die( esc_html__( 'You do not have sufficient permissions to manage capabilities.', 'plugin-wp-support-thisismyurl' ) );
	}

	require TIMU_CORE_PATH . 'includes/views/capabilities.php';
}

/**
 * Handle capability mapping submissions.
 *
 * @return void
 */
function timu_core_handle_capabilities_post(): void {
	if ( ! is_admin() ) {
		return;
	}

	$action = isset( $_POST['timu_capability_action'] ) ? sanitize_key( wp_unslash( $_POST['timu_capability_action'] ) ) : '';
	if ( 'add' !== $action ) {
		return;
	}

	$required_cap = is_network_admin() ? 'manage_network_options' : 'manage_options';
	if ( ! current_user_can( $required_cap ) ) {
		wp_die( esc_html__( 'You do not have sufficient permissions to manage capabilities.', 'plugin-wp-support-thisismyurl' ) );
	}

	$nonce = isset( $_POST['timu_capabilities_nonce'] ) ? wp_unslash( $_POST['timu_capabilities_nonce'] ) : '';
	if ( ! wp_verify_nonce( $nonce, 'timu_capabilities' ) ) {
		wp_die( esc_html__( 'Nonce verification failed. Please try again.', 'plugin-wp-support-thisismyurl' ) );
	}

	$module_slug    = isset( $_POST['timu_module_slug'] ) ? sanitize_key( wp_unslash( $_POST['timu_module_slug'] ) ) : '';
	$capability_key = isset( $_POST['timu_capability_key'] ) ? sanitize_key( wp_unslash( $_POST['timu_capability_key'] ) ) : '';
	$wp_capability  = isset( $_POST['timu_wp_capability'] ) ? sanitize_key( wp_unslash( $_POST['timu_wp_capability'] ) ) : '';

	if ( empty( $module_slug ) || empty( $capability_key ) || empty( $wp_capability ) ) {
		add_settings_error(
			'timu_capabilities',
			'timu_capabilities_invalid',
			esc_html__( 'All fields are required to register a capability mapping.', 'plugin-wp-support-thisismyurl' ),
			'error'
		);
	} else {
		TIMU_Capabilities::register_capability( $module_slug, $capability_key, $wp_capability );
		add_settings_error(
			'timu_capabilities',
			'timu_capabilities_saved',
			esc_html__( 'Capability mapping saved.', 'plugin-wp-support-thisismyurl' ),
			'updated'
		);
	}

	$redirect = is_network_admin() ? network_admin_url( 'admin.php?page=timu-capabilities' ) : admin_url( 'admin.php?page=timu-capabilities' );

	wp_safe_redirect( add_query_arg( 'settings-updated', 'true', $redirect ) );
	exit;
}

/**
 * Tab-based router for all admin pages.
 *
 * @return void
 */
function timu_core_render_tab_router(): void {
	if ( ! current_user_can( 'manage_options' ) && ! current_user_can( 'manage_network_options' ) ) {
		wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'plugin-wp-support-thisismyurl' ) );
	}

	$context = TIMU_Tab_Navigation::get_current_context();
	$hub     = $context['hub'];
	$spoke   = $context['spoke'];
	$tab     = $context['tab'];
	$level   = $context['level'];

	// Render breadcrumbs (except at Core level).
	if ( 'core' !== $level ) {
		TIMU_Tab_Navigation::render_breadcrumbs( $context );
	}

	// Determine tabs based on level.
	if ( 'spoke' === $level && ! empty( $hub ) && ! empty( $spoke ) ) {
		$tabs        = TIMU_Tab_Navigation::get_spoke_tabs( $hub, $spoke );
		$active_tabs = array_merge( $tabs, timu_get_hub_tabs_for_spoke( $hub, $spoke ) );
		TIMU_Tab_Navigation::render_tabs( $active_tabs, $tab );
	} elseif ( 'hub' === $level && ! empty( $hub ) ) {
		$tabs        = TIMU_Tab_Navigation::get_hub_tabs( $hub );
		$active_tabs = array_merge( $tabs, timu_get_spoke_tabs_for_hub( $hub ) );
		TIMU_Tab_Navigation::render_tabs( $active_tabs, $tab );
	} else {
		$tabs        = TIMU_Tab_Navigation::get_core_tabs();
		$active_tabs = array_merge( $tabs, timu_get_active_hub_tabs() );
		TIMU_Tab_Navigation::render_tabs( $active_tabs, $tab );
	}

	// Route to appropriate content based on level and tab.
	if ( 'spoke' === $level && ! empty( $hub ) && ! empty( $spoke ) ) {
		timu_core_render_spoke_content( $hub, $spoke, $tab );
	} elseif ( 'hub' === $level && ! empty( $hub ) ) {
		timu_core_render_hub_content( $hub, $tab );
	} else {
		timu_core_render_core_content( $tab );
	}
}

/**
 * Setup Screen Options, Help tabs, and register dashboard meta boxes.
 *
 * @return void
 */
function timu_core_setup_dashboard_screen(): void {
	$screen = get_current_screen();
	if ( ! $screen || 'toplevel_page_wp-support' !== $screen->id ) {
		return;
	}

	// Add Help tabs.
	$screen->add_help_tab(
		array(
			'id'      => 'timu_overview',
			'title'   => __( 'Overview', 'plugin-wp-support-thisismyurl' ),
			'content' => '<p>' . esc_html__( 'This dashboard provides a suite overview, active hubs, recent activity, and quick actions. Use Screen Options to show/hide cards and arrange them.', 'plugin-wp-support-thisismyurl' ) . '</p>',
		)
	);

	$screen->add_help_tab(
		array(
			'id'      => 'timu_shortcuts',
			'title'   => __( 'Shortcuts', 'plugin-wp-support-thisismyurl' ),
			'content' => '<p>' . esc_html__( 'Drag cards to rearrange. Click the toggle arrow to hide/show cards. Use Quick Actions to jump to common tasks.', 'plugin-wp-support-thisismyurl' ) . '</p>',
		)
	);

	$screen->set_help_sidebar(
		'<p><strong>' . esc_html__( 'More Help', 'plugin-wp-support-thisismyurl' ) . '</strong></p>' .
		'<p><a href="https://thisismyurl.com/plugin-wp-support-thisismyurl/" target="_blank" rel="noopener">' . esc_html__( 'Documentation', 'plugin-wp-support-thisismyurl' ) . '</a></p>'
	);

	// Enable Screen Options for number of columns (2 by default).
	add_screen_option(
		'layout_columns',
		array(
			'max'     => 2,
			'default' => 2,
		)
	);

	// Register meta boxes for the Core dashboard (left column: 'normal').
	add_meta_box(
		'timu_quick_actions',
		__( 'Quick Actions', 'plugin-wp-support-thisismyurl' ),
		array( '\\TIMU\\CoreSupport\\TIMU_Dashboard_Widgets', 'render_metabox_quick_actions' ),
		$screen->id,
		'normal',
		'high'
	);

	add_meta_box(
		'timu_modules',
		__( 'Modules', 'plugin-wp-support-thisismyurl' ),
		array( '\\TIMU\\CoreSupport\\TIMU_Dashboard_Widgets', 'render_metabox_modules' ),
		$screen->id,
		'normal',
		'default'
	);

	add_meta_box(
		'timu_activity',
		__( 'Activity', 'plugin-wp-support-thisismyurl' ),
		array( '\\TIMU\\CoreSupport\\TIMU_Dashboard_Widgets', 'render_metabox_activity' ),
		$screen->id,
		'normal',
		'low'
	);

	add_meta_box(
		'timu_events_and_news',
		__( 'Support Suite Events and News', 'plugin-wp-support-thisismyurl' ),
		array( '\\TIMU\\CoreSupport\\TIMU_Dashboard_Widgets', 'render_metabox_events_and_news' ),
		$screen->id,
		'normal',
		'default'
	);

	// Register meta boxes for the Core dashboard (right column: 'side').
	add_meta_box(
		'timu_health',
		__( 'Health', 'plugin-wp-support-thisismyurl' ),
		array( '\\TIMU\\CoreSupport\\TIMU_Dashboard_Widgets', 'render_metabox_health' ),
		$screen->id,
		'side',
		'high'
	);

	add_meta_box(
		'timu_at_a_glance',
		__( 'At a Glance', 'plugin-wp-support-thisismyurl' ),
		array( '\\TIMU\\CoreSupport\\TIMU_Dashboard_Widgets', 'render_metabox_at_a_glance' ),
		$screen->id,
		'side',
		'default'
	);

	add_meta_box(
		'timu_scheduled_tasks',
		__( 'Scheduled Tasks', 'plugin-wp-support-thisismyurl' ),
		array( '\\TIMU\\CoreSupport\\TIMU_Dashboard_Widgets', 'render_metabox_scheduled_tasks' ),
		$screen->id,
		'side',
		'low'
	);

	// Initialize postboxes on this screen (drag/toggle).
	add_action(
		'admin_print_footer_scripts',
		static function () use ( $screen ): void {
			?>
			<script>
			jQuery(document).ready(function($){
				if (typeof postboxes !== 'undefined') {
					postboxes.add_postbox_toggles('<?php echo esc_js( $screen->id ); ?>');
				}
			});
			</script>
			<?php
		}
	);
}

/**
 * Setup Screen Options and register dashboard meta boxes for hub pages.
 *
 * @param string $hub_id Hub identifier.
 * @return void
 */
function timu_core_setup_hub_dashboard_screen( string $hub_id ): void {
	$screen = get_current_screen();
	if ( ! $screen ) {
		return;
	}

	// Register metaboxes based on hub type.
	switch ( $hub_id ) {
		case 'media':
			add_meta_box(
				'timu_media_overview',
				__( 'Media Hub Overview', 'plugin-wp-support-thisismyurl' ),
				array( '\\TIMU\\CoreSupport\\TIMU_Dashboard_Widgets', 'render_metabox_media_overview' ),
				$screen->id,
				'normal',
				'high'
			);
			add_meta_box(
				'timu_media_activity',
				__( 'Activity', 'plugin-wp-support-thisismyurl' ),
				array( '\\TIMU\\CoreSupport\\TIMU_Dashboard_Widgets', 'render_metabox_media_activity' ),
				$screen->id,
				'normal',
				'default'
			);
			add_meta_box(
				'timu_media_quick_actions',
				__( 'Quick Actions', 'plugin-wp-support-thisismyurl' ),
				array( '\\TIMU\\CoreSupport\\TIMU_Dashboard_Widgets', 'render_metabox_quick_actions' ),
				$screen->id,
				'side',
				'high'
			);
			add_meta_box(
				'timu_media_health',
				__( 'Health', 'plugin-wp-support-thisismyurl' ),
				array( '\\TIMU\\CoreSupport\\TIMU_Dashboard_Widgets', 'render_metabox_media_health' ),
				$screen->id,
				'side',
				'default'
			);
			break;

		case 'vault':
			add_meta_box(
				'timu_vault_overview',
				__( 'Vault Overview', 'plugin-wp-support-thisismyurl' ),
				array( '\\TIMU\\CoreSupport\\TIMU_Dashboard_Widgets', 'render_metabox_vault_overview' ),
				$screen->id,
				'normal',
				'high'
			);
			add_meta_box(
				'timu_vault_activity',
				__( 'Activity', 'plugin-wp-support-thisismyurl' ),
				array( '\\TIMU\\CoreSupport\\TIMU_Dashboard_Widgets', 'render_metabox_vault_activity' ),
				$screen->id,
				'normal',
				'default'
			);
			add_meta_box(
				'timu_vault_stats',
				__( 'Vault Statistics', 'plugin-wp-support-thisismyurl' ),
				array( '\\TIMU\\CoreSupport\\TIMU_Dashboard_Widgets', 'render_metabox_vault_status' ),
				$screen->id,
				'side',
				'high'
			);
			add_meta_box(
				'timu_vault_quick_actions',
				__( 'Quick Actions', 'plugin-wp-support-thisismyurl' ),
				array( '\\TIMU\\CoreSupport\\TIMU_Dashboard_Widgets', 'render_metabox_quick_actions' ),
				$screen->id,
				'side',
				'default'
			);
			add_meta_box(
				'timu_vault_health',
				__( 'Health', 'plugin-wp-support-thisismyurl' ),
				array( '\\TIMU\\CoreSupport\\TIMU_Dashboard_Widgets', 'render_metabox_vault_health' ),
				$screen->id,
				'side',
				'low'
			);
			break;
	}

	// Enable Screen Options for number of columns.
	add_screen_option(
		'layout_columns',
		array(
			'max'     => 2,
			'default' => 2,
		)
	);

	// Initialize postboxes on this screen (drag/toggle).
	add_action(
		'admin_print_footer_scripts',
		static function () use ( $screen ): void {
			?>
			<script>
			jQuery(document).ready(function($){
				if (typeof postboxes !== 'undefined') {
					postboxes.add_postbox_toggles('<?php echo esc_js( $screen->id ); ?>');
				}
			});
			</script>
			<?php
		}
	);
}

/**
 * Render Core-level content based on active tab.
 *
 * @param string $tab Active tab ID.
 * @return void
 */
function timu_core_render_core_content( string $tab ): void {
	switch ( $tab ) {
		case 'register':
			/* check if plugin is registered */
			if ( ! is_licensed() ) {
				echo '<div class="wrap"><h1>' . esc_html__( 'Register', 'plugin-wp-support-thisismyurl' ) . '</h1>';
				echo '<p>' . esc_html__( 'Register content will be added here.', 'plugin-wp-support-thisismyurl' ) . '</p></div>';
			}
			break;
		case 'help':
			echo '<div class="wrap"><h1>' . esc_html__( 'Help', 'plugin-wp-support-thisismyurl' ) . '</h1>';
			echo '<p>' . esc_html__( 'Help content will be added here.', 'plugin-wp-support-thisismyurl' ) . '</p></div>';
			break;
		case 'modules':
			timu_core_render_modules();
			break;
		case 'dashboard':
		default:
			// Render meta box-based dashboard to enable Screen Options and drag sorting.
			$screen = get_current_screen();
			?>
			<div class="wrap">
				<h1><?php echo esc_html__( 'Support Dashboard', 'plugin-wp-support-thisismyurl' ); ?></h1>
				<div id="poststuff">
					<div id="post-body" class="metabox-holder columns-2">
						<div id="postbox-container-1" class="postbox-container">
							<?php do_meta_boxes( $screen->id, 'side', null ); ?>
						</div>
						<div id="postbox-container-2" class="postbox-container">
							<?php do_meta_boxes( $screen->id, 'normal', null ); ?>
						</div>
					</div>
				</div>
			</div>
			<?php
			break;
	}
}

/**
 * Render Hub-level content based on active tab.
 *
 * @param string $hub_id Hub identifier.
 * @param string $tab Active tab ID.
 * @return void
 */
function timu_core_render_hub_content( string $hub_id, string $tab ): void {
	switch ( $tab ) {
		case 'help':
			echo '<div class="wrap"><h1>' . esc_html( ucfirst( $hub_id ) . ' - ' . __( 'Help', 'plugin-wp-support-thisismyurl' ) ) . '</h1>';
			echo '<p>' . esc_html__( 'Help content will be added here.', 'plugin-wp-support-thisismyurl' ) . '</p></div>';
			break;
		case 'dashboard':
		default:
			// Setup metaboxes for hub dashboard.
			timu_core_setup_hub_dashboard_screen( $hub_id );
			$screen = get_current_screen();
			?>
			<div class="wrap">
				<h1><?php echo esc_html( ucfirst( $hub_id ) . ' ' . __( 'Hub', 'plugin-wp-support-thisismyurl' ) ); ?></h1>
				<div id="poststuff">
					<div id="post-body" class="metabox-holder columns-2">
						<div id="postbox-container-1" class="postbox-container">
							<?php do_meta_boxes( $screen->id, 'side', null ); ?>
						</div>
						<div id="postbox-container-2" class="postbox-container">
							<?php do_meta_boxes( $screen->id, 'normal', null ); ?>
						</div>
					</div>
				</div>
			</div>
			<?php
			break;
	}
}

/**
 * Render Spoke-level content based on active tab.
 *
 * @param string $hub_id Hub identifier.
 * @param string $spoke_id Spoke identifier.
 * @param string $tab Active tab ID.
 * @return void
 */
function timu_core_render_spoke_content( string $hub_id, string $spoke_id, string $tab ): void {
	switch ( $tab ) {
		case 'help':
			echo '<div class="wrap"><h1>' . esc_html( strtoupper( $spoke_id ) . ' - ' . __( 'Help', 'plugin-wp-support-thisismyurl' ) ) . '</h1>';
			echo '<p>' . esc_html__( 'Help content will be added here.', 'plugin-wp-support-thisismyurl' ) . '</p></div>';
			break;
		case 'dashboard':
		default:
			TIMU_Dashboard_Widgets::render_spoke_dashboard( $hub_id, $spoke_id );
			break;
	}
}

/**
 * Get active Hub tabs dynamically based on installed hubs.
 *
 * @return array<array{id: string, label: string, icon: string, url: string}>
 */
function timu_get_active_hub_tabs(): array {
	$catalog = TIMU_Module_Registry::get_catalog_with_status();
	$hubs    = array_filter( $catalog, fn( $m ) => 'hub' === ( $m['type'] ?? '' ) && ! empty( $m['status']['active'] ) );
	$tabs    = array();

	foreach ( $hubs as $hub ) {
		$hub_id = sanitize_key( str_replace( '-support-thisismyurl', '', $hub['id'] ?? '' ) );
		$tabs[] = array(
			'id'    => $hub_id,
			'label' => esc_html( $hub['name'] ?? ucfirst( $hub_id ) ),
			'icon'  => 'dashicons-networking',
			'url'   => TIMU_Tab_Navigation::build_hub_url( $hub_id ),
		);
	}

	return $tabs;
}

/**
 * Get Spoke tabs for a specific Hub.
 *
 * @param string $hub_id Hub identifier.
 * @return array<array{id: string, label: string, icon: string, url: string}>
 */
function timu_get_spoke_tabs_for_hub( string $hub_id ): array {
	$catalog = TIMU_Module_Registry::get_catalog_with_status();
	$spokes  = array_filter(
		$catalog,
		fn( $m ) => 'spoke' === ( $m['type'] ?? '' )
			&& ! empty( $m['status']['active'] )
			&& str_starts_with( $m['id'] ?? '', $hub_id )
	);
	$tabs    = array();

	foreach ( $spokes as $spoke ) {
		$full_id  = $spoke['id'] ?? '';
		$spoke_id = sanitize_key( str_replace( $hub_id . '-support-thisismyurl', '', $full_id ) );
		$spoke_id = str_replace( '-', '', $spoke_id );
		$tabs[]   = array(
			'id'    => $spoke_id,
			'label' => esc_html( $spoke['name'] ?? strtoupper( $spoke_id ) ),
			'icon'  => 'dashicons-hammer',
			'url'   => TIMU_Tab_Navigation::build_spoke_url( $hub_id, $spoke_id ),
		);
	}

	return $tabs;
}

/**
 * Get Hub tabs for breadcrumb context when in Spoke view.
 *
 * @param string $hub_id Hub identifier.
 * @param string $spoke_id Spoke identifier.
 * @return array<array{id: string, label: string, icon: string, url: string}>
 */
function timu_get_hub_tabs_for_spoke( string $hub_id, string $spoke_id ): array {
	return timu_get_spoke_tabs_for_hub( $hub_id );
}

/**
 * Render modules dashboard.
 *
 * @return void
 */
function timu_core_render_dashboard(): void {
	if ( ! current_user_can( 'manage_options' ) && ! current_user_can( 'manage_network_options' ) ) {
		wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'plugin-wp-support-thisismyurl' ) );
	}

	$catalog_modules = TIMU_Module_Registry::get_catalog_with_status();
	$modules         = $catalog_modules;

	// Top stats derived from catalog with real activation state.
	$total_count     = count( $modules );
	$hub_modules     = array_filter(
		$modules,
		static function ( $m ) {
			return ( $m['type'] ?? '' ) === 'hub';
		}
	);
	$spoke_modules   = array_filter(
		$modules,
		static function ( $m ) {
			return ( $m['type'] ?? '' ) === 'spoke';
		}
	);
	$hubs_count      = count( $hub_modules );
	$spokes_count    = count( $spoke_modules );
	$available_count = count(
		array_filter(
			$modules,
			static function ( $m ) {
				return empty( $m['installed'] );
			}
		)
	);
	$updates_count   = count(
		array_filter(
			$modules,
			static function ( $m ) {
				return ! empty( $m['update_available'] );
			}
		)
	);
	$enabled_count   = count(
		array_filter(
			$modules,
			static function ( $m ) {
				$slug = $m['slug'] ?? null;
				if ( empty( $m['installed'] ) || ! $slug ) {
					return false;
				}
				$plugin = $slug . '/' . $slug . '.php';
				return is_plugin_active( $plugin ) || ( is_multisite() && is_plugin_active_for_network( $plugin ) );
			}
		)
	);

	$activity_logs     = TIMU_Vault::get_logs( 0, 10 );
	$pending_uploads   = TIMU_Vault::get_pending_contributor_uploads( 5 );
	$schedule_snapshot = TIMU_Module_Registry::get_schedule_snapshot();
	$run_now_nonce     = wp_create_nonce( 'timu_run_task_now' );

	require_once TIMU_CORE_PATH . 'includes/views/dashboard.php';
}

/**
 * Render modules view.
 *
 * @return void
 */
function timu_core_render_modules(): void {
	if ( ! current_user_can( 'manage_options' ) && ! current_user_can( 'manage_network_options' ) ) {
		wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'plugin-wp-support-thisismyurl' ) );
	}

	$catalog_modules = TIMU_Module_Registry::get_catalog_with_status();
	$modules         = $catalog_modules;

	// Top stats derived from catalog with real activation state.
	$total_count     = count( $modules );
	$hub_modules     = array_filter(
		$modules,
		static function ( $m ) {
			return ( $m['type'] ?? '' ) === 'hub';
		}
	);
	$spoke_modules   = array_filter(
		$modules,
		static function ( $m ) {
			return ( $m['type'] ?? '' ) === 'spoke';
		}
	);
	$hubs_count      = count( $hub_modules );
	$spokes_count    = count( $spoke_modules );
	$available_count = count(
		array_filter(
			$modules,
			static function ( $m ) {
				return empty( $m['installed'] );
			}
		)
	);
	$updates_count   = count(
		array_filter(
			$modules,
			static function ( $m ) {
				return ! empty( $m['update_available'] );
			}
		)
	);
	$enabled_count   = count(
		array_filter(
			$modules,
			static function ( $m ) {
				$slug = $m['slug'] ?? null;
				if ( empty( $m['installed'] ) || ! $slug ) {
					return false;
				}
				$plugin = $slug . '/' . $slug . '.php';
				return is_plugin_active( $plugin ) || ( is_multisite() && is_plugin_active_for_network( $plugin ) );
			}
		)
	);

	require_once TIMU_CORE_PATH . 'includes/views/modules.php';
}

/**
 * Render network settings page.
 *
 * @return void
 */
function timu_core_render_network_settings(): void {
	if ( ! current_user_can( 'manage_network_options' ) ) {
		wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'plugin-wp-support-thisismyurl' ) );
	}

	// Licenses are site-specific; Network Admin view is read-only.
	TIMU_Vault::maybe_handle_settings_submission( true );
	TIMU_Vault::maybe_handle_tools_submission( true );
	TIMU_Vault::maybe_handle_log_action();

	$license_state = TIMU_License::get_state( false );

	require_once TIMU_CORE_PATH . 'includes/views/settings.php';
}

/**
 * Render settings page.
 *
 * @return void
 */
function timu_core_render_settings_page(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'plugin-wp-support-thisismyurl' ) );
	}

	TIMU_License::maybe_handle_submission( false );
	TIMU_Vault::maybe_handle_settings_submission( false );
	TIMU_Vault::maybe_handle_tools_submission( false );
	TIMU_Vault::maybe_handle_log_action();

	$license_state = TIMU_License::get_state( false );

	require_once TIMU_CORE_PATH . 'includes/views/settings.php';
}

/**
 * Handle AJAX toggle module request.
 *
 * @return void
 */
function timu_ajax_toggle_module(): void {
	check_ajax_referer( 'timu_toggle_module', 'nonce' );

	if ( ! current_user_can( 'manage_options' ) && ! current_user_can( 'manage_network_options' ) ) {
		wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'plugin-wp-support-thisismyurl' ) ) );
	}

	$slug    = sanitize_text_field( wp_unslash( $_POST['slug'] ?? '' ) );
	$enabled = isset( $_POST['enabled'] ) && 'true' === $_POST['enabled'];
	$network = isset( $_POST['network'] ) && 'true' === $_POST['network'] && is_multisite();

	if ( empty( $slug ) ) {
		wp_send_json_error( array( 'message' => __( 'Invalid module slug.', 'plugin-wp-support-thisismyurl' ) ) );
	}

	$settings = array( 'enabled' => $enabled );
	$success  = TIMU_Module_Registry::update_module_settings( $slug, $settings, $network );

	$user      = wp_get_current_user();
	$user_name = $user && $user->exists() ? $user->display_name : __( 'System', 'plugin-wp-support-thisismyurl' );

	if ( $success ) {
		TIMU_Vault::add_log(
			'info',
			0,
			sprintf( 'Module %1$s %2$s', $slug, $enabled ? 'enabled' : 'disabled' ),
			'module_toggle',
			array(
				'task'    => 'module_toggle',
				'file'    => $slug,
				'user'    => $user_name,
				'user_id' => (int) $user->ID,
			)
		);
		wp_send_json_success( array( 'message' => __( 'Module settings updated.', 'plugin-wp-support-thisismyurl' ) ) );
	} else {
		TIMU_Vault::add_log(
			'error',
			0,
			sprintf( 'Failed to toggle %s', $slug ),
			'module_toggle',
			array(
				'task'    => 'module_toggle',
				'file'    => $slug,
				'user'    => $user_name,
				'user_id' => (int) $user->ID,
			)
		);
		wp_send_json_error( array( 'message' => __( 'Failed to update settings.', 'plugin-wp-support-thisismyurl' ) ) );
	}
}

/**
 * Handle AJAX install module request.
 *
 * @return void
 */
function timu_ajax_install_module(): void {
	check_ajax_referer( 'timu_module_action', 'nonce' );

	if ( is_multisite() && is_network_admin() ) {
		if ( ! current_user_can( 'manage_network_plugins' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'plugin-wp-support-thisismyurl' ) ) );
		}
	} elseif ( ! current_user_can( 'install_plugins' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'plugin-wp-support-thisismyurl' ) ) );
	}

	$slug      = sanitize_text_field( wp_unslash( $_POST['slug'] ?? '' ) );
	$user      = wp_get_current_user();
	$user_name = $user && $user->exists() ? $user->display_name : __( 'System', 'plugin-wp-support-thisismyurl' );

	if ( empty( $slug ) ) {
		TIMU_Vault::add_log( 'error', 0, __( 'Install failed: empty slug.', 'plugin-wp-support-thisismyurl' ), 'module_install' );
		wp_send_json_error( array( 'message' => __( 'Invalid module slug.', 'plugin-wp-support-thisismyurl' ) ) );
	}

	$catalog = TIMU_Module_Registry::get_catalog_with_status();
	$module  = $catalog[ $slug ] ?? null;

	if ( empty( $module ) || empty( $module['download_url'] ) ) {
		TIMU_Vault::add_log(
			'error',
			0,
			sprintf( 'Install failed: no download for %s', $slug ),
			'module_install',
			array(
				'task'    => 'module_install',
				'file'    => $slug,
				'user'    => $user_name,
				'user_id' => (int) $user->ID,
			)
		);
		wp_send_json_error( array( 'message' => __( 'No download available for this module.', 'plugin-wp-support-thisismyurl' ) ) );
	}

	require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
	require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
	require_once ABSPATH . 'wp-admin/includes/file.php';

	if ( ! WP_Filesystem() ) {
		TIMU_Vault::add_log(
			'error',
			0,
			sprintf( 'Install failed: filesystem credentials needed for %s', $slug ),
			'module_install',
			array(
				'task'    => 'module_install',
				'file'    => $slug,
				'user'    => $user_name,
				'user_id' => (int) $user->ID,
			)
		);
		wp_send_json_error( array( 'message' => __( 'File system credentials are required to install plugins.', 'plugin-wp-support-thisismyurl' ) ) );
	}

	$skin     = new \Automatic_Upgrader_Skin();
	$upgrader = new \Plugin_Upgrader( $skin );
	$download = timu_core_resolve_download_url( $module );
	$result   = $upgrader->install( $download );

	if ( is_wp_error( $result ) || ! $result ) {
		$message = is_wp_error( $result ) ? $result->get_error_message() : __( 'Installation failed.', 'plugin-wp-support-thisismyurl' );
		TIMU_Vault::add_log(
			'error',
			0,
			wp_strip_all_tags( $message ),
			'module_install',
			array(
				'task'    => 'module_install',
				'file'    => $slug,
				'user'    => $user_name,
				'user_id' => (int) $user->ID,
			)
		);
		wp_send_json_error( array( 'message' => $message ) );
	}

	// Attempt activation.
	$plugin_file = timu_core_find_plugin_file_by_slug( $slug );
	if ( $plugin_file ) {
		$network_wide = is_multisite() && is_network_admin();
		$activation   = activate_plugin( $plugin_file, '', $network_wide, false );
		if ( is_wp_error( $activation ) ) {
			do_action(
				'timu_catalog_install_warning',
				array(
					'slug'    => $slug,
					'message' => $activation->get_error_message(),
				)
			);
		}
	}

	TIMU_Module_Registry::clear_cache();
	TIMU_Module_Registry::discover_modules();
	TIMU_Module_Registry::load_catalog();

	TIMU_Vault::add_log(
		'info',
		0,
		sprintf( 'Module installed: %s', $slug ),
		'module_install',
		array(
			'task'    => 'module_install',
			'file'    => $slug,
			'user'    => $user_name,
			'user_id' => (int) $user->ID,
		)
	);

	wp_send_json_success( array( 'message' => __( 'Module installed.', 'plugin-wp-support-thisismyurl' ) ) );
}

/**
 * Handle AJAX update module request.
 *
 * @return void
 */
function timu_ajax_update_module(): void {
	check_ajax_referer( 'timu_module_action', 'nonce' );

	if ( is_multisite() && is_network_admin() ) {
		if ( ! current_user_can( 'manage_network_plugins' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'plugin-wp-support-thisismyurl' ) ) );
		}
	} elseif ( ! current_user_can( 'update_plugins' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'plugin-wp-support-thisismyurl' ) ) );
	}

	$slug      = sanitize_text_field( wp_unslash( $_POST['slug'] ?? '' ) );
	$user      = wp_get_current_user();
	$user_name = $user && $user->exists() ? $user->display_name : __( 'System', 'plugin-wp-support-thisismyurl' );

	if ( empty( $slug ) ) {
		TIMU_Vault::add_log( 'error', 0, __( 'Update failed: empty slug.', 'plugin-wp-support-thisismyurl' ), 'module_update' );
		wp_send_json_error( array( 'message' => __( 'Invalid module slug.', 'plugin-wp-support-thisismyurl' ) ) );
	}

	$catalog   = TIMU_Module_Registry::get_catalog_with_status();
	$installed = TIMU_Module_Registry::get_module( $slug );
	$module    = $catalog[ $slug ] ?? null;

	if ( empty( $module ) || empty( $module['download_url'] ) || empty( $installed['file'] ) ) {
		TIMU_Vault::add_log(
			'error',
			0,
			sprintf( 'Update failed: missing data for %s', $slug ),
			'module_update',
			array(
				'task'    => 'module_update',
				'file'    => $slug,
				'user'    => $user_name,
				'user_id' => (int) $user->ID,
			)
		);
		wp_send_json_error( array( 'message' => __( 'Update information is missing for this module.', 'plugin-wp-support-thisismyurl' ) ) );
	}

	require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
	require_once ABSPATH . 'wp-admin/includes/file.php';

	if ( ! WP_Filesystem() ) {
		TIMU_Vault::add_log(
			'error',
			0,
			sprintf( 'Update failed: filesystem credentials needed for %s', $slug ),
			'module_update',
			array(
				'task'    => 'module_update',
				'file'    => $slug,
				'user'    => $user_name,
				'user_id' => (int) $user->ID,
			)
		);
		wp_send_json_error( array( 'message' => __( 'File system credentials are required to update plugins.', 'plugin-wp-support-thisismyurl' ) ) );
	}

	$skin     = new \Automatic_Upgrader_Skin();
	$upgrader = new \Plugin_Upgrader( $skin );

	// Allow overwriting existing destination during update.
	$filter = function ( array $options ): array {
		$options['clear_destination']           = true;
		$options['abort_if_destination_exists'] = false;
		return $options;
	};
	add_filter( 'upgrader_package_options', $filter );

	$download = timu_core_resolve_download_url( $module );
	$result   = $upgrader->install( $download );

	// Remove filter after run.
	remove_filter( 'upgrader_package_options', $filter );

	if ( is_wp_error( $result ) || ! $result ) {
		$message = is_wp_error( $result ) ? $result->get_error_message() : __( 'Update failed.', 'plugin-wp-support-thisismyurl' );
		TIMU_Vault::add_log(
			'error',
			0,
			wp_strip_all_tags( $message ),
			'module_update',
			array(
				'task'    => 'module_update',
				'file'    => $slug,
				'user'    => $user_name,
				'user_id' => (int) $user->ID,
			)
		);
		wp_send_json_error( array( 'message' => $message ) );
	}

	TIMU_Module_Registry::clear_cache();
	TIMU_Module_Registry::discover_modules();
	TIMU_Module_Registry::load_catalog();

	TIMU_Vault::add_log(
		'info',
		0,
		sprintf( 'Module updated: %s', $slug ),
		'module_update',
		array(
			'task'    => 'module_update',
			'file'    => $slug,
			'user'    => $user_name,
			'user_id' => (int) $user->ID,
		)
	);

	wp_send_json_success( array( 'message' => __( 'Module updated.', 'plugin-wp-support-thisismyurl' ) ) );
}

/**
 * Handle network license broadcast via AJAX.
 *
 * @return void
 */
function timu_ajax_broadcast_license(): void {
	if ( empty( $_POST['nonce'] ) ) {
		wp_send_json_error( array( 'message' => __( 'Nonce failed.', 'plugin-wp-support-thisismyurl' ) ) );
	}

	if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'timu_broadcast_license' ) ) {
		wp_send_json_error( array( 'message' => __( 'Nonce verification failed.', 'plugin-wp-support-thisismyurl' ) ) );
	}

	if ( ! is_multisite() ) {
		wp_send_json_error( array( 'message' => __( 'Multisite not enabled.', 'plugin-wp-support-thisismyurl' ) ) );
	}

	if ( ! current_user_can( 'manage_network_options' ) ) {
		wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'plugin-wp-support-thisismyurl' ) ) );
	}

	$key            = isset( $_POST['key'] ) ? sanitize_text_field( wp_unslash( $_POST['key'] ) ) : '';
	$site_ids_json  = isset( $_POST['site_ids'] ) ? sanitize_text_field( wp_unslash( $_POST['site_ids'] ) ) : '[]';
	$auto_broadcast = isset( $_POST['auto_broadcast'] ) ? absint( $_POST['auto_broadcast'] ) : 0;

	if ( empty( $key ) ) {
		wp_send_json_error( array( 'message' => __( 'License key cannot be empty.', 'plugin-wp-support-thisismyurl' ) ) );
	}

	// Parse site IDs from JSON.
	$site_ids = (array) json_decode( $site_ids_json, true );
	$site_ids = array_map( 'absint', array_filter( $site_ids ) );

	if ( empty( $site_ids ) ) {
		wp_send_json_error( array( 'message' => __( 'No sites selected.', 'plugin-wp-support-thisismyurl' ) ) );
	}

	// Call the license broadcast method.
	$result = TIMU_License::broadcast_network_key( $key, $site_ids, (bool) $auto_broadcast );

	wp_send_json_success( $result );
}

/**
 * Attempt to find a plugin file by slug.
 *
 * @param string $slug Module slug.
 * @return string|null Plugin file path or null.
 */
function timu_core_find_plugin_file_by_slug( string $slug ): ?string {
	if ( ! function_exists( 'get_plugins' ) ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
	}

	$plugins = get_plugins();

	foreach ( $plugins as $file => $data ) {
		if ( dirname( $file ) === $slug || basename( $file, '.php' ) === $slug ) {
			return $file;
		}
	}

	return null;
}

/**
 * Resolve a module's download URL to a direct ZIP when possible.
 *
 * Supports GitHub release pages by converting to the latest asset download URL
 * following a convention of {slug}.zip.
 *
 * @param array $module Module data including 'download_url' and 'slug'.
 * @return string Resolved URL suitable for Plugin_Upgrader::install().
 */
function timu_core_resolve_download_url( array $module ): string {
	$url  = isset( $module['download_url'] ) ? (string) $module['download_url'] : '';
	$slug = isset( $module['slug'] ) ? (string) $module['slug'] : '';

	if ( empty( $url ) ) {
		return '';
	}

	$parts = wp_parse_url( $url );
	$host  = $parts['host'] ?? '';
	$path  = $parts['path'] ?? '';

	// Convert GitHub release page to direct asset download if following suite convention.
	if ( strpos( strtolower( $host ), 'github.com' ) !== false && str_ends_with( (string) $path, '/releases/latest' ) && ! empty( $slug ) ) {
		// Build /releases/latest/download/{slug}.zip
		$base = rtrim( $url, '/' );
		$url  = $base . '/download/' . rawurlencode( $slug ) . '.zip';
	}

	/**
	 * Filter to customize download URL resolution.
	 *
	 * @param string $url    Resolved URL.
	 * @param array  $module Module data.
	 */
	$url = (string) apply_filters( 'timu_resolve_download_url', $url, $module );

	return esc_url_raw( $url );
}

/**
 * Handle "Run Now" requests for scheduled tasks.
 *
 * @return void
 */
function timu_run_task_now(): void {
	if ( empty( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'timu_run_task_now' ) ) {
		$redirect_url = wp_get_referer();
		if ( empty( $redirect_url ) ) {
			$redirect_url = admin_url();
		}
		wp_safe_redirect( $redirect_url );
		exit;
	}

	if ( ! current_user_can( 'manage_options' ) && ! current_user_can( 'manage_network_options' ) ) {
		$redirect_url = wp_get_referer();
		if ( empty( $redirect_url ) ) {
			$redirect_url = admin_url();
		}
		wp_safe_redirect( $redirect_url );
		exit;
	}

	$hook          = isset( $_POST['hook'] ) ? sanitize_text_field( wp_unslash( $_POST['hook'] ) ) : '';
	$allowed_hooks = array( 'timu_refresh_modules', 'timu_vault_queue_runner' );
	if ( ! in_array( $hook, $allowed_hooks, true ) ) {
		$redirect_url = wp_get_referer();
		if ( empty( $redirect_url ) ) {
			$redirect_url = admin_url();
		}
		wp_safe_redirect( $redirect_url );
		exit;
	}

	// Run the task immediately.
	do_action( $hook );

	$user      = wp_get_current_user();
	$user_name = $user && $user->exists() ? $user->display_name : __( 'System', 'plugin-wp-support-thisismyurl' );

	TIMU_Vault::add_log(
		'info',
		0,
		sprintf( 'Manual run triggered for %s', $hook ),
		'schedule_run',
		array(
			'task'    => 'run_now',
			'file'    => $hook,
			'user'    => $user_name,
			'user_id' => (int) $user->ID,
		)
	);

	$redirect_url = wp_get_referer();
	if ( empty( $redirect_url ) ) {
		$redirect_url = admin_url();
	}
	$redirect = add_query_arg( 'timu_run_now', '1', $redirect_url );
	wp_safe_redirect( $redirect );
	exit;
}

/**
 * Add action links to the plugins list for WordPress Support.
 *
 * @param array $links Plugin action links.
 * @return array Modified action links.
 */
function timu_core_plugin_action_links( array $links ): array {
	$dashboard_link = sprintf(
		'<a href="%s">%s</a>',
		esc_url( admin_url( 'admin.php?page=wp-support' ) ),
		esc_html__( 'Dashboard', 'plugin-wp-support-thisismyurl' )
	);

	$settings_link = '';
	if ( current_user_can( 'manage_options' ) ) {
		$settings_link = sprintf(
			'<a href="%s">%s</a>',
			esc_url( admin_url( 'admin.php?page=wp-support&timu_tab=settings' ) ),
			esc_html__( 'Settings', 'plugin-wp-support-thisismyurl' )
		);
	} elseif ( is_multisite() && current_user_can( 'manage_network_options' ) ) {
		$settings_link = sprintf(
			'<a href="%s">%s</a>',
			esc_url( admin_url( 'admin.php?page=timu-core-network-settings', 'network' ) ),
			esc_html__( 'Network Settings', 'plugin-wp-support-thisismyurl' )
		);
	}

	array_unshift( $links, $dashboard_link );
	if ( ! empty( $settings_link ) ) {
		array_unshift( $links, $settings_link );
	}

	return $links;
}

/**
 * Add row meta to the plugins list for WordPress Support.
 *
 * @param array  $meta Plugin row meta.
 * @param string $file Plugin file.
 * @return array Modified row meta.
 */
function timu_core_plugin_row_meta( array $meta, string $file ): array {
	if ( TIMU_CORE_BASENAME !== $file ) {
		return $meta;
	}

	$docs_link = sprintf(
		'<a href="%s" target="_blank" rel="noopener noreferrer">%s</a>',
		esc_url( 'https://github.com/thisismyurl/plugin-plugin-wp-support-thisismyurl' ),
		esc_html__( 'Documentation', 'plugin-wp-support-thisismyurl' )
	);

	$privacy_link = sprintf(
		'<a href="%s">%s</a>',
		esc_url( 'https://thisismyurl.com/privacy' ),
		esc_html__( 'Privacy', 'plugin-wp-support-thisismyurl' )
	);

	$support_link = sprintf(
		'<a href="%s" target="_blank" rel="noopener noreferrer">%s</a>',
		esc_url( 'https://thisismyurl.com/support' ),
		esc_html__( 'Support', 'plugin-wp-support-thisismyurl' )
	);

	$meta[] = $docs_link;
	$meta[] = $privacy_link;
	$meta[] = $support_link;

	return $meta;
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

	// TEMP: Load only CSS while debugging menu issue (scripts remain off).

	// Cache-bust using file modification times to avoid stale admin assets.
	$ui_system_file   = TIMU_CORE_PATH . 'assets/css/timu-ui-system.css';
	$admin_css_file   = TIMU_CORE_PATH . 'assets/css/admin.css';
	$tab_nav_css_file = TIMU_CORE_PATH . 'assets/css/tab-navigation.css';
	$admin_js_file    = TIMU_CORE_PATH . 'assets/js/admin.js';
	$actions_js_file  = TIMU_CORE_PATH . 'assets/js/module-actions.js';

	$ui_system_ver   = file_exists( $ui_system_file ) ? TIMU_CORE_VERSION . '.' . filemtime( $ui_system_file ) : TIMU_CORE_VERSION;
	$admin_css_ver   = file_exists( $admin_css_file ) ? TIMU_CORE_VERSION . '.' . filemtime( $admin_css_file ) : TIMU_CORE_VERSION;
	$tab_nav_css_ver = file_exists( $tab_nav_css_file ) ? TIMU_CORE_VERSION . '.' . filemtime( $tab_nav_css_file ) : TIMU_CORE_VERSION;
	$admin_js_ver    = file_exists( $admin_js_file ) ? TIMU_CORE_VERSION . '.' . filemtime( $admin_js_file ) : TIMU_CORE_VERSION;
	$actions_js_ver  = file_exists( $actions_js_file ) ? TIMU_CORE_VERSION . '.' . filemtime( $actions_js_file ) : TIMU_CORE_VERSION;

	$load_admin_js   = true;
	$load_actions_js = true;

	// Enqueue modern design system (shared across all TIMU plugins).
	wp_enqueue_style(
		'timu-ui-system',
		TIMU_CORE_URL . 'assets/css/timu-ui-system.css',
		array(),
		$ui_system_ver
	);

	wp_enqueue_style(
		'timu-core-admin',
		TIMU_CORE_URL . 'assets/css/admin.css',
		array( 'timu-ui-system' ),
		$admin_css_ver
	);

	wp_enqueue_style(
		'timu-tab-navigation',
		TIMU_CORE_URL . 'assets/css/tab-navigation.css',
		array( 'timu-ui-system' ),
		$tab_nav_css_ver
	);

	// Enable WordPress core postbox (drag/drop/toggle) for meta boxes.
	wp_enqueue_script( 'postbox' );

	if ( $load_admin_js ) {
		wp_enqueue_script(
			'timu-core-admin',
			TIMU_CORE_URL . 'assets/js/admin.js',
			array( 'jquery' ),
			$admin_js_ver,
			true
		);

		// Localize script for AJAX and i18n.
		wp_localize_script(
			'timu-core-admin',
			'timuAdminData',
			array(
				'toggleNonce' => wp_create_nonce( 'timu_toggle_module' ),
				'actionNonce' => wp_create_nonce( 'timu_module_action' ),
				'i18n'        => array(
					'enabled'      => __( 'Enabled', 'plugin-wp-support-thisismyurl' ),
					'disabled'     => __( 'Disabled', 'plugin-wp-support-thisismyurl' ),
					'ajaxError'    => __( 'An error occurred. Please try again.', 'plugin-wp-support-thisismyurl' ),
					'noResults'    => __( 'No modules match this filter.', 'plugin-wp-support-thisismyurl' ),
					'installFirst' => __( 'Install the module before enabling it.', 'plugin-wp-support-thisismyurl' ),
					'installing'   => __( 'Installing...', 'plugin-wp-support-thisismyurl' ),
					'updating'     => __( 'Updating...', 'plugin-wp-support-thisismyurl' ),
					'install'      => __( 'Install', 'plugin-wp-support-thisismyurl' ),
					'update'       => __( 'Update', 'plugin-wp-support-thisismyurl' ),
				),
			)
		);

	}

	if ( $load_actions_js ) {
		// Enqueue module actions script (install/update/activate).
		wp_enqueue_script(
			'timu-module-actions',
			TIMU_CORE_URL . 'assets/js/module-actions.js',
			array(),
			$actions_js_ver,
			true
		);

		// Localize module actions script with nonce and AJAX URL.
		wp_localize_script(
			'timu-module-actions',
			'timuModuleActions',
			array(
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'timu_module_actions' ),
			)
		);
	}
}

/**
 * Register the Vault exporter with WordPress Personal Data Export.
 *
 * @param array $exporters Existing exporters.
 * @return array Modified exporters.
 */
function timu_core_register_privacy_exporters( array $exporters ): array {
	$exporters['timu-vault-exporter'] = array(
		'exporter_friendly_name' => __( 'TIMU Vault', 'plugin-wp-support-thisismyurl' ),
		'callback'               => __NAMESPACE__ . '\\timu_core_vault_exporter_callback',
	);

	return $exporters;
}

/**
 * Vault exporter: returns attachment metadata stored in the Vault for the requesting user.
 * Implements batching per WordPress privacy API contract.
 *
 * @param string $email_address User email being exported.
 * @param int    $page          Page number for batching (1-indexed).
 * @return array{data:array,done:bool}
 */
function timu_core_vault_exporter_callback( string $email_address, int $page = 1 ): array {
	$email_address = sanitize_email( $email_address );
	if ( empty( $email_address ) ) {
		return array(
			'data' => array(),
			'done' => true,
		);
	}

	$user = get_user_by( 'email', $email_address );
	if ( ! $user || ! $user->exists() ) {
		return array(
			'data' => array(),
			'done' => true,
		);
	}

	$paged    = max( 1, $page );
	$per_page = 50;

	$query = new \WP_Query(
		array(
			'post_type'      => 'attachment',
			'post_status'    => 'any',
			'paged'          => $paged,
			'posts_per_page' => $per_page,
			'fields'         => 'ids',
			'meta_query'     => array(
				array(
					'key'   => '_timu_vault_uploader_user_id',
					'value' => (int) $user->ID,
				),
			),
		)
	);

	$items = array();

	if ( $query->have_posts() ) {
		foreach ( $query->posts as $attachment_id ) {
			$attachment_id  = (int) $attachment_id;
			$file_path      = (string) get_attached_file( $attachment_id );
			$vault_path     = (string) get_post_meta( $attachment_id, '_timu_vault_path', true );
			$vault_mode     = (string) get_post_meta( $attachment_id, '_timu_vault_mode', true );
			$vault_created  = (string) get_post_meta( $attachment_id, '_timu_vault_created', true );
			$hash_raw       = (string) get_post_meta( $attachment_id, '_timu_vault_sha256_raw', true );
			$hash_store     = (string) get_post_meta( $attachment_id, '_timu_vault_sha256_store', true );
			$anonymized_at  = (string) get_post_meta( $attachment_id, '_timu_vault_anonymized', true );
			$encrypted_flag = (string) get_post_meta( $attachment_id, '_timu_vault_encrypted', true );

			$items[] = array(
				'group_id'    => 'timu-vault',
				'group_label' => __( 'TIMU Vault', 'plugin-wp-support-thisismyurl' ),
				'item_id'     => 'attachment-' . $attachment_id,
				'data'        => array(
					array(
						'name'  => __( 'Attachment ID', 'plugin-wp-support-thisismyurl' ),
						'value' => $attachment_id,
					),
					array(
						'name'  => __( 'File name', 'plugin-wp-support-thisismyurl' ),
						'value' => wp_basename( $file_path ),
					),
					array(
						'name'  => __( 'MIME type', 'plugin-wp-support-thisismyurl' ),
						'value' => (string) get_post_mime_type( $attachment_id ),
					),
					array(
						'name'  => __( 'Vault path', 'plugin-wp-support-thisismyurl' ),
						'value' => $vault_path,
					),
					array(
						'name'  => __( 'Vault mode', 'plugin-wp-support-thisismyurl' ),
						'value' => ! empty( $vault_mode ) ? $vault_mode : 'raw',
					),
					array(
						'name'  => __( 'Encrypted', 'plugin-wp-support-thisismyurl' ),
						'value' => $encrypted_flag ? 'yes' : 'no',
					),
					array(
						'name'  => __( 'Checksum (store)', 'plugin-wp-support-thisismyurl' ),
						'value' => $hash_store ? substr( $hash_store, 0, 12 ) : '',
					),
					array(
						'name'  => __( 'Checksum (raw)', 'plugin-wp-support-thisismyurl' ),
						'value' => $hash_raw ? substr( $hash_raw, 0, 12 ) : '',
					),
					array(
						'name'  => __( 'Vault created', 'plugin-wp-support-thisismyurl' ),
						'value' => $vault_created,
					),
					array(
						'name'  => __( 'Anonymized at', 'plugin-wp-support-thisismyurl' ),
						'value' => $anonymized_at,
					),
				),
			);
		}
	}

	$max_pages = ! empty( $query->max_num_pages ) ? (int) $query->max_num_pages : 1;
	$done      = $paged >= $max_pages;

	return array(
		'data' => $items,
		'done' => $done,
	);
}

/**
 * Register the Vault eraser with WordPress Personal Data Erasure.
 *
 * @param array $erasers Existing erasers.
 * @return array Modified erasers.
 */
function timu_core_register_privacy_erasers( array $erasers ): array {
	$erasers['timu-vault-eraser'] = array(
		'eraser_friendly_name' => __( 'TIMU Vault (anonymize originals & derivatives)', 'plugin-wp-support-thisismyurl' ),
		'callback'             => __NAMESPACE__ . '\\timu_core_vault_eraser_callback',
	);
	return $erasers;
}

/**
 * Vault eraser: anonymize attachments tied to the user; retain originals in Vault.
 * Implements batching per WordPress privacy API contract.
 *
 * @param string $email_address User email being erased.
 * @param int    $page          Page number for batching (1-indexed).
 * @return array{items_removed:int,items_retained:int,messages:array,done:bool}
 */
function timu_core_vault_eraser_callback( string $email_address, int $page = 1 ): array {
	$email_address = sanitize_email( $email_address );
	if ( empty( $email_address ) ) {
		return array(
			'items_removed'  => 0,
			'items_retained' => 0,
			'messages'       => array( __( 'Invalid email address.', 'plugin-wp-support-thisismyurl' ) ),
			'done'           => true,
		);
	}

	$user = get_user_by( 'email', $email_address );
	if ( ! $user || ! $user->exists() ) {
		return array(
			'items_removed'  => 0,
			'items_retained' => 0,
			'messages'       => array( __( 'No user found for email; nothing to anonymize.', 'plugin-wp-support-thisismyurl' ) ),
			'done'           => true,
		);
	}

	// Delegate to Vault anonymization (retains originals, scrubs personal data).
	$result = TIMU_Vault::erase_user_personal_data( (int) $user->ID, max( 1, $page ), 50 );

	// Ensure messages are sanitized.
	$messages = array_map(
		static function ( $m ) {
			return wp_strip_all_tags( (string) $m );
		},
		(array) ( $result['messages'] ?? array() )
	);

	return array(
		'items_removed'  => (int) ( $result['items_removed'] ?? 0 ),
		'items_retained' => (int) ( $result['items_retained'] ?? 0 ),
		'messages'       => $messages,
		'done'           => (bool) ( $result['done'] ?? true ),
	);
}

// Register activation and deactivation hooks.
register_activation_hook( __FILE__, __NAMESPACE__ . '\\timu_core_activate' );
register_deactivation_hook( __FILE__, __NAMESPACE__ . '\\timu_core_deactivate' );

// Initialize the plugin.
add_action( 'plugins_loaded', __NAMESPACE__ . '\\timu_core_init' );

/* @changelog
 * [1.2601.72060] - 2026-01-08 20:06
 * - Added dashboard activity log with task/file/user context
 * - Surfaced scheduled task snapshots for catalog refresh and Vault queue
 * - Flagged contributor uploads for Editor+ review while keeping them optimized
 * - Logged module install/update/toggle outcomes for auditability
 * - Dashboard now lists pending contributor uploads for review
 *
 * [1.2601.71920] - 2026-01-07 19:35
 * - Issue #33: In-dashboard install/update flows
 * - Added AJAX handlers: timu_ajax_install_module and timu_ajax_update_module
 * - Implement WP_Plugin_Upgrader for direct installation from catalog
 * - Auto-activate installed modules after installation
 * - Support for multisite with network-wide install/update
 * - Permission checks for install_plugins and update_plugins capabilities
 * - Helper function timu_core_find_plugin_file_by_slug() for plugin location
 * - Cache invalidation and module discovery after install/update
 * - Added actionNonce for install/update AJAX requests
 * - Dashboard provides Install button for available modules
 * - Dashboard provides Update button for modules with updates
 * - Localized i18n strings for button labels and status messages
 * - Files Modified:
 *   - core-support-thisismyurl.php: Added install/update handlers + helpers
 *   - assets/js/admin.js: Added handleInstall() and handleUpdate() methods
 *   - includes/views/dashboard.php: Install/Update buttons for available/updateable modules
 *   - assets/css/admin.css: Styling for install/update buttons
 *
 * [1.2601.71910] - 2026-01-07 19:05
 * - Added remote catalog fetch with retries, timeouts, and allowed-host guard
 * - Added checksum validation for catalog payload with fallback to bundled JSON
 * - Filterable catalog URL and cache TTL; logging hook on fetch failures
 * - Maintains bundled catalog as authoritative fallback for offline/resilience
 * - Issue #32: Catalog reliability and integrity improvements
 *
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
 * - Confirmed text domain 'plugin-wp-support-thisismyurl' loads via load_plugin_textdomain()
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
