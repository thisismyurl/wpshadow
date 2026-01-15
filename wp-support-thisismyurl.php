<?php
/**
 * Author:              Christopher Ross
 * Author URI:          https://thisismyurl.com/?source=plugin-wp-support-thisismyurl
 * Plugin Name:         WP Support (thisismyurl)
 * Plugin URI:          https://thisismyurl.com/plugin-wp-support-thisismyurl/?source=plugin-wp-support-thisismyurl
 * Donate link:         https://thisismyurl.com/plugin-wp-support-thisismyurl/#register?source=plugin-wp-support-thisismyurl
 * Description:         The foundational support plugin for WordPress with comprehensive health diagnostics, emergency recovery, backup verification, and documentation management. Optionally extends with module ecosystem (Media Hub, Image Formats, Vault Storage, and more).
 * Tags:                WordPress, plugin, foundation, hub, architecture, management, suite, diagnostics, health, backup
 * Version:             1.2601.73002
 * Requires at least:   6.4
 * Requires PHP:        8.1.29
 * Update URI:          https://github.com/thisismyurl/plugin-wp-support-thisismyurl
 * GitHub Plugin URI:   https://github.com/thisismyurl/plugin-wp-support-thisismyurl
 * Primary Branch:      main
 * Text Domain:         plugin-wp-support-thisismyurl
 * License:             GPL2
 * License URI:         https://www.gnu.org/licenses/gpl-2.0.html
 * @package WPS_WP_SUPPORT_THISISMYURL
 */

declare(strict_types=1);

namespace WPS\CoreSupport;

use WPS\CoreSupport\WPS_Feature_Core_Diagnostics;
use WPS\CoreSupport\WPS_Feature_Image_Smart_Focus;
use WPS\CoreSupport\WPS_Feature_Vault_Audit;
use WPS\CoreSupport\WPS_Feature_Vulnerability_Watch;
use WPS\CoreSupport\WPS_Feature_Script_Deferral;
use WPS\CoreSupport\WPS_Feature_Asset_Version_Removal;
use WPS\CoreSupport\WPS_Feature_Head_Cleanup;
use WPS\CoreSupport\WPS_Feature_Block_Cleanup;
use WPS\CoreSupport\WPS_Feature_CSS_Class_Cleanup;
use WPS\CoreSupport\WPS_Feature_Plugin_Cleanup;
use WPS\CoreSupport\WPS_Feature_HTML_Cleanup;
use WPS\CoreSupport\WPS_Feature_Resource_Hints;
use WPS\CoreSupport\WPS_Feature_Nav_Accessibility;
use WPS\CoreSupport\WPS_Feature_Skiplinks;
use WPS\CoreSupport\WPS_Feature_Embed_Disable;
use WPS\CoreSupport\WPS_Feature_jQuery_Cleanup;
use WPS\CoreSupport\WPS_Feature_Block_CSS_Cleanup;
use WPS\CoreSupport\WPS_Feature_Interactivity_Cleanup;
use WPS\CoreSupport\WPS_Feature_Consent_Checks;
use WPS\CoreSupport\WPS_Feature_Hardening;
use WPS\CoreSupport\WPS_Feature_Registry;
use WPS\CoreSupport\WPS_Feature_Image_Lazy_Loading;
use WPS\CoreSupport\WPS_Feature_Asset_Minification;
use WPS\CoreSupport\WPS_Feature_Database_Cleanup;
use WPS\CoreSupport\WPS_Feature_Auto_Rollback;
use WPS\CoreSupport\WPS_Feature_Visual_Regression;
use WPS\CoreSupport\WPS_Feature_Weekly_Performance_Report;
use WPS\CoreSupport\WPS_Feature_Conditional_Loading;
use WPS\CoreSupport\WPS_Feature_Google_Fonts_Disabler;
use WPS\CoreSupport\WPS_Feature_Critical_CSS;
use WPS\CoreSupport\WPS_Feature_Script_Optimizer;
use WPS\CoreSupport\WPS_Feature_Conflict_Sandbox;
use WPS\CoreSupport\WPS_Feature_Smart_Recommendations;

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Initialize dashboard assets on init hook
 *
 * @return void
 */
function wp_support_init_dashboard_assets(): void {
	if ( class_exists( '\\WPS\\CoreSupport\\Admin\\WPS_Dashboard_Assets' ) ) {
		\WPS\CoreSupport\Admin\WPS_Dashboard_Assets::init( wp_support_PATH, wp_support_URL );
	}
}

/**
 * Render Settings view (Core or Hub) using the same metabox layout as the dashboard.
 * Widgets here represent settings groups for the current context.
 *
 * @param string $hub_id Optional hub identifier for hub-level settings.
 * @return void
 */
function wp_support_render_settings( string $hub_id = '' ): void {
	if ( ! wps_can_manage_settings() ) {
		wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'plugin-wp-support-thisismyurl' ) );
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
		'wps_settings_i18n',
		array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'saving'  => __( 'Saving...', 'plugin-wp-support-thisismyurl' ),
			'saved'   => __( 'Saved', 'plugin-wp-support-thisismyurl' ),
			'error'   => __( 'Save failed', 'plugin-wp-support-thisismyurl' ),
		)
	);

	// Title mirrors dashboard style.
	$settings_title = __( 'Support Settings', 'plugin-wp-support-thisismyurl' );
	if ( ! empty( $hub_id ) ) {
		$settings_title = ucfirst( $hub_id ) . ' ' . __( 'Settings', 'plugin-wp-support-thisismyurl' );
	}

	// Register metaboxes for core-level settings.
	if ( empty( $hub_id ) ) {
		add_meta_box(
			'wps_settings_module_registry',
			__( 'Module Discovery', 'plugin-wp-support-thisismyurl' ),
			__NAMESPACE__ . '\\render_settings_module_registry',
			$screen->id,
			'normal'
		);

		add_meta_box(
			'wps_settings_capabilities',
			__( 'Capability Mapping', 'plugin-wp-support-thisismyurl' ),
			__NAMESPACE__ . '\\render_settings_capabilities',
			$screen->id,
			'normal'
		);

		add_meta_box(
			'wps_settings_privacy',
			__( 'Privacy & GDPR', 'plugin-wp-support-thisismyurl' ),
			__NAMESPACE__ . '\\render_settings_privacy',
			$screen->id,
			'normal'
		);

		add_meta_box(
			'wps_settings_database_cleanup',
			__( 'Database Cleanup', 'plugin-wp-support-thisismyurl' ),
			__NAMESPACE__ . '\\render_settings_database_cleanup',
			$screen->id,
			'normal'
		);
	}

	add_meta_box(
		'wps_settings_dashboard',
		__( 'Dashboard & UI', 'plugin-wp-support-thisismyurl' ),
		__NAMESPACE__ . '\\render_settings_dashboard',
		$screen->id,
		'side'
	);

	add_meta_box(
		'wps_settings_license',
		__( 'License & Updates', 'plugin-wp-support-thisismyurl' ),
		__NAMESPACE__ . '\\render_settings_license',
		$screen->id,
		'side'
	);

	// Initialize postboxes on this screen (drag/toggle) in footer.
	add_action(
		'admin_print_footer_scripts',
		static function () use ( $screen, $hub_id ): void {
			// Use hub-specific state key for settings.
			$state_key = 'wp-support-settings' . ( $hub_id ? '-' . $hub_id : '' );
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
 * Early guard to hide disabled module submenus based on localStorage state.
 * Runs in admin_head to reduce initial hover flicker.
 *
 * @return void
 */
function wp_support_hide_disabled_submenus(): void {
	?>
	<script>
	(function(){
		var scope = (document.documentElement.classList.contains('network-admin') ? 'network' : 'site');
		var storagePrefix = 'wpsToggleState:' + scope + ':';
		function applyHide(){
			var top = document.getElementById('toplevel_page_wp-support');
			if (!top){
				var link = document.querySelector('#adminmenu a.menu-top[href*="page=wp-support"]');
				if (link) { top = link.closest('li'); }
			}
			if (!top) return;
			var submenu = top.querySelector('ul.wp-submenu-wrap') || top.querySelector('ul.wp-submenu');
			if (!submenu) return;
			var hideSlugs = [];
			for (var i = 0; i < localStorage.length; i++){
				var key = localStorage.key(i);
				if (!key || key.indexOf(storagePrefix) !== 0) continue;
				if (localStorage.getItem(key) === '0'){
					hideSlugs.push(key.substring(storagePrefix.length));
				}
			}
			if (!hideSlugs.length) return;
			hideSlugs.forEach(function(slug){
				var target = 'page=wp-support&module=' + encodeURIComponent(slug.replace(/-support-thisismyurl$/, ''));
				submenu.querySelectorAll('a[href*="' + target + '"]').forEach(function(anchor){
					var li = anchor.closest('li');
					if (li){ li.style.display = 'none'; }
					anchor.style.display = 'none';
					anchor.setAttribute('aria-hidden','true');
				});
			});
			var anyVisible = Array.from(submenu.querySelectorAll('li')).some(function(li){ return li.style.display !== 'none'; });
			submenu.style.display = anyVisible ? '' : 'none';
		}
		if (document.readyState === 'loading'){
			var tries = 0;
			var waiter = function(){
				tries++;
				applyHide();
				if (tries < 10) { requestAnimationFrame(waiter); }
			};
			waiter();
		} else {
			applyHide();
		}
		window.addEventListener('storage', function(ev){
			if (!ev || typeof ev.key !== 'string') return;
			if (ev.key.indexOf(storagePrefix) === 0) { applyHide(); }
		});
	})();
	</script>
	<?php
}

/**
 * Register core features with the feature registry.
 *
 * @return void
 */
function wps_register_core_features(): void {
	// Sample features.
	register_WPS_feature( new WPS_Feature_Core_Diagnostics() );
	register_WPS_feature( new WPS_Feature_Vault_Audit() );
	register_WPS_feature( new WPS_Feature_Vulnerability_Watch() );
	register_WPS_feature( new WPS_Feature_Image_Smart_Focus() );

	// Performance optimization features.
	register_WPS_feature( new WPS_Feature_Script_Deferral() );
	register_WPS_feature( new WPS_Feature_Asset_Version_Removal() );
	register_WPS_feature( new WPS_Feature_Head_Cleanup() );
	register_WPS_feature( new WPS_Feature_Block_Cleanup() );
	register_WPS_feature( new WPS_Feature_CSS_Class_Cleanup() );
	register_WPS_feature( new WPS_Feature_Plugin_Cleanup() );
	register_WPS_feature( new WPS_Feature_HTML_Cleanup() );
	register_WPS_feature( new WPS_Feature_Resource_Hints() );
	register_WPS_feature( new WPS_Feature_Nav_Accessibility() );
	register_WPS_feature( new WPS_Feature_Skiplinks() );
	register_WPS_feature( new WPS_Feature_Embed_Disable() );
	register_WPS_feature( new WPS_Feature_jQuery_Cleanup() );
	register_WPS_feature( new WPS_Feature_Block_CSS_Cleanup() );
	register_WPS_feature( new WPS_Feature_Interactivity_Cleanup() );
	
	// New performance optimization features.
	register_WPS_feature( new WPS_Feature_Image_Lazy_Loading() );
	register_WPS_feature( new WPS_Feature_Asset_Minification() );
	register_WPS_feature( new WPS_Feature_Database_Cleanup() );
	register_WPS_feature( new WPS_Feature_Conditional_Loading() );
	register_WPS_feature( new WPS_Feature_Google_Fonts_Disabler() );
	register_WPS_feature( new WPS_Feature_Critical_CSS() );
	register_WPS_feature( new WPS_Feature_Script_Optimizer() );

	// Reporting and analytics features.
	register_WPS_feature( new WPS_Feature_Weekly_Performance_Report() );
	register_WPS_feature( new WPS_Feature_Performance_Alerts() );
	register_WPS_feature( new WPS_Feature_Smart_Recommendations() );

	// Privacy and compliance features.
	register_WPS_feature( new WPS_Feature_Consent_Checks() );
	// Security features.
	register_WPS_feature( new WPS_Feature_Hardening() );
	// Safety features.
	register_WPS_feature( new WPS_Feature_Auto_Rollback() );
	// Debugging features.
	register_WPS_feature( new WPS_Feature_Conflict_Sandbox() );
	register_WPS_feature( new WPS_Feature_Visual_Regression() );
}

/**
 * Admin guard: if a module is disabled, redirect to the parent dashboard when accessed directly.
 *
 * @return void
 */
function wp_support_guard_disabled_modules(): void {
	if ( ! is_admin() || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
		return;
	}
	$raw_module = isset( $_GET['module'] ) ? sanitize_key( wp_unslash( $_GET['module'] ) ) : '';
	if ( empty( $raw_module ) ) {
		return;
	}
	// Normalize to full slug regardless of whether the suffix is included in the query param.
	$slug = str_contains( $raw_module, '-support-thisismyurl' ) ? $raw_module : $raw_module . '-support-thisismyurl';

	// Use live is_enabled() check instead of cached catalog to ensure accurate state.
	if ( WPS_Module_Registry::is_enabled( $slug ) ) {
		return;
	}

	$target = is_network_admin() ? network_admin_url( 'admin.php?page=wp-support' ) : admin_url( 'admin.php?page=wp-support' );
	wp_safe_redirect( $target );
	exit;
}

// Plugin constants.
define( 'wp_support_VERSION', '1.2601.73001' );
define( 'wp_support_FILE', __FILE__ );
define( 'wp_support_PATH', str_replace( '/', DIRECTORY_SEPARATOR, trailingslashit( plugin_dir_path( __FILE__ ) ) ) );
define( 'wp_support_URL', plugin_dir_url( __FILE__ ) );
define( 'wp_support_BASENAME', plugin_basename( __FILE__ ) );
define( 'wp_support_TEXT_DOMAIN', 'plugin-wp-support-thisismyurl' );

/**
 * Filter parent_file to ensure correct parent menu is set.
 *
 * @param string $parent_file The parent file.
 * @return string The potentially modified parent file.
 */
function wp_support_filter_parent_file( string $parent_file ): string {
	global $submenu_file;

	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( empty( $_GET['page'] ) || 'wp-support' !== $_GET['page'] ) {
		return $parent_file;
	}

	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$module = isset( $_GET['module'] ) ? sanitize_key( wp_unslash( $_GET['module'] ) ) : '';

	if ( ! empty( $module ) ) {
		$submenu_file = 'wp-support&module=' . $module;

	}

	return 'wp-support';
}

/**
 * Filter submenu_file to highlight the correct submenu item when viewing module dashboards.
 * WordPress only checks the "page" parameter by default, but we use "module" to route to different dashboards.
 * This filter ensures the correct submenu (Vault, Media, etc.) is highlighted when active.
 *
 * @param string|null $submenu_file The submenu file.
 * @return string|null The potentially modified submenu file.
 */
function wp_support_filter_submenu_file( ?string $submenu_file ): ?string {
	// Only apply when we're on the wp-support admin page.
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( empty( $_GET['page'] ) || 'wp-support' !== $_GET['page'] ) {
		return $submenu_file;
	}

	// Check if there's a module parameter that specifies which dashboard we're viewing.
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$module = isset( $_GET['module'] ) ? sanitize_key( wp_unslash( $_GET['module'] ) ) : '';
	if ( empty( $module ) ) {
		// No module param means we're on the main dashboard, don't override.
		return $submenu_file;
	}

	// Return the submenu file that matches the module parameter.
	// The submenu items are registered with slugs like 'wp-support&module=media', 'wp-support&module=vault', etc.
	$target = 'wp-support&module=' . $module;

	return $target;
}


// Suite Identifier for Hub & Spoke handshake.
define( 'WPS_SUITE_ID', 'thisismyurl-media-suite-2026' );

// Minimum requirements.
define( 'wp_support_MIN_PHP', '8.1.29' );
define( 'wp_support_MIN_WP', '6.4.0' );

/**
 * Plugin activation hook.
 *
 * @return void
 */
function wp_support_activate(): void {
	// Check PHP version.
	if ( version_compare( PHP_VERSION, wp_support_MIN_PHP, '<' ) ) {
		deactivate_plugins( wp_support_BASENAME );
		wp_die(
			sprintf(
				/* translators: 1: Required PHP version, 2: Current PHP version */
				esc_html__( 'WordPress Support requires PHP %1$s or higher. You are running PHP %2$s.', 'plugin-wp-support-thisismyurl' ),
				esc_html( wp_support_MIN_PHP ),
				esc_html( PHP_VERSION )
			),
			esc_html__( 'Plugin Activation Error', 'plugin-wp-support-thisismyurl' ),
			array( 'back_link' => true )
		);
	}

	// Check WordPress version.
	global $wp_version;
	if ( version_compare( $wp_version, wp_support_MIN_WP, '<' ) ) {
		deactivate_plugins( wp_support_BASENAME );
		wp_die(
			sprintf(
				/* translators: 1: Required WordPress version, 2: Current WordPress version */
				esc_html__( 'WordPress Support requires WordPress %1$s or higher. You are running WordPress %2$s.', 'plugin-wp-support-thisismyurl' ),
				esc_html( wp_support_MIN_WP ),
				esc_html( $wp_version )
			),
			esc_html__( 'Plugin Activation Error', 'plugin-wp-support-thisismyurl' ),
			array( 'back_link' => true )
		);
	}

	// Vault setup moved to Vault module; handled when module initializes.

	// Flush rewrite rules.
	flush_rewrite_rules();
}

/**
 * Plugin deactivation hook.
 *
 * @return void
 */
function wp_support_deactivate(): void {
	// Flush rewrite rules.
	flush_rewrite_rules();
}

/**
 * Setup the vault directory for secure original storage.
 *
 * @return bool True on success, false on failure.
 */
// Vault helpers moved to the Vault module. Keep a thin compatibility wrapper
// for dashboard widgets that display key presence.
function wp_support_get_vault_key(): ?string {
	if ( class_exists( '\\WPS\\VaultSupport\\WPS_Vault' ) ) {
		// Prefer module-provided key.
		if ( method_exists( '\\WPS\\VaultSupport\\WPS_Vault', 'get_current_key' ) ) {
			$key = \WPS\VaultSupport\WPS_Vault::get_current_key();
			return ! empty( $key ) ? (string) $key : null;
		}
	}
	// Fallback to legacy storage if module not loaded yet.
	if ( defined( 'WPS_VAULT_KEY' ) && WPS_VAULT_KEY ) {
		return (string) WPS_VAULT_KEY;
	}
	$stored_key = get_option( 'WPS_vault_enc_key', '' );
	return ! empty( $stored_key ) ? (string) $stored_key : null;
}

/**
 * Initialize the plugin.
 *
 * @return void
 */
function wp_support_init(): void {
	// Load text domain for translations.
	load_plugin_textdomain(
		wp_support_TEXT_DOMAIN,
		false,
		dirname( wp_support_BASENAME ) . '/languages'
	);

	// Load DRY helper functions and traits.
	require_once wp_support_PATH . 'includes/helpers/wps-input-helpers.php';
	require_once wp_support_PATH . 'includes/helpers/wps-ajax-helpers.php';
	require_once wp_support_PATH . 'includes/helpers/wps-array-helpers.php';
	require_once wp_support_PATH . 'includes/traits/trait-wps-ajax-security.php';

	// Load update server client for automatic updates.
	require_once wp_support_PATH . 'includes/class-wps-update-client.php';
	\WPS\CoreSupport\WPS_Update_Client::init( wp_support_BASENAME );

	// Load license widget for dashboard.
	require_once wp_support_PATH . 'includes/class-wps-license-widget.php';
	\WPS\CoreSupport\WPS_License_Widget::init();

	// Load help content API for dynamic documentation.
	require_once wp_support_PATH . 'includes/class-wps-help-content-api.php';
	\WPS\CoreSupport\WPS_Help_Content_API::init();
	// Load REST API.
	require_once wp_support_PATH . 'includes/api/class-wps-rest-api.php';
	\WPS\CoreSupport\API\WPS_REST_API::init();

	// Load module bootstrap for child plugin installation and activation.
	require_once wp_support_PATH . 'includes/class-wps-module-bootstrap.php';
	WPS_Module_Bootstrap::init();

	// Load module toggles for feature flags.
	require_once wp_support_PATH . 'includes/class-wps-module-toggles.php';
	WPS_Module_Toggles::init();

	// Load module registry.
	require_once wp_support_PATH . 'includes/class-wps-module-registry.php';
	WPS_Module_Registry::init();

	// Load Ghost Features system for module feature discovery.
	require_once wp_support_PATH . 'includes/class-wps-ghost-features.php';
	require_once wp_support_PATH . 'includes/class-wps-feature-detector.php';
	require_once wp_support_PATH . 'includes/class-wps-features-discovery-widget.php';
	require_once wp_support_PATH . 'includes/ghost-features-catalog.php';
	WPS_Ghost_Features::init();
	WPS_Features_Discovery_Widget::init();

	// Load DRY Hub initializer before loading modules.
	require_once wp_support_PATH . 'includes/class-wps-module-hub-initializer.php';

	// Load module loader (manages independent module repositories).
	require_once wp_support_PATH . 'includes/class-wps-module-loader.php';
	Module_Loader::init();

	// Register ghost features from catalog.
	add_action(
		'plugins_loaded',
		static function (): void {
			$catalog = \WPS\CoreSupport\get_ghost_features_catalog();
			foreach ( $catalog as $module_slug => $features ) {
				$is_installed = WPS_Module_Registry::is_installed( $module_slug );
				$modules      = WPS_Module_Registry::get_catalog_modules();
				$module_data  = array();
				foreach ( $modules as $module ) {
					if ( $module['slug'] === $module_slug ) {
						$module_data = $module;
						break;
					}
				}
				foreach ( $features as $feature ) {
					WPS_Ghost_Features::register_feature(
						array_merge(
							$feature,
							array(
								'module_slug'   => $module_slug,
								'module_name'   => $module_data['name'] ?? '',
								'module_type'   => $module_data['type'] ?? 'spoke',
								'is_available'  => $is_installed,
								'download_url'  => $module_data['download_url'] ?? '',
								'requires_core' => $module_data['requires_core'] ?? '',
								'requires_php'  => $module_data['requires_php'] ?? '',
								'requires_wp'   => $module_data['requires_wp'] ?? '',
								'requires_hub'  => $module_data['requires_hub'] ?? '',
							)
						)
					);
				}
			}
		},
		20
	);

	// Load settings API (network + site with overrides).
	require_once wp_support_PATH . 'includes/class-wps-settings.php';
	WPS_Settings::init();
	require_once wp_support_PATH . 'includes/wps-settings-functions.php';

	// Load capability manager.
	require_once wp_support_PATH . 'includes/class-wps-capabilities.php';

	// Load Environment Checker for server capability validation.
	require_once wp_support_PATH . 'includes/class-wps-environment-checker.php';
	WPS_Environment_Checker::init();

	// Load Server Limits Manager for resource monitoring and graceful degradation.
	require_once wp_support_PATH . 'includes/class-wps-server-limits.php';
	WPS_Server_Limits::init();

	// Load Site Health integration.
	require_once wp_support_PATH . 'includes/class-wps-site-health.php';
	WPS_Site_Health::init();

	// Load Activity Logger.
	require_once wp_support_PATH . 'includes/class-wps-activity-logger.php';
	WPS_Activity_Logger::init();

	// Load Performance Monitor for real-time performance tracking.
	require_once wp_support_PATH . 'includes/class-wps-performance-monitor.php';
	WPS_Performance_Monitor::init();

	// Load Achievement Badges system.
	require_once wp_support_PATH . 'includes/class-wps-achievement-badges.php';
	WPS_Achievement_Badges::init();

	// Load Spoke Collection system for gamified spoke management.
	require_once wp_support_PATH . 'includes/class-wps-spoke-collection.php';
	WPS_Spoke_Collection::init();

	// Load Snapshot Manager for site snapshots and rollback.
	require_once wp_support_PATH . 'includes/class-wps-snapshot-manager.php';
	WPS_Snapshot_Manager::init();

	// Load Site Audit for performance, security, and optimization analysis.
	require_once wp_support_PATH . 'includes/class-wps-site-audit.php';
	WPS_Site_Audit::init();

	// Load Hidden Diagnostic API for secure support access.
	require_once wp_support_PATH . 'includes/class-wps-hidden-diagnostic-api.php';
	WPS_Hidden_Diagnostic_API::init();

	// Load Safe Staging Manager for isolated testing environments.
	require_once wp_support_PATH . 'includes/class-wps-staging-manager.php';
	WPS_Staging_Manager::init();

	// Load Backup Verification for recovery drills and integrity testing.
	require_once wp_support_PATH . 'includes/class-wps-backup-verification.php';
	WPS_Backup_Verification::init();

	// Load Emergency Support for critical error surfaces.
	require_once wp_support_PATH . 'includes/class-wps-emergency-support.php';
	WPS_Emergency_Support::init();

	// Load White Screen Auto-Recovery for fatal error handling.
	require_once wp_support_PATH . 'includes/class-wps-white-screen-recovery.php';
	WPS_White_Screen_Recovery::init();

	// Register emergency support admin menu.
	add_action(
		'admin_menu',
		static function (): void {
			add_submenu_page(
				'wp-support',
				__( 'Emergency Support', 'plugin-wp-support-thisismyurl' ),
				__( 'Emergency', 'plugin-wp-support-thisismyurl' ),
				'manage_options',
				'wps-emergency-support',
				array( '\\WPS\\CoreSupport\\WPS_Emergency_Support', 'render_emergency_page' )
			);
		}
	);

	// Handle recovery actions from emergency dashboard.
	add_action( 'admin_init', array( '\\WPS\\CoreSupport\\WPS_White_Screen_Recovery', 'handle_recovery_actions' ) );

	// Load Site Documentation Manager for blueprint, protected plugins, and export.
	require_once wp_support_PATH . 'includes/class-wps-site-documentation-manager.php';
	WPS_Site_Documentation_Manager::init();

	// Load Update Simulator for safe plugin/theme update testing.
	require_once wp_support_PATH . 'includes/class-wps-update-simulator.php';
	WPS_Update_Simulator::init();

	// Load Guided Walkthroughs for step-by-step task assistance.
	require_once wp_support_PATH . 'includes/class-wps-guided-walkthroughs.php';
	WPS_Guided_Walkthroughs::init();

	// Load Video Walkthroughs for auto-generated video tutorials.
	require_once wp_support_PATH . 'includes/class-wps-video-walkthroughs.php';
	WPS_Video_Walkthroughs::init();

	// Load Magic Link Support for secure time-limited developer access.
	require_once wp_support_PATH . 'includes/class-wps-magic-link-support.php';
	WPS_Magic_Link_Support::init();

	// Load Debug Mode Manager for one-click debug toggles.
	require_once wp_support_PATH . 'includes/class-wps-debug-mode.php';
	WPS_Debug_Mode::init();
	// Load System Report Generator for comprehensive debug information.
	require_once wp_support_PATH . 'includes/class-wps-system-report-generator.php';
	WPS_System_Report_Generator::init();

	// Register AJAX handlers for Diagnostic API.
	add_action(
		'wp_ajax_wps_create_diagnostic_token',
		static function (): void {
			check_ajax_referer( 'wp_ajax' );
			if ( ! current_user_can( 'manage_options' ) ) {
				\WPS\CoreSupport\wps_ajax_permission_denied();
			}
			$name   = \WPS\CoreSupport\wps_get_post_text( 'name' );
			$reason = \WPS\CoreSupport\wps_get_post_text( 'reason' );
			$token  = WPS_Hidden_Diagnostic_API::create_token( $name, $reason );
			\WPS\CoreSupport\wps_ajax_success( array( 'token' => $token ) );
		}
	);

	add_action(
		'wp_ajax_wps_revoke_diagnostic_token',
		static function (): void {
			check_ajax_referer( 'wp_ajax' );
			if ( ! current_user_can( 'manage_options' ) ) {
				\WPS\CoreSupport\wps_ajax_permission_denied();
			}
			$token  = \WPS\CoreSupport\wps_get_post_text( 'token' );
			$result = WPS_Hidden_Diagnostic_API::revoke_token( $token );
			\WPS\CoreSupport\wps_ajax_success( array( 'revoked' => $result ) );
		}
	);

	// Load license utilities.
	require_once wp_support_PATH . 'includes/class-wps-license.php';
	WPS_License::init();

	// Load registration handler.
	require_once wp_support_PATH . 'includes/class-wps-registration.php';
	WPS_Registration::init();

	// Load dashboard assets on 'init' hook instead of here.
	require_once str_replace( '/', DIRECTORY_SEPARATOR, wp_support_PATH . 'includes/admin/class-wps-dashboard-assets.php' );
	add_action(
		'init',
		__NAMESPACE__ . '\\wp_support_init_dashboard_assets',
		11
	);

	// Load feature registry for flexible plugin dependencies.
	require_once str_replace( '/', DIRECTORY_SEPARATOR, wp_support_PATH . 'includes/class-wps-settings-cache.php' );
	require_once str_replace( '/', DIRECTORY_SEPARATOR, wp_support_PATH . 'includes/features/interface-wps-feature.php' );
	require_once str_replace( '/', DIRECTORY_SEPARATOR, wp_support_PATH . 'includes/features/class-wps-feature-abstract.php' );
	require_once str_replace( '/', DIRECTORY_SEPARATOR, wp_support_PATH . 'includes/features/class-wps-script-utils.php' );
	
	// Initialize settings cache early.
	WPS_Settings_Cache::init();
	require_once str_replace( '/', DIRECTORY_SEPARATOR, wp_support_PATH . 'includes/features/class-wps-feature-core-diagnostics.php' );
	require_once str_replace( '/', DIRECTORY_SEPARATOR, wp_support_PATH . 'includes/features/class-wps-feature-vault-audit.php' );
	require_once str_replace( '/', DIRECTORY_SEPARATOR, wp_support_PATH . 'includes/features/class-wps-feature-vulnerability-watch.php' );
	require_once str_replace( '/', DIRECTORY_SEPARATOR, wp_support_PATH . 'includes/features/class-wps-feature-image-smart-focus.php' );
	require_once str_replace( '/', DIRECTORY_SEPARATOR, wp_support_PATH . 'includes/features/class-wps-feature-script-deferral.php' );
	require_once str_replace( '/', DIRECTORY_SEPARATOR, wp_support_PATH . 'includes/features/class-wps-feature-asset-version-removal.php' );
	require_once str_replace( '/', DIRECTORY_SEPARATOR, wp_support_PATH . 'includes/features/class-wps-feature-head-cleanup.php' );
	require_once str_replace( '/', DIRECTORY_SEPARATOR, wp_support_PATH . 'includes/features/class-wps-feature-block-cleanup.php' );
	require_once str_replace( '/', DIRECTORY_SEPARATOR, wp_support_PATH . 'includes/features/class-wps-feature-css-class-cleanup.php' );
	require_once str_replace( '/', DIRECTORY_SEPARATOR, wp_support_PATH . 'includes/features/class-wps-feature-plugin-cleanup.php' );
	require_once str_replace( '/', DIRECTORY_SEPARATOR, wp_support_PATH . 'includes/features/class-wps-feature-html-cleanup.php' );
	require_once str_replace( '/', DIRECTORY_SEPARATOR, wp_support_PATH . 'includes/features/class-wps-feature-resource-hints.php' );
	require_once str_replace( '/', DIRECTORY_SEPARATOR, wp_support_PATH . 'includes/features/class-wps-feature-nav-accessibility.php' );
	require_once str_replace( '/', DIRECTORY_SEPARATOR, wp_support_PATH . 'includes/features/class-wps-feature-skiplinks.php' );
	require_once str_replace( '/', DIRECTORY_SEPARATOR, wp_support_PATH . 'includes/features/class-wps-feature-embed-disable.php' );
	require_once str_replace( '/', DIRECTORY_SEPARATOR, wp_support_PATH . 'includes/features/class-wps-feature-jquery-cleanup.php' );
	require_once str_replace( '/', DIRECTORY_SEPARATOR, wp_support_PATH . 'includes/features/class-wps-feature-block-css-cleanup.php' );
	require_once str_replace( '/', DIRECTORY_SEPARATOR, wp_support_PATH . 'includes/features/class-wps-feature-interactivity-cleanup.php' );
	require_once str_replace( '/', DIRECTORY_SEPARATOR, wp_support_PATH . 'includes/features/class-wps-feature-consent-checks.php' );
	require_once str_replace( '/', DIRECTORY_SEPARATOR, wp_support_PATH . 'includes/features/class-wps-feature-hardening.php' );
	require_once str_replace( '/', DIRECTORY_SEPARATOR, wp_support_PATH . 'includes/features/class-wps-feature-a11y-audit.php' );
	require_once str_replace( '/', DIRECTORY_SEPARATOR, wp_support_PATH . 'includes/features/class-wps-feature-tips-coach.php' );
	require_once str_replace( '/', DIRECTORY_SEPARATOR, wp_support_PATH . 'includes/features/class-wps-feature-image-lazy-loading.php' );
	require_once str_replace( '/', DIRECTORY_SEPARATOR, wp_support_PATH . 'includes/features/class-wps-feature-asset-minification.php' );
	require_once str_replace( '/', DIRECTORY_SEPARATOR, wp_support_PATH . 'includes/features/class-wps-feature-database-cleanup.php' );
	require_once str_replace( '/', DIRECTORY_SEPARATOR, wp_support_PATH . 'includes/features/class-wps-feature-auto-rollback.php' );
	require_once str_replace( '/', DIRECTORY_SEPARATOR, wp_support_PATH . 'includes/features/class-wps-feature-weekly-performance-report.php' );
	require_once str_replace( '/', DIRECTORY_SEPARATOR, wp_support_PATH . 'includes/features/class-wps-feature-performance-alerts.php' );
	require_once str_replace( '/', DIRECTORY_SEPARATOR, wp_support_PATH . 'includes/features/class-wps-feature-smart-recommendations.php' );
	require_once str_replace( '/', DIRECTORY_SEPARATOR, wp_support_PATH . 'includes/features/class-wps-feature-conditional-loading.php' );
	require_once str_replace( '/', DIRECTORY_SEPARATOR, wp_support_PATH . 'includes/features/class-wps-feature-google-fonts-disabler.php' );
	require_once str_replace( '/', DIRECTORY_SEPARATOR, wp_support_PATH . 'includes/features/class-wps-feature-critical-css.php' );
	require_once str_replace( '/', DIRECTORY_SEPARATOR, wp_support_PATH . 'includes/features/class-wps-feature-script-optimizer.php' );
	require_once str_replace( '/', DIRECTORY_SEPARATOR, wp_support_PATH . 'includes/features/class-wps-feature-conflict-sandbox.php' );
	require_once str_replace( '/', DIRECTORY_SEPARATOR, wp_support_PATH . 'includes/features/class-wps-feature-visual-regression.php' );
	require_once str_replace( '/', DIRECTORY_SEPARATOR, wp_support_PATH . 'includes/features/class-wps-feature-registry.php' );
	require_once str_replace( '/', DIRECTORY_SEPARATOR, wp_support_PATH . 'includes/wps-feature-functions.php' );
	WPS_Feature_Registry::init();
	add_action( 'WPS_register_features', __NAMESPACE__ . '\\wps_register_core_features' );
	
	// Initialize Tips Coach feature
	\WPS\CoreSupport\WPS_Feature_Tips_Coach::init();

	// Initialize Weekly Performance Report feature
	\WPS\CoreSupport\WPS_Feature_Weekly_Performance_Report::init();

	// Load Spoke Base for spoke plugins (Image, Media, etc).
	require_once wp_support_PATH . 'includes/class-wps-spoke-base.php';

	// Load Vault service (canonical implementation in vault-support plugin).
	// Core aliases it for backward compatibility.
	if ( ! class_exists( '\\WPS\\VaultSupport\\WPS_Vault' ) ) {
		// Vault plugin not loaded yet; defer to vault-support plugin.
		// If vault-support is active, it will provide WPS_Vault.
		// Core will alias it when available.
	}

	// Always load the alias file which will create the alias if vault-support is available.
	if ( file_exists( wp_support_PATH . 'includes/class-wps-vault.php' ) ) {
		require_once wp_support_PATH . 'includes/class-wps-vault.php';
	}

	// Initialize Vault if available (via vault-support's implementation).
	if ( class_exists( '\\WPS\\CoreSupport\\WPS_Vault' ) ) {
		WPS_Vault::init();
	}

	// Load vault size monitoring (real-time alerts) - only if Vault is available.
	if ( class_exists( '\\WPS\\CoreSupport\\WPS_Vault' ) ) {
		require_once wp_support_PATH . 'includes/class-wps-vault-size-monitor.php';
		WPS_Vault_Size_Monitor::init();
	}

	// Load network license broadcaster for multisite (Super Admin push to all sites).
	require_once wp_support_PATH . 'includes/class-wps-network-license.php';
	WPS_Network_License::init();

	// Load module downloader for resilient downloads.
	require_once wp_support_PATH . 'includes/class-wps-module-downloader.php';

	// Load plugin upgrader for install/update flows.
	require_once wp_support_PATH . 'includes/class-wps-plugin-upgrader.php';

	// Load module action handlers for AJAX install/update/activate.
	require_once wp_support_PATH . 'includes/class-wps-module-actions.php';
	WPS_Module_Actions::init();

	// Centralized router guard for disabled modules.
	require_once wp_support_PATH . 'includes/class-wps-router-guard.php';

	// Load tab navigation system.
	require_once wp_support_PATH . 'includes/class-wps-tab-navigation.php';
	require_once wp_support_PATH . 'includes/class-wps-dashboard-widgets.php';
	require_once wp_support_PATH . 'includes/class-wps-dashboard-layout.php';
	require_once wp_support_PATH . 'includes/admin/class-wps-settings-ajax.php';
	\WPS\CoreSupport\Admin\WPS_Settings_Ajax::init();
	require_once wp_support_PATH . 'includes/wps-capability-helpers.php';

	// Load extracted admin assets, screens, dashboard view, and AJAX handlers.
	require_once wp_support_PATH . 'includes/admin/assets.php';
	require_once wp_support_PATH . 'includes/admin/screens.php';
	require_once wp_support_PATH . 'includes/views/dashboard-renderer.php';
	require_once wp_support_PATH . 'includes/admin/ajax-modules.php';
	require_once wp_support_PATH . 'includes/admin/ajax-spoke-collection.php';

	// Load CLI commands when WP-CLI present.
	if ( defined( 'WP_CLI' ) && WP_CLI ) {
		require_once wp_support_PATH . 'includes/class-wps-cli.php';
		\WP_CLI::add_command( 'wps modules', '\\WPS\\CoreSupport\\WPS_CLI_Modules' );
		\WP_CLI::add_command( 'wps settings', '\\WPS\\CoreSupport\\WPS_CLI_Settings' );
	}

	// Load notice manager for persistent dismissal.
	require_once wp_support_PATH . 'includes/class-wps-notice-manager.php';
	WPS_Notice_Manager::init();

	// Initialize multisite support if applicable.
	if ( is_multisite() ) {
		add_action( 'network_admin_menu', __NAMESPACE__ . '\\wp_support_network_admin_menu' );
	}

	// Register admin menu.
	add_action( 'admin_menu', __NAMESPACE__ . '\\wp_support_admin_menu' );
	add_action( 'admin_head', __NAMESPACE__ . '\\wp_support_hide_disabled_submenus' );
	add_action( 'admin_init', __NAMESPACE__ . '\wp_support_guard_disabled_modules' );

	// Fix sidebar menu active state for module navigation (#174).
	add_filter( 'parent_file', __NAMESPACE__ . '\\wp_support_filter_parent_file', 10 );
	add_filter( 'submenu_file', __NAMESPACE__ . '\\wp_support_filter_submenu_file', 10 );

	// Handle capability mapping submissions.
	add_action( 'admin_init', __NAMESPACE__ . '\\wp_support_handle_capabilities_post' );

	// Handle AJAX actions.
	add_action( 'wp_ajax_wps_toggle_module', __NAMESPACE__ . '\\wps_ajax_toggle_module' );
	add_action( 'wp_ajax_wps_install_module', __NAMESPACE__ . '\\wps_ajax_install_module' );
	add_action( 'wp_ajax_wps_update_module', __NAMESPACE__ . '\\wps_ajax_update_module' );
	add_action( 'wp_ajax_wps_broadcast_license', __NAMESPACE__ . '\\wps_ajax_broadcast_license' );
	add_action( 'wp_ajax_wps_save_metabox_state', __NAMESPACE__ . '\\wps_ajax_save_metabox_state' );
	add_action( 'wp_ajax_wps_save_postbox_order', __NAMESPACE__ . '\\wps_ajax_save_postbox_order' );
	add_action( 'wp_ajax_wps_save_postbox_state', __NAMESPACE__ . '\\wps_ajax_save_postbox_state' );
	add_action( 'wp_ajax_wps_save_dashboard_layout', array( 'WPS\\CoreSupport\\WPS_Dashboard_Layout', 'ajax_save_layout' ) );
	add_action( 'wp_ajax_wps_apply_dashboard_layout', array( 'WPS\\CoreSupport\\WPS_Dashboard_Layout', 'ajax_apply_layout' ) );

	// Admin-post action to force scheduled tasks to run immediately.
	add_action( 'admin_post_wps_run_task_now', __NAMESPACE__ . '\wps_run_task_now' );

	// Plugin page links and meta.
	add_filter( 'plugin_action_links_' . wp_support_BASENAME, __NAMESPACE__ . '\\wp_support_plugin_action_links' );
	add_filter( 'plugin_row_meta', __NAMESPACE__ . '\\wp_support_plugin_row_meta', 10, 2 );

	// Enqueue admin scripts and styles.
	add_action( 'admin_enqueue_scripts', __NAMESPACE__ . '\\wp_support_admin_enqueue' );

	// Save screen options for dashboard.
	add_filter( 'set-screen-option', __NAMESPACE__ . '\\wp_support_save_screen_option', 10, 3 );

	// Filter postbox classes to load state from custom keys.
	add_filter( 'postbox_classes_toplevel_page_wp-support', __NAMESPACE__ . '\\wp_support_postbox_classes', 10, 2 );
	add_filter( 'get_user_option_meta-box-order_toplevel_page_wp-support', __NAMESPACE__ . '\\wp_support_get_metabox_order' );
	add_filter( 'get_user_option_closedpostboxes_toplevel_page_wp-support', __NAMESPACE__ . '\\wp_support_get_closed_postboxes' );

	// Register GDPR Personal Data Exporter and Eraser.
	// Moved to Vault module: privacy exporters/erasers are registered
	// by WPS\VaultSupport when the module is enabled.
}

/**
 * Register network admin menu for multisite.
 *
 * @return void
 */
function wp_support_network_admin_menu(): void {
	add_menu_page(
		__( 'Support Dashboard', 'plugin-wp-support-thisismyurl' ),
		__( 'Support', 'plugin-wp-support-thisismyurl' ),
		'manage_network_options',
		'wp-support',
		__NAMESPACE__ . '\\wp_support_render_tab_router',
		'dashicons-admin-generic',
		999
	);

	add_submenu_page(
		'wp-support',
		__( 'Support Dashboard', 'plugin-wp-support-thisismyurl' ),
		__( 'Dashboard', 'plugin-wp-support-thisismyurl' ),
		'manage_network_options',
		'wp-support',
		__NAMESPACE__ . '\\wp_support_render_tab_router'
	);

	// Dynamically register module submenu items (Vault, Media, etc.).
	wp_support_register_module_submenus( 'manage_network_options' );

	// Initialize dashboard screen extras (Screen Options, Help) and metaboxes.
	add_action( 'load-toplevel_page_wp-support', __NAMESPACE__ . '\\wp_support_setup_dashboard_screen' );
}

/**
 * Register admin menu.
 *
 * @return void
 */
function wp_support_admin_menu(): void {
	add_menu_page(
		__( 'Support Dashboard', 'plugin-wp-support-thisismyurl' ),
		__( 'Support', 'plugin-wp-support-thisismyurl' ),
		'manage_options',
		'wp-support',
		__NAMESPACE__ . '\\wp_support_render_tab_router',
		'dashicons-admin-generic',
		999
	);

	add_submenu_page(
		'wp-support',
		__( 'Support Dashboard', 'plugin-wp-support-thisismyurl' ),
		__( 'Dashboard', 'plugin-wp-support-thisismyurl' ),
		'manage_options',
		'wp-support',
		__NAMESPACE__ . '\\wp_support_render_tab_router'
	);

	// Add Get Help quick-access submenu (#119).
	add_submenu_page(
		'wp-support',
		__( 'Get Help', 'plugin-wp-support-thisismyurl' ),
		__( 'Get Help', 'plugin-wp-support-thisismyurl' ),
		'manage_options',
		'wp-support&tab=help',
		__NAMESPACE__ . '\\wp_support_render_tab_router'
	);

	// Dynamically register module submenu items (Vault, Media, etc.).
	wp_support_register_module_submenus( 'manage_options' );

	// Initialize dashboard screen extras (Screen Options, Help) and metaboxes.
	add_action( 'load-toplevel_page_wp-support', __NAMESPACE__ . '\\wp_support_setup_dashboard_screen' );
}

/**
 * Register submenu items for active top-level modules (children of wp-support).
 *
 * @param string $capability Required capability (manage_options or manage_network_options).
 * @return void
 */
function wp_support_register_module_submenus( string $capability ): void {
	$catalog = WPS_Module_Registry::get_catalog_with_status();
	$modules = array_filter(
		$catalog,
		function ( $m ) {
			// Only include active hub modules that are direct children of wp-support
			// AND have actual module folders (not just catalog entries).
			if ( 'hub' !== ( $m['type'] ?? '' )
				|| empty( $m['installed'] )
				|| ! empty( $m['requires_hub'] )
			) {
				return false;
			}

			// Verify module folder actually exists.
			$module_id   = sanitize_key( str_replace( '-support-thisismyurl', '', $m['slug'] ?? '' ) );
			$module_path = wp_support_PATH . 'modules/hubs/' . $module_id . '/';

			if ( ! is_dir( $module_path ) ) {
				return false;
			}

			// Always register if installed; pruning will handle visibility.
			return true;
		}
	);

	foreach ( $modules as $module ) {
		$module_id   = sanitize_key( str_replace( '-support-thisismyurl', '', $module['slug'] ?? '' ) );
		$module_name = esc_html( $module['name'] ?? ucfirst( $module_id ) );

		add_submenu_page(
			'wp-support',
			$module_name,
			$module_name,
			$capability,
			'wp-support&module=' . $module_id,
			__NAMESPACE__ . '\\wp_support_render_tab_router'
		);
	}
}

/**
 * Prune Support submenus to only allow hub modules (vault, media) and core screens.
 *
 * This removes legacy/experimental submenu entries registered elsewhere.
 *
 * @return void
 */
function wp_support_prune_submenus(): void {
	// Parent menu slug.
	$parent = 'wp-support';

	// Allowed submenu slugs under wp-support (core screens always allowed).
	$allowed = array(
		'wp-support',         // Dashboard
		'wp-support-modules', // Modules grid
	);

	// Only allow hub submenus when the module is enabled.
	if ( \WPS\CoreSupport\WPS_Module_Registry::is_enabled( 'vault-support-thisismyurl' ) ) {
		$allowed[] = 'wp-support&module=vault';
	}
	if ( \WPS\CoreSupport\WPS_Module_Registry::is_enabled( 'media-support-thisismyurl' ) ) {
		$allowed[] = 'wp-support&module=media';
	}

	// Nothing to do if submenu is empty.
	if ( empty( $GLOBALS['submenu'][ $parent ] ) || ! is_array( $GLOBALS['submenu'][ $parent ] ) ) {
		return;
	}

	foreach ( $GLOBALS['submenu'][ $parent ] as $index => $item ) {
		// $item structure: [0] => title, [1] => capability, [2] => slug, ...
		$slug = $item[2] ?? '';
		if ( ! in_array( $slug, $allowed, true ) ) {
			remove_submenu_page( $parent, $slug );
		}
	}
}

// Run pruning after all menus are registered.
add_action( 'admin_menu', __NAMESPACE__ . '\\wp_support_prune_submenus', 999 );
/**
 * Add a one-time admin notice, shown on next page load.
 *
 * @param string $message Notice message.
 * @param string $type    Notice type: 'error'|'updated'|'warning'|'success'.
 * @return void
 */
function wp_support_add_admin_notice( string $message, string $type = 'warning' ): void {
	// Store transient for display in admin_notices.
	set_transient(
		'WPS_admin_notice',
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
function wp_support_render_admin_notice(): void {
	$notice = get_transient( 'WPS_admin_notice' );
	if ( empty( $notice ) || ! is_array( $notice ) ) {
		return;
	}

	delete_transient( 'WPS_admin_notice' );

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

add_action( 'admin_notices', __NAMESPACE__ . '\wp_support_render_admin_notice' );
add_action( 'network_admin_notices', __NAMESPACE__ . '\wp_support_render_admin_notice' );


/**
 * Render the capabilities management page.
 *
 * @return void
 */
function wp_support_render_capabilities_page(): void {
	$required_cap = is_network_admin() ? 'manage_network_options' : 'manage_options';

	if ( ! current_user_can( $required_cap ) ) {
		wp_die( esc_html__( 'You do not have sufficient permissions to manage capabilities.', 'plugin-wp-support-thisismyurl' ) );
	}

	require wp_support_PATH . 'includes/views/capabilities.php';
}

/**
 * Handle capability mapping submissions.
 *
 * @return void
 */
function wp_support_handle_capabilities_post(): void {
	if ( ! is_admin() ) {
		return;
	}

	$action = isset( $_POST['WPS_capability_action'] ) ? sanitize_key( wp_unslash( $_POST['WPS_capability_action'] ) ) : '';
	if ( 'add' !== $action ) {
		return;
	}

	$required_cap = is_network_admin() ? 'manage_network_options' : 'manage_options';
	if ( ! current_user_can( $required_cap ) ) {
		wp_die( esc_html__( 'You do not have sufficient permissions to manage capabilities.', 'plugin-wp-support-thisismyurl' ) );
	}

	$nonce = isset( $_POST['WPS_capabilities_nonce'] ) ? wp_unslash( $_POST['WPS_capabilities_nonce'] ) : '';
	if ( ! wp_verify_nonce( $nonce, 'WPS_capabilities' ) ) {
		wp_die( esc_html__( 'Nonce verification failed. Please try again.', 'plugin-wp-support-thisismyurl' ) );
	}

	$module_slug    = isset( $_POST['WPS_module_slug'] ) ? sanitize_key( wp_unslash( $_POST['WPS_module_slug'] ) ) : '';
	$capability_key = isset( $_POST['WPS_capability_key'] ) ? sanitize_key( wp_unslash( $_POST['WPS_capability_key'] ) ) : '';
	$wp_capability  = isset( $_POST['WPS_wp_capability'] ) ? sanitize_key( wp_unslash( $_POST['WPS_wp_capability'] ) ) : '';

	if ( empty( $module_slug ) || empty( $capability_key ) || empty( $wp_capability ) ) {
		add_settings_error(
			'WPS_capabilities',
			'WPS_capabilities_invalid',
			esc_html__( 'All fields are required to register a capability mapping.', 'plugin-wp-support-thisismyurl' ),
			'error'
		);
	} else {
		WPS_Capabilities::register_capability( $module_slug, $capability_key, $wp_capability );
		add_settings_error(
			'WPS_capabilities',
			'WPS_capabilities_saved',
			esc_html__( 'Capability mapping saved.', 'plugin-wp-support-thisismyurl' ),
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
function wp_support_render_tab_router(): void {
	if ( ! current_user_can( 'manage_options' ) && ! current_user_can( 'manage_network_options' ) ) {
		wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'plugin-wp-support-thisismyurl' ) );
	}

	// Apply centralized router guard (raw module, hub, and spoke checks).
	\WPS\CoreSupport\WPS_Router_Guard::execute();

	$context = WPS_Tab_Navigation::get_current_context();
	$hub     = $context['hub'];
	$spoke   = $context['spoke'];
	$tab     = $context['tab'];
	$level   = $context['level'];

	// Render breadcrumbs (except at Core level, unless on dashboard_settings tab).
	if ( 'core' !== $level || WPS_Tab_Navigation::TAB_DASHBOARD_SETTINGS === $tab ) {
		WPS_Tab_Navigation::render_breadcrumbs( $context );
	}

	// Determine tabs based on level.
	if ( 'spoke' === $level && ! empty( $hub ) && ! empty( $spoke ) ) {
		$tabs        = WPS_Tab_Navigation::get_spoke_tabs( $hub, $spoke );
		$active_tabs = array_merge( $tabs, wps_get_hub_tabs_for_spoke( $hub, $spoke ) );
		WPS_Tab_Navigation::render_tabs( $active_tabs, $tab );
	} elseif ( 'hub' === $level && ! empty( $hub ) ) {
		$tabs        = WPS_Tab_Navigation::get_hub_tabs( $hub );
		$active_tabs = array_merge( $tabs, wps_get_spoke_tabs_for_hub( $hub ) );
		WPS_Tab_Navigation::render_tabs( $active_tabs, $tab );
	} else {
		$tabs        = WPS_Tab_Navigation::get_core_tabs();
		$active_tabs = array_merge( $tabs, wps_get_active_hub_tabs() );
		WPS_Tab_Navigation::render_tabs( $active_tabs, $tab );
	}

	// Route to appropriate content based on level and tab.
	if ( 'spoke' === $level && ! empty( $hub ) && ! empty( $spoke ) ) {
		wp_support_render_spoke_content( $hub, $spoke, $tab );
	} elseif ( 'hub' === $level && ! empty( $hub ) ) {
		wp_support_render_hub_content( $hub, $tab );
	} else {
		wp_support_render_core_content( $tab );
	}
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
function wp_support_render_core_content( string $tab ): void {
	switch ( $tab ) {
		case 'register':
			require_once wp_support_PATH . 'includes/views/register.php';
			break;
		case 'help':
			wp_support_render_help_layout();
			break;
		case 'features':
			wp_support_render_features_page( 'core' );
			break;
		case 'collection':
			wp_support_render_spoke_collection();
			break;
		case 'modules':
			wp_support_render_modules();
			break;
		case 'dashboard_settings':
		case 'settings': // Backward compatibility redirect.
			if ( 'settings' === $tab ) {
				// Redirect old settings URL to new dashboard_settings.
				$redirect_url = add_query_arg( 'WPS_tab', 'dashboard_settings', admin_url( 'admin.php?page=wp-support' ) );
				wp_safe_redirect( $redirect_url );
				exit;
			}
			wp_support_render_settings();
			break;
		case 'dashboard':
		default:
			// Route to unified dashboard renderer.
			wp_support_render_dashboard();
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
function wp_support_render_hub_content( string $hub_id, string $tab ): void {
	switch ( $tab ) {
		case 'help':
			wp_support_render_help_layout();
			break;
	case 'features':
		wp_support_render_features_page( 'hub', $hub_id );
		break;
		case 'dashboard':
		default:
			// Route to unified dashboard renderer.
			wp_support_render_dashboard( $hub_id );
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
function wp_support_render_spoke_content( string $hub_id, string $spoke_id, string $tab ): void {
	switch ( $tab ) {
		case 'help':
			wp_support_render_help_layout();
			break;
	case 'features':
		wp_support_render_features_page( 'spoke', $hub_id, $spoke_id );
		break;
			wp_support_render_dashboard( $hub_id, $spoke_id );
			break;
	}
}

/**
 * Get active Hub tabs dynamically based on installed hubs.
 *
 * @return array<array{id: string, label: string, icon: string, url: string}>
 */
function wps_get_active_hub_tabs(): array {
	$catalog = WPS_Module_Registry::get_catalog_with_status();
	$hubs    = array_filter( $catalog, fn( $m ) => 'hub' === ( $m['type'] ?? '' ) && ! empty( $m['status']['active'] ) );
	$tabs    = array();

	foreach ( $hubs as $hub ) {
		$hub_id = sanitize_key( str_replace( '-support-thisismyurl', '', $hub['id'] ?? '' ) );
		$tabs[] = array(
			'id'    => $hub_id,
			'label' => esc_html( $hub['name'] ?? ucfirst( $hub_id ) ),
			'icon'  => 'dashicons-networking',
			'url'   => WPS_Tab_Navigation::build_hub_url( $hub_id ),
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
function wps_get_spoke_tabs_for_hub( string $hub_id ): array {
	$catalog = WPS_Module_Registry::get_catalog_with_status();
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
			'url'   => WPS_Tab_Navigation::build_spoke_url( $hub_id, $spoke_id ),
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
function wps_get_hub_tabs_for_spoke( string $hub_id, string $spoke_id ): array {
	return wps_get_spoke_tabs_for_hub( $hub_id );
}

/**
 * Render dashboard (Core, Hub, or Spoke).
 *
 * @param string $hub_id Optional hub identifier for hub-level dashboards.
 * @param string $spoke_id Optional spoke identifier for spoke-level dashboards.
 * @return void
 */
// Moved to includes/views/dashboard.php

/**
 * Render Help view with enhanced documentation and FAQ.
 *
 * @return void
 */
function wp_support_render_help_layout(): void {
	require_once wp_support_PATH . 'includes/views/help.php';
}

/**
 * Render Performance Dashboard tab.
 *
 * @return void
 */
function wp_support_render_performance_dashboard(): void {
	require_once wp_support_PATH . 'includes/views/performance-dashboard.php';
}

/**
 * Render Spoke Collection tab.
 *
 * @return void
 */
function wp_support_render_spoke_collection(): void {
	require_once wp_support_PATH . 'includes/views/spoke-collection.php';
}

/**
 * Render Features tab for a given context.
 *
 * @param string $level    Context level: core|hub|spoke.
 * @param string $hub_id   Hub identifier when applicable.
 * @param string $spoke_id Spoke identifier when applicable.
 * @return void
 */
function wp_support_render_features_page( string $level, string $hub_id = '', string $spoke_id = '' ): void {
	$required_cap = is_network_admin() ? 'manage_network_options' : 'manage_options';
	if ( ! current_user_can( $required_cap ) ) {
		wp_die( esc_html__( 'You do not have sufficient permissions to manage features.', 'plugin-wp-support-thisismyurl' ) );
	}

	// Enqueue postbox script for widget dragging
	wp_enqueue_script( 'postbox' );
	wp_enqueue_script( 'common' );
	wp_enqueue_style( 'common' );

	$network_scope = is_multisite() && is_network_admin();
	$features      = WPS_Feature_Registry::get_features_by_scope( $level, $hub_id, $spoke_id, $network_scope );

	if ( 'POST' === $_SERVER['REQUEST_METHOD'] && isset( $_POST['wps_features_nonce'] ) ) {
		check_admin_referer( 'WPS_save_features', 'wps_features_nonce' );

		$enabled_ids = array();
		if ( isset( $_POST['features'] ) && is_array( $_POST['features'] ) ) {
			foreach ( $_POST['features'] as $feature_id => $flag ) {
				$enabled_ids[] = sanitize_key( (string) $feature_id );
			}
		}

		WPS_Feature_Registry::save_feature_states( array_values( $features ), $enabled_ids, $network_scope );
		$features = WPS_Feature_Registry::get_features_by_scope( $level, $hub_id, $spoke_id, $network_scope );

		add_settings_error(
			'WPS_features',
			'WPS_features_saved',
			esc_html__( 'Feature settings updated.', 'plugin-wp-support-thisismyurl' ),
			'updated'
		);
	}

	require wp_support_PATH . 'includes/views/features.php';
}

/**
 * Render modules view.
 *
 * @return void
 */
function wp_support_render_modules(): void {
	if ( ! current_user_can( 'manage_options' ) && ! current_user_can( 'manage_network_options' ) ) {
		wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'plugin-wp-support-thisismyurl' ) );
	}

	// Display informational notice about modules being optional.
	WPS_Notice_Manager::render_notice(
		'wps_modules_are_optional',
		wp_kses_post(
			__( '<strong>Modules are optional enhancements.</strong> WordPress Support works perfectly as a standalone core with full diagnostics, emergency recovery, backup verification, and documentation management. Install modules only if you need specialized features like media optimization or vault storage.', 'plugin-wp-support-thisismyurl' )
		),
		'info',
		array( 'capability' => 'manage_options' )
	);

	$catalog_modules = WPS_Module_Registry::get_catalog_with_status();
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

	require_once wp_support_PATH . 'includes/views/modules.php';
}

/**
 * Render network settings page.
 *
 * @return void
 */
function wp_support_render_network_settings(): void {
	if ( ! current_user_can( 'manage_network_options' ) ) {
		wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'plugin-wp-support-thisismyurl' ) );
	}

	// Licenses are site-specific; Network Admin view is read-only.
	WPS_Vault::maybe_handle_settings_submission( true );
	WPS_Vault::maybe_handle_tools_submission( true );
	WPS_Vault::maybe_handle_log_action();

	$license_state = WPS_License::get_state( false );

	require_once wp_support_PATH . 'includes/views/settings.php';
}

/**
 * Render settings page.
 *
 * @return void
 */
function wp_support_render_settings_page(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'plugin-wp-support-thisismyurl' ) );
	}

	WPS_License::maybe_handle_submission( false );
	WPS_Vault::maybe_handle_settings_submission( false );
	WPS_Vault::maybe_handle_tools_submission( false );
	WPS_Vault::maybe_handle_log_action();

	$license_state = WPS_License::get_state( false );

	require_once wp_support_PATH . 'includes/views/settings.php';
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
function wps_ajax_save_metabox_state(): void {
	check_ajax_referer( 'WPS_metabox_state', 'nonce' );

	if ( ! current_user_can( 'manage_options' ) && ! current_user_can( 'manage_network_options' ) ) {
		\WPS\CoreSupport\wps_ajax_permission_denied();
	}

	$state = \WPS\CoreSupport\wps_get_post_text( 'state' );

	if ( empty( $state ) ) {
		\WPS\CoreSupport\wps_ajax_invalid_request( 'state' );
	}

	update_user_meta( get_current_user_id(), 'WPS_metabox_state', $state );

	\WPS\CoreSupport\wps_ajax_success();
}

/**
 * AJAX handler to save postbox order.
 *
 * @return void
 */
function wps_ajax_save_postbox_order(): void {
	check_ajax_referer( 'WPS_postbox_state', 'nonce' );

	if ( ! current_user_can( 'manage_options' ) && ! current_user_can( 'manage_network_options' ) ) {
		\WPS\CoreSupport\wps_ajax_permission_denied();
	}

	$page  = \WPS\CoreSupport\wps_get_post_key( 'page' );
	$order = isset( $_POST['order'] ) ? wp_unslash( $_POST['order'] ) : array();

	if ( empty( $page ) ) {
		\WPS\CoreSupport\wps_ajax_invalid_request( 'page' );
	}

	// Ensure order is an associative array
	if ( ! is_array( $order ) ) {
		$order = array();
	}

	$user_id = get_current_user_id();

	// Get existing state
	$all_states = get_user_meta( $user_id, 'WPS_postbox_states', true );
	if ( ! is_array( $all_states ) ) {
		$all_states = array();
	}

	// Update order for this page
	$all_states[ $page ]['order'] = $order;

	// Save back to JSON store
	update_user_meta( $user_id, 'WPS_postbox_states', $all_states );



	\WPS\CoreSupport\wps_ajax_success(
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
function wps_ajax_save_postbox_state(): void {
	check_ajax_referer( 'WPS_postbox_state', 'nonce' );

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
	$all_states = get_user_meta( $user_id, 'WPS_postbox_states', true );
	if ( ! is_array( $all_states ) ) {
		$all_states = array();
	}

	// Update closed for this page
	$all_states[ $page ]['closed'] = $closed;

	// Save back to JSON store
	update_user_meta( $user_id, 'WPS_postbox_states', $all_states );



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
function wp_support_find_plugin_file_by_slug( string $slug ): ?string {
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
function wp_support_resolve_download_url( array $module ): string {
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
	$url = (string) apply_filters( 'WPS_resolve_download_url', $url, $module );

	return esc_url_raw( $url );
}

/**
 * Handle "Run Now" requests for scheduled tasks.
 *
 * @return void
 */
function wps_run_task_now(): void {
	if ( empty( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'wps_run_task_now' ) ) {
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
	$allowed_hooks = array( 'WPS_refresh_modules', 'WPS_vault_queue_runner' );
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

	WPS_Vault::add_log(
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
	$redirect = add_query_arg( 'wps_run_now', '1', $redirect_url );
	wp_safe_redirect( $redirect );
	exit;
}

/**
 * Add action links to the plugins list for WordPress Support.
 *
 * @param array $links Plugin action links.
 * @return array Modified action links.
 */
function wp_support_plugin_action_links( array $links ): array {
	$dashboard_link = sprintf(
		'<a href="%s">%s</a>',
		esc_url( admin_url( 'admin.php?page=wp-support' ) ),
		esc_html__( 'Dashboard', 'plugin-wp-support-thisismyurl' )
	);

	$settings_link = '';
	if ( current_user_can( 'manage_options' ) ) {
		$settings_link = sprintf(
			'<a href="%s">%s</a>',
			esc_url( admin_url( 'admin.php?page=wp-support&WPS_tab=dashboard_settings' ) ),
			esc_html__( 'Settings', 'plugin-wp-support-thisismyurl' )
		);
	} elseif ( is_multisite() && current_user_can( 'manage_network_options' ) ) {
		$settings_link = sprintf(
			'<a href="%s">%s</a>',
			esc_url( admin_url( 'admin.php?page=wps-core-network-settings', 'network' ) ),
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
function wp_support_plugin_row_meta( array $meta, string $file ): array {
	if ( wp_support_BASENAME !== $file ) {
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
 * Save screen options for dashboard pages.
 *
 * @param mixed  $status Screen option value. Default false to skip.
 * @param string $option The option name.
 * @param mixed  $value  The option value.
 * @return mixed
 */
function wp_support_save_screen_option( $status, string $option, $value ) {
	if ( 'layout_columns' === $option ) {
		return $value;
	}
	return $status;
}

/**
 * Filter postbox classes to apply saved closed state.
 *
 * @param array  $classes Postbox classes.
 * @param string $box_id  Postbox ID.
 * @return array
 */
function wp_support_postbox_classes( array $classes, string $box_id ): array {
	$closed = wp_support_get_closed_postboxes( false );
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
function wp_support_get_metabox_order( $result ) {
	$context   = WPS_Tab_Navigation::get_current_context();
	$hub_id    = $context['hub'] ?? '';
	$state_key = 'wp-support' . ( $hub_id ? '-' . $hub_id : '' );

	// Get all states from JSON store
	$user_id    = get_current_user_id();
	$all_states = get_user_meta( $user_id, 'WPS_postbox_states', true );

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
		update_user_meta( $user_id, 'WPS_postbox_states', $all_states );
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
function wp_support_get_closed_postboxes( $result ) {
	$context   = WPS_Tab_Navigation::get_current_context();
	$hub_id    = $context['hub'] ?? '';
	$state_key = 'wp-support' . ( $hub_id ? '-' . $hub_id : '' );

	// Get all states from JSON store
	$user_id    = get_current_user_id();
	$all_states = get_user_meta( $user_id, 'WPS_postbox_states', true );

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
		update_user_meta( $user_id, 'WPS_postbox_states', $all_states );
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

/**
 * Register the Vault exporter with WordPress Personal Data Export.
 *
 * @param array $exporters Existing exporters.
 * @return array Modified exporters.
 */
// Vault privacy exporters/erasers moved to the Vault module.

// Register activation and deactivation hooks.
register_activation_hook( __FILE__, __NAMESPACE__ . '\\wp_support_activate' );
register_deactivation_hook( __FILE__, __NAMESPACE__ . '\\wp_support_deactivate' );

// Initialize the plugin.
add_action( 'plugins_loaded', __NAMESPACE__ . '\\wp_support_init' );

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
 * - Added AJAX handlers: wps_ajax_install_module and wps_ajax_update_module
 * - Implement WP_Plugin_Upgrader for direct installation from catalog
 * - Auto-activate installed modules after installation
 * - Support for multisite with network-wide install/update
 * - Permission checks for install_plugins and update_plugins capabilities
 * - Helper function wp_support_find_plugin_file_by_slug() for plugin location
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
 * - Created WPS_Module_Registry class for action-based module discovery
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
 * - includes/class-wps-module-registry.php: Full registry implementation
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
 */

/**
 * Render Module Registry / Discovery settings widget.
 *
 * @return void
 */
function render_settings_module_registry(): void {
	$enabled   = (bool) get_option( 'WPS_module_discovery_enabled', true );
	$frequency = get_option( 'WPS_module_discovery_frequency', 'on-demand' );
	?>
	<form method="post" class="wps-settings-form" data-settings-group="module_registry" style="max-width: 600px;">
		<?php wp_nonce_field( 'WPS_settings_module_registry', 'WPS_settings_nonce' ); ?>
		<table class="form-table" role="presentation">
			<tbody>
				<tr>
					<th scope="row"><label for="WPS_module_discovery_enabled"><?php esc_html_e( 'Auto-Discovery', 'plugin-wp-support-thisismyurl' ); ?></label></th>
					<td>
						<input type="checkbox" id="WPS_module_discovery_enabled" name="WPS_module_discovery_enabled" value="1" <?php checked( $enabled, true ); ?> />
						<label for="WPS_module_discovery_enabled"><?php esc_html_e( 'Automatically discover modules from installed plugins', 'plugin-wp-support-thisismyurl' ); ?></label>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="WPS_module_discovery_frequency"><?php esc_html_e( 'Discovery Frequency', 'plugin-wp-support-thisismyurl' ); ?></label></th>
					<td>
						<select id="WPS_module_discovery_frequency" name="WPS_module_discovery_frequency">
							<option value="on-demand" <?php selected( $frequency, 'on-demand' ); ?>><?php esc_html_e( 'On-Demand (Manual)', 'plugin-wp-support-thisismyurl' ); ?></option>
							<option value="daily" <?php selected( $frequency, 'daily' ); ?>><?php esc_html_e( 'Daily', 'plugin-wp-support-thisismyurl' ); ?></option>
							<option value="weekly" <?php selected( $frequency, 'weekly' ); ?>><?php esc_html_e( 'Weekly', 'plugin-wp-support-thisismyurl' ); ?></option>
						</select>
						<p class="description"><?php esc_html_e( 'How often the module catalog should refresh.', 'plugin-wp-support-thisismyurl' ); ?></p>
					</td>
				</tr>
			</tbody>
		</table>
		<div class="wps-settings-save-status" style="margin-top: 10px; font-size: 13px; color: #666;"></div>
	</form>
	<hr style="margin-top: 20px;">
	<p>
		<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?action=WPS_rescan_modules' ), 'WPS_rescan_modules' ) ); ?>" class="button"><?php esc_html_e( 'Rescan Modules Now', 'plugin-wp-support-thisismyurl' ); ?></a>
	</p>
	<?php
}

/**
 * Render Capability Mapping settings widget.
 *
 * @return void
 */
function render_settings_capabilities(): void {
	$dashboard_role = get_option( 'WPS_capability_dashboard_role', 'manage_options' );
	$install_roles  = (array) get_option( 'WPS_capability_install_roles', array( 'manage_options' ) );
	$update_roles   = (array) get_option( 'WPS_capability_update_roles', array( 'manage_options' ) );
	?>
	<form method="post" class="wps-settings-form" data-settings-group="capabilities" style="max-width: 600px;">
		<?php wp_nonce_field( 'WPS_settings_capabilities', 'WPS_settings_nonce' ); ?>
		<table class="form-table" role="presentation">
			<tbody>
				<tr>
					<th scope="row"><label for="WPS_capability_dashboard_role"><?php esc_html_e( 'Dashboard Access', 'plugin-wp-support-thisismyurl' ); ?></label></th>
					<td>
						<select id="WPS_capability_dashboard_role" name="WPS_capability_dashboard_role">
							<option value="manage_options" <?php selected( $dashboard_role, 'manage_options' ); ?>><?php esc_html_e( 'Admin (manage_options)', 'plugin-wp-support-thisismyurl' ); ?></option>
							<option value="manage_network_options" <?php selected( $dashboard_role, 'manage_network_options' ); ?>><?php esc_html_e( 'Super Admin (manage_network_options)', 'plugin-wp-support-thisismyurl' ); ?></option>
						</select>
						<p class="description"><?php esc_html_e( 'Minimum capability to access the Support dashboard.', 'plugin-wp-support-thisismyurl' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Install Permissions', 'plugin-wp-support-thisismyurl' ); ?></th>
					<td>
						<label><input type="checkbox" name="WPS_capability_install_roles[]" value="manage_options" <?php checked( in_array( 'manage_options', $install_roles, true ) ); ?> /> <?php esc_html_e( 'Admin', 'plugin-wp-support-thisismyurl' ); ?></label><br/>
						<label><input type="checkbox" name="WPS_capability_install_roles[]" value="manage_network_options" <?php checked( in_array( 'manage_network_options', $install_roles, true ) ); ?> /> <?php esc_html_e( 'Super Admin', 'plugin-wp-support-thisismyurl' ); ?></label><br/>
						<p class="description"><?php esc_html_e( 'Which roles can install modules.', 'plugin-wp-support-thisismyurl' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Update Permissions', 'plugin-wp-support-thisismyurl' ); ?></th>
					<td>
						<label><input type="checkbox" name="WPS_capability_update_roles[]" value="manage_options" <?php checked( in_array( 'manage_options', $update_roles, true ) ); ?> /> <?php esc_html_e( 'Admin', 'plugin-wp-support-thisismyurl' ); ?></label><br/>
						<label><input type="checkbox" name="WPS_capability_update_roles[]" value="manage_network_options" <?php checked( in_array( 'manage_network_options', $update_roles, true ) ); ?> /> <?php esc_html_e( 'Super Admin', 'plugin-wp-support-thisismyurl' ); ?></label><br/>
						<p class="description"><?php esc_html_e( 'Which roles can update modules.', 'plugin-wp-support-thisismyurl' ); ?></p>
					</td>
				</tr>
			</tbody>
		</table>
		<div class="wps-settings-save-status" style="margin-top: 10px; font-size: 13px; color: #666;"></div>
	</form>
	<?php
}

/**
 * Render Dashboard & UI settings widget.
 *
 * @return void
 */
function render_settings_dashboard(): void {
	$default_cols   = (int) get_option( 'WPS_dashboard_default_columns', 2 );
	$sticky_widgets = (array) get_option( 'WPS_dashboard_sticky_widgets', array() );
	$widget_sorting = get_option( 'WPS_dashboard_widget_sorting', 'drag-order' );
	?>
	<form method="post" class="wps-settings-form" data-settings-group="dashboard" style="max-width: 600px;">
		<?php wp_nonce_field( 'WPS_settings_dashboard', 'WPS_settings_nonce' ); ?>
		<table class="form-table" role="presentation">
			<tbody>
				<tr>
					<th scope="row"><?php esc_html_e( 'Default Column Layout', 'plugin-wp-support-thisismyurl' ); ?></th>
					<td>
						<label><input type="radio" name="WPS_dashboard_default_columns" value="1" <?php checked( $default_cols, 1 ); ?> /> <?php esc_html_e( '1 Column', 'plugin-wp-support-thisismyurl' ); ?></label><br/>
						<label><input type="radio" name="WPS_dashboard_default_columns" value="2" <?php checked( $default_cols, 2 ); ?> /> <?php esc_html_e( '2 Columns', 'plugin-wp-support-thisismyurl' ); ?></label><br/>
						<p class="description"><?php esc_html_e( 'Default dashboard layout for new users.', 'plugin-wp-support-thisismyurl' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Sticky Widgets', 'plugin-wp-support-thisismyurl' ); ?></th>
					<td>
						<label><input type="checkbox" name="WPS_dashboard_sticky_widgets[]" value="WPS_quick_actions" <?php checked( in_array( 'WPS_quick_actions', $sticky_widgets, true ) ); ?> /> <?php esc_html_e( 'Always show Quick Actions', 'plugin-wp-support-thisismyurl' ); ?></label><br/>
						<label><input type="checkbox" name="WPS_dashboard_sticky_widgets[]" value="WPS_modules" <?php checked( in_array( 'WPS_modules', $sticky_widgets, true ) ); ?> /> <?php esc_html_e( 'Always show Modules', 'plugin-wp-support-thisismyurl' ); ?></label><br/>
						<p class="description"><?php esc_html_e( 'Widgets that cannot be hidden by users.', 'plugin-wp-support-thisismyurl' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="WPS_dashboard_widget_sorting"><?php esc_html_e( 'Widget Sorting', 'plugin-wp-support-thisismyurl' ); ?></label></th>
					<td>
						<select id="WPS_dashboard_widget_sorting" name="WPS_dashboard_widget_sorting">
							<option value="drag-order" <?php selected( $widget_sorting, 'drag-order' ); ?>><?php esc_html_e( 'Allow Drag & Drop', 'plugin-wp-support-thisismyurl' ); ?></option>
							<option value="locked" <?php selected( $widget_sorting, 'locked' ); ?>><?php esc_html_e( 'Locked (Fixed Order)', 'plugin-wp-support-thisismyurl' ); ?></option>
						</select>
						<p class="description"><?php esc_html_e( 'Allow users to rearrange dashboard widgets.', 'plugin-wp-support-thisismyurl' ); ?></p>
					</td>
				</tr>
			</tbody>
		</table>
		<div class="wps-settings-save-status" style="margin-top: 10px; font-size: 13px; color: #666;"></div>
	</form>
	<?php
}

/**
 * Render License & Updates settings widget.
 *
 * @return void
 */
function render_settings_license(): void {
	$license_key    = get_option( 'WPS_license_key', '' );
	$is_licensed    = ! empty( $license_key );
	$auto_update    = (array) get_option( 'WPS_license_auto_update_types', array( 'minor', 'patch' ) );
	$update_channel = get_option( 'WPS_license_update_channel', 'stable' );
	?>
	<form method="post" class="wps-settings-form" data-settings-group="license" style="max-width: 600px;">
		<?php wp_nonce_field( 'WPS_settings_license', 'WPS_settings_nonce' ); ?>
		<table class="form-table" role="presentation">
			<tbody>
				<tr>
					<th scope="row"><?php esc_html_e( 'License Status', 'plugin-wp-support-thisismyurl' ); ?></th>
					<td>
						<p><?php echo $is_licensed ? '<span style="color: green;">✓ ' . esc_html__( 'Licensed', 'plugin-wp-support-thisismyurl' ) . '</span>' : '<span style="color: #999;">' . esc_html__( 'Not Licensed', 'plugin-wp-support-thisismyurl' ) . '</span>'; ?></p>
						<p class="description"><?php esc_html_e( 'Updates are pulled from GitHub releases.', 'plugin-wp-support-thisismyurl' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="WPS_license_key"><?php esc_html_e( 'License Key', 'plugin-wp-support-thisismyurl' ); ?></label></th>
					<td>
						<input type="password" id="WPS_license_key" name="WPS_license_key" value="<?php echo esc_attr( $license_key ); ?>" placeholder="<?php esc_attr_e( 'Enter license key', 'plugin-wp-support-thisismyurl' ); ?>" style="width: 100%; max-width: 300px;" />
						<p class="description"><?php esc_html_e( 'Masked for security. Leave empty to disable licensing.', 'plugin-wp-support-thisismyurl' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Auto-Update', 'plugin-wp-support-thisismyurl' ); ?></th>
					<td>
						<label><input type="checkbox" name="WPS_license_auto_update_types[]" value="major" <?php checked( in_array( 'major', $auto_update, true ) ); ?> /> <?php esc_html_e( 'Major Versions', 'plugin-wp-support-thisismyurl' ); ?></label><br/>
						<label><input type="checkbox" name="WPS_license_auto_update_types[]" value="minor" <?php checked( in_array( 'minor', $auto_update, true ) ); ?> /> <?php esc_html_e( 'Minor Versions', 'plugin-wp-support-thisismyurl' ); ?></label><br/>
						<label><input type="checkbox" name="WPS_license_auto_update_types[]" value="patch" <?php checked( in_array( 'patch', $auto_update, true ) ); ?> /> <?php esc_html_e( 'Patch Updates', 'plugin-wp-support-thisismyurl' ); ?></label><br/>
						<p class="description"><?php esc_html_e( 'Which update types to install automatically.', 'plugin-wp-support-thisismyurl' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="WPS_license_update_channel"><?php esc_html_e( 'Update Channel', 'plugin-wp-support-thisismyurl' ); ?></label></th>
					<td>
						<select id="WPS_license_update_channel" name="WPS_license_update_channel">
							<option value="stable" <?php selected( $update_channel, 'stable' ); ?>><?php esc_html_e( 'Stable', 'plugin-wp-support-thisismyurl' ); ?></option>
							<option value="beta" <?php selected( $update_channel, 'beta' ); ?>><?php esc_html_e( 'Beta', 'plugin-wp-support-thisismyurl' ); ?></option>
							<option value="dev" <?php selected( $update_channel, 'dev' ); ?>><?php esc_html_e( 'Development', 'plugin-wp-support-thisismyurl' ); ?></option>
						</select>
						<p class="description"><?php esc_html_e( 'Release channel for updates.', 'plugin-wp-support-thisismyurl' ); ?></p>
					</td>
				</tr>
			</tbody>
		</table>
		<div class="wps-settings-save-status" style="margin-top: 10px; font-size: 13px; color: #666;"></div>
	</form>
	<?php
}

/**
 * Render Privacy & GDPR settings widget.
 *
 * @return void
 */
function render_settings_privacy(): void {
	$log_retention       = (int) get_option( 'WPS_privacy_log_retention_days', 90 );
	$auto_delete_enabled = (bool) get_option( 'WPS_privacy_auto_delete_enabled', false );
	$auto_delete_days    = (int) get_option( 'WPS_privacy_auto_delete_days', 90 );
	$audit_level         = get_option( 'WPS_privacy_audit_logging_level', 'standard' );
	$export_format       = get_option( 'WPS_privacy_export_format', 'json' );
	$contrib_see_user    = (bool) get_option( 'WPS_privacy_contributors_see_user_activity', false );
	$editor_see_admin    = (bool) get_option( 'WPS_privacy_editors_see_admin_activity', false );
	$diagnostic_logging  = (bool) get_option( 'wps_diagnostic_logging_enabled', false );
	?>
	<form method="post" class="wps-settings-form" data-settings-group="privacy" style="max-width: 600px;">
		<?php wp_nonce_field( 'WPS_settings_privacy', 'WPS_settings_nonce' ); ?>
		<table class="form-table" role="presentation">
			<tbody>
				<tr>
					<th scope="row"><label for="WPS_privacy_log_retention_days"><?php esc_html_e( 'Activity Log Retention', 'plugin-wp-support-thisismyurl' ); ?></label></th>
					<td>
						<input type="number" id="WPS_privacy_log_retention_days" name="WPS_privacy_log_retention_days" value="<?php echo esc_attr( $log_retention ); ?>" min="1" max="3650" /> <?php esc_html_e( 'days', 'plugin-wp-support-thisismyurl' ); ?>
						<p class="description"><?php esc_html_e( 'How long to keep activity logs.', 'plugin-wp-support-thisismyurl' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Auto-Delete Old Logs', 'plugin-wp-support-thisismyurl' ); ?></th>
					<td>
						<label><input type="checkbox" name="WPS_privacy_auto_delete_enabled" value="1" <?php checked( $auto_delete_enabled, true ); ?> /> <?php esc_html_e( 'Automatically delete logs older than', 'plugin-wp-support-thisismyurl' ); ?></label>
						<input type="number" name="WPS_privacy_auto_delete_days" value="<?php echo esc_attr( $auto_delete_days ); ?>" min="1" max="3650" style="width: 80px;" /> <?php esc_html_e( 'days', 'plugin-wp-support-thisismyurl' ); ?>
						<p class="description"><?php esc_html_e( 'Clean up old activity records automatically.', 'plugin-wp-support-thisismyurl' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="WPS_privacy_audit_logging_level"><?php esc_html_e( 'Audit Logging Level', 'plugin-wp-support-thisismyurl' ); ?></label></th>
					<td>
						<select id="WPS_privacy_audit_logging_level" name="WPS_privacy_audit_logging_level">
							<option value="minimal" <?php selected( $audit_level, 'minimal' ); ?>><?php esc_html_e( 'Minimal', 'plugin-wp-support-thisismyurl' ); ?></option>
							<option value="standard" <?php selected( $audit_level, 'standard' ); ?>><?php esc_html_e( 'Standard', 'plugin-wp-support-thisismyurl' ); ?></option>
							<option value="verbose" <?php selected( $audit_level, 'verbose' ); ?>><?php esc_html_e( 'Verbose', 'plugin-wp-support-thisismyurl' ); ?></option>
						</select>
						<p class="description"><?php esc_html_e( 'How detailed the activity logs should be.', 'plugin-wp-support-thisismyurl' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Diagnostic Logging', 'plugin-wp-support-thisismyurl' ); ?></th>
					<td>
						<label><input type="checkbox" name="wps_diagnostic_logging_enabled" value="1" <?php checked( $diagnostic_logging, true ); ?> /> <?php esc_html_e( 'Enable diagnostic logging for support', 'plugin-wp-support-thisismyurl' ); ?></label>
						<p class="description"><?php esc_html_e( 'Log environment checks and resource usage for troubleshooting. Recommended for debugging performance issues.', 'plugin-wp-support-thisismyurl' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="WPS_privacy_export_format"><?php esc_html_e( 'GDPR Export Format', 'plugin-wp-support-thisismyurl' ); ?></label></th>
					<td>
						<select id="WPS_privacy_export_format" name="WPS_privacy_export_format">
							<option value="json" <?php selected( $export_format, 'json' ); ?>><?php esc_html_e( 'JSON', 'plugin-wp-support-thisismyurl' ); ?></option>
							<option value="csv" <?php selected( $export_format, 'csv' ); ?>><?php esc_html_e( 'CSV', 'plugin-wp-support-thisismyurl' ); ?></option>
							<option value="zip" <?php selected( $export_format, 'zip' ); ?>><?php esc_html_e( 'ZIP Archive', 'plugin-wp-support-thisismyurl' ); ?></option>
						</select>
						<p class="description"><?php esc_html_e( 'Format for data exports (privacy requests).', 'plugin-wp-support-thisismyurl' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Activity Visibility', 'plugin-wp-support-thisismyurl' ); ?></th>
					<td>
						<label><input type="checkbox" name="WPS_privacy_contributors_see_user_activity" value="1" <?php checked( $contrib_see_user, true ); ?> /> <?php esc_html_e( 'Contributors can view other user activity', 'plugin-wp-support-thisismyurl' ); ?></label><br/>
						<label><input type="checkbox" name="WPS_privacy_editors_see_admin_activity" value="1" <?php checked( $editor_see_admin, true ); ?> /> <?php esc_html_e( 'Editors can view admin activity', 'plugin-wp-support-thisismyurl' ); ?></label><br/>
						<p class="description"><?php esc_html_e( 'Control who can see activity logs from other roles.', 'plugin-wp-support-thisismyurl' ); ?></p>
					</td>
				</tr>
			</tbody>
		</table>
		<div class="wps-settings-save-status" style="margin-top: 10px; font-size: 13px; color: #666;"></div>
	</form>
	<?php
}

/**
 * Render Database Cleanup settings widget.
 *
 * @return void
 */
function render_settings_database_cleanup(): void {
// Get the database cleanup feature instance
$feature = \WPS\CoreSupport\WPS_Feature_Registry::get_feature( 'database-cleanup' );

if ( ! $feature ) {
echo '<p>' . esc_html__( 'Database cleanup feature is not available.', 'plugin-wp-support-thisismyurl' ) . '</p>';
return;
}

$enabled            = $feature->is_enabled();
$cleanup_frequency  = $feature->get_setting( 'cleanup_frequency', 'weekly' );
$cleanup_options    = $feature->get_setting( 'cleanup_options', array() );

// Default options if not set
$default_options = array(
'cleanup_revisions'     => true,
'cleanup_transients'    => true,
'cleanup_spam'          => true,
'cleanup_orphaned_meta' => true,
'cleanup_auto_drafts'   => true,
'optimize_tables'       => false,
'keep_revisions'        => 5,
);

$cleanup_options = array_merge( $default_options, $cleanup_options );

// Calculate next scheduled run
$next_run = wp_next_scheduled( 'wps_database_cleanup' );
$next_run_text = $next_run ? wp_date( 'F j, Y g:i A', $next_run ) : __( 'Not scheduled', 'plugin-wp-support-thisismyurl' );

// Get last cleanup from activity log if available
$last_cleanup = get_option( 'wps_last_database_cleanup', 0 );
$last_cleanup_text = $last_cleanup ? wp_date( 'F j, Y g:i A', $last_cleanup ) : __( 'Never', 'plugin-wp-support-thisismyurl' );

?>
<form method="post" class="wps-settings-form" data-settings-group="database_cleanup" style="max-width: 600px;">
<?php wp_nonce_field( 'WPS_settings_database_cleanup', 'WPS_settings_nonce' ); ?>
<table class="form-table" role="presentation">
<tbody>
<tr>
<th scope="row"><?php esc_html_e( 'Automatic Cleanup', 'plugin-wp-support-thisismyurl' ); ?></th>
<td>
<label>
<input type="checkbox" name="wps_database_cleanup_enabled" value="1" <?php checked( $enabled, true ); ?> />
<?php esc_html_e( 'Enable automatic database cleanup', 'plugin-wp-support-thisismyurl' ); ?>
</label>
<p class="description">
<?php esc_html_e( 'Automatically clean up database overhead on a scheduled basis.', 'plugin-wp-support-thisismyurl' ); ?>
</p>
</td>
</tr>

<tr>
<th scope="row"><label for="wps_cleanup_frequency"><?php esc_html_e( 'Schedule', 'plugin-wp-support-thisismyurl' ); ?></label></th>
<td>
<select id="wps_cleanup_frequency" name="wps_cleanup_frequency">
<option value="daily" <?php selected( $cleanup_frequency, 'daily' ); ?>><?php esc_html_e( 'Daily', 'plugin-wp-support-thisismyurl' ); ?></option>
<option value="weekly" <?php selected( $cleanup_frequency, 'weekly' ); ?>><?php esc_html_e( 'Weekly', 'plugin-wp-support-thisismyurl' ); ?></option>
<option value="monthly" <?php selected( $cleanup_frequency, 'monthly' ); ?>><?php esc_html_e( 'Monthly', 'plugin-wp-support-thisismyurl' ); ?></option>
</select>
<p class="description">
<?php esc_html_e( 'How often to run automatic cleanup.', 'plugin-wp-support-thisismyurl' ); ?>
</p>
</td>
</tr>

<tr>
<th scope="row"><?php esc_html_e( 'Cleanup Status', 'plugin-wp-support-thisismyurl' ); ?></th>
<td>
<p><strong><?php esc_html_e( 'Last Cleanup:', 'plugin-wp-support-thisismyurl' ); ?></strong> <?php echo esc_html( $last_cleanup_text ); ?></p>
<p><strong><?php esc_html_e( 'Next Scheduled:', 'plugin-wp-support-thisismyurl' ); ?></strong> <?php echo esc_html( $next_run_text ); ?></p>
</td>
</tr>

<tr>
<th scope="row"><?php esc_html_e( 'Cleanup Options', 'plugin-wp-support-thisismyurl' ); ?></th>
<td>
<label>
<input type="checkbox" name="wps_cleanup_options[cleanup_revisions]" value="1" <?php checked( $cleanup_options['cleanup_revisions'], true ); ?> />
<?php esc_html_e( 'Clean up post revisions', 'plugin-wp-support-thisismyurl' ); ?>
</label>
<br/>
<label style="margin-left: 24px;">
<?php esc_html_e( 'Keep', 'plugin-wp-support-thisismyurl' ); ?>
<input type="number" name="wps_cleanup_options[keep_revisions]" value="<?php echo esc_attr( $cleanup_options['keep_revisions'] ); ?>" min="0" max="50" style="width: 60px;" />
<?php esc_html_e( 'most recent revisions per post', 'plugin-wp-support-thisismyurl' ); ?>
</label>
<br/><br/>

<label>
<input type="checkbox" name="wps_cleanup_options[cleanup_transients]" value="1" <?php checked( $cleanup_options['cleanup_transients'], true ); ?> />
<?php esc_html_e( 'Clean up expired transients', 'plugin-wp-support-thisismyurl' ); ?>
</label>
<br/>

<label>
<input type="checkbox" name="wps_cleanup_options[cleanup_spam]" value="1" <?php checked( $cleanup_options['cleanup_spam'], true ); ?> />
<?php esc_html_e( 'Clean up spam comments', 'plugin-wp-support-thisismyurl' ); ?>
</label>
<br/>

<label>
<input type="checkbox" name="wps_cleanup_options[cleanup_orphaned_meta]" value="1" <?php checked( $cleanup_options['cleanup_orphaned_meta'], true ); ?> />
<?php esc_html_e( 'Clean up orphaned post metadata', 'plugin-wp-support-thisismyurl' ); ?>
</label>
<br/>

<label>
<input type="checkbox" name="wps_cleanup_options[cleanup_auto_drafts]" value="1" <?php checked( $cleanup_options['cleanup_auto_drafts'], true ); ?> />
<?php esc_html_e( 'Clean up old auto-drafts', 'plugin-wp-support-thisismyurl' ); ?>
</label>
<br/>

<label>
<input type="checkbox" name="wps_cleanup_options[optimize_tables]" value="1" <?php checked( $cleanup_options['optimize_tables'], true ); ?> />
<?php esc_html_e( 'Optimize database tables', 'plugin-wp-support-thisismyurl' ); ?>
</label>

<p class="description">
<?php esc_html_e( 'Select which cleanup tasks to perform automatically.', 'plugin-wp-support-thisismyurl' ); ?>
</p>
</td>
</tr>

<tr>
<th scope="row"><?php esc_html_e( 'Manual Cleanup', 'plugin-wp-support-thisismyurl' ); ?></th>
<td>
<?php
$cleanup_url = wp_nonce_url(
admin_url( 'admin-post.php?action=wps_run_database_cleanup' ),
'wps_run_database_cleanup'
);
?>
<a href="<?php echo esc_url( $cleanup_url ); ?>" class="button button-secondary">
<?php esc_html_e( 'Run Cleanup Now', 'plugin-wp-support-thisismyurl' ); ?>
</a>
<p class="description">
<?php esc_html_e( 'Manually trigger a database cleanup immediately.', 'plugin-wp-support-thisismyurl' ); ?>
</p>
</td>
</tr>
</tbody>
</table>
<div class="wps-settings-save-status" style="margin-top: 10px; font-size: 13px; color: #666;"></div>
</form>
<?php
}

/**
 * Render Database Cleanup settings widget.
 *
 * @return void
 */
