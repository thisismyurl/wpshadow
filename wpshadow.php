<?php
/**
 * Author:              WPShadow
 * Author URI:          https://wpshadow.com/
 * Plugin Name:         WPShadow
 * Plugin URI:          https://wpshadow.com/
 * Donate link:         https://wpshadow.com/
 * Description:         WordPress plugin featuring comprehensive health diagnostics, emergency recovery, backup verification, and documentation management with intelligent real-time diagnostics and visual regression protection.
 * Tags:                WordPress, plugin, foundation, diagnostics, health, backup, emergency, recovery
 * Version:             1.2601.75000
 * Requires at least:   6.4
 * Requires PHP:        8.1.29
 * Primary Branch:      main
 * Text Domain:         plugin-wpshadow
 * Domain Path:         /languages
 * Network:             true
 * License:             GPL2
 * License URI:         https://www.gnu.org/licenses/gpl-2.0.html
 * @package WPSHADOW
 */

declare(strict_types=1);

namespace WPShadow;

// ========================================================
// FREE FEATURES (limited build)
// ========================================================
use WPShadow\CoreSupport\WPSHADOW_Feature_Asset_Version_Removal;

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// ========================================================
// PLUGIN CONSTANTS
// ========================================================
define( 'WPSHADOW_VERSION', '1.2601.73001' );
define( 'WPSHADOW_FILE', __FILE__ );
define( 'WPSHADOW_PATH', str_replace( '/', DIRECTORY_SEPARATOR, trailingslashit( plugin_dir_path( __FILE__ ) ) ) );
define( 'WPSHADOW_URL', plugin_dir_url( __FILE__ ) );
define( 'WPSHADOW_BASENAME', plugin_basename( __FILE__ ) );
define( 'WPSHADOW_TEXT_DOMAIN', 'wpshadow' );
define( 'WPSHADOW_MIN_PHP', '8.1.29' );
define( 'WPSHADOW_MIN_WP', '6.4.0' );

/**
 * Enqueue admin assets for WPShadow pages.
 *
 * @return void
 */
function wpshadow_enqueue_admin_assets(): void {
	// Only load on WPShadow admin pages
	$screen = get_current_screen();
	if ( ! $screen || strpos( $screen->id, 'wpshadow' ) === false ) {
		return;
	}
	
	// Enqueue WordPress core admin styles
	wp_enqueue_style( 'dashboard' );
	wp_enqueue_style( 'common' );
	wp_enqueue_style( 'forms' );
	wp_enqueue_script( 'postbox' );
	wp_enqueue_script( 'jquery' );
	
	// Enqueue WPShadow admin CSS
	wp_enqueue_style(
		'wpshadow-admin',
		WPSHADOW_URL . 'assets/css/wpshadow-admin.css',
		array(),
		WPSHADOW_VERSION
	);
}

/**
 * Initialize dashboard assets on init hook
 *
 * @return void
 */
function wpshadow_init_dashboard_assets(): void {
	if ( class_exists( '\\WPShadow\\Admin\\WPSHADOW_Dashboard_Assets' ) ) {
		\WPShadow\Admin\WPSHADOW_Dashboard_Assets::init( WPSHADOW_PATH, WPSHADOW_URL );
	}
}

/**
 * Render Settings view using the same metabox layout as the dashboard.
 * Widgets here represent settings groups.
 *
 * @return void
 */
function wpshadow_render_settings(): void {
	if ( ! WPSHADOW_can_manage_settings() ) {
		wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'wpshadow' ) );
	}

	$screen = get_current_screen();
	if ( ! $screen ) {
		return;
	}

	// Enqueue postbox script for draggable/closable widgets.
	wp_enqueue_script( 'postbox' );
	wp_enqueue_style( 'dashboard' );

	// Enqueue auto-save script for settings forms.
	wp_enqueue_script(
		'wps-settings-autosave',
		plugin_dir_url( __FILE__ ) . 'assets/js/settings-autosave.js',
		array( 'jquery' ),
		'1.0.0',
		true
	);

	// Localize script for AJAX and i18n.
	wp_localize_script(
		'wps-settings-autosave',
		'wpshadow_settings_i18n',
		array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'saving'  => __( 'Saving...', 'wpshadow' ),
			'saved'   => __( 'Saved', 'wpshadow' ),
			'error'   => __( 'Save failed', 'wpshadow' ),
		)
	);

	// Title mirrors dashboard style.
	$settings_title = __( 'Support Settings', 'wpshadow' );

	// Register metaboxes for settings.
	// Module Discovery removed - features auto-loaded from directory
	// Note: All settings widgets are only used by features not loaded in this simplified
	// single-feature build. The settings page shows basic information only.

	// Initialize postboxes on this screen (drag/toggle) in footer.
	add_action(
		'admin_print_footer_scripts',
		static function () use ( $screen ): void {
			// State key for settings.
			$state_key = 'wpshadow-settings';
			?>
			<script>
			jQuery(document).ready(function($){
				if (typeof postboxes !== 'undefined') {
					postboxes.add_postbox_toggles('<?php echo esc_js( $state_key ); ?>');
				}
			});
			</script>
			<?php
		}
	);

	?>
	<div class="wrap">
		<h1><?php echo esc_html( $settings_title ); ?></h1>
		<div id="dashboard-widgets" class="metabox-holder">
			<div id="postbox-container-1" class="postbox-container">
				<?php do_meta_boxes( $screen->id, 'normal', null ); ?>
			</div>
			<div id="postbox-container-2" class="postbox-container">
				<?php do_meta_boxes( $screen->id, 'side', null ); ?>
			</div>
		</div>
	</div>
	<?php
}


/**
 * Filter parent_file to ensure correct parent menu is set.
 *
 * @param string $parent_file The parent file.
 * @return string The potentially modified parent file.
 */
function wpshadow_filter_parent_file( string $parent_file ): string {
	global $submenu_file;

	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( empty( $_GET['page'] ) || 'wpshadow' !== $_GET['page'] ) {
		return $parent_file;
	}

	return 'wpshadow';
}

/**
 * Filter submenu_file to highlight the correct submenu item.
 *
 * @param string|null $submenu_file The submenu file.
 * @return string|null The submenu file.
 */
function wpshadow_filter_submenu_file( ?string $submenu_file ): ?string {
	// Simple pass-through - features are auto-loaded, no manual routing needed.
	return $submenu_file;
}

/**
 * Plugin activation hook.
 *
 * @return void
 */
function wpshadow_activate(): void {
	// Check PHP version.
	if ( version_compare( PHP_VERSION, WPSHADOW_MIN_PHP, '<' ) ) {
		deactivate_plugins( WPSHADOW_BASENAME );
		wp_die(
			sprintf(
				/* translators: 1: Required PHP version, 2: Current PHP version */
				esc_html__( 'WPShadow requires PHP %1$s or higher. You are running PHP %2$s.', 'wpshadow' ),
				esc_html( WPSHADOW_MIN_PHP ),
				esc_html( PHP_VERSION )
			),
			esc_html__( 'Plugin Activation Error', 'wpshadow' ),
			array( 'back_link' => true )
		);
	}

	// Check WordPress version.
	global $wp_version;
	if ( version_compare( $wp_version, WPSHADOW_MIN_WP, '<' ) ) {
		deactivate_plugins( WPSHADOW_BASENAME );
		wp_die(
			sprintf(
				/* translators: 1: Required WordPress version, 2: Current WordPress version */
				esc_html__( 'WPShadow requires WordPress %1$s or higher. You are running WordPress %2$s.', 'wpshadow' ),
				esc_html( WPSHADOW_MIN_WP ),
				esc_html( $wp_version )
			),
			esc_html__( 'Plugin Activation Error', 'wpshadow' ),
			array( 'back_link' => true )
		);
	}


	// Flush rewrite rules.
	flush_rewrite_rules();
}

/**
 * Plugin deactivation hook.
 *
 * @return void
 */
function wpshadow_deactivate(): void {
	// Flush rewrite rules.
	flush_rewrite_rules();
}

/**
 * Initialize the plugin.
 *
 * @return void
 */
function wpshadow_init(): void {
	// ========================================================================
	// ASSET VERSION REMOVAL FEATURE (Simplified Single-Feature Build)
	// ========================================================================
	
	// Load widget groups configuration (required by abstract feature class)
	require_once WPSHADOW_PATH . 'includes/admin/class-wps-widget-groups.php';
	
	// Load feature base classes
	require_once WPSHADOW_PATH . 'features/interface-wps-feature.php';
	require_once WPSHADOW_PATH . 'features/class-wps-feature-abstract.php';
	
	// Load feature registry for managing feature toggles
	require_once WPSHADOW_PATH . 'includes/core/class-wps-feature-registry.php';
	\WPShadow\CoreSupport\WPSHADOW_Feature_Registry::init();
	
	// Load the single feature: Asset Version Removal
	require_once WPSHADOW_PATH . 'features/class-wps-asset-version-helpers.php';
	require_once WPSHADOW_PATH . 'features/class-wps-feature-asset-version-removal.php';
	
	// Register and initialize the feature
	$asset_version_feature = new \WPShadow\CoreSupport\WPSHADOW_Feature_Asset_Version_Removal();
	\WPShadow\CoreSupport\WPSHADOW_Feature_Registry::register_feature( $asset_version_feature );
	if ( method_exists( $asset_version_feature, 'init' ) ) {
		$asset_version_feature->init();
	}

	// ========================================================================
	// MINIMAL ADMIN INFRASTRUCTURE
	// ========================================================================

	// Load capability helpers (permission checks)
	require_once WPSHADOW_PATH . 'includes/core/wps-capability-helpers.php';

	// Load notice manager for persistent dismissal.
	require_once WPSHADOW_PATH . 'includes/core/class-wps-notice-manager.php';
	\WPShadow\CoreSupport\WPSHADOW_Notice_Manager::init();

	// Load help content API
	require_once WPSHADOW_PATH . 'includes/core/class-wps-help-content-api.php';

	// Load feature history logging
	require_once WPSHADOW_PATH . 'includes/feature-history.php';
	// Load admin infrastructure for dashboard screens
	require_once WPSHADOW_PATH . 'includes/admin/class-wps-dashboard-assets.php';
	\WPShadow\Admin\WPSHADOW_Dashboard_Assets::init( WPSHADOW_PATH, WPSHADOW_URL );
	
	// Load dashboard widgets - DISABLED: Using unified dashboard renderer instead
	// require_once WPSHADOW_PATH . 'includes/admin/class-wps-dashboard-widgets.php';
	require_once WPSHADOW_PATH . 'includes/admin/class-wps-tab-navigation.php';
	// Load dashboard layout - DISABLED: Using unified dashboard renderer instead
	// require_once WPSHADOW_PATH . 'includes/admin/class-wps-dashboard-layout.php';
	require_once WPSHADOW_PATH . 'includes/admin/screens.php';
	require_once WPSHADOW_PATH . 'includes/views/dashboard-renderer.php';
	
	// Enqueue dashboard assets on admin pages
	add_action( 'admin_enqueue_scripts', __NAMESPACE__ . '\\wpshadow_enqueue_admin_assets' );

	// Register AJAX handlers for meta box state saving
	add_action( 'wp_ajax_meta-box-order', 'wpshadow_ajax_save_meta_box_order' );
	add_action( 'wp_ajax_closed-postboxes', 'wpshadow_ajax_save_closed_postboxes' );

	// Register AJAX handlers for feature toggles
	add_action( 'wp_ajax_wpshadow_toggle_feature', 'wpshadow_ajax_toggle_feature' );
	add_action( 'wp_ajax_wpshadow_toggle_subfeature', 'wpshadow_ajax_toggle_subfeature' );
	add_action( 'wp_ajax_wpshadow_get_feature_history', 'wpshadow_ajax_get_feature_history' );

	// Initialize multisite support if applicable.
	if ( is_multisite() ) {
		add_action( 'network_admin_menu', __NAMESPACE__ . '\\wpshadow_network_admin_menu' );
	}

	// Register admin menu.
	add_action( 'admin_menu', __NAMESPACE__ . '\\wpshadow_admin_menu' );

	// Fix sidebar menu active state.
	add_filter( 'parent_file', __NAMESPACE__ . '\\wpshadow_filter_parent_file', 10 );
	add_filter( 'submenu_file', __NAMESPACE__ . '\\wpshadow_filter_submenu_file', 10 );

	// Plugin page links and meta.
	add_filter( 'plugin_action_links_' . WPSHADOW_BASENAME, __NAMESPACE__ . '\\wpshadow_plugin_action_links' );
	add_filter( 'plugin_row_meta', __NAMESPACE__ . '\\wpshadow_plugin_row_meta', 10, 2 );

	// Phase 3 Optimization: Clear plugins cache on activation/deactivation
	add_action( 'activated_plugin', __NAMESPACE__ . '\\wpshadow_clear_plugins_cache' );
	add_action( 'deactivated_plugin', __NAMESPACE__ . '\\wpshadow_clear_plugins_cache' );
	
	// Enable saving of metabox order and state for WPShadow screens
	add_filter( 'screen_settings', __NAMESPACE__ . '\\wpshadow_screen_settings', 10, 2 );
	add_filter( 'set-screen-option', __NAMESPACE__ . '\\wpshadow_set_screen_option', 10, 3 );
}

/**
 * Filter screen settings to add our custom options.
 *
 * @param string    $settings Screen settings HTML.
 * @param WP_Screen $screen   Current screen object.
 * @return string Modified settings HTML.
 */
function wpshadow_screen_settings( string $settings, $screen ): string {
	if ( ! $screen || strpos( $screen->id, 'wpshadow' ) === false ) {
		return $settings;
	}
	
	return $settings;
}

/**
 * Filter to save screen options.
 *
 * @param mixed  $status Screen option value. Default false to skip.
 * @param string $option The option name.
 * @param mixed  $value  The option value.
 * @return mixed The option value to save.
 */
function wpshadow_set_screen_option( $status, string $option, $value ) {
	if ( 'layout_columns' === $option ) {
		return $value;
	}
	return $status;
}

/**
 * AJAX handler to save meta box order for WPShadow pages.
 *
 * @return void
 */
function wpshadow_ajax_save_meta_box_order(): void {
	check_ajax_referer( 'meta-box-order' );

	if ( ! current_user_can( 'edit_dashboard' ) ) {
		wp_die( -1 );
	}

	$page = isset( $_POST['page'] ) ? sanitize_key( $_POST['page'] ) : '';
	$order = isset( $_POST['order'] ) ? (array) $_POST['order'] : array();

	// Only handle WPShadow pages
	if ( empty( $page ) || ! str_starts_with( $page, 'toplevel_page_wpshadow' ) ) {
		wp_die( 0 );
	}

	// Sanitize the order array
	$sanitized_order = array();
	foreach ( $order as $key => $value ) {
		$sanitized_order[ sanitize_key( $key ) ] = sanitize_text_field( $value );
	}

	// Save to user meta
	$user_id = get_current_user_id();
	update_user_meta( $user_id, 'meta-box-order_' . $page, $sanitized_order );

	wp_die( 1 );
}

/**
 * AJAX handler to save closed postboxes state for WPShadow pages.
 *
 * @return void
 */
function wpshadow_ajax_save_closed_postboxes(): void {
	check_ajax_referer( 'closedpostboxes', 'closedpostboxesnonce' );

	if ( ! current_user_can( 'edit_dashboard' ) ) {
		wp_die( -1 );
	}

	$page = isset( $_POST['page'] ) ? sanitize_key( $_POST['page'] ) : '';
	$closed = isset( $_POST['closed'] ) ? explode( ',', sanitize_text_field( $_POST['closed'] ) ) : array();

	// Only handle WPShadow pages
	if ( empty( $page ) || ! str_starts_with( $page, 'toplevel_page_wpshadow' ) ) {
		wp_die( 0 );
	}

	// Sanitize closed boxes
	$sanitized_closed = array_map( 'sanitize_key', $closed );

	// Save to user meta
	$user_id = get_current_user_id();
	update_user_meta( $user_id, 'closedpostboxes_' . $page, $sanitized_closed );

	wp_die( 1 );
}

/**
 * AJAX handler to toggle feature on/off.
 *
 * @return void
 */
function wpshadow_ajax_toggle_feature(): void {
	check_ajax_referer( 'wpshadow_toggle_feature', 'nonce' );

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( __( 'Insufficient permissions.', 'wpshadow' ) );
	}

	$feature_id = isset( $_POST['feature_id'] ) ? sanitize_key( $_POST['feature_id'] ) : '';
	$enabled = isset( $_POST['enabled'] ) ? (bool) intval( $_POST['enabled'] ) : false;

	if ( empty( $feature_id ) ) {
		wp_send_json_error( __( 'Invalid feature ID.', 'wpshadow' ) );
	}

	$network_scope = is_multisite() && is_network_admin();
	
	try {
		\WPShadow\CoreSupport\WPSHADOW_Feature_Registry::set_feature_toggle( $feature_id, $enabled, $network_scope );
		
		// Log the activity
		wpshadow_log_feature_activity( $feature_id, $enabled ? 'enabled' : 'disabled', '' );
		
		wp_send_json_success( array(
			'message' => $enabled ? __( 'Feature enabled.', 'wpshadow' ) : __( 'Feature disabled.', 'wpshadow' ),
			'feature_id' => $feature_id,
		) );
	} catch ( \Exception $e ) {
		wp_send_json_error( $e->getMessage() );
	}
}

/**
 * AJAX handler to toggle sub-feature on/off.
 *
 * @return void
 */
function wpshadow_ajax_toggle_subfeature(): void {
	check_ajax_referer( 'wpshadow_toggle_subfeature', 'nonce' );

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( __( 'Insufficient permissions.', 'wpshadow' ) );
	}

	$feature_id = isset( $_POST['feature_id'] ) ? sanitize_key( $_POST['feature_id'] ) : '';
	$subfeature_key = isset( $_POST['subfeature_key'] ) ? sanitize_key( $_POST['subfeature_key'] ) : '';
	$enabled = isset( $_POST['enabled'] ) ? (bool) intval( $_POST['enabled'] ) : false;

	if ( empty( $feature_id ) || empty( $subfeature_key ) ) {
		wp_send_json_error( __( 'Invalid feature or sub-feature ID.', 'wpshadow' ) );
		return;
	}

	$option_name = "wpshadow_{$feature_id}_{$subfeature_key}";
	update_option( $option_name, $enabled, false );
	
	// Log the activity for the sub-feature
	wpshadow_log_feature_activity( $subfeature_key, $enabled ? 'enabled' : 'disabled', sprintf( 'Part of %s', $feature_id ) );

	wp_send_json_success( array(
		'message' => $enabled ? __( 'Sub-feature enabled.', 'wpshadow' ) : __( 'Sub-feature disabled.', 'wpshadow' ),
		'feature_id' => $subfeature_key,
	) );
}

/**
 * Register network admin menu for multisite.
 *
 * @return void
 */
function wpshadow_network_admin_menu(): void {
	add_menu_page(
		__( 'WPShadow', 'wpshadow' ),
		__( 'WPShadow', 'wpshadow' ),
		'manage_network_options',
		'wpshadow',
		__NAMESPACE__ . '\\wpshadow_render_tab_router',
		'dashicons-admin-generic',
		999
	);

	// Add dashboard submenu first to ensure menu links to dashboard
	add_submenu_page(
		'wpshadow',
		__( 'WPShadow Dashboard', 'wpshadow' ),
		__( 'Dashboard', 'wpshadow' ),
		'manage_network_options',
		'wpshadow',
		__NAMESPACE__ . '\\wpshadow_render_tab_router'
	);

	// Features create QuickLinks in dashboard instead of submenus

	// Initialize dashboard screen extras (Screen Options, Help) and metaboxes.
	add_action( 'load-toplevel_page_wpshadow', 'WPShadow\\CoreSupport\\wpshadow_setup_dashboard_screen' );
}

/**
 * Register admin menu.
 *
 * @return void
 */
function wpshadow_admin_menu(): void {
	add_menu_page(
		__( 'WPShadow', 'wpshadow' ),
		__( 'WPShadow', 'wpshadow' ),
		'manage_options',
		'wpshadow',
		__NAMESPACE__ . '\\wpshadow_render_tab_router',
		'dashicons-admin-generic',
		999
	);

	// Add dashboard submenu first to ensure menu links to dashboard
	add_submenu_page(
		'wpshadow',
		__( 'WPShadow Dashboard', 'wpshadow' ),
		__( 'Dashboard', 'wpshadow' ),
		'manage_options',
		'wpshadow',
		__NAMESPACE__ . '\\wpshadow_render_tab_router'
	);

	// Features create QuickLinks in dashboard instead of submenus

	// Initialize dashboard screen extras (Screen Options, Help) and metaboxes.
	add_action( 'load-toplevel_page_wpshadow', 'WPShadow\\CoreSupport\\wpshadow_setup_dashboard_screen' );
}

/**
 * Add a one-time admin notice, shown on next page load.
 *
 * @param string $message Notice message.
 * @param string $type    Notice type: 'error'|'updated'|'warning'|'success'.
 * @return void
 */
function wpshadow_add_admin_notice( string $message, string $type = 'warning' ): void {
	// Store transient for display in admin_notices.
	set_transient(
		'wpshadow_admin_notice',
		array(
			'message' => sanitize_text_field( $message ),
			'type'    => sanitize_key( $type ),
		),
		60
	);
}

/**
 * Render one-time admin notice and clear it.
 *
 * @return void
 */
function wpshadow_render_admin_notice(): void {
	$notice = get_transient( 'wpshadow_admin_notice' );
	if ( empty( $notice ) || ! is_array( $notice ) ) {
		return;
	}

	delete_transient( 'wpshadow_admin_notice' );

	$type    = $notice['type'] ?? 'updated';
	$message = $notice['message'] ?? '';
	if ( empty( $message ) ) {
		return;
	}

	// Map type to CSS class.
	$class = 'notice';
	switch ( $type ) {
		case 'error':
			$class .= ' notice-error';
			break;
		case 'warning':
			$class .= ' notice-warning';
			break;
		case 'success':
			$class .= ' notice-success';
			break;
		default:
			$class .= ' notice-info';
			break;
	}

	echo '<div class="' . esc_attr( $class ) . ' is-dismissible"><p>' . esc_html( $message ) . '</p></div>';
}

add_action( 'admin_notices', __NAMESPACE__ . '\wpshadow_render_admin_notice' );
add_action( 'network_admin_notices', __NAMESPACE__ . '\wpshadow_render_admin_notice' );


/**
 * Render the capabilities management page.
 *
 * @return void
 */
function wpshadow_render_capabilities_page(): void {
	$required_cap = is_network_admin() ? 'manage_network_options' : 'manage_options';

	if ( ! current_user_can( $required_cap ) ) {
		wp_die( esc_html__( 'You do not have sufficient permissions to manage capabilities.', 'wpshadow' ) );
	}

	require WPSHADOW_PATH . 'includes/views/capabilities.php';
}

/**
 * Handle capability mapping submissions.
 *
 * @return void
 */
function wpshadow_handle_capabilities_post(): void {
	if ( ! is_admin() ) {
		return;
	}

	$action = isset( $_POST['wpshadow_capability_action'] ) ? sanitize_key( wp_unslash( $_POST['wpshadow_capability_action'] ) ) : '';
	if ( 'add' !== $action ) {
		return;
	}

	$required_cap = is_network_admin() ? 'manage_network_options' : 'manage_options';
	if ( ! current_user_can( $required_cap ) ) {
		wp_die( esc_html__( 'You do not have sufficient permissions to manage capabilities.', 'wpshadow' ) );
	}

	$nonce = isset( $_POST['wpshadow_capabilities_nonce'] ) ? wp_unslash( $_POST['wpshadow_capabilities_nonce'] ) : '';
	if ( ! wp_verify_nonce( $nonce, 'wpshadow_capabilities' ) ) {
		wp_die( esc_html__( 'Nonce verification failed. Please try again.', 'wpshadow' ) );
	}

	$module_slug    = isset( $_POST['wpshadow_module_slug'] ) ? sanitize_key( wp_unslash( $_POST['wpshadow_module_slug'] ) ) : '';
	$capability_key = isset( $_POST['wpshadow_capability_key'] ) ? sanitize_key( wp_unslash( $_POST['wpshadow_capability_key'] ) ) : '';
	$wp_capability  = isset( $_POST['wpshadow_wp_capability'] ) ? sanitize_key( wp_unslash( $_POST['wpshadow_wp_capability'] ) ) : '';

	if ( empty( $module_slug ) || empty( $capability_key ) || empty( $wp_capability ) ) {
		add_settings_error(
			'wpshadow_capabilities',
			'wpshadow_capabilities_invalid',
			esc_html__( 'All fields are required to register a capability mapping.', 'wpshadow' ),
			'error'
		);
	} else {
		\WPShadow\CoreSupport\WPSHADOW_Capabilities::register_capability( $module_slug, $capability_key, $wp_capability );
		add_settings_error(
			'wpshadow_capabilities',
			'wpshadow_capabilities_saved',
			esc_html__( 'Capability mapping saved.', 'wpshadow' ),
			'updated'
		);
	}

	$redirect = is_network_admin() ? network_admin_url( 'admin.php?page=wps-capabilities' ) : admin_url( 'admin.php?page=wps-capabilities' );

	wp_safe_redirect( add_query_arg( 'settings-updated', 'true', $redirect ) );
	exit;
}

/**
 * Tab-based router for all admin pages.
 *
 * @return void
 */
function wpshadow_render_tab_router(): void {
	if ( ! current_user_can( 'manage_options' ) && ! current_user_can( 'manage_network_options' ) ) {
		wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'wpshadow' ) );
	}

	$context = \WPShadow\CoreSupport\WPSHADOW_Tab_Navigation::get_current_context();
	$tab     = $context['tab'];

	// Render tabs
	$tabs = \WPShadow\CoreSupport\WPSHADOW_Tab_Navigation::get_core_tabs();
	\WPShadow\CoreSupport\WPSHADOW_Tab_Navigation::render_tabs( $tabs, $tab );

	// Route to core content
	wpshadow_render_core_content( $tab );
}

/**
 * Setup Screen Options, Help tabs, and register dashboard meta boxes.
 *
 * @return void
 */
// Moved to includes/admin/screens.php

/**
 * Setup Screen Options and register dashboard meta boxes for hub pages.
 *
 * @param string $hub_id Hub identifier.
 * @return void
 */
// Moved to includes/admin/screens.php

/**
 * Render Core-level content based on active tab.
 *
 * @param string $tab Active tab ID.
 * @return void
 */
function wpshadow_render_core_content( string $tab ): void {
	switch ( $tab ) {
		case 'register':
			require_once WPSHADOW_PATH . 'includes/views/register.php';
			break;
		case 'modules':
			wpshadow_render_modules();
			break;
		case 'dashboard_settings':
		case 'settings': // Backward compatibility redirect.
			if ( 'settings' === $tab ) {
				// Redirect old settings URL to new dashboard_settings.
				$redirect_url = add_query_arg( 'wpshadow_tab', 'dashboard_settings', admin_url( 'admin.php?page=wpshadow' ) );
				wp_safe_redirect( $redirect_url );
				exit;
			}
			wpshadow_render_settings();
			break;
		case 'help':
		case 'features':
		case 'dashboard':
		default:
			// Route to unified two-column widget layout for all main tabs.
			\WPShadow\CoreSupport\wpshadow_render_unified_layout( $tab );
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
function wpshadow_render_hub_content( string $hub_id, string $tab ): void {
	switch ( $tab ) {
		case 'help':
			wpshadow_render_help_layout();
			break;
	case 'features':
		wpshadow_render_features_page( 'hub', $hub_id );
		break;
		case 'dashboard':
		default:
			// Route to unified dashboard renderer.
			\WPShadow\CoreSupport\wpshadow_render_dashboard( $hub_id );
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
function wpshadow_render_spoke_content( string $hub_id, string $spoke_id, string $tab ): void {
	switch ( $tab ) {
		case 'help':
			wpshadow_render_help_layout();
			break;
	case 'features':
		wpshadow_render_features_page( 'spoke', $hub_id, $spoke_id );
		break;
			\WPShadow\CoreSupport\wpshadow_render_dashboard( $hub_id, $spoke_id );
			break;
	}
}

/**
 * Render dashboard.
 *
 * @param string $hub_id Deprecated - kept for compatibility.
 * @param string $spoke_id Deprecated - kept for compatibility.
 * @return void
 */
// Moved to includes/views/dashboard.php

/**
 * Render Help view with enhanced documentation and FAQ.
 *
 * @return void
 */
function wpshadow_render_help_layout(): void {
	require_once WPSHADOW_PATH . 'includes/views/help.php';
}

/**
 * Render Performance Dashboard tab.
 *
 * @return void
 */
function wpshadow_render_performance_dashboard(): void {
	require_once WPSHADOW_PATH . 'includes/views/performance-dashboard.php';
}

/**
 * Render Features tab for a given context.
 *
 * @param string $level    Context level: core|hub|spoke.
 * @param string $hub_id   Hub identifier when applicable.
 * @param string $spoke_id Spoke identifier when applicable.
 * @return void
 */
function wpshadow_render_features_page( string $level, string $hub_id = '', string $spoke_id = '' ): void {
	$required_cap = is_network_admin() ? 'manage_network_options' : 'manage_options';
	if ( ! current_user_can( $required_cap ) ) {
		wp_die( esc_html__( 'You do not have sufficient permissions to manage features.', 'wpshadow' ) );
	}

	// Enqueue the pre-registered handles (inline CSS/JS attached in wpshadow_init_dashboard_assets)
	// Enable WordPress postbox drag/drop persistence on the Features screen.
	wp_enqueue_script( 'postbox' );
	wp_enqueue_script( 'jquery-ui-sortable' );

	// Note: postbox script removed due to History API SecurityError with GitHub Codespaces domains
	// The feature toggles now use custom AJAX without WordPress postbox drag/drop functionality
	// If needed in future, postbox can be re-enabled with proper domain/origin handling

	$network_scope = is_multisite() && is_network_admin();
	$features      = \WPShadow\CoreSupport\WPSHADOW_Feature_Registry::get_features_by_scope( $level, $hub_id, $spoke_id, $network_scope );

	// Optional feature filter (?feature=some-id) to reuse the same page for focused view
	$feature_filter = isset( $_GET['feature'] ) ? sanitize_key( (string) $_GET['feature'] ) : '';
	if ( ! empty( $feature_filter ) ) {
		$features = wpshadow_filter_features_for_focus( $features, $feature_filter );
	}

	// Enrich parent features with sub-feature enabled states
	foreach ( $features as &$feature ) {
		if ( ! empty( $feature['sub_features'] ) ) {
			foreach ( $feature['sub_features'] as $sub_key => &$sub_data ) {
				$option_name       = 'wpshadow_' . $feature['id'] . '_' . $sub_key;
				$is_enabled        = get_option( $option_name, (bool) ( $sub_data['default_enabled'] ?? false ) );
				$sub_data['enabled'] = (bool) $is_enabled;
			}
			unset( $sub_data );
		}
	}
	unset( $feature );

	if ( 'POST' === $_SERVER['REQUEST_METHOD'] && isset( $_POST['wpshadow_features_nonce'] ) ) {
		check_admin_referer( 'wpshadow_save_features', 'wpshadow_features_nonce' );

		$enabled_ids = array();
		$sub_features_map = array(); // Map sub-feature IDs to their parent and key
		
		// Build a map of sub-feature IDs to their parent feature and key
		foreach ( $features as $feature ) {
			if ( ! empty( $feature['sub_features'] ) ) {
				$parent_id = $feature['id'] ?? '';
				foreach ( $feature['sub_features'] as $sub_key => $sub_data ) {
					$sub_features_map[ $sub_key ] = array(
						'parent_id' => $parent_id,
						'sub_key'   => $sub_key,
					);
				}
			}
		}
		
		if ( isset( $_POST['features'] ) && is_array( $_POST['features'] ) ) {
			foreach ( $_POST['features'] as $feature_id => $flag ) {
				$feature_id = sanitize_key( (string) $feature_id );
				
				// Check if this is a sub-feature ID
				if ( isset( $sub_features_map[ $feature_id ] ) ) {
					// Save as a sub-feature option
					$sub_map     = $sub_features_map[ $feature_id ];
					$option_name = 'wpshadow_' . $sub_map['parent_id'] . '_' . $sub_map['sub_key'];
					$is_enabled  = ! empty( $flag );
					update_option( $option_name, $is_enabled ? 1 : 0 );
				} else {
					// Regular feature toggle
					$enabled_ids[] = $feature_id;
				}
			}
		}
		
		// Save unchecked sub-features as disabled (they won't be in POST data)
		foreach ( $sub_features_map as $sub_id => $sub_map ) {
			if ( ! isset( $_POST['features'][ $sub_id ] ) ) {
				$option_name = 'wpshadow_' . $sub_map['parent_id'] . '_' . $sub_map['sub_key'];
				update_option( $option_name, 0 );
			}
		}

		\WPShadow\CoreSupport\WPSHADOW_Feature_Registry::save_feature_states( array_values( $features ), $enabled_ids, $network_scope );
		$features = \WPShadow\CoreSupport\WPSHADOW_Feature_Registry::get_features_by_scope( $level, $hub_id, $spoke_id, $network_scope );

		// Re-enrich parent features with sub-feature enabled states after save
		foreach ( $features as &$feature ) {
			if ( ! empty( $feature['sub_features'] ) ) {
				foreach ( $feature['sub_features'] as $sub_key => &$sub_data ) {
					$option_name = 'wpshadow_' . $feature['id'] . '_' . $sub_key;
					$is_enabled = get_option( $option_name, (bool) ( $sub_data['default_enabled'] ?? false ) );
					$sub_data['enabled'] = (bool) $is_enabled;
				}
				unset( $sub_data );
			}
		}
		unset( $feature );

		add_settings_error(
			'wpshadow_features',
			'wpshadow_features_saved',
			esc_html__( 'Feature settings updated.', 'wpshadow' ),
			'updated'
		);
	}

	// Register metaboxes for feature widgets BEFORE rendering the view
	wpshadow_register_feature_metaboxes( $features );

	require WPSHADOW_PATH . 'includes/views/features.php';
}

/**
 * Filter the features array to a specific feature (or its parent if a sub-feature ID is provided).
 * Keeps the parent + its children so the existing UI/JS continue to function identically.
 *
 * @param array  $features       Full feature list.
 * @param string $target_feature Target feature or sub-feature ID.
 * @return array Filtered features.
 */
function wpshadow_filter_features_for_focus( array $features, string $target_feature ): array {
	$GLOBALS['wpshadow_focus_feature_id'] = '';
	$parent_id        = null;
	$filtered_parent  = null;

	// Identify whether target is a parent or a sub-feature and capture the parent
	foreach ( $features as $feat ) {
		$fid = $feat['id'] ?? '';
		if ( $fid === $target_feature ) {
			$parent_id       = $fid;
			$filtered_parent = $feat;
			break;
		}
		if ( isset( $feat['sub_features'][ $target_feature ] ) ) {
			$parent_id       = $fid;
			// clone parent but keep only the requested child sub-feature
			$parent_clone                 = $feat;
			$parent_clone['sub_features'] = array( $target_feature => $feat['sub_features'][ $target_feature ] );
			$filtered_parent              = $parent_clone;
			$GLOBALS['wpshadow_focus_feature_id'] = $target_feature;
			break;
		}
	}

	if ( ! $parent_id || ! $filtered_parent ) {
		$GLOBALS['wpshadow_focus_feature_id'] = '';
		return $features; // fallback: no filtering if not found
	}

	// Return only the parent (with possibly trimmed sub-features) so the list shows only the target
	return array( $filtered_parent );
}

/**
 * Register metaboxes for grouped features.
 *
 * @param array $features Array of feature definitions.
 * @return void
 */
function wpshadow_register_feature_metaboxes( array $features ): void {
	// Group features by widget_group
	$grouped_features = array();
	foreach ( $features as $feature ) {
		$group = $feature['widget_group'] ?? 'general';
		if ( ! isset( $grouped_features[ $group ] ) ) {
			// Get label and description from centralized Widget_Groups class
			$grouped_features[ $group ] = array(
				'label'       => \WPShadow\CoreSupport\WPSHADOW_Widget_Groups::get_label( $group ),
				'description' => \WPShadow\CoreSupport\WPSHADOW_Widget_Groups::get_description( $group ),
				'features'    => array(),
			);
		}
		$grouped_features[ $group ]['features'][] = $feature;
	}

	// Store grouped features in global for metabox callback access
	$GLOBALS['wpshadow_grouped_features'] = $grouped_features;

	// Register a metabox for each widget group
	foreach ( $grouped_features as $group_id => $group_data ) {
		add_meta_box(
			'wpshadow_features_' . sanitize_key( $group_id ),
			esc_html( $group_data['label'] ),
			__NAMESPACE__ . '\\wpshadow_render_feature_group_metabox',
			'toplevel_page_wpshadow',
			'normal',
			'default',
			array( 'group_id' => $group_id )
		);
	}
}

/**
 * Render a feature group metabox.
 *
 * @param mixed $post Not used (required by WordPress metabox signature).
 * @param array $metabox Metabox configuration including 'args' with 'group_id'.
 * @return void
 */
function wpshadow_render_feature_group_metabox( $post, array $metabox ): void {
	$group_id = $metabox['args']['group_id'] ?? '';
	$grouped_features = $GLOBALS['wpshadow_grouped_features'] ?? array();

	if ( empty( $group_id ) || empty( $grouped_features[ $group_id ] ) ) {
		return;
	}

	$group_data = $grouped_features[ $group_id ];
	$features   = $group_data['features'] ?? array();
	$focus_feature_id = $GLOBALS['wpshadow_focus_feature_id'] ?? '';

	if ( empty( $features ) ) {
		echo '<p>' . esc_html__( 'No features in this group.', 'wpshadow' ) . '</p>';
		return;
	}

	// Separate parent and child features
	$parent_features = array();
	$child_features  = array();

	foreach ( $features as $feature ) {
		$feature_id = $feature['id'] ?? '';
		$sub_features = $feature['sub_features'] ?? array();

		if ( ! empty( $sub_features ) ) {
			// This is a parent feature
			$parent_features[ $feature_id ] = array(
				'feature' => $feature,
				'children' => $sub_features,
			);
		} else {
			// Check if this is a child of one of the parent features
			$is_child = false;
			foreach ( $parent_features as $parent_id => $parent_data ) {
				if ( isset( $parent_data['children'][ $feature_id ] ) ) {
					$is_child = true;
					break;
				}
			}

			if ( ! $is_child ) {
				// Regular feature without children or parent
				$child_features[] = $feature;
			}
		}
	}

	?>
	<table class="wp-list-table widefat fixed striped wpshadow-features-list">
		<tbody>
			<?php
			// Render parent features first
			foreach ( $parent_features as $parent_id => $parent_data ) :
				$feature = $parent_data['feature'];
				$children = $parent_data['children'];
				
				$feature_id   = $feature['id'] ?? '';
				$feature_name = $feature['name'] ?? $feature_id;
				$feature_desc = $feature['description'] ?? '';
				$is_enabled   = ! empty( $feature['enabled'] );
				
				// Get health score for this feature
				$feature_score = \WPShadow\CoreSupport\WPSHADOW_Site_Health_Integration::get_feature_score( $feature_id );
				$health_score = $feature_score['score'] ?? 0;
				$score_color = '#999'; // Default gray
				if ( $health_score >= 80 ) {
					$score_color = '#46b450'; // Green
				} elseif ( $health_score >= 60 ) {
					$score_color = '#ffb900'; // Yellow/Orange
				} elseif ( $health_score > 0 ) {
					$score_color = '#dc3232'; // Red
				}

				$render_parent_row = true;
				if ( $focus_feature_id && $focus_feature_id !== $feature_id && isset( $children[ $focus_feature_id ] ) ) {
					$render_parent_row = false;
				}
				?>
				<?php if ( $render_parent_row ) : ?>
					<tr class="wpshadow-parent-feature" data-parent-id="<?php echo esc_attr( $feature_id ); ?>">
						<td class="check-column" style="width: 60px; padding: 12px;">
							<label class="wps-feature-toggle-label">
								<input 
									type="checkbox" 
									class="wps-feature-toggle-input wpshadow-parent-toggle"
									name="features[<?php echo esc_attr( $feature_id ); ?>]" 
									value="1" 
									data-parent-id="<?php echo esc_attr( $feature_id ); ?>"
									data-feature-name="<?php echo esc_attr( $feature_name ); ?>"
									data-default-enabled="<?php echo esc_attr( (int) $is_enabled ); ?>"
									<?php checked( $is_enabled ); ?>
								/>
								<span class="wps-feature-toggle-switch"></span>
								<span class="screen-reader-text">
									<?php printf( esc_html__( 'Enable %s', 'wpshadow' ), esc_html( $feature_name ) ); ?>
								</span>
							</label>
						</td>
						<td style="position: relative;">
							<div style="display: flex; justify-content: space-between; align-items: flex-start;">
								<div style="flex: 1;">
									<strong><?php echo esc_html( $feature_name ); ?></strong>
									<?php if ( ! empty( $feature_desc ) ) : ?>
										<p style="margin: 4px 0 0; color: #666;">
											<?php echo esc_html( $feature_desc ); ?>
										</p>
									<?php endif; ?>
								</div>
								<div style="margin-left: 15px; display: flex; align-items: center; gap: 10px;">
									<div class="wpshealth-circle wps-health-score-badge" data-feature-id="<?php echo esc_attr( $feature_id ); ?>" data-score="<?php echo esc_attr( (int) $health_score ); ?>">
										<span class="wps-score-value"><?php echo esc_html( $health_score ); ?></span>
									</div>
									<?php if ( ! \WPShadow\CoreSupport\WPSHADOW_Feature_Details_Page::is_details_page( $feature_id ) ) : ?>
										<?php
										$feature_url = \WPShadow\CoreSupport\WPSHADOW_Feature_Details_Page::get_feature_url( $feature_id );
										?>
										<a href="<?php echo esc_url( $feature_url ); ?>" 
										   class="button button-small wps-feature-settings-btn" 
										   data-feature-id="<?php echo esc_attr( $feature_id ); ?>">
											<?php esc_html_e( 'Details', 'wpshadow' ); ?>
										</a>
									<?php endif; ?>
								</div>
							</div>
						</td>
					</tr>
				<?php endif; ?>

				<?php
				// Render child features indented
				foreach ( $children as $child_id => $child_data ) :
					// Handle both simple string labels and detailed child data
					$child_label = is_array( $child_data ) ? ( $child_data['name'] ?? $child_id ) : $child_data;
					$child_desc  = is_array( $child_data ) ? ( $child_data['description'] ?? '' ) : '';
					$child_enabled = is_array( $child_data ) && isset( $child_data['enabled'] ) ? (bool) $child_data['enabled'] : (bool) ( $child_data['default_enabled'] ?? false );
					?>
					<tr class="wpshadow-child-feature wpshadow-child-of-<?php echo esc_attr( $feature_id ); ?>" data-parent-id="<?php echo esc_attr( $feature_id ); ?>" style="background-color: #f9f9f9; --wps-child-indent: 5%;">
						<td class="check-column" style="width: 60px; padding: 12px; padding-left: calc(var(--wps-child-indent, 5%) + 10px);">
							<label class="wps-feature-toggle-label">
								<input 
									type="checkbox" 
									class="wps-feature-toggle-input wpshadow-child-toggle"
									name="features[<?php echo esc_attr( $child_id ); ?>]" 
									value="1" 
									data-parent-id="<?php echo esc_attr( $feature_id ); ?>"
									data-feature-id="<?php echo esc_attr( $child_id ); ?>"
									data-feature-name="<?php echo esc_attr( $child_label ); ?>"
									data-default-enabled="<?php echo esc_attr( (int) $child_enabled ); ?>"
									<?php checked( $child_enabled ); ?>
								/>
								<span class="wps-feature-toggle-switch"></span>
								<span class="screen-reader-text">
									<?php printf( esc_html__( 'Enable %s', 'wpshadow' ), esc_html( $child_label ) ); ?>
								</span>
							</label>
						</td>
						<td style="position: relative;">
							<div style="display: flex; justify-content: space-between; align-items: flex-start; padding-left: var(--wps-child-indent, 5%); border-left: 3px solid #ddd;">
								<div style="flex: 1;">
									<strong style="color: #555; font-size: 13px;">
										<?php echo esc_html( $child_label ); ?>
									</strong>
									<?php if ( ! empty( $child_desc ) ) : ?>
										<p style="margin: 4px 0 0; color: #666; font-size: 12px;">
											<?php echo esc_html( $child_desc ); ?>
										</p>
									<?php endif; ?>
								</div>
								<div style="margin-left: 12px; display: flex; align-items: center; gap: 10px;">
									<div class="wpshealth-circle wps-health-score-badge" data-feature-id="<?php echo esc_attr( $child_id ); ?>" data-score="<?php echo esc_attr( $child_enabled ? 100 : 0 ); ?>" aria-label="<?php esc_attr_e( 'Health score', 'wpshadow' ); ?>">
										<span class="wps-score-value"><?php echo esc_html( $child_enabled ? 100 : 0 ); ?></span>
									</div>
									<?php if ( ! \WPShadow\CoreSupport\WPSHADOW_Feature_Details_Page::is_details_page( $child_id ) ) : ?>
										<?php
										$child_url = \WPShadow\CoreSupport\WPSHADOW_Feature_Details_Page::get_feature_url( $child_id );
										?>
										<a href="<?php echo esc_url( $child_url ); ?>" class="button button-small wps-feature-settings-btn">
											<?php esc_html_e( 'Details', 'wpshadow' ); ?>
										</a>
									<?php endif; ?>
								</div>
							</div>
						</td>
					</tr>
				<?php endforeach; ?>
			<?php endforeach; ?>

			<?php
			// Render regular features (non-parent, non-child)
			foreach ( $child_features as $feature ) :
				?>
				<?php
				$feature_id   = $feature['id'] ?? '';
				$feature_name = $feature['name'] ?? $feature_id;
				$feature_desc = $feature['description'] ?? '';
				$is_enabled   = ! empty( $feature['enabled'] );
				
				// Get health score for this feature
				$feature_score = \WPShadow\CoreSupport\WPSHADOW_Site_Health_Integration::get_feature_score( $feature_id );
				$health_score = $feature_score['score'] ?? 0;
				$score_color = '#999'; // Default gray
				if ( $health_score >= 80 ) {
					$score_color = '#46b450'; // Green
				} elseif ( $health_score >= 60 ) {
					$score_color = '#ffb900'; // Yellow/Orange
				} elseif ( $health_score > 0 ) {
					$score_color = '#dc3232'; // Red
				}
				?>
				<tr>
					<td class="check-column" style="width: 60px; padding: 12px;">
						<label class="wps-feature-toggle-label">
							<input 
								type="checkbox" 
								class="wps-feature-toggle-input"
								name="features[<?php echo esc_attr( $feature_id ); ?>]" 
								value="1" 
								<?php checked( $is_enabled ); ?>
							/>
							<span class="wps-feature-toggle-switch"></span>
							<span class="screen-reader-text">
								<?php printf( esc_html__( 'Enable %s', 'wpshadow' ), esc_html( $feature_name ) ); ?>
							</span>
						</label>
					</td>
					<td style="position: relative;">
						<div style="display: flex; justify-content: space-between; align-items: flex-start;">
							<div style="flex: 1;">
								<?php if ( $is_enabled ) : ?>
									<?php
									$feature_url = \WPShadow\CoreSupport\WPSHADOW_Feature_Details_Page::get_feature_url( $feature_id );
									?>
									<strong>
										<a href="<?php echo esc_url( $feature_url ); ?>" style="color: #2271b1; text-decoration: none;">
											<?php echo esc_html( $feature_name ); ?>
										</a>
									</strong>
								<?php else : ?>
									<strong><?php echo esc_html( $feature_name ); ?></strong>
								<?php endif; ?>
								<?php if ( ! empty( $feature_desc ) ) : ?>
									<p style="margin: 4px 0 0; color: #666;">
										<?php echo esc_html( $feature_desc ); ?>
									</p>
								<?php endif; ?>
							</div>
							<div style="margin-left: 15px; display: flex; align-items: center; gap: 10px;">
								<?php if ( $is_enabled && $health_score > 0 ) : ?>
									<div class="wps-health-score-badge" style="display: inline-flex; align-items: center; justify-content: center; min-width: 45px; height: 32px; padding: 0 10px; background: <?php echo esc_attr( $score_color ); ?>; color: #fff; border-radius: 16px; font-weight: 600; font-size: 14px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
									<span style="font-size: 16px; margin-right: 2px;">⚡</span>
									<span><?php echo esc_html( $health_score ); ?></span>
									</div>
								<?php endif; ?>
								<?php
								$feature_url = \WPShadow\CoreSupport\WPSHADOW_Feature_Details_Page::get_feature_url( $feature_id );
								?>
								<a href="<?php echo esc_url( $feature_url ); ?>" 
								   class="button button-small wps-feature-settings-btn" 
								   data-feature-id="<?php echo esc_attr( $feature_id ); ?>">
									<?php esc_html_e( 'Details', 'wpshadow' ); ?>
								</a>
							</div>
						</div>
					</td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
	<?php
}

/**
 * Render modules view (deprecated - features are auto-loaded).
 *
 * @return void
 */
function wpshadow_render_modules(): void {
	if ( ! current_user_can( 'manage_options' ) && ! current_user_can( 'manage_network_options' ) ) {
		wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'wpshadow' ) );
	}

	// Features are automatically loaded from the features directory.
	// Module system has been removed in favor of a simpler feature-based architecture.
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'WPShadow Features', 'wpshadow' ); ?></h1>
		<p><?php esc_html_e( 'Features are automatically loaded from the features directory. No manual installation required.', 'wpshadow' ); ?></p>
	</div>
	<?php
}

/**
 * Render network settings page.
 *
 * @return void
 */
function wpshadow_render_network_settings(): void {
	if ( ! current_user_can( 'manage_network_options' ) ) {
		wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'wpshadow' ) );
	}


	require_once WPSHADOW_PATH . 'includes/views/settings.php';
}

/**
 * Render settings page.
 *
 * @return void
 */
function wpshadow_render_settings_page(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'wpshadow' ) );
	}

	require_once WPSHADOW_PATH . 'includes/views/settings.php';
}

/**
 * Handle AJAX toggle module request.
 *
 * @return void
 */
// Moved to includes/admin/ajax-modules.php

/**
 * Handle AJAX install module request.
 *
 * @return void
 */
// Moved to includes/admin/ajax-modules.php

/**
 * Handle AJAX update module request.
 *
 * @return void
 */
// Moved to includes/admin/ajax-modules.php

/**
 * Handle network license broadcast via AJAX.
 *
 * @return void
 */
// Moved to includes/admin/ajax-modules.php

/**
 * AJAX handler to save metabox state (order and collapsed state).
 *
 * @return void
 */
function WPSHADOW_ajax_save_metabox_state(): void {
	check_ajax_referer( 'wpshadow_metabox_state', 'nonce' );

	if ( ! current_user_can( 'manage_options' ) && ! current_user_can( 'manage_network_options' ) ) {
		\WPShadow\CoreSupport\WPSHADOW_ajax_permission_denied();
	}

	$state = \WPShadow\CoreSupport\WPSHADOW_get_post_text( 'state' );

	if ( empty( $state ) ) {
		\WPShadow\CoreSupport\WPSHADOW_ajax_invalid_request( 'state' );
	}

	update_user_meta( get_current_user_id(), 'wpshadow_metabox_state', $state );

	\WPShadow\CoreSupport\WPSHADOW_ajax_success();
}

/**
 * AJAX handler to save postbox order.
 *
 * @return void
 */
function WPSHADOW_ajax_save_postbox_order(): void {
	check_ajax_referer( 'wpshadow_postbox_state', 'nonce' );

	if ( ! current_user_can( 'manage_options' ) && ! current_user_can( 'manage_network_options' ) ) {
		\WPShadow\CoreSupport\WPSHADOW_ajax_permission_denied();
	}

	$page  = \WPShadow\CoreSupport\WPSHADOW_get_post_key( 'page' );
	$order = isset( $_POST['order'] ) ? wp_unslash( $_POST['order'] ) : array();

	if ( empty( $page ) ) {
		\WPShadow\CoreSupport\WPSHADOW_ajax_invalid_request( 'page' );
	}

	// Ensure order is an associative array
	if ( ! is_array( $order ) ) {
		$order = array();
	}

	$user_id = get_current_user_id();

	// Get existing state
	$all_states = get_user_meta( $user_id, 'wpshadow_postbox_states', true );
	if ( ! is_array( $all_states ) ) {
		$all_states = array();
	}

	// Update order for this page
	$all_states[ $page ]['order'] = $order;

	// Save back to JSON store
	update_user_meta( $user_id, 'wpshadow_postbox_states', $all_states );



	\WPShadow\CoreSupport\WPSHADOW_ajax_success(
		array(
			'message' => 'Order saved',
			'page'    => $page,
			'order'   => $order,
		)
	);
}

/**
 * AJAX handler to save postbox closed state.
 *
 * @return void
 */
function WPSHADOW_ajax_save_postbox_state(): void {
	check_ajax_referer( 'wpshadow_postbox_state', 'nonce' );

	if ( ! current_user_can( 'manage_options' ) && ! current_user_can( 'manage_network_options' ) ) {
		wp_send_json_error( array( 'message' => 'Insufficient permissions' ) );
	}

	$page   = isset( $_POST['page'] ) ? sanitize_key( $_POST['page'] ) : '';
	$closed = isset( $_POST['closed'] ) ? wp_unslash( $_POST['closed'] ) : array();

	if ( empty( $page ) ) {
		wp_send_json_error( array( 'message' => 'Invalid page parameter' ) );
	}

	// Ensure closed is an array and sanitize
	if ( ! is_array( $closed ) ) {
		$closed = array();
	} else {
		$closed = array_map( 'sanitize_key', $closed );
	}

	$user_id = get_current_user_id();

	// Get existing state
	$all_states = get_user_meta( $user_id, 'wpshadow_postbox_states', true );
	if ( ! is_array( $all_states ) ) {
		$all_states = array();
	}

	// Update closed for this page
	$all_states[ $page ]['closed'] = $closed;

	// Save back to JSON store
	update_user_meta( $user_id, 'wpshadow_postbox_states', $all_states );



	wp_send_json_success(
		array(
			'message' => 'State saved',
			'page'    => $page,
			'closed'  => $closed,
		)
	);
}

/**
 * Attempt to find a plugin file by slug.
 *
 * @param string $slug Module slug.
 * @return string|null Plugin file path or null.
 */
function wpshadow_find_plugin_file_by_slug( string $slug ): ?string {
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
 * Handle "Run Now" requests for scheduled tasks.
 *
 * @return void
 */
function WPSHADOW_run_task_now(): void {
	if ( empty( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'wpshadow_run_task_now' ) ) {
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
	$allowed_hooks = array( 'wpshadow_refresh_modules', 'wpshadow_vault_queue_runner' );
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
	$user_name = $user && $user->exists() ? $user->display_name : __( 'System', 'wpshadow' );

	$redirect_url = wp_get_referer();
	if ( empty( $redirect_url ) ) {
		$redirect_url = admin_url();
	}
	$redirect = add_query_arg( 'wpshadow_run_now', '1', $redirect_url );
	wp_safe_redirect( $redirect );
	exit;
}

/**
 * Add action links to the plugins list for WPShadow.
 *
 * @param array $links Plugin action links.
 * @return array Modified action links.
 */
function wpshadow_plugin_action_links( array $links ): array {
	$dashboard_link = sprintf(
		'<a href="%s">%s</a>',
		esc_url( admin_url( 'admin.php?page=wpshadow' ) ),
		esc_html__( 'Dashboard', 'wpshadow' )
	);

	array_unshift( $links, $dashboard_link );

	return $links;
}

/**
 * Add row meta to the plugins list for WPShadow.
 *
 * @param array  $meta Plugin row meta.
 * @param string $file Plugin file.
 * @return array Modified row meta.
 */
function wpshadow_plugin_row_meta( array $meta, string $file ): array {
	if ( WPSHADOW_BASENAME !== $file ) {
		return $meta;
	}

	// Meta links removed per requirements
	return $meta;
}

/**
 * Save screen options for dashboard pages.
 *
 * @param mixed  $status Screen option value. Default false to skip.
 * @param string $option The option name.
 * @param mixed  $value  The option value.
 * @return mixed
 */
function wpshadow_save_screen_option( $status, string $option, $value ) {
	if ( 'layout_columns' === $option ) {
		return $value;
	}
	return $status;
}

/**
 * Clear plugins list cache on plugin activation/deactivation.
 *
 * Phase 3 Optimization: Invalidate cached plugins list when
 * plugins are activated or deactivated.
 *
 * @return void
 */
function wpshadow_clear_plugins_cache(): void {
	if ( function_exists( 'WPShadow\\Helpers\\wpshadow_clear_plugins_cache' ) ) {
		\WPShadow\Helpers\wpshadow_clear_plugins_cache();
	}
}

/**
 * Filter postbox classes to apply saved closed state.
 *
 * @param array  $classes Postbox classes.
 * @param string $box_id  Postbox ID.
 * @return array
 */
function wpshadow_postbox_classes( array $classes, string $box_id ): array {
	$closed = wpshadow_get_closed_postboxes( false );
	if ( is_array( $closed ) && in_array( $box_id, $closed, true ) ) {
		$classes[] = 'closed';
	}
	return $classes;
}

/**
 * Get metabox order from custom state key.
 *
 * @param mixed $result Default result.
 * @return mixed
 */
function wpshadow_get_metabox_order( $result ) {
	$context   = \WPShadow\CoreSupport\WPSHADOW_Tab_Navigation::get_current_context();
	$hub_id    = $context['hub'] ?? '';
	$state_key = 'wpshadow' . ( $hub_id ? '-' . $hub_id : '' );

	// Get all states from JSON store
	$user_id    = get_current_user_id();
	$all_states = get_user_meta( $user_id, 'wpshadow_postbox_states', true );

	if ( ! is_array( $all_states ) || ! isset( $all_states[ $state_key ]['order'] ) ) {

		return false;
	}

	$order = $all_states[ $state_key ]['order'];

	// Validate shape: must be array of container => array of IDs
	$valid = is_array( $order );
	if ( $valid ) {
		foreach ( $order as $container => $ids ) {
			if ( ! is_array( $ids ) ) {
				$valid = false;
				break;
			}
			foreach ( $ids as $id ) {
				if ( ! is_string( $id ) ) {
					$valid = false;
					break 2;
				}
			}
		}
	}

	if ( ! $valid ) {

		unset( $all_states[ $state_key ] );
		update_user_meta( $user_id, 'wpshadow_postbox_states', $all_states );
		return false;
	}

	// Convert to WordPress expected keys: normal/side/column3/column4
	$normalized = array(
		'normal'  => '',
		'side'    => '',
		'column3' => '',
		'column4' => '',
	);

	$map = array(
		'postbox-container-1' => 'normal',
		'postbox-container-2' => 'side',
		'postbox-container-3' => 'column3',
		'postbox-container-4' => 'column4',
	);

	foreach ( $order as $container => $ids ) {
		$target = $map[ $container ] ?? null;
		if ( $target ) {
			$normalized[ $target ] = implode( ',', $ids );
		}
	}



	return $normalized;
}

/**
 * Get closed postboxes from custom state key.
 *
 * @param mixed $result Default result.
 * @return mixed
 */
function wpshadow_get_closed_postboxes( $result ) {
	$context   = \WPShadow\CoreSupport\WPSHADOW_Tab_Navigation::get_current_context();
	$hub_id    = $context['hub'] ?? '';
	$state_key = 'wpshadow' . ( $hub_id ? '-' . $hub_id : '' );

	// Get all states from JSON store
	$user_id    = get_current_user_id();
	$all_states = get_user_meta( $user_id, 'wpshadow_postbox_states', true );

	if ( ! is_array( $all_states ) || ! isset( $all_states[ $state_key ]['closed'] ) ) {

		return false;
	}

	$closed = $all_states[ $state_key ]['closed'];

	// Validate shape: array of strings
	$valid = is_array( $closed );
	if ( $valid ) {
		foreach ( $closed as $id ) {
			if ( ! is_string( $id ) ) {
				$valid = false;
				break;
			}
		}
	}

	if ( ! $valid ) {

		unset( $all_states[ $state_key ] );
		update_user_meta( $user_id, 'wpshadow_postbox_states', $all_states );
		return false;
	}

	// Convert to string for WordPress explode()
	$closed_str = implode( ',', $closed );



	return $closed_str;
}

/**
 * Enqueue admin scripts and styles.
 *
 * @param string $hook The current admin page hook.
 * @return void
 */
// Moved to includes/admin/assets.php


// Register activation and deactivation hooks.
register_activation_hook( __FILE__, __NAMESPACE__ . '\\wpshadow_activate' );
register_deactivation_hook( __FILE__, __NAMESPACE__ . '\\wpshadow_deactivate' );

// Suppress WordPress 6.7+ translation timing notice (feature constructors run before init)
add_filter( 'doing_it_wrong_trigger_error', function( $trigger, $function ) {
	if ( '_load_textdomain_just_in_time' === $function ) {
		return false;
	}
	return $trigger;
}, 10, 2 );

// Suppress PHP 8.1+ deprecation warnings for null parameter values in WordPress core
if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
	set_error_handler( function( $errno, $errstr, $errfile, $errline ) {
		// Suppress deprecation warnings about null parameters in WordPress core
		if ( $errno === E_DEPRECATED && strpos( $errfile, 'wp-includes' ) !== false ) {
			if ( strpos( $errstr, 'Passing null to parameter' ) !== false ) {
				return true; // Suppress the warning
			}
		}
		return false; // Let other errors through
	}, E_DEPRECATED );
}

// Load translations early to prevent WordPress 6.7+ notices
add_action( 'init', function() {
	load_plugin_textdomain(
		WPSHADOW_TEXT_DOMAIN,
		false,
		dirname( WPSHADOW_BASENAME ) . '/languages'
	);
}, 1 );

// Initialize the plugin.
add_action( 'plugins_loaded', __NAMESPACE__ . '\\wpshadow_init' );


