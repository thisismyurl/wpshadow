<?php
/**
 * Author:              WPShadow
 * Author URI:          https://wpshadow.com/
 * Plugin Name:         WPShadow
 * Plugin URI:          https://wpshadow.com/
 * Donate link:         https://wpshadow.com/
 * Description:         The foundational WordPress plugin with comprehensive health diagnostics, emergency recovery, backup verification, and documentation management. Optionally extends with module ecosystem (Media Hub, Image Formats, Vault Storage, and more).The foundational platform for WordPress health, featuring intelligent emergency recovery, real-time diagnostics, and visual regression protection. Effortlessly scale performance and security with the optional module ecosystem, including Media Hub and Vault Storage.
 * Tags:                WordPress, plugin, foundation, hub, architecture, management, suite, diagnostics, health, backup
 * Version:             1.2601.75000
 * Requires at least:   6.4
 * Requires PHP:        8.1.29
 * Update URI:          https://github.com/thisismyurl/plugin-wpshadow
 * GitHub Plugin URI:   https://github.com/thisismyurl/plugin-wpshadow
 * Primary Branch:      main
 * Text Domain:         plugin-wpshadow
 * License:             GPL2
 * License URI:         https://www.gnu.org/licenses/gpl-2.0.html
 * @package WPSHADOW
 */

declare(strict_types=1);

namespace WPShadow;

use WPShadow\CoreSupport\WPSHADOW_Feature_Core_Diagnostics;
// ========================================================
// FREE FEATURES - use statements below
// PAID FEATURES - moved to wpshadow-pro.php
// ========================================================

use WPShadow\CoreSupport\WPSHADOW_Feature_Asset_Version_Removal;
use WPShadow\CoreSupport\WPSHADOW_Feature_Head_Cleanup;
use WPShadow\CoreSupport\WPSHADOW_Feature_Block_Cleanup;
use WPShadow\CoreSupport\WPSHADOW_Feature_CSS_Class_Cleanup;
use WPShadow\CoreSupport\WPSHADOW_Feature_Plugin_Cleanup;
use WPShadow\CoreSupport\WPSHADOW_Feature_HTML_Cleanup;
use WPShadow\CoreSupport\WPSHADOW_Feature_Resource_Hints;
use WPShadow\CoreSupport\WPSHADOW_Feature_Nav_Accessibility;
use WPShadow\CoreSupport\WPSHADOW_Feature_Color_Contrast_Checker;
use WPShadow\CoreSupport\WPSHADOW_Feature_Skiplinks;
use WPShadow\CoreSupport\WPSHADOW_Feature_Embed_Disable;
use WPShadow\CoreSupport\WPSHADOW_Feature_jQuery_Cleanup;
use WPShadow\CoreSupport\WPSHADOW_Feature_Block_CSS_Cleanup;
use WPShadow\CoreSupport\WPSHADOW_Feature_Interactivity_Cleanup;
use WPShadow\CoreSupport\WPSHADOW_Feature_Consent_Checks;
use WPShadow\CoreSupport\WPSHADOW_Feature_Iframe_Busting;
use WPShadow\CoreSupport\WPSHADOW_Feature_HTTP_SSL_Audit;
use WPShadow\CoreSupport\WPSHADOW_Feature_Registry;
use WPShadow\CoreSupport\WPSHADOW_Feature_Image_Lazy_Loading;
use WPShadow\CoreSupport\WPSHADOW_Feature_Google_Fonts_Disabler;
use WPShadow\CoreSupport\WPSHADOW_Feature_Core_Integrity;
use WPShadow\CoreSupport\WPSHADOW_Feature_Maintenance_Cleanup;

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
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
 * Render Settings view (Core or Hub) using the same metabox layout as the dashboard.
 * Widgets here represent settings groups for the current context.
 *
 * @param string $hub_id Optional hub identifier for hub-level settings.
 * @return void
 */
function wpshadow_render_settings( string $hub_id = '' ): void {
	if ( ! WPSHADOW_can_manage_settings() ) {
		wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'plugin-wpshadow' ) );
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
			'saving'  => __( 'Saving...', 'plugin-wpshadow' ),
			'saved'   => __( 'Saved', 'plugin-wpshadow' ),
			'error'   => __( 'Save failed', 'plugin-wpshadow' ),
		)
	);

	// Title mirrors dashboard style.
	$settings_title = __( 'Support Settings', 'plugin-wpshadow' );
	if ( ! empty( $hub_id ) ) {
		$settings_title = ucfirst( $hub_id ) . ' ' . __( 'Settings', 'plugin-wpshadow' );
	}

	// Register metaboxes for core-level settings.
	if ( empty( $hub_id ) ) {
		add_meta_box(
			'wpshadow_settings_module_registry',
			__( 'Module Discovery', 'plugin-wpshadow' ),
			__NAMESPACE__ . '\\render_settings_module_registry',
			$screen->id,
			'normal'
		);

		add_meta_box(
			'wpshadow_settings_capabilities',
			__( 'Capability Mapping', 'plugin-wpshadow' ),
			__NAMESPACE__ . '\\render_settings_capabilities',
			$screen->id,
			'normal'
		);

		add_meta_box(
			'wpshadow_settings_privacy',
			__( 'Privacy & GDPR', 'plugin-wpshadow' ),
			__NAMESPACE__ . '\\render_settings_privacy',
			$screen->id,
			'normal'
		);

		add_meta_box(
			'wpshadow_settings_database_cleanup',
			__( 'Database Cleanup', 'plugin-wpshadow' ),
			__NAMESPACE__ . '\\render_settings_database_cleanup',
			$screen->id,
			'normal'
		);
	}

	add_meta_box(
		'wpshadow_settings_dashboard',
		__( 'Dashboard & UI', 'plugin-wpshadow' ),
		__NAMESPACE__ . '\\render_settings_dashboard',
		$screen->id,
		'side'
	);

	add_meta_box(
		'wpshadow_settings_license',
		__( 'License & Updates', 'plugin-wpshadow' ),
		__NAMESPACE__ . '\\render_settings_license',
		$screen->id,
		'side'
	);

	// Initialize postboxes on this screen (drag/toggle) in footer.
	add_action(
		'admin_print_footer_scripts',
		static function () use ( $screen, $hub_id ): void {
			// Use hub-specific state key for settings.
			$state_key = 'wpshadow-settings' . ( $hub_id ? '-' . $hub_id : '' );
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
function wpshadow_hide_disabled_submenus(): void {
	?>
	<script>
	(function(){
		var scope = (document.documentElement.classList.contains('network-admin') ? 'network' : 'site');
		var storagePrefix = 'wpsToggleState:' + scope + ':';
		function applyHide(){
			var top = document.getElementById('toplevel_page_wpshadow');
			if (!top){
				var link = document.querySelector('#adminmenu a.menu-top[href*="page=wpshadow"]');
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
				var target = 'page=wpshadow&module=' + encodeURIComponent(slug.replace(/-wpshadow$/, ''));
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
function WPSHADOW_register_core_features(): void {
	// ====================================================================
	// FREE FEATURES (License Level 1-2)
	// ====================================================================

	// Core diagnostics.
	register_WPSHADOW_feature( new WPSHADOW_Feature_Core_Diagnostics() );
	register_WPSHADOW_feature( new WPSHADOW_Feature_Favicon_Checker() );

	// Performance optimization features.
	register_WPSHADOW_feature( new WPSHADOW_Feature_Asset_Version_Removal() );
	register_WPSHADOW_feature( new WPSHADOW_Feature_Head_Cleanup() );
	register_WPSHADOW_feature( new WPSHADOW_Feature_Block_Cleanup() );
	register_WPSHADOW_feature( new WPSHADOW_Feature_CSS_Class_Cleanup() );
	register_WPSHADOW_feature( new WPSHADOW_Feature_Plugin_Cleanup() );
	register_WPSHADOW_feature( new WPSHADOW_Feature_HTML_Cleanup() );
	register_WPSHADOW_feature( new WPSHADOW_Feature_Resource_Hints() );
	register_WPSHADOW_feature( new WPSHADOW_Feature_Nav_Accessibility() );
	register_WPSHADOW_feature( new WPSHADOW_Feature_Color_Contrast_Checker() );
	register_WPSHADOW_feature( new WPSHADOW_Feature_Skiplinks() );
	register_WPSHADOW_feature( new WPSHADOW_Feature_Embed_Disable() );
	register_WPSHADOW_feature( new WPSHADOW_Feature_jQuery_Cleanup() );
	register_WPSHADOW_feature( new WPSHADOW_Feature_Block_CSS_Cleanup() );
	register_WPSHADOW_feature( new WPSHADOW_Feature_Interactivity_Cleanup() );
	
	// Free performance optimization features.
	register_WPSHADOW_feature( new WPSHADOW_Feature_Image_Lazy_Loading() );
	register_WPSHADOW_feature( new WPSHADOW_Feature_Google_Fonts_Disabler() );

	// Privacy and compliance features.
	register_WPSHADOW_feature( new WPSHADOW_Feature_Consent_Checks() );
	
	// Accessibility and mobile features.
	register_WPSHADOW_feature( new WPSHADOW_Feature_Mobile_Friendliness() );
	
	// Security features (free tier).
	register_WPSHADOW_feature( new WPSHADOW_Feature_Iframe_Busting() );
	register_WPSHADOW_feature( new WPSHADOW_Feature_HTTP_SSL_Audit() );
	register_WPSHADOW_feature( new WPSHADOW_Feature_Hotlink_Protection() );
	register_WPSHADOW_feature( new WPSHADOW_Feature_Core_Integrity() );

	// Tool features.
	register_WPSHADOW_feature( new WPSHADOW_Feature_Maintenance_Cleanup() );
	
	// SEO and social media features.
	register_WPSHADOW_feature( new WPSHADOW_Feature_Open_Graph_Previewer() );
	register_WPSHADOW_feature( new WPSHADOW_Feature_Broken_Link_Checker() );

	// ====================================================================
	// PAID FEATURES (License Level 3+)
	// ====================================================================
	// Paid features are now loaded by wpshadow-pro.php via hook:
	// do_action( 'wpshadow_pro_register_features' );
	// See _PAID_FEATURES_BACKUP.php for reference
}

/**
 * Admin guard: if a module is disabled, redirect to the parent dashboard when accessed directly.
 *
 * @return void
 */
function wpshadow_guard_disabled_modules(): void {
	if ( ! is_admin() || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
		return;
	}
	$raw_module = isset( $_GET['module'] ) ? sanitize_key( wp_unslash( $_GET['module'] ) ) : '';
	if ( empty( $raw_module ) ) {
		return;
	}
	// Normalize to full slug regardless of whether the suffix is included in the query param.
	$slug = str_contains( $raw_module, '-wpshadow' ) ? $raw_module : $raw_module . '-wpshadow';

	// Use live is_enabled() check instead of cached catalog to ensure accurate state.
	if ( \WPShadow\CoreSupport\WPSHADOW_Module_Registry::is_enabled( $slug ) ) {
		return;
	}

	$target = is_network_admin() ? network_admin_url( 'admin.php?page=wpshadow' ) : admin_url( 'admin.php?page=wpshadow' );
	wp_safe_redirect( $target );
	exit;
}

// Plugin constants.
define( 'WPSHADOW_VERSION', '1.2601.73001' );
define( 'WPSHADOW_FILE', __FILE__ );
define( 'WPSHADOW_PATH', str_replace( '/', DIRECTORY_SEPARATOR, trailingslashit( plugin_dir_path( __FILE__ ) ) ) );
define( 'WPSHADOW_URL', plugin_dir_url( __FILE__ ) );
define( 'WPSHADOW_BASENAME', plugin_basename( __FILE__ ) );
define( 'WPSHADOW_TEXT_DOMAIN', 'plugin-wpshadow' );

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

	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$module = isset( $_GET['module'] ) ? sanitize_key( wp_unslash( $_GET['module'] ) ) : '';

	if ( ! empty( $module ) ) {
		$submenu_file = 'wpshadow&module=' . $module;

	}

	return 'wpshadow';
}

/**
 * Filter submenu_file to highlight the correct submenu item when viewing module dashboards.
 * WordPress only checks the "page" parameter by default, but we use "module" to route to different dashboards.
 * This filter ensures the correct submenu (Vault, Media, etc.) is highlighted when active.
 *
 * @param string|null $submenu_file The submenu file.
 * @return string|null The potentially modified submenu file.
 */
function wpshadow_filter_submenu_file( ?string $submenu_file ): ?string {
	// Only apply when we're on the wpshadow admin page.
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( empty( $_GET['page'] ) || 'wpshadow' !== $_GET['page'] ) {
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
	// The submenu items are registered with slugs like 'wpshadow&module=media', 'wpshadow&module=vault', etc.
	$target = 'wpshadow&module=' . $module;

	return $target;
}


// Suite Identifier for Hub & Spoke handshake.
define( 'WPSHADOW_SUITE_ID', 'wpshadow-media-suite-2026' );

// Minimum requirements.
define( 'WPSHADOW_MIN_PHP', '8.1.29' );
define( 'WPSHADOW_MIN_WP', '6.4.0' );

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
				esc_html__( 'WPShadow requires PHP %1$s or higher. You are running PHP %2$s.', 'plugin-wpshadow' ),
				esc_html( WPSHADOW_MIN_PHP ),
				esc_html( PHP_VERSION )
			),
			esc_html__( 'Plugin Activation Error', 'plugin-wpshadow' ),
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
				esc_html__( 'WPShadow requires WordPress %1$s or higher. You are running WordPress %2$s.', 'plugin-wpshadow' ),
				esc_html( WPSHADOW_MIN_WP ),
				esc_html( $wp_version )
			),
			esc_html__( 'Plugin Activation Error', 'plugin-wpshadow' ),
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
function wpshadow_deactivate(): void {
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
function wpshadow_get_vault_key(): ?string {
	if ( class_exists( '\\WPShadow\\VaultSupport\\WPSHADOW_Vault' ) ) {
		// Prefer module-provided key.
		if ( method_exists( '\\WPShadow\\VaultSupport\\WPSHADOW_Vault', 'get_current_key' ) ) {
			$key = \WPShadow\VaultSupport\WPSHADOW_Vault::get_current_key();
			return ! empty( $key ) ? (string) $key : null;
		}
	}
	// Fallback to legacy storage if module not loaded yet.
	if ( defined( 'wpshadow_VAULT_KEY' ) && WPSHADOW_VAULT_KEY ) {
		return (string) WPSHADOW_VAULT_KEY;
	}
	$stored_key = get_option( 'wpshadow_vault_enc_key', '' );
	return ! empty( $stored_key ) ? (string) $stored_key : null;
}

/**
 * Initialize the plugin.
 *
 * @return void
 */
function wpshadow_init(): void {
	// Load text domain for translations.
	load_plugin_textdomain(
		WPSHADOW_TEXT_DOMAIN,
		false,
		dirname( WPSHADOW_BASENAME ) . '/languages'
	);

	// Load DRY helper functions and traits.
	require_once WPSHADOW_PATH . 'includes/helpers/wps-input-helpers.php';
	require_once WPSHADOW_PATH . 'includes/helpers/wps-ajax-helpers.php';
	require_once WPSHADOW_PATH . 'includes/helpers/wps-array-helpers.php';
	require_once WPSHADOW_PATH . 'includes/helpers/wps-color-contrast-helpers.php';
	require_once WPSHADOW_PATH . 'includes/traits/trait-wps-ajax-security.php';

	// Load update server client for automatic updates.
	require_once WPSHADOW_PATH . 'includes/class-wps-update-client.php';
	\WPShadow\CoreSupport\WPSHADOW_Update_Client::init( WPSHADOW_BASENAME );

	// Load license widget for dashboard.
	require_once WPSHADOW_PATH . 'includes/class-wps-license-widget.php';
	\WPShadow\CoreSupport\WPSHADOW_License_Widget::init();

	// Load help content API for dynamic documentation.
	require_once WPSHADOW_PATH . 'includes/class-wps-help-content-api.php';
	\WPShadow\CoreSupport\WPSHADOW_Help_Content_API::init();
	// Load REST API.
	require_once WPSHADOW_PATH . 'includes/api/class-wps-rest-api.php';
	\WPShadow\API\WPSHADOW_REST_API::init();

	// TEMPORARILY DISABLED: Module bootstrap for child plugin installation and activation.
	// require_once WPSHADOW_PATH . 'includes/class-wps-module-bootstrap.php';
	// \WPShadow\CoreSupport\WPSHADOW_Module_Bootstrap::init();

	// TEMPORARILY DISABLED: Module toggles for feature flags.
	// require_once WPSHADOW_PATH . 'includes/class-wps-module-toggles.php';
	// \WPShadow\CoreSupport\WPSHADOW_Module_Toggles::init();

	// TEMPORARILY DISABLED: Module registry.
	// require_once WPSHADOW_PATH . 'includes/class-wps-module-registry.php';
	// \WPShadow\CoreSupport\WPSHADOW_Module_Registry::init();

	// Load Feature Registry and base feature classes (independent of modules).
	require_once WPSHADOW_PATH . 'includes/features/interface-wps-feature.php';
	require_once WPSHADOW_PATH . 'includes/features/class-wps-feature-abstract.php';
	require_once WPSHADOW_PATH . 'includes/class-wps-feature-registry.php';
	\WPShadow\CoreSupport\WPSHADOW_Feature_Registry::init();

	// Load Ghost Features system for module feature discovery.
	require_once WPSHADOW_PATH . 'includes/class-wps-ghost-features.php';
	require_once WPSHADOW_PATH . 'includes/class-wps-feature-detector.php';
	require_once WPSHADOW_PATH . 'includes/class-wps-features-discovery-widget.php';
	require_once WPSHADOW_PATH . 'includes/ghost-features-catalog.php';
	\WPShadow\CoreSupport\WPSHADOW_Ghost_Features::init();
	\WPShadow\CoreSupport\WPSHADOW_Features_Discovery_Widget::init();

	// TEMPORARILY DISABLED: DRY Hub initializer before loading modules.
	// require_once WPSHADOW_PATH . 'includes/class-wps-module-hub-initializer.php';

	// TEMPORARILY DISABLED: Module loader (manages independent module repositories).
	// require_once WPSHADOW_PATH . 'includes/class-wps-module-loader.php';
	// \WPShadow\CoreSupport\Module_Loader::init();

	// Ghost features registration removed (function no longer exists).
	// TODO: Re-implement ghost features if needed.

	// Load settings API (network + site with overrides).
	require_once WPSHADOW_PATH . 'includes/class-wps-settings.php';
	\WPShadow\CoreSupport\WPSHADOW_Settings::init();
	require_once WPSHADOW_PATH . 'includes/wps-settings-functions.php';

	// Load capability manager.
	require_once WPSHADOW_PATH . 'includes/class-wps-capabilities.php';

	// Load Environment Checker for server capability validation.
	require_once WPSHADOW_PATH . 'includes/class-wps-environment-checker.php';
	\WPShadow\CoreSupport\WPSHADOW_Environment_Checker::init();

	// Load Server Limits Manager for resource monitoring and graceful degradation.
	require_once WPSHADOW_PATH . 'includes/class-wps-server-limits.php';
	\WPShadow\CoreSupport\WPSHADOW_Server_Limits::init();

	// Load Site Health integration.
	require_once WPSHADOW_PATH . 'includes/class-wps-site-health.php';
	\WPShadow\CoreSupport\WPSHADOW_Site_Health::init();

	// Load Activity Logger.
	require_once WPSHADOW_PATH . 'includes/class-wps-activity-logger.php';
	\WPShadow\CoreSupport\WPSHADOW_Activity_Logger::init();

	// Load Performance Monitor for real-time performance tracking.
	require_once WPSHADOW_PATH . 'includes/class-wps-performance-monitor.php';
	\WPShadow\CoreSupport\WPSHADOW_Performance_Monitor::init();

	// Load Achievement Badges system.
	require_once WPSHADOW_PATH . 'includes/class-wps-achievement-badges.php';
	\WPShadow\CoreSupport\WPSHADOW_Achievement_Badges::init();

	// Load Snapshot Manager for site snapshots and rollback.
	require_once WPSHADOW_PATH . 'includes/class-wps-snapshot-manager.php';
	\WPShadow\CoreSupport\WPSHADOW_Snapshot_Manager::init();

	// Load Site Audit for performance, security, and optimization analysis.
	require_once WPSHADOW_PATH . 'includes/class-wps-site-audit.php';
	\WPShadow\CoreSupport\WPSHADOW_Site_Audit::init();

	// Load Hidden Diagnostic API for secure support access.
	require_once WPSHADOW_PATH . 'includes/class-wps-hidden-diagnostic-api.php';
	\WPShadow\CoreSupport\WPSHADOW_Hidden_Diagnostic_API::init();

	// Load Safe Staging Manager for isolated testing environments.
	require_once WPSHADOW_PATH . 'includes/class-wps-staging-manager.php';
	\WPShadow\CoreSupport\WPSHADOW_Staging_Manager::init();

	// Load Backup Verification for recovery drills and integrity testing.
	require_once WPSHADOW_PATH . 'includes/class-wps-backup-verification.php';
	\WPShadow\CoreSupport\WPSHADOW_Backup_Verification::init();

	// Load Emergency Support for critical error surfaces.
	require_once WPSHADOW_PATH . 'includes/class-wps-emergency-support.php';
	\WPShadow\CoreSupport\WPSHADOW_Emergency_Support::init();

	// Load White Screen Auto-Recovery for fatal error handling.
	require_once WPSHADOW_PATH . 'includes/class-wps-white-screen-recovery.php';
	\WPShadow\CoreSupport\WPSHADOW_White_Screen_Recovery::init();

	// Register emergency support admin menu.
	add_action(
		'admin_menu',
		static function (): void {
			add_submenu_page(
				'wpshadow',
				__( 'Emergency Support', 'plugin-wpshadow' ),
				__( 'Emergency', 'plugin-wpshadow' ),
				'manage_options',
				'wps-emergency-support',
				array( '\\WPShadow\\CoreSupport\\WPSHADOW_Emergency_Support', 'render_emergency_page' )
			);
		}
	);

	// Handle recovery actions from emergency dashboard.
	add_action( 'admin_init', array( '\\WPShadow\\CoreSupport\\WPSHADOW_White_Screen_Recovery', 'handle_recovery_actions' ) );

	// Load Site Documentation Manager for blueprint, protected plugins, and export.
	require_once WPSHADOW_PATH . 'includes/class-wps-site-documentation-manager.php';
	\WPShadow\CoreSupport\WPSHADOW_Site_Documentation_Manager::init();

	// Load Update Simulator for safe plugin/theme update testing.
	require_once WPSHADOW_PATH . 'includes/class-wps-update-simulator.php';
	\WPShadow\CoreSupport\WPSHADOW_Update_Simulator::init();

	// Load Guided Walkthroughs for step-by-step task assistance.
	require_once WPSHADOW_PATH . 'includes/class-wps-guided-walkthroughs.php';
	\WPShadow\CoreSupport\WPSHADOW_Guided_Walkthroughs::init();

	// Load Video Walkthroughs for auto-generated video tutorials.
	require_once WPSHADOW_PATH . 'includes/class-wps-video-walkthroughs.php';
	\WPShadow\CoreSupport\WPSHADOW_Video_Walkthroughs::init();

	// Load Magic Link Support for secure time-limited developer access.
	require_once WPSHADOW_PATH . 'includes/class-wps-magic-link-support.php';
	\WPShadow\CoreSupport\WPSHADOW_Magic_Link_Support::init();

	// Load Debug Mode Manager for one-click debug toggles.
	require_once WPSHADOW_PATH . 'includes/class-wps-debug-mode.php';
	\WPShadow\CoreSupport\WPSHADOW_Debug_Mode::init();

	// Load Site Health Integration for scoring and WordPress integration.
	require_once WPSHADOW_PATH . 'includes/class-wps-site-health-integration.php';
	\WPShadow\CoreSupport\WPSHADOW_Site_Health_Integration::init();

	// Load Health Score Dashboard Widget.
	require_once WPSHADOW_PATH . 'includes/class-wps-health-score-widget.php';
	\WPShadow\CoreSupport\WPSHADOW_Health_Score_Widget::init();

	// Load System Report Generator for comprehensive debug information.
	require_once WPSHADOW_PATH . 'includes/class-wps-system-report-generator.php';
	\WPShadow\CoreSupport\WPSHADOW_System_Report_Generator::init();

	// Register AJAX handlers for Diagnostic API.
	add_action(
		'wp_ajax_WPSHADOW_create_diagnostic_token',
		static function (): void {
			check_ajax_referer( 'wp_ajax' );
			if ( ! current_user_can( 'manage_options' ) ) {
				\WPShadow\CoreSupport\WPSHADOW_ajax_permission_denied();
			}
			$name   = \WPShadow\CoreSupport\WPSHADOW_get_post_text( 'name' );
			$reason = \WPShadow\CoreSupport\WPSHADOW_get_post_text( 'reason' );
			$token  = WPSHADOW_Hidden_Diagnostic_API::create_token( $name, $reason );
			\WPShadow\CoreSupport\WPSHADOW_ajax_success( array( 'token' => $token ) );
		}
	);

	add_action(
		'wp_ajax_WPSHADOW_revoke_diagnostic_token',
		static function (): void {
			check_ajax_referer( 'wp_ajax' );
			if ( ! current_user_can( 'manage_options' ) ) {
				\WPShadow\CoreSupport\WPSHADOW_ajax_permission_denied();
			}
			$token  = \WPShadow\CoreSupport\WPSHADOW_get_post_text( 'token' );
			$result = WPSHADOW_Hidden_Diagnostic_API::revoke_token( $token );
			\WPShadow\CoreSupport\WPSHADOW_ajax_success( array( 'revoked' => $result ) );
		}
	);

	// Load license utilities.
	require_once WPSHADOW_PATH . 'includes/class-wps-license.php';
	\WPShadow\CoreSupport\WPSHADOW_License::init();

	// Load registration handler.
	require_once WPSHADOW_PATH . 'includes/class-wps-registration.php';
	\WPShadow\CoreSupport\WPSHADOW_Registration::init();

	// Load dashboard assets on 'init' hook instead of here.
	require_once str_replace( '/', DIRECTORY_SEPARATOR, WPSHADOW_PATH . 'includes/admin/class-wps-dashboard-assets.php' );
	add_action(
		'init',
		__NAMESPACE__ . '\\wpshadow_init_dashboard_assets',
		11
	);

	// Load feature registry for flexible plugin dependencies.
	require_once str_replace( '/', DIRECTORY_SEPARATOR, WPSHADOW_PATH . 'includes/class-wps-settings-cache.php' );
	require_once str_replace( '/', DIRECTORY_SEPARATOR, WPSHADOW_PATH . 'includes/features/interface-wps-feature.php' );
	require_once str_replace( '/', DIRECTORY_SEPARATOR, WPSHADOW_PATH . 'includes/features/class-wps-feature-abstract.php' );
	require_once str_replace( '/', DIRECTORY_SEPARATOR, WPSHADOW_PATH . 'includes/features/class-wps-script-utils.php' );
	
	// Initialize settings cache early.
	\WPShadow\CoreSupport\WPSHADOW_Settings_Cache::init();

	// Load Registry System (Unified Metadata System).
	require_once str_replace( '/', DIRECTORY_SEPARATOR, WPSHADOW_PATH . 'includes/class-wps-feature-registry.php' );
	require_once str_replace( '/', DIRECTORY_SEPARATOR, WPSHADOW_PATH . 'includes/class-wps-widget-groups.php' );
	require_once str_replace( '/', DIRECTORY_SEPARATOR, WPSHADOW_PATH . 'includes/class-wps-widget-registry.php' );
	require_once str_replace( '/', DIRECTORY_SEPARATOR, WPSHADOW_PATH . 'includes/class-wps-dashboard-registry.php' );

	// ========================================================================
	// FREE FEATURES (License Level 1-2)
	// ========================================================================
	// Base classes (shared with Pro)
	require_once str_replace( '/', DIRECTORY_SEPARATOR, WPSHADOW_PATH . 'features/interface-wps-feature.php' );
	require_once str_replace( '/', DIRECTORY_SEPARATOR, WPSHADOW_PATH . 'features/class-wps-feature-abstract.php' );

	// Free features (27 features)
	require_once str_replace( '/', DIRECTORY_SEPARATOR, WPSHADOW_PATH . 'features/class-wps-feature-core-diagnostics.php' );
	require_once str_replace( '/', DIRECTORY_SEPARATOR, WPSHADOW_PATH . 'features/class-wps-feature-asset-version-removal.php' );
	require_once str_replace( '/', DIRECTORY_SEPARATOR, WPSHADOW_PATH . 'features/class-wps-feature-head-cleanup.php' );
	require_once str_replace( '/', DIRECTORY_SEPARATOR, WPSHADOW_PATH . 'features/class-wps-feature-block-cleanup.php' );
	require_once str_replace( '/', DIRECTORY_SEPARATOR, WPSHADOW_PATH . 'features/class-wps-feature-css-class-cleanup.php' );
	require_once str_replace( '/', DIRECTORY_SEPARATOR, WPSHADOW_PATH . 'features/class-wps-feature-plugin-cleanup.php' );
	require_once str_replace( '/', DIRECTORY_SEPARATOR, WPSHADOW_PATH . 'features/class-wps-feature-html-cleanup.php' );
	require_once str_replace( '/', DIRECTORY_SEPARATOR, WPSHADOW_PATH . 'features/class-wps-feature-resource-hints.php' );
	require_once str_replace( '/', DIRECTORY_SEPARATOR, WPSHADOW_PATH . 'features/class-wps-feature-nav-accessibility.php' );
	require_once str_replace( '/', DIRECTORY_SEPARATOR, WPSHADOW_PATH . 'features/class-wps-feature-skiplinks.php' );
	require_once str_replace( '/', DIRECTORY_SEPARATOR, WPSHADOW_PATH . 'features/class-wps-feature-embed-disable.php' );
	require_once str_replace( '/', DIRECTORY_SEPARATOR, WPSHADOW_PATH . 'features/class-wps-feature-jquery-cleanup.php' );
	require_once str_replace( '/', DIRECTORY_SEPARATOR, WPSHADOW_PATH . 'features/class-wps-feature-block-css-cleanup.php' );
	require_once str_replace( '/', DIRECTORY_SEPARATOR, WPSHADOW_PATH . 'features/class-wps-feature-interactivity-cleanup.php' );
	require_once str_replace( '/', DIRECTORY_SEPARATOR, WPSHADOW_PATH . 'features/class-wps-feature-consent-checks.php' );
	require_once str_replace( '/', DIRECTORY_SEPARATOR, WPSHADOW_PATH . 'features/class-wps-feature-iframe-busting.php' );
	require_once str_replace( '/', DIRECTORY_SEPARATOR, WPSHADOW_PATH . 'features/class-wps-feature-hotlink-protection.php' );
	require_once str_replace( '/', DIRECTORY_SEPARATOR, WPSHADOW_PATH . 'features/class-wps-feature-a11y-audit.php' );
	require_once str_replace( '/', DIRECTORY_SEPARATOR, WPSHADOW_PATH . 'features/class-wps-feature-mobile-friendliness.php' );
	require_once str_replace( '/', DIRECTORY_SEPARATOR, WPSHADOW_PATH . 'features/class-wps-feature-tips-coach.php' );
	require_once str_replace( '/', DIRECTORY_SEPARATOR, WPSHADOW_PATH . 'features/class-wps-feature-image-lazy-loading.php' );
	require_once str_replace( '/', DIRECTORY_SEPARATOR, WPSHADOW_PATH . 'features/class-wps-feature-open-graph-previewer.php' );
	require_once str_replace( '/', DIRECTORY_SEPARATOR, WPSHADOW_PATH . 'features/class-wps-feature-broken-link-checker.php' );
	require_once str_replace( '/', DIRECTORY_SEPARATOR, WPSHADOW_PATH . 'features/class-wps-feature-seo-validator.php' );
	require_once str_replace( '/', DIRECTORY_SEPARATOR, WPSHADOW_PATH . 'features/class-wps-feature-favicon-checker.php' );
	require_once str_replace( '/', DIRECTORY_SEPARATOR, WPSHADOW_PATH . 'features/class-wps-feature-color-contrast-checker.php' );

	// ========================================================================
	// PAID FEATURES - Now loaded by wpshadow-pro.php
	// ========================================================================
	// License Level 3+ features moved to wpshadow-pro.php
	// - See _PAID_FEATURES_BACKUP.php for reference
	// - wpshadow-pro.php registers them via wpshadow_register_features hook

	require_once str_replace( '/', DIRECTORY_SEPARATOR, WPSHADOW_PATH . 'includes/wps-feature-functions.php' );
	
	// Initialize Registry System.
	\WPShadow\CoreSupport\WPSHADOW_Feature_Registry::init();
	\WPShadow\CoreSupport\WPSHADOW_Widget_Registry::init();
	\WPShadow\CoreSupport\WPSHADOW_Dashboard_Registry::init();
	
	// Register free features and trigger Pro plugin to register paid features
	add_action( 'wpshadow_register_features', __NAMESPACE__ . '\\WPSHADOW_register_core_features' );
	
	// ====================================================================
	// ALLOW PRO PLUGIN TO REGISTER PAID FEATURES
	// ====================================================================
	// wpshadow-pro.php hooks into this action to register paid features
	// Fire the registration hook to load both free and paid features
	do_action( 'wpshadow_register_features' );
	
	// Initialize Tips Coach feature
	\WPShadow\CoreSupport\WPSHADOW_Feature_Tips_Coach::init();

	// Initialize Open Graph Previewer feature
	\WPShadow\CoreSupport\WPSHADOW_Feature_Open_Graph_Previewer::init();
	// Initialize Broken Link Checker feature
	\WPShadow\CoreSupport\WPSHADOW_Feature_Broken_Link_Checker::init();

	// Load Spoke Base for spoke plugins (Image, Media, etc).
	require_once WPSHADOW_PATH . 'includes/class-wps-spoke-base.php';

	// Load Vault service (canonical implementation in vault-support plugin).
	// Core aliases it for backward compatibility.
	if ( ! class_exists( '\\WPShadow\\VaultSupport\\WPSHADOW_Vault' ) ) {
		// Vault plugin not loaded yet; defer to vault-support plugin.
		// If vault-support is active, it will provide WPSHADOW_Vault.
		// Core will alias it when available.
	}

	// Always load the alias file which will create the alias if vault-support is available.
	if ( file_exists( WPSHADOW_PATH . 'includes/class-wps-vault.php' ) ) {
		require_once WPSHADOW_PATH . 'includes/class-wps-vault.php';
	}

	// Initialize Vault if available (via vault-support's implementation).
	if ( class_exists( '\\WPShadow\\VaultSupport\\WPSHADOW_Vault' ) ) {
		\WPShadow\VaultSupport\WPSHADOW_Vault::init();
	}

	// Load vault size monitoring (real-time alerts) - only if Vault is available.
	if ( class_exists( '\\WPShadow\\VaultSupport\\WPSHADOW_Vault' ) ) {
		require_once WPSHADOW_PATH . 'includes/class-wps-vault-size-monitor.php';
		\WPShadow\CoreSupport\WPSHADOW_Vault_Size_Monitor::init();
	}

	// Load network license broadcaster for multisite (Super Admin push to all sites).
	require_once WPSHADOW_PATH . 'includes/class-wps-network-license.php';
	\WPShadow\CoreSupport\WPSHADOW_Network_License::init();

	// Load module downloader for resilient downloads.
	require_once WPSHADOW_PATH . 'includes/class-wps-module-downloader.php';

	// Load plugin upgrader for install/update flows.
	require_once WPSHADOW_PATH . 'includes/class-wps-plugin-upgrader.php';

	// Load module action handlers for AJAX install/update/activate.
	require_once WPSHADOW_PATH . 'includes/class-wps-module-actions.php';
	\WPShadow\CoreSupport\WPSHADOW_Module_Actions::init();

	// Centralized router guard for disabled modules.
	require_once WPSHADOW_PATH . 'includes/class-wps-router-guard.php';

	// Load tab navigation system.
	require_once WPSHADOW_PATH . 'includes/class-wps-tab-navigation.php';
	require_once WPSHADOW_PATH . 'includes/class-wps-dashboard-widgets.php';
	require_once WPSHADOW_PATH . 'includes/class-wps-dashboard-layout.php';
	require_once WPSHADOW_PATH . 'includes/class-wps-feature-details-page.php';
	\WPShadow\CoreSupport\WPSHADOW_Feature_Details_Page::init();
	require_once WPSHADOW_PATH . 'includes/class-wps-feature-search.php';
	\WPShadow\CoreSupport\WPSHADOW_Feature_Search::init();
	require_once WPSHADOW_PATH . 'includes/admin/class-wps-settings-ajax.php';
	\WPShadow\Admin\WPSHADOW_Settings_Ajax::init();
	require_once WPSHADOW_PATH . 'includes/admin/class-wps-scheduled-tasks-ajax.php';
	\WPShadow\Admin\WPSHADOW_Scheduled_Tasks_Ajax::init();
	require_once WPSHADOW_PATH . 'includes/class-wps-smart-suggestions.php';
	\WPShadow\CoreSupport\WPSHADOW_Smart_Suggestions::init();
	require_once WPSHADOW_PATH . 'includes/wps-capability-helpers.php';

	// Load extracted admin assets, screens, dashboard view, and AJAX handlers.
	require_once WPSHADOW_PATH . 'includes/admin/assets.php';
	require_once WPSHADOW_PATH . 'includes/admin/screens.php';
	require_once WPSHADOW_PATH . 'includes/views/dashboard-renderer.php';
	require_once WPSHADOW_PATH . 'includes/admin/ajax-modules.php';
	// Load CLI commands when WP-CLI present.
	if ( defined( 'WP_CLI' ) && WP_CLI ) {
		require_once WPSHADOW_PATH . 'includes/class-wps-cli.php';
		\WP_CLI::add_command( 'wps modules', '\\WPShadow\\CoreSupport\\WPSHADOW_CLI_Modules' );
		\WP_CLI::add_command( 'wps settings', '\\WPShadow\\CoreSupport\\WPSHADOW_CLI_Settings' );
	}

	// Load notice manager for persistent dismissal.
	require_once WPSHADOW_PATH . 'includes/class-wps-notice-manager.php';
	\WPShadow\CoreSupport\WPSHADOW_Notice_Manager::init();

	// Initialize multisite support if applicable.
	if ( is_multisite() ) {
		add_action( 'network_admin_menu', __NAMESPACE__ . '\\wpshadow_network_admin_menu' );
	}

	// Register admin menu.
	add_action( 'admin_menu', __NAMESPACE__ . '\\wpshadow_admin_menu' );
	add_action( 'admin_head', __NAMESPACE__ . '\\wpshadow_hide_disabled_submenus' );
	add_action( 'admin_init', __NAMESPACE__ . '\wpshadow_guard_disabled_modules' );

	// Fix sidebar menu active state for module navigation (#174).
	add_filter( 'parent_file', __NAMESPACE__ . '\\wpshadow_filter_parent_file', 10 );
	add_filter( 'submenu_file', __NAMESPACE__ . '\\wpshadow_filter_submenu_file', 10 );

	// Handle capability mapping submissions.
	add_action( 'admin_init', __NAMESPACE__ . '\\wpshadow_handle_capabilities_post' );

	// Handle AJAX actions.
	add_action( 'wp_ajax_WPSHADOW_toggle_module', __NAMESPACE__ . '\\WPSHADOW_ajax_toggle_module' );
	add_action( 'wp_ajax_WPSHADOW_install_module', __NAMESPACE__ . '\\WPSHADOW_ajax_install_module' );
	add_action( 'wp_ajax_WPSHADOW_update_module', __NAMESPACE__ . '\\WPSHADOW_ajax_update_module' );
	add_action( 'wp_ajax_WPSHADOW_broadcast_license', __NAMESPACE__ . '\\WPSHADOW_ajax_broadcast_license' );
	add_action( 'wp_ajax_WPSHADOW_save_metabox_state', __NAMESPACE__ . '\\WPSHADOW_ajax_save_metabox_state' );
	add_action( 'wp_ajax_WPSHADOW_save_postbox_order', __NAMESPACE__ . '\\WPSHADOW_ajax_save_postbox_order' );
	add_action( 'wp_ajax_WPSHADOW_save_postbox_state', __NAMESPACE__ . '\\WPSHADOW_ajax_save_postbox_state' );
	add_action( 'wp_ajax_WPSHADOW_save_dashboard_layout', array( 'WPShadow\\WPSHADOW_Dashboard_Layout', 'ajax_save_layout' ) );
	add_action( 'wp_ajax_WPSHADOW_apply_dashboard_layout', array( 'WPShadow\\WPSHADOW_Dashboard_Layout', 'ajax_apply_layout' ) );

	// Admin-post action to force scheduled tasks to run immediately.
	add_action( 'admin_post_WPSHADOW_run_task_now', __NAMESPACE__ . '\WPSHADOW_run_task_now' );

	// Plugin page links and meta.
	add_filter( 'plugin_action_links_' . WPSHADOW_BASENAME, __NAMESPACE__ . '\\wpshadow_plugin_action_links' );
	add_filter( 'plugin_row_meta', __NAMESPACE__ . '\\wpshadow_plugin_row_meta', 10, 2 );

	// Enqueue admin scripts and styles.
	// TODO: Re-implement wpshadow_admin_enqueue if global admin assets are needed.
	// add_action( 'admin_enqueue_scripts', __NAMESPACE__ . '\\wpshadow_admin_enqueue' );

	// Save screen options for dashboard.
	add_filter( 'set-screen-option', __NAMESPACE__ . '\\wpshadow_save_screen_option', 10, 3 );

	// Filter postbox classes to load state from custom keys.
	add_filter( 'postbox_classes_toplevel_page_wpshadow', __NAMESPACE__ . '\\wpshadow_postbox_classes', 10, 2 );
	add_filter( 'get_user_option_meta-box-order_toplevel_page_wpshadow', __NAMESPACE__ . '\\wpshadow_get_metabox_order' );
	add_filter( 'get_user_option_closedpostboxes_toplevel_page_wpshadow', __NAMESPACE__ . '\\wpshadow_get_closed_postboxes' );

	// Register GDPR Personal Data Exporter and Eraser.
	// Moved to Vault module: privacy exporters/erasers are registered
	// by WPShadow\VaultSupport when the module is enabled.
}

/**
 * Register network admin menu for multisite.
 *
 * @return void
 */
function wpshadow_network_admin_menu(): void {
	add_menu_page(
		__( 'WPShadow Dashboard', 'plugin-wpshadow' ),
		__( 'WPShadow', 'plugin-wpshadow' ),
		'manage_network_options',
		'wpshadow',
		__NAMESPACE__ . '\\wpshadow_render_tab_router',
		'dashicons-admin-generic',
		999
	);

	add_submenu_page(
		'wpshadow',
		__( 'WPShadow Dashboard', 'plugin-wpshadow' ),
		__( 'Dashboard', 'plugin-wpshadow' ),
		'manage_network_options',
		'wpshadow',
		__NAMESPACE__ . '\\wpshadow_render_tab_router'
	);

	// TEMPORARILY DISABLED: Dynamically register module submenu items (Vault, Media, etc.).
	// wpshadow_register_module_submenus( 'manage_network_options' );

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
		__( 'WPShadow Dashboard', 'plugin-wpshadow' ),
		__( 'WPShadow', 'plugin-wpshadow' ),
		'manage_options',
		'wpshadow',
		__NAMESPACE__ . '\\wpshadow_render_tab_router',
		'dashicons-admin-generic',
		999
	);

	add_submenu_page(
		'wpshadow',
		__( 'WPShadow Dashboard', 'plugin-wpshadow' ),
		__( 'Dashboard', 'plugin-wpshadow' ),
		'manage_options',
		'wpshadow',
		__NAMESPACE__ . '\\wpshadow_render_tab_router'
	);

	// Add Get Help quick-access submenu (#119).
	add_submenu_page(
		'wpshadow',
		__( 'Get Help', 'plugin-wpshadow' ),
		__( 'Get Help', 'plugin-wpshadow' ),
		'manage_options',
		'wpshadow&tab=help',
		__NAMESPACE__ . '\\wpshadow_render_tab_router'
	);

	// TEMPORARILY DISABLED: Dynamically register module submenu items (Vault, Media, etc.).
	// wpshadow_register_module_submenus( 'manage_options' );

	// Initialize dashboard screen extras (Screen Options, Help) and metaboxes.
	add_action( 'load-toplevel_page_wpshadow', 'WPShadow\\CoreSupport\\wpshadow_setup_dashboard_screen' );
}

/**
 * Register submenu items for active top-level modules (children of wpshadow).
 *
 * @param string $capability Required capability (manage_options or manage_network_options).
 * @return void
 */
function wpshadow_register_module_submenus( string $capability ): void {
	$catalog = \WPShadow\CoreSupport\WPSHADOW_Module_Registry::get_catalog_with_status();
	$modules = array_filter(
		$catalog,
		function ( $m ) {
			// Only include active hub modules that are direct children of wpshadow
			// AND have actual module folders (not just catalog entries).
			if ( 'hub' !== ( $m['type'] ?? '' )
				|| empty( $m['installed'] )
				|| ! empty( $m['requires_hub'] )
			) {
				return false;
			}

			// Verify module folder actually exists.
			$module_id   = sanitize_key( str_replace( '-wpshadow', '', $m['slug'] ?? '' ) );
			$module_path = WPSHADOW_PATH . 'modules/hubs/' . $module_id . '/';

			if ( ! is_dir( $module_path ) ) {
				return false;
			}

			// Always register if installed; pruning will handle visibility.
			return true;
		}
	);

	foreach ( $modules as $module ) {
		$module_id   = sanitize_key( str_replace( '-wpshadow', '', $module['slug'] ?? '' ) );
		$module_name = esc_html( $module['name'] ?? ucfirst( $module_id ) );

		add_submenu_page(
			'wpshadow',
			$module_name,
			$module_name,
			$capability,
			'wpshadow&module=' . $module_id,
			__NAMESPACE__ . '\\wpshadow_render_tab_router'
		);
	}
}

/**
 * TEMPORARILY DISABLED: Prune Support submenus to only allow hub modules (vault, media) and core screens.
 *
 * This removes legacy/experimental submenu entries registered elsewhere.
 *
 * @return void
 */
/*
function wpshadow_prune_submenus(): void {
	// Parent menu slug.
	$parent = 'wpshadow';

	// Allowed submenu slugs under wpshadow (core screens always allowed).
	$allowed = array(
		'wpshadow',         // Dashboard
		'wpshadow-modules', // Modules grid
	);

	// Only allow hub submenus when the module is enabled.
	if ( \WPShadow\CoreSupport\WPSHADOW_Module_Registry::is_enabled( 'vault-wpshadow' ) ) {
		$allowed[] = 'wpshadow&module=vault';
	}
	if ( \WPShadow\CoreSupport\WPSHADOW_Module_Registry::is_enabled( 'media-wpshadow' ) ) {
		$allowed[] = 'wpshadow&module=media';
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
add_action( 'admin_menu', __NAMESPACE__ . '\\wpshadow_prune_submenus', 999 );
*/
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
		wp_die( esc_html__( 'You do not have sufficient permissions to manage capabilities.', 'plugin-wpshadow' ) );
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
		wp_die( esc_html__( 'You do not have sufficient permissions to manage capabilities.', 'plugin-wpshadow' ) );
	}

	$nonce = isset( $_POST['wpshadow_capabilities_nonce'] ) ? wp_unslash( $_POST['wpshadow_capabilities_nonce'] ) : '';
	if ( ! wp_verify_nonce( $nonce, 'wpshadow_capabilities' ) ) {
		wp_die( esc_html__( 'Nonce verification failed. Please try again.', 'plugin-wpshadow' ) );
	}

	$module_slug    = isset( $_POST['wpshadow_module_slug'] ) ? sanitize_key( wp_unslash( $_POST['wpshadow_module_slug'] ) ) : '';
	$capability_key = isset( $_POST['wpshadow_capability_key'] ) ? sanitize_key( wp_unslash( $_POST['wpshadow_capability_key'] ) ) : '';
	$wp_capability  = isset( $_POST['wpshadow_wp_capability'] ) ? sanitize_key( wp_unslash( $_POST['wpshadow_wp_capability'] ) ) : '';

	if ( empty( $module_slug ) || empty( $capability_key ) || empty( $wp_capability ) ) {
		add_settings_error(
			'wpshadow_capabilities',
			'wpshadow_capabilities_invalid',
			esc_html__( 'All fields are required to register a capability mapping.', 'plugin-wpshadow' ),
			'error'
		);
	} else {
		\WPShadow\CoreSupport\WPSHADOW_Capabilities::register_capability( $module_slug, $capability_key, $wp_capability );
		add_settings_error(
			'wpshadow_capabilities',
			'wpshadow_capabilities_saved',
			esc_html__( 'Capability mapping saved.', 'plugin-wpshadow' ),
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
		wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'plugin-wpshadow' ) );
	}

	// Apply centralized router guard (raw module, hub, and spoke checks).
	\WPShadow\CoreSupport\WPSHADOW_Router_Guard::execute();

	$context = \WPShadow\CoreSupport\WPSHADOW_Tab_Navigation::get_current_context();
	$hub     = $context['hub'];
	$spoke   = $context['spoke'];
	$tab     = $context['tab'];
	$level   = $context['level'];

	// Render breadcrumbs (except at Core level, unless on dashboard_settings tab).
	if ( 'core' !== $level || \WPShadow\CoreSupport\WPSHADOW_Tab_Navigation::TAB_DASHBOARD_SETTINGS === $tab ) {
		\WPShadow\CoreSupport\WPSHADOW_Tab_Navigation::render_breadcrumbs( $context );
	}

	// Determine tabs based on level.
	if ( 'spoke' === $level && ! empty( $hub ) && ! empty( $spoke ) ) {
		$tabs        = \WPShadow\CoreSupport\WPSHADOW_Tab_Navigation::get_spoke_tabs( $hub, $spoke );
		$active_tabs = array_merge( $tabs, WPSHADOW_get_hub_tabs_for_spoke( $hub, $spoke ) );
		\WPShadow\CoreSupport\WPSHADOW_Tab_Navigation::render_tabs( $active_tabs, $tab );
	} elseif ( 'hub' === $level && ! empty( $hub ) ) {
		$tabs        = \WPShadow\CoreSupport\WPSHADOW_Tab_Navigation::get_hub_tabs( $hub );
		// TEMPORARILY DISABLED: Spoke tabs from module registry
		// $active_tabs = array_merge( $tabs, WPSHADOW_get_spoke_tabs_for_hub( $hub ) );
		$active_tabs = $tabs; // Only show hub tabs with modules disabled
		\WPShadow\CoreSupport\WPSHADOW_Tab_Navigation::render_tabs( $active_tabs, $tab );
	} else {
		$tabs        = \WPShadow\CoreSupport\WPSHADOW_Tab_Navigation::get_core_tabs();
		// TEMPORARILY DISABLED: Hub tabs from module registry
		// $active_tabs = array_merge( $tabs, WPSHADOW_get_active_hub_tabs() );
		$active_tabs = $tabs; // Only show core tabs with modules disabled
		\WPShadow\CoreSupport\WPSHADOW_Tab_Navigation::render_tabs( $active_tabs, $tab );
	}

	// Route to appropriate content based on level and tab.
	if ( 'spoke' === $level && ! empty( $hub ) && ! empty( $spoke ) ) {
		wpshadow_render_spoke_content( $hub, $spoke, $tab );
	} elseif ( 'hub' === $level && ! empty( $hub ) ) {
		wpshadow_render_hub_content( $hub, $tab );
	} else {
		wpshadow_render_core_content( $tab );
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
function wpshadow_render_core_content( string $tab ): void {
	switch ( $tab ) {
		case 'register':
			require_once WPSHADOW_PATH . 'includes/views/register.php';
			break;
		case 'help':
			wpshadow_render_help_layout();
			break;
		case 'features':
			wpshadow_render_features_page( 'core' );
			break;
		// Collection tab removed - spoke management in Modules tab.
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
		case 'dashboard':
		default:
			// Route to unified dashboard renderer.
			\WPShadow\CoreSupport\wpshadow_render_dashboard();
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
 * TEMPORARILY DISABLED: Get active Hub tabs dynamically based on installed hubs.
 * These functions require the Module Registry which is temporarily disabled.
 *
 * @return array<array{id: string, label: string, icon: string, url: string}>
 */
/*
function WPSHADOW_get_active_hub_tabs(): array {
	$catalog = \WPShadow\CoreSupport\WPSHADOW_Module_Registry::get_catalog_with_status();
	$hubs    = array_filter( $catalog, fn( $m ) => 'hub' === ( $m['type'] ?? '' ) && ! empty( $m['status']['active'] ) );
	$tabs    = array();

	foreach ( $hubs as $hub ) {
		$hub_id = sanitize_key( str_replace( '-wpshadow', '', $hub['id'] ?? '' ) );
		$tabs[] = array(
			'id'    => $hub_id,
			'label' => esc_html( $hub['name'] ?? ucfirst( $hub_id ) ),
			'icon'  => 'dashicons-networking',
			'url'   => \WPShadow\CoreSupport\WPSHADOW_Tab_Navigation::build_hub_url( $hub_id ),
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
function WPSHADOW_get_spoke_tabs_for_hub( string $hub_id ): array {
	$catalog = \WPShadow\CoreSupport\WPSHADOW_Module_Registry::get_catalog_with_status();
	$spokes  = array_filter(
		$catalog,
		fn( $m ) => 'spoke' === ( $m['type'] ?? '' )
			&& ! empty( $m['status']['active'] )
			&& str_starts_with( $m['id'] ?? '', $hub_id )
	);
	$tabs    = array();

	foreach ( $spokes as $spoke ) {
		$full_id  = $spoke['id'] ?? '';
		$spoke_id = sanitize_key( str_replace( $hub_id . '-wpshadow', '', $full_id ) );
		$spoke_id = str_replace( '-', '', $spoke_id );
		$tabs[]   = array(
			'id'    => $spoke_id,
			'label' => esc_html( $spoke['name'] ?? strtoupper( $spoke_id ) ),
			'icon'  => 'dashicons-hammer',
			'url'   => \WPShadow\CoreSupport\WPSHADOW_Tab_Navigation::build_spoke_url( $hub_id, $spoke_id ),
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
/*
function WPSHADOW_get_hub_tabs_for_spoke( string $hub_id, string $spoke_id ): array {
	return WPSHADOW_get_spoke_tabs_for_hub( $hub_id );
}
*/

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
		wp_die( esc_html__( 'You do not have sufficient permissions to manage features.', 'plugin-wpshadow' ) );
	}

	// Enqueue postbox script for widget dragging
	wp_enqueue_script( 'postbox' );
	wp_enqueue_script( 'common' );
	wp_enqueue_style( 'common' );

	$network_scope = is_multisite() && is_network_admin();
	$features      = \WPShadow\CoreSupport\WPSHADOW_Feature_Registry::get_features_by_scope( $level, $hub_id, $spoke_id, $network_scope );

	if ( 'POST' === $_SERVER['REQUEST_METHOD'] && isset( $_POST['wpshadow_features_nonce'] ) ) {
		check_admin_referer( 'wpshadow_save_features', 'wpshadow_features_nonce' );

		$enabled_ids = array();
		if ( isset( $_POST['features'] ) && is_array( $_POST['features'] ) ) {
			foreach ( $_POST['features'] as $feature_id => $flag ) {
				$enabled_ids[] = sanitize_key( (string) $feature_id );
			}
		}

		\WPShadow\CoreSupport\WPSHADOW_Feature_Registry::save_feature_states( array_values( $features ), $enabled_ids, $network_scope );
		$features = \WPShadow\CoreSupport\WPSHADOW_Feature_Registry::get_features_by_scope( $level, $hub_id, $spoke_id, $network_scope );

		add_settings_error(
			'wpshadow_features',
			'wpshadow_features_saved',
			esc_html__( 'Feature settings updated.', 'plugin-wpshadow' ),
			'updated'
		);
	}

	// Register metaboxes for feature widgets BEFORE rendering the view
	wpshadow_register_feature_metaboxes( $features );

	require WPSHADOW_PATH . 'includes/views/features.php';
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
			$grouped_features[ $group ] = array(
				'label'       => $feature['widget_label'] ?? 'General',
				'description' => $feature['widget_description'] ?? 'Features',
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

	if ( empty( $features ) ) {
		echo '<p>' . esc_html__( 'No features in this group.', 'plugin-wpshadow' ) . '</p>';
		return;
	}

	?>
	<table class="wp-list-table widefat fixed striped">
		<tbody>
			<?php foreach ( $features as $feature ) : ?>
				<?php
				$feature_id   = $feature['id'] ?? '';
				$feature_name = $feature['name'] ?? $feature_id;
				$feature_desc = $feature['description'] ?? '';
				$is_enabled   = ! empty( $feature['enabled'] );
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
								<?php printf( esc_html__( 'Enable %s', 'plugin-wpshadow' ), esc_html( $feature_name ) ); ?>
							</span>
						</label>
					</td>
					<td>
						<strong><?php echo esc_html( $feature_name ); ?></strong>
						<?php if ( ! empty( $feature_desc ) ) : ?>
							<p style="margin: 4px 0 0; color: #666;">
								<?php echo esc_html( $feature_desc ); ?>
							</p>
						<?php endif; ?>
					</td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
	<?php
}

/**
 * Render modules view.
 *
 * @return void
 */
function wpshadow_render_modules(): void {
	if ( ! current_user_can( 'manage_options' ) && ! current_user_can( 'manage_network_options' ) ) {
		wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'plugin-wpshadow' ) );
	}

	// Display informational notice about modules being optional.
	\WPShadow\CoreSupport\WPSHADOW_Notice_Manager::render_notice(
		'wpshadow_modules_are_optional',
		wp_kses_post(
			__( '<strong>Modules are optional enhancements.</strong> WPShadow works perfectly as a standalone core with full diagnostics, emergency recovery, backup verification, and documentation management. Install modules only if you need specialized features like media optimization or vault storage.', 'plugin-wpshadow' )
		),
		'info',
		array( 'capability' => 'manage_options' )
	);

	$catalog_modules = \WPShadow\CoreSupport\WPSHADOW_Module_Registry::get_catalog_with_status();
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

	require_once WPSHADOW_PATH . 'includes/views/modules.php';
}

/**
 * Render network settings page.
 *
 * @return void
 */
function wpshadow_render_network_settings(): void {
	if ( ! current_user_can( 'manage_network_options' ) ) {
		wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'plugin-wpshadow' ) );
	}

	// Licenses are site-specific; Network Admin view is read-only.
	\WPShadow\VaultSupport\WPSHADOW_Vault::maybe_handle_settings_submission( true );
	\WPShadow\VaultSupport\WPSHADOW_Vault::maybe_handle_tools_submission( true );
	\WPShadow\VaultSupport\WPSHADOW_Vault::maybe_handle_log_action();

	$license_state = \WPShadow\CoreSupport\WPSHADOW_License::get_state( false );

	require_once WPSHADOW_PATH . 'includes/views/settings.php';
}

/**
 * Render settings page.
 *
 * @return void
 */
function wpshadow_render_settings_page(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'plugin-wpshadow' ) );
	}

	\WPShadow\CoreSupport\WPSHADOW_License::maybe_handle_submission( false );
	\WPShadow\VaultSupport\WPSHADOW_Vault::maybe_handle_settings_submission( false );
	\WPShadow\VaultSupport\WPSHADOW_Vault::maybe_handle_tools_submission( false );
	\WPShadow\VaultSupport\WPSHADOW_Vault::maybe_handle_log_action();

	$license_state = \WPShadow\CoreSupport\WPSHADOW_License::get_state( false );

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
 * Resolve a module's download URL to a direct ZIP when possible.
 *
 * Supports GitHub release pages by converting to the latest asset download URL
 * following a convention of {slug}.zip.
 *
 * @param array $module Module data including 'download_url' and 'slug'.
 * @return string Resolved URL suitable for Plugin_Upgrader::install().
 */
function wpshadow_resolve_download_url( array $module ): string {
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
	$url = (string) apply_filters( 'wpshadow_resolve_download_url', $url, $module );

	return esc_url_raw( $url );
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
	$user_name = $user && $user->exists() ? $user->display_name : __( 'System', 'plugin-wpshadow' );

	\WPShadow\VaultSupport\WPSHADOW_Vault::add_log(
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
		esc_html__( 'Dashboard', 'plugin-wpshadow' )
	);

	$settings_link = '';
	if ( current_user_can( 'manage_options' ) ) {
		$settings_link = sprintf(
			'<a href="%s">%s</a>',
			esc_url( admin_url( 'admin.php?page=wpshadow&WPSHADOW_tab=dashboard_settings' ) ),
			esc_html__( 'Settings', 'plugin-wpshadow' )
		);
	} elseif ( is_multisite() && current_user_can( 'manage_network_options' ) ) {
		$settings_link = sprintf(
			'<a href="%s">%s</a>',
			esc_url( admin_url( 'admin.php?page=wps-core-network-settings', 'network' ) ),
			esc_html__( 'Network Settings', 'plugin-wpshadow' )
		);
	}

	array_unshift( $links, $dashboard_link );
	if ( ! empty( $settings_link ) ) {
		array_unshift( $links, $settings_link );
	}

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

	$docs_link = sprintf(
		'<a href="%s" target="_blank" rel="noopener noreferrer">%s</a>',
		esc_url( 'https://github.com/thisismyurl/plugin-plugin-wpshadow' ),
		esc_html__( 'Documentation', 'plugin-wpshadow' )
	);

	$privacy_link = sprintf(
		'<a href="%s">%s</a>',
		esc_url( 'https://wpshadow.com/privacy' ),
		esc_html__( 'Privacy', 'plugin-wpshadow' )
	);

	$support_link = sprintf(
		'<a href="%s" target="_blank" rel="noopener noreferrer">%s</a>',
		esc_url( 'https://wpshadow.com/support' ),
		esc_html__( 'Support', 'plugin-wpshadow' )
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
function wpshadow_save_screen_option( $status, string $option, $value ) {
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

/**
 * Register the Vault exporter with WordPress Personal Data Export.
 *
 * @param array $exporters Existing exporters.
 * @return array Modified exporters.
 */
// Vault privacy exporters/erasers moved to the Vault module.

// Register activation and deactivation hooks.
register_activation_hook( __FILE__, __NAMESPACE__ . '\\wpshadow_activate' );
register_deactivation_hook( __FILE__, __NAMESPACE__ . '\\wpshadow_deactivate' );

// Initialize the plugin.
add_action( 'plugins_loaded', __NAMESPACE__ . '\\wpshadow_init' );

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
 * - Added AJAX handlers: WPSHADOW_ajax_install_module and WPSHADOW_ajax_update_module
 * - Implement WP_Plugin_Upgrader for direct installation from catalog
 * - Auto-activate installed modules after installation
 * - Support for multisite with network-wide install/update
 * - Permission checks for install_plugins and update_plugins capabilities
 * - Helper function wpshadow_find_plugin_file_by_slug() for plugin location
 * - Cache invalidation and module discovery after install/update
 * - Added actionNonce for install/update AJAX requests
 * - Dashboard provides Install button for available modules
 * - Dashboard provides Update button for modules with updates
 * - Localized i18n strings for button labels and status messages
 * - Files Modified:
 *   - core-wpshadow.php: Added install/update handlers + helpers
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
 * - Created WPSHADOW_Module_Registry class for action-based module discovery
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
 *   - core-wpshadow.php: Added menu structure, AJAX handlers
 * - includes/class-wps-module-registry.php: Full registry implementation
 *   - includes/views/dashboard.php: Dashboard template with module cards
 *   - assets/css/admin.css: Extended with toggle, grid, and loading styles
 *   - assets/js/admin.js: Dashboard controller with AJAX and filtering
 *
 * - Completed Issue #24: Internationalization Baseline
 * - Created languages/ directory with placeholder POT file
 * - Verified all user-facing strings use gettext functions (__(), _e(), esc_html__())
 * - Confirmed text domain 'plugin-wpshadow' loads via load_plugin_textdomain()
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
	$enabled   = (bool) get_option( 'wpshadow_module_discovery_enabled', true );
	$frequency = get_option( 'wpshadow_module_discovery_frequency', 'on-demand' );
	?>
	<form method="post" class="wps-settings-form" data-settings-group="module_registry" style="max-width: 600px;">
		<?php wp_nonce_field( 'wpshadow_settings_module_registry', 'wpshadow_settings_nonce' ); ?>
		<table class="form-table" role="presentation">
			<tbody>
				<tr>
					<th scope="row"><label for="wpshadow_module_discovery_enabled"><?php esc_html_e( 'Auto-Discovery', 'plugin-wpshadow' ); ?></label></th>
					<td>
						<input type="checkbox" id="wpshadow_module_discovery_enabled" name="wpshadow_module_discovery_enabled" value="1" <?php checked( $enabled, true ); ?> />
						<label for="wpshadow_module_discovery_enabled"><?php esc_html_e( 'Automatically discover modules from installed plugins', 'plugin-wpshadow' ); ?></label>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="wpshadow_module_discovery_frequency"><?php esc_html_e( 'Discovery Frequency', 'plugin-wpshadow' ); ?></label></th>
					<td>
						<select id="wpshadow_module_discovery_frequency" name="wpshadow_module_discovery_frequency">
							<option value="on-demand" <?php selected( $frequency, 'on-demand' ); ?>><?php esc_html_e( 'On-Demand (Manual)', 'plugin-wpshadow' ); ?></option>
							<option value="daily" <?php selected( $frequency, 'daily' ); ?>><?php esc_html_e( 'Daily', 'plugin-wpshadow' ); ?></option>
							<option value="weekly" <?php selected( $frequency, 'weekly' ); ?>><?php esc_html_e( 'Weekly', 'plugin-wpshadow' ); ?></option>
						</select>
						<p class="description"><?php esc_html_e( 'How often the module catalog should refresh.', 'plugin-wpshadow' ); ?></p>
					</td>
				</tr>
			</tbody>
		</table>
		<div class="wps-settings-save-status" style="margin-top: 10px; font-size: 13px; color: #666;"></div>
	</form>
	<hr style="margin-top: 20px;">
	<p>
		<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?action=WPSHADOW_rescan_modules' ), 'wpshadow_rescan_modules' ) ); ?>" class="button"><?php esc_html_e( 'Rescan Modules Now', 'plugin-wpshadow' ); ?></a>
	</p>
	<?php
}

/**
 * Render Capability Mapping settings widget.
 *
 * @return void
 */
function render_settings_capabilities(): void {
	$dashboard_role = get_option( 'wpshadow_capability_dashboard_role', 'manage_options' );
	$install_roles  = (array) get_option( 'wpshadow_capability_install_roles', array( 'manage_options' ) );
	$update_roles   = (array) get_option( 'wpshadow_capability_update_roles', array( 'manage_options' ) );
	?>
	<form method="post" class="wps-settings-form" data-settings-group="capabilities" style="max-width: 600px;">
		<?php wp_nonce_field( 'wpshadow_settings_capabilities', 'wpshadow_settings_nonce' ); ?>
		<table class="form-table" role="presentation">
			<tbody>
				<tr>
					<th scope="row"><label for="wpshadow_capability_dashboard_role"><?php esc_html_e( 'Dashboard Access', 'plugin-wpshadow' ); ?></label></th>
					<td>
						<select id="wpshadow_capability_dashboard_role" name="wpshadow_capability_dashboard_role">
							<option value="manage_options" <?php selected( $dashboard_role, 'manage_options' ); ?>><?php esc_html_e( 'Admin (manage_options)', 'plugin-wpshadow' ); ?></option>
							<option value="manage_network_options" <?php selected( $dashboard_role, 'manage_network_options' ); ?>><?php esc_html_e( 'Super Admin (manage_network_options)', 'plugin-wpshadow' ); ?></option>
						</select>
						<p class="description"><?php esc_html_e( 'Minimum capability to access the WPShadow dashboard.', 'plugin-wpshadow' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Install Permissions', 'plugin-wpshadow' ); ?></th>
					<td>
						<label><input type="checkbox" name="wpshadow_capability_install_roles[]" value="manage_options" <?php checked( in_array( 'manage_options', $install_roles, true ) ); ?> /> <?php esc_html_e( 'Admin', 'plugin-wpshadow' ); ?></label><br/>
						<label><input type="checkbox" name="wpshadow_capability_install_roles[]" value="manage_network_options" <?php checked( in_array( 'manage_network_options', $install_roles, true ) ); ?> /> <?php esc_html_e( 'Super Admin', 'plugin-wpshadow' ); ?></label><br/>
						<p class="description"><?php esc_html_e( 'Which roles can install modules.', 'plugin-wpshadow' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Update Permissions', 'plugin-wpshadow' ); ?></th>
					<td>
						<label><input type="checkbox" name="wpshadow_capability_update_roles[]" value="manage_options" <?php checked( in_array( 'manage_options', $update_roles, true ) ); ?> /> <?php esc_html_e( 'Admin', 'plugin-wpshadow' ); ?></label><br/>
						<label><input type="checkbox" name="wpshadow_capability_update_roles[]" value="manage_network_options" <?php checked( in_array( 'manage_network_options', $update_roles, true ) ); ?> /> <?php esc_html_e( 'Super Admin', 'plugin-wpshadow' ); ?></label><br/>
						<p class="description"><?php esc_html_e( 'Which roles can update modules.', 'plugin-wpshadow' ); ?></p>
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
	$default_cols   = (int) get_option( 'wpshadow_dashboard_default_columns', 2 );
	$sticky_widgets = (array) get_option( 'wpshadow_dashboard_sticky_widgets', array() );
	$widget_sorting = get_option( 'wpshadow_dashboard_widget_sorting', 'drag-order' );
	?>
	<form method="post" class="wps-settings-form" data-settings-group="dashboard" style="max-width: 600px;">
		<?php wp_nonce_field( 'wpshadow_settings_dashboard', 'wpshadow_settings_nonce' ); ?>
		<table class="form-table" role="presentation">
			<tbody>
				<tr>
					<th scope="row"><?php esc_html_e( 'Default Column Layout', 'plugin-wpshadow' ); ?></th>
					<td>
						<label><input type="radio" name="wpshadow_dashboard_default_columns" value="1" <?php checked( $default_cols, 1 ); ?> /> <?php esc_html_e( '1 Column', 'plugin-wpshadow' ); ?></label><br/>
						<label><input type="radio" name="wpshadow_dashboard_default_columns" value="2" <?php checked( $default_cols, 2 ); ?> /> <?php esc_html_e( '2 Columns', 'plugin-wpshadow' ); ?></label><br/>
						<p class="description"><?php esc_html_e( 'Default dashboard layout for new users.', 'plugin-wpshadow' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Sticky Widgets', 'plugin-wpshadow' ); ?></th>
					<td>
						<label><input type="checkbox" name="wpshadow_dashboard_sticky_widgets[]" value="wpshadow_quick_actions" <?php checked( in_array( 'wpshadow_quick_actions', $sticky_widgets, true ) ); ?> /> <?php esc_html_e( 'Always show Quick Actions', 'plugin-wpshadow' ); ?></label><br/>
						<label><input type="checkbox" name="wpshadow_dashboard_sticky_widgets[]" value="wpshadow_modules" <?php checked( in_array( 'wpshadow_modules', $sticky_widgets, true ) ); ?> /> <?php esc_html_e( 'Always show Modules', 'plugin-wpshadow' ); ?></label><br/>
						<p class="description"><?php esc_html_e( 'Widgets that cannot be hidden by users.', 'plugin-wpshadow' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="wpshadow_dashboard_widget_sorting"><?php esc_html_e( 'Widget Sorting', 'plugin-wpshadow' ); ?></label></th>
					<td>
						<select id="wpshadow_dashboard_widget_sorting" name="wpshadow_dashboard_widget_sorting">
							<option value="drag-order" <?php selected( $widget_sorting, 'drag-order' ); ?>><?php esc_html_e( 'Allow Drag & Drop', 'plugin-wpshadow' ); ?></option>
							<option value="locked" <?php selected( $widget_sorting, 'locked' ); ?>><?php esc_html_e( 'Locked (Fixed Order)', 'plugin-wpshadow' ); ?></option>
						</select>
						<p class="description"><?php esc_html_e( 'Allow users to rearrange dashboard widgets.', 'plugin-wpshadow' ); ?></p>
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
	$license_key    = get_option( 'wpshadow_license_key', '' );
	$is_licensed    = ! empty( $license_key );
	$auto_update    = (array) get_option( 'wpshadow_license_auto_update_types', array( 'minor', 'patch' ) );
	$update_channel = get_option( 'wpshadow_license_update_channel', 'stable' );
	?>
	<form method="post" class="wps-settings-form" data-settings-group="license" style="max-width: 600px;">
		<?php wp_nonce_field( 'wpshadow_settings_license', 'wpshadow_settings_nonce' ); ?>
		<table class="form-table" role="presentation">
			<tbody>
				<tr>
					<th scope="row"><?php esc_html_e( 'License Status', 'plugin-wpshadow' ); ?></th>
					<td>
						<p><?php echo $is_licensed ? '<span style="color: green;">✓ ' . esc_html__( 'Licensed', 'plugin-wpshadow' ) . '</span>' : '<span style="color: #999;">' . esc_html__( 'Not Licensed', 'plugin-wpshadow' ) . '</span>'; ?></p>
						<p class="description"><?php esc_html_e( 'Updates are pulled from GitHub releases.', 'plugin-wpshadow' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="wpshadow_license_key"><?php esc_html_e( 'License Key', 'plugin-wpshadow' ); ?></label></th>
					<td>
						<input type="password" id="wpshadow_license_key" name="wpshadow_license_key" value="<?php echo esc_attr( $license_key ); ?>" placeholder="<?php esc_attr_e( 'Enter license key', 'plugin-wpshadow' ); ?>" style="width: 100%; max-width: 300px;" />
						<p class="description"><?php esc_html_e( 'Masked for security. Leave empty to disable licensing.', 'plugin-wpshadow' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Auto-Update', 'plugin-wpshadow' ); ?></th>
					<td>
						<label><input type="checkbox" name="wpshadow_license_auto_update_types[]" value="major" <?php checked( in_array( 'major', $auto_update, true ) ); ?> /> <?php esc_html_e( 'Major Versions', 'plugin-wpshadow' ); ?></label><br/>
						<label><input type="checkbox" name="wpshadow_license_auto_update_types[]" value="minor" <?php checked( in_array( 'minor', $auto_update, true ) ); ?> /> <?php esc_html_e( 'Minor Versions', 'plugin-wpshadow' ); ?></label><br/>
						<label><input type="checkbox" name="wpshadow_license_auto_update_types[]" value="patch" <?php checked( in_array( 'patch', $auto_update, true ) ); ?> /> <?php esc_html_e( 'Patch Updates', 'plugin-wpshadow' ); ?></label><br/>
						<p class="description"><?php esc_html_e( 'Which update types to install automatically.', 'plugin-wpshadow' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="wpshadow_license_update_channel"><?php esc_html_e( 'Update Channel', 'plugin-wpshadow' ); ?></label></th>
					<td>
						<select id="wpshadow_license_update_channel" name="wpshadow_license_update_channel">
							<option value="stable" <?php selected( $update_channel, 'stable' ); ?>><?php esc_html_e( 'Stable', 'plugin-wpshadow' ); ?></option>
							<option value="beta" <?php selected( $update_channel, 'beta' ); ?>><?php esc_html_e( 'Beta', 'plugin-wpshadow' ); ?></option>
							<option value="dev" <?php selected( $update_channel, 'dev' ); ?>><?php esc_html_e( 'Development', 'plugin-wpshadow' ); ?></option>
						</select>
						<p class="description"><?php esc_html_e( 'Release channel for updates.', 'plugin-wpshadow' ); ?></p>
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
	$log_retention       = (int) get_option( 'wpshadow_privacy_log_retention_days', 90 );
	$auto_delete_enabled = (bool) get_option( 'wpshadow_privacy_auto_delete_enabled', false );
	$auto_delete_days    = (int) get_option( 'wpshadow_privacy_auto_delete_days', 90 );
	$audit_level         = get_option( 'wpshadow_privacy_audit_logging_level', 'standard' );
	$export_format       = get_option( 'wpshadow_privacy_export_format', 'json' );
	$contrib_see_user    = (bool) get_option( 'wpshadow_privacy_contributors_see_user_activity', false );
	$editor_see_admin    = (bool) get_option( 'wpshadow_privacy_editors_see_admin_activity', false );
	$diagnostic_logging  = (bool) get_option( 'wpshadow_diagnostic_logging_enabled', false );
	?>
	<form method="post" class="wps-settings-form" data-settings-group="privacy" style="max-width: 600px;">
		<?php wp_nonce_field( 'wpshadow_settings_privacy', 'wpshadow_settings_nonce' ); ?>
		<table class="form-table" role="presentation">
			<tbody>
				<tr>
					<th scope="row"><label for="wpshadow_privacy_log_retention_days"><?php esc_html_e( 'Activity Log Retention', 'plugin-wpshadow' ); ?></label></th>
					<td>
						<input type="number" id="wpshadow_privacy_log_retention_days" name="wpshadow_privacy_log_retention_days" value="<?php echo esc_attr( $log_retention ); ?>" min="1" max="3650" /> <?php esc_html_e( 'days', 'plugin-wpshadow' ); ?>
						<p class="description"><?php esc_html_e( 'How long to keep activity logs.', 'plugin-wpshadow' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Auto-Delete Old Logs', 'plugin-wpshadow' ); ?></th>
					<td>
						<label><input type="checkbox" name="wpshadow_privacy_auto_delete_enabled" value="1" <?php checked( $auto_delete_enabled, true ); ?> /> <?php esc_html_e( 'Automatically delete logs older than', 'plugin-wpshadow' ); ?></label>
						<input type="number" name="wpshadow_privacy_auto_delete_days" value="<?php echo esc_attr( $auto_delete_days ); ?>" min="1" max="3650" style="width: 80px;" /> <?php esc_html_e( 'days', 'plugin-wpshadow' ); ?>
						<p class="description"><?php esc_html_e( 'Clean up old activity records automatically.', 'plugin-wpshadow' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="wpshadow_privacy_audit_logging_level"><?php esc_html_e( 'Audit Logging Level', 'plugin-wpshadow' ); ?></label></th>
					<td>
						<select id="wpshadow_privacy_audit_logging_level" name="wpshadow_privacy_audit_logging_level">
							<option value="minimal" <?php selected( $audit_level, 'minimal' ); ?>><?php esc_html_e( 'Minimal', 'plugin-wpshadow' ); ?></option>
							<option value="standard" <?php selected( $audit_level, 'standard' ); ?>><?php esc_html_e( 'Standard', 'plugin-wpshadow' ); ?></option>
							<option value="verbose" <?php selected( $audit_level, 'verbose' ); ?>><?php esc_html_e( 'Verbose', 'plugin-wpshadow' ); ?></option>
						</select>
						<p class="description"><?php esc_html_e( 'How detailed the activity logs should be.', 'plugin-wpshadow' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Diagnostic Logging', 'plugin-wpshadow' ); ?></th>
					<td>
						<label><input type="checkbox" name="wpshadow_diagnostic_logging_enabled" value="1" <?php checked( $diagnostic_logging, true ); ?> /> <?php esc_html_e( 'Enable diagnostic logging for support', 'plugin-wpshadow' ); ?></label>
						<p class="description"><?php esc_html_e( 'Log environment checks and resource usage for troubleshooting. Recommended for debugging performance issues.', 'plugin-wpshadow' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="wpshadow_privacy_export_format"><?php esc_html_e( 'GDPR Export Format', 'plugin-wpshadow' ); ?></label></th>
					<td>
						<select id="wpshadow_privacy_export_format" name="wpshadow_privacy_export_format">
							<option value="json" <?php selected( $export_format, 'json' ); ?>><?php esc_html_e( 'JSON', 'plugin-wpshadow' ); ?></option>
							<option value="csv" <?php selected( $export_format, 'csv' ); ?>><?php esc_html_e( 'CSV', 'plugin-wpshadow' ); ?></option>
							<option value="zip" <?php selected( $export_format, 'zip' ); ?>><?php esc_html_e( 'ZIP Archive', 'plugin-wpshadow' ); ?></option>
						</select>
						<p class="description"><?php esc_html_e( 'Format for data exports (privacy requests).', 'plugin-wpshadow' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Activity Visibility', 'plugin-wpshadow' ); ?></th>
					<td>
						<label><input type="checkbox" name="wpshadow_privacy_contributors_see_user_activity" value="1" <?php checked( $contrib_see_user, true ); ?> /> <?php esc_html_e( 'Contributors can view other user activity', 'plugin-wpshadow' ); ?></label><br/>
						<label><input type="checkbox" name="wpshadow_privacy_editors_see_admin_activity" value="1" <?php checked( $editor_see_admin, true ); ?> /> <?php esc_html_e( 'Editors can view admin activity', 'plugin-wpshadow' ); ?></label><br/>
						<p class="description"><?php esc_html_e( 'Control who can see activity logs from other roles.', 'plugin-wpshadow' ); ?></p>
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
$feature = \WPShadow\CoreSupport\WPSHADOW_Feature_Registry::get_feature( 'database-cleanup' );

if ( ! $feature ) {
echo '<p>' . esc_html__( 'Database cleanup feature is not available.', 'plugin-wpshadow' ) . '</p>';
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
$next_run = wp_next_scheduled( 'wpshadow_database_cleanup' );
$next_run_text = $next_run ? wp_date( 'F j, Y g:i A', $next_run ) : __( 'Not scheduled', 'plugin-wpshadow' );

// Get last cleanup from activity log if available
$last_cleanup = get_option( 'wpshadow_last_database_cleanup', 0 );
$last_cleanup_text = $last_cleanup ? wp_date( 'F j, Y g:i A', $last_cleanup ) : __( 'Never', 'plugin-wpshadow' );

?>
<form method="post" class="wps-settings-form" data-settings-group="database_cleanup" style="max-width: 600px;">
<?php wp_nonce_field( 'wpshadow_settings_database_cleanup', 'wpshadow_settings_nonce' ); ?>
<table class="form-table" role="presentation">
<tbody>
<tr>
<th scope="row"><?php esc_html_e( 'Automatic Cleanup', 'plugin-wpshadow' ); ?></th>
<td>
<label>
<input type="checkbox" name="wpshadow_database_cleanup_enabled" value="1" <?php checked( $enabled, true ); ?> />
<?php esc_html_e( 'Enable automatic database cleanup', 'plugin-wpshadow' ); ?>
</label>
<p class="description">
<?php esc_html_e( 'Automatically clean up database overhead on a scheduled basis.', 'plugin-wpshadow' ); ?>
</p>
</td>
</tr>

<tr>
<th scope="row"><label for="wpshadow_cleanup_frequency"><?php esc_html_e( 'Schedule', 'plugin-wpshadow' ); ?></label></th>
<td>
<select id="wpshadow_cleanup_frequency" name="wpshadow_cleanup_frequency">
<option value="daily" <?php selected( $cleanup_frequency, 'daily' ); ?>><?php esc_html_e( 'Daily', 'plugin-wpshadow' ); ?></option>
<option value="weekly" <?php selected( $cleanup_frequency, 'weekly' ); ?>><?php esc_html_e( 'Weekly', 'plugin-wpshadow' ); ?></option>
<option value="monthly" <?php selected( $cleanup_frequency, 'monthly' ); ?>><?php esc_html_e( 'Monthly', 'plugin-wpshadow' ); ?></option>
</select>
<p class="description">
<?php esc_html_e( 'How often to run automatic cleanup.', 'plugin-wpshadow' ); ?>
</p>
</td>
</tr>

<tr>
<th scope="row"><?php esc_html_e( 'Cleanup Status', 'plugin-wpshadow' ); ?></th>
<td>
<p><strong><?php esc_html_e( 'Last Cleanup:', 'plugin-wpshadow' ); ?></strong> <?php echo esc_html( $last_cleanup_text ); ?></p>
<p><strong><?php esc_html_e( 'Next Scheduled:', 'plugin-wpshadow' ); ?></strong> <?php echo esc_html( $next_run_text ); ?></p>
</td>
</tr>

<tr>
<th scope="row"><?php esc_html_e( 'Cleanup Options', 'plugin-wpshadow' ); ?></th>
<td>
<label>
<input type="checkbox" name="wpshadow_cleanup_options[cleanup_revisions]" value="1" <?php checked( $cleanup_options['cleanup_revisions'], true ); ?> />
<?php esc_html_e( 'Clean up post revisions', 'plugin-wpshadow' ); ?>
</label>
<br/>
<label style="margin-left: 24px;">
<?php esc_html_e( 'Keep', 'plugin-wpshadow' ); ?>
<input type="number" name="wpshadow_cleanup_options[keep_revisions]" value="<?php echo esc_attr( $cleanup_options['keep_revisions'] ); ?>" min="0" max="50" style="width: 60px;" />
<?php esc_html_e( 'most recent revisions per post', 'plugin-wpshadow' ); ?>
</label>
<br/><br/>

<label>
<input type="checkbox" name="wpshadow_cleanup_options[cleanup_transients]" value="1" <?php checked( $cleanup_options['cleanup_transients'], true ); ?> />
<?php esc_html_e( 'Clean up expired transients', 'plugin-wpshadow' ); ?>
</label>
<br/>

<label>
<input type="checkbox" name="wpshadow_cleanup_options[cleanup_spam]" value="1" <?php checked( $cleanup_options['cleanup_spam'], true ); ?> />
<?php esc_html_e( 'Clean up spam comments', 'plugin-wpshadow' ); ?>
</label>
<br/>

<label>
<input type="checkbox" name="wpshadow_cleanup_options[cleanup_orphaned_meta]" value="1" <?php checked( $cleanup_options['cleanup_orphaned_meta'], true ); ?> />
<?php esc_html_e( 'Clean up orphaned post metadata', 'plugin-wpshadow' ); ?>
</label>
<br/>

<label>
<input type="checkbox" name="wpshadow_cleanup_options[cleanup_auto_drafts]" value="1" <?php checked( $cleanup_options['cleanup_auto_drafts'], true ); ?> />
<?php esc_html_e( 'Clean up old auto-drafts', 'plugin-wpshadow' ); ?>
</label>
<br/>

<label>
<input type="checkbox" name="wpshadow_cleanup_options[optimize_tables]" value="1" <?php checked( $cleanup_options['optimize_tables'], true ); ?> />
<?php esc_html_e( 'Optimize database tables', 'plugin-wpshadow' ); ?>
</label>

<p class="description">
<?php esc_html_e( 'Select which cleanup tasks to perform automatically.', 'plugin-wpshadow' ); ?>
</p>
</td>
</tr>

<tr>
<th scope="row"><?php esc_html_e( 'Manual Cleanup', 'plugin-wpshadow' ); ?></th>
<td>
<?php
$cleanup_url = wp_nonce_url(
admin_url( 'admin-post.php?action=WPSHADOW_run_database_cleanup' ),
'wpshadow_run_database_cleanup'
);
?>
<a href="<?php echo esc_url( $cleanup_url ); ?>" class="button button-secondary">
<?php esc_html_e( 'Run Cleanup Now', 'plugin-wpshadow' ); ?>
</a>
<p class="description">
<?php esc_html_e( 'Manually trigger a database cleanup immediately.', 'plugin-wpshadow' ); ?>
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
// Placeholder - implementation to be added
