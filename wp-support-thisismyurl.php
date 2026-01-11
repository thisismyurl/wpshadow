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
 * @package WPS_WP_SUPPORT_THISISMYURL
 */

declare(strict_types=1);

namespace WPS\CoreSupport;

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
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
define( 'wp_support_PATH', plugin_dir_path( __FILE__ ) );
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
		error_log( "parent_file filter: setting submenu_file to wp-support&module={$module}" );
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
	error_log( sprintf( 'submenu_file filter: original=%s, module=%s, returning=%s', $submenu_file, $module, $target ) );
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

	// Create vault directory with proper permissions.
	wp_support_setup_vault();

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
function wp_support_setup_vault(): bool {
	$upload_dir = wp_upload_dir();

	// Get or generate vault directory name (hidden with random suffix).
	$vault_dirname = get_option( 'wps_vault_dirname' );
	if ( empty( $vault_dirname ) ) {
		// Generate random directory name (e.g., .vault_a1b2c3d4e5f6).
		$random_suffix = bin2hex( random_bytes( 6 ) );
		$vault_dirname = '.vault_' . $random_suffix;
		update_option( 'wps_vault_dirname', $vault_dirname );
	}

	$vault_path = $upload_dir['basedir'] . '/' . $vault_dirname;

	// Create vault directory if it doesn't exist.
	if ( ! file_exists( $vault_path ) ) {
		if ( ! wp_mkdir_p( $vault_path ) ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			error_log( 'WPS Core: Failed to create vault directory at ' . $vault_path );
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
	wp_support_setup_encryption_keys();

	return true;
}

/**
 * Setup encryption keys for vault files.
 * Checks wp-config for WPS_VAULT_KEY; generates if missing.
 *
 * @return bool True if keys are available, false otherwise.
 */
function wp_support_setup_encryption_keys(): bool {
	// If wp-config defines the key, use it.
	if ( defined( 'WPS_VAULT_KEY' ) && WPS_VAULT_KEY ) {
		return true;
	}

	// If not in wp-config, check if stored in options (for backward compatibility).
	$stored_key = get_option( 'wps_vault_encryption_key' );
	if ( ! empty( $stored_key ) ) {
		return true;
	}

	// For production, keys MUST be in wp-config.
	// For development, auto-generate and store (with warning).
	if ( 'production' === wp_get_environment_type() ) {
		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
		error_log( 'WPS Core: Encryption enabled but WPS_VAULT_KEY not defined in wp-config.php. Define it for production use.' );
		return false;
	}

	// Auto-generate for development.
	$new_key = bin2hex( random_bytes( 32 ) ); // 256-bit key.
	update_option( 'wps_vault_encryption_key', $new_key );

	// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
	error_log( 'WPS Core: Generated temporary encryption key. For production, add this to wp-config.php: define( "WPS_VAULT_KEY", "' . $new_key . '" );' );

	return true;
}

/**
 * Get the encryption key for vault operations.
 *
 * @return string|null Encryption key, or null if not available.
 */
function wp_support_get_vault_key(): ?string {
	if ( defined( 'WPS_VAULT_KEY' ) && WPS_VAULT_KEY ) {
		return WPS_VAULT_KEY;
	}

	$stored_key = get_option( 'wps_vault_encryption_key' );
	return ! empty( $stored_key ) ? $stored_key : null;
}

/**
 * Check if encryption is supported and enabled.
 *
 * @return bool True if openssl is available, false otherwise.
 */
function wp_support_encryption_supported(): bool {
	return extension_loaded( 'openssl' );
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

	// Load module bootstrap for child plugin installation and activation.
	require_once wp_support_PATH . 'includes/class-wps-module-bootstrap.php';
	WPS_Module_Bootstrap::init();

	// Load module toggles for feature flags.
	require_once wp_support_PATH . 'includes/class-wps-module-toggles.php';
	WPS_Module_Toggles::init();

	// Load module registry.
	require_once wp_support_PATH . 'includes/class-wps-module-registry.php';
	WPS_Module_Registry::init();

	// Load DRY Hub initializer before loading modules.
	require_once wp_support_PATH . 'includes/class-wps-module-hub-initializer.php';

	// Load module loader (manages independent module repositories).
	require_once wp_support_PATH . 'includes/class-wps-module-loader.php';
	\WPS\Core\Module_Loader::init();

	// Load settings API (network + site with overrides).
	require_once wp_support_PATH . 'includes/class-wps-settings.php';
	WPS_Settings::init();
	require_once wp_support_PATH . 'includes/wps-settings-functions.php';

	// Load capability manager.
	require_once wp_support_PATH . 'includes/class-wps-capabilities.php';

	// Load Site Health integration.
	require_once wp_support_PATH . 'includes/class-wps-site-health.php';
	WPS_Site_Health::init();

	// Load Activity Logger.
	require_once wp_support_PATH . 'includes/class-wps-activity-logger.php';
	WPS_Activity_Logger::init();

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

	// Register emergency support admin menu.
	add_action( 'admin_menu', static function (): void {
		add_submenu_page(
			'wp-support',
			__( 'Emergency Support', 'plugin-wp-support-thisismyurl' ),
			__( 'Emergency', 'plugin-wp-support-thisismyurl' ),
			'manage_options',
			'wps-emergency-support',
			array( '\\WPS\\CoreSupport\\WPS_Emergency_Support', 'render_emergency_page' )
		);
	} );

	// Load Site Documentation Manager for blueprint, protected plugins, and export.
	require_once wp_support_PATH . 'includes/class-wps-site-documentation-manager.php';
	WPS_Site_Documentation_Manager::init();

	// Load Update Simulator for safe plugin/theme update testing.
	require_once wp_support_PATH . 'includes/class-wps-update-simulator.php';
	WPS_Update_Simulator::init();

	// Load Guided Walkthroughs for step-by-step task assistance.
	require_once wp_support_PATH . 'includes/class-wps-guided-walkthroughs.php';
	WPS_Guided_Walkthroughs::init();

	// Register AJAX handlers for Diagnostic API.
	add_action( 'wp_ajax_wps_create_diagnostic_token', static function (): void {
		check_ajax_referer( 'wp_ajax' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'Insufficient permissions', 'plugin-wp-support-thisismyurl' ) );
		}
		$name  = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
		$reason = isset( $_POST['reason'] ) ? sanitize_text_field( wp_unslash( $_POST['reason'] ) ) : '';
		$token = WPS_Hidden_Diagnostic_API::create_token( $name, $reason );
		wp_send_json_success( array( 'token' => $token ) );
	} );

	add_action( 'wp_ajax_wps_revoke_diagnostic_token', static function (): void {
		check_ajax_referer( 'wp_ajax' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'Insufficient permissions', 'plugin-wp-support-thisismyurl' ) );
		}
		$token = isset( $_POST['token'] ) ? sanitize_text_field( wp_unslash( $_POST['token'] ) ) : '';
		$result = WPS_Hidden_Diagnostic_API::revoke_token( $token );
		wp_send_json_success( array( 'revoked' => $result ) );
	} );

	// Load license utilities.
	require_once wp_support_PATH . 'includes/class-wps-license.php';
	WPS_License::init();

	// Load feature registry for flexible plugin dependencies.
	require_once wp_support_PATH . 'includes/class-wps-feature-registry.php';
	require_once wp_support_PATH . 'includes/wps-feature-functions.php';
	WPS_Feature_Registry::init();

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

	// Load vault size monitoring (real-time alerts).
	require_once wp_support_PATH . 'includes/class-wps-vault-size-monitor.php';
	WPS_Vault_Size_Monitor::init();

	// Load network license broadcaster for multisite (Super Admin push to all sites).
	require_once wp_support_PATH . 'includes/class-wps-network-license.php';
	WPS_Network_License::init();

	// Load plugin upgrader for install/update flows.
	require_once wp_support_PATH . 'includes/class-wps-plugin-upgrader.php';

	// Load module action handlers for AJAX install/update/activate.
	require_once wp_support_PATH . 'includes/class-wps-module-actions.php';
	WPS_Module_Actions::init();

	// Load tab navigation system.
	require_once wp_support_PATH . 'includes/class-wps-tab-navigation.php';
	require_once wp_support_PATH . 'includes/class-wps-dashboard-widgets.php';
	require_once wp_support_PATH . 'includes/class-wps-dashboard-layout.php';
	require_once wp_support_PATH . 'includes/class-settings-ajax.php';
	require_once wp_support_PATH . 'includes/wps-capability-helpers.php';

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
	add_filter( 'wp_privacy_personal_data_exporters', __NAMESPACE__ . '\\wp_support_register_privacy_exporters' );
	// Register GDPR Personal Data Eraser for Vault.
	add_filter( 'wp_privacy_personal_data_erasers', __NAMESPACE__ . '\\wp_support_register_privacy_erasers' );
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
			$module_id = sanitize_key( str_replace( '-support-thisismyurl', '', $m['slug'] ?? '' ) );
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

	// Early guard: if a module is specified via URL and is disabled, block direct access.
	// This complements the context-based guard below and covers cases where context parsing fails.
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$raw_module = isset( $_GET['module'] ) ? sanitize_key( wp_unslash( $_GET['module'] ) ) : '';
	if ( ! empty( $raw_module ) ) {
		$module_slug = $raw_module . '-support-thisismyurl';
		if ( ! \WPS\CoreSupport\WPS_Module_Registry::is_enabled( $module_slug ) ) {
			$parent_url = is_network_admin() ? network_admin_url( 'admin.php?page=wp-support' ) : admin_url( 'admin.php?page=wp-support' );
			wp_safe_redirect( $parent_url );
			exit;
		}
	}

	$context = WPS_Tab_Navigation::get_current_context();
	$hub     = $context['hub'];
	$spoke   = $context['spoke'];
	$tab     = $context['tab'];
	$level   = $context['level'];

	// Block and redirect if hub module is disabled (avoid dead dashboard access).
	if ( ! empty( $hub ) ) {
		$slug = $hub . '-support-thisismyurl';
		if ( ! WPS_Module_Registry::is_enabled( $slug ) ) {
			$parent_url = is_network_admin() ? network_admin_url( 'admin.php?page=wp-support' ) : admin_url( 'admin.php?page=wp-support' );
			wp_safe_redirect( $parent_url );
			exit;
		}

		// Also block if spoke module is disabled.
		if ( ! empty( $spoke ) ) {
			$spoke_slug = $spoke . '-support-thisismyurl';
			if ( ! WPS_Module_Registry::is_enabled( $spoke_slug ) ) {
				$parent_url = is_network_admin() ? network_admin_url( 'admin.php?page=wp-support&module=' . $hub ) : admin_url( 'admin.php?page=wp-support&module=' . $hub );
				wp_safe_redirect( $parent_url );
				exit;
			}
		}
	}

	// Render breadcrumbs (except at Core level).
	if ( 'core' !== $level ) {
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
function wp_support_setup_dashboard_screen(): void {
	$screen = get_current_screen();
	if ( ! $screen ) {
		return;
	}

	// Register dashboard metaboxes for all levels (core, hub, spoke) on dashboard tab.
	$context = WPS_Tab_Navigation::get_current_context();
	$tab     = $context['tab'] ?? 'dashboard';
	$hub_id  = $context['hub'] ?? '';
	$spoke_id = $context['spoke'] ?? '';

	// Only register metaboxes when on dashboard tab.
	if ( 'dashboard' !== $tab ) {
		return;
	}

	// Determine context string for layout manager.
	$layout_context = 'core';
	if ( ! empty( $spoke_id ) && ! empty( $hub_id ) ) {
		$layout_context = $hub_id . '_' . $spoke_id;
	} elseif ( ! empty( $hub_id ) ) {
		$layout_context = $hub_id;
	}

	$network = is_network_admin();

	// Add Help tabs.
	$screen->add_help_tab(
		array(
			'id'      => 'WPS_overview',
			'title'   => __( 'Overview', 'plugin-wp-support-thisismyurl' ),
			'content' => '<p>' . esc_html__( 'This dashboard provides a suite overview, active hubs, recent activity, and quick actions. Use Screen Options to show/hide cards and arrange them.', 'plugin-wp-support-thisismyurl' ) . '</p>',
		)
	);

	$screen->add_help_tab(
		array(
			'id'      => 'WPS_shortcuts',
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

	// Use dashboard layout manager to setup widgets with proper ordering.
	WPS_Dashboard_Layout::setup_dashboard_screen( $layout_context, $network );
}

/**
 * Setup Screen Options and register dashboard meta boxes for hub pages.
 *
 * @param string $hub_id Hub identifier.
 * @return void
 */
function wp_support_setup_hub_dashboard_screen( string $hub_id ): void {
	error_log( 'wp_support_setup_hub_dashboard_screen: Called for hub_id=' . $hub_id );
	
	$screen = get_current_screen();
	if ( ! $screen ) {
		error_log( 'wp_support_setup_hub_dashboard_screen: No screen available' );
		return;
	}

	error_log( 'wp_support_setup_hub_dashboard_screen: Screen ID=' . $screen->id );

	// Format hub display name.
	$hub_name = esc_html( ucfirst( str_replace( '-', ' ', $hub_id ) ) );

	// Register metaboxes based on hub type.
	switch ( $hub_id ) {
		case 'media':
			add_meta_box(
				'WPS_media_overview',
				__( 'Media Overview', 'plugin-wp-support-thisismyurl' ),
				array( '\\\\WPS\\\\CoreSupport\\WPS_Dashboard_Widgets', 'render_metabox_media_overview' ),
				$screen->id,
				'normal',
				'high'
			);
			add_meta_box(
				'WPS_media_activity',
				__( 'Media Activity', 'plugin-wp-support-thisismyurl' ),
				array( '\\\\WPS\\\\CoreSupport\\WPS_Dashboard_Widgets', 'render_metabox_media_activity' ),
				$screen->id,
				'normal',
				'default'
			);
			add_meta_box(
				'WPS_media_modules',
				$hub_name . ' ' . __( 'Modules', 'plugin-wp-support-thisismyurl' ),
				array( '\\\\WPS\\\\CoreSupport\\WPS_Dashboard_Widgets', 'render_metabox_modules' ),
				$screen->id,
				'normal',
				'low'
			);
			add_meta_box(
				'WPS_media_quick_actions',
				__( 'Media Quick Actions', 'plugin-wp-support-thisismyurl' ),
				array( '\\\\WPS\\\\CoreSupport\\WPS_Dashboard_Widgets', 'render_metabox_quick_actions' ),
				$screen->id,
				'side',
				'high'
			);
			break;

		case 'vault':
			add_meta_box(
				'WPS_vault_overview',
				__( 'Vault Overview', 'plugin-wp-support-thisismyurl' ),
				array( '\\\\WPS\\\\CoreSupport\\WPS_Dashboard_Widgets', 'render_metabox_vault_overview' ),
				$screen->id,
				'normal',
				'high'
			);
			add_meta_box(
				'WPS_vault_activity',
				__( 'Vault Activity', 'plugin-wp-support-thisismyurl' ),
				array( '\\\\WPS\\\\CoreSupport\\WPS_Dashboard_Widgets', 'render_metabox_vault_activity' ),
				$screen->id,
				'normal',
				'default'
			);
			add_meta_box(
				'WPS_vault_modules',
				$hub_name . ' ' . __( 'Modules', 'plugin-wp-support-thisismyurl' ),
				array( '\\\\WPS\\\\CoreSupport\\WPS_Dashboard_Widgets', 'render_metabox_modules' ),
				$screen->id,
				'normal',
				'low'
			);
			add_meta_box(
				'WPS_vault_stats',
				__( 'Vault Status', 'plugin-wp-support-thisismyurl' ),
				array( '\\\\WPS\\\\CoreSupport\\WPS_Dashboard_Widgets', 'render_metabox_vault_status' ),
				$screen->id,
				'side',
				'high'
			);
			add_meta_box(
				'WPS_vault_quick_actions',
				__( 'Vault Quick Actions', 'plugin-wp-support-thisismyurl' ),
				array( '\\\\WPS\\\\CoreSupport\\WPS_Dashboard_Widgets', 'render_metabox_quick_actions' ),
				$screen->id,
				'side',
				'default'
			);
			add_meta_box(
				'WPS_vault_health',
				__( 'Vault Health', 'plugin-wp-support-thisismyurl' ),
				array( '\\\\WPS\\\\CoreSupport\\WPS_Dashboard_Widgets', 'render_metabox_vault_health' ),
				$screen->id,
				'side',
				'low'
			);
			break;

		default:
			// Generic hub dashboard: always include Modules widget.
			add_meta_box(
				'WPS_' . sanitize_html_class( $hub_id ) . '_modules',
				$hub_name . ' ' . __( 'Modules', 'plugin-wp-support-thisismyurl' ),
				array( '\\\\WPS\\\\CoreSupport\\WPS_Dashboard_Widgets', 'render_metabox_modules' ),
				$screen->id,
				'normal',
				'default'
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
		static function () use ( $screen, $hub_id ): void {
			// Use hub-specific state key.
			$state_key = 'wp-support-' . $hub_id;
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
}

/**
 * Render Core-level content based on active tab.
 *
 * @param string $tab Active tab ID.
 * @return void
 */
function wp_support_render_core_content( string $tab ): void {
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
			wp_support_render_modules();
			break;
		case 'settings':
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
			echo '<div class="wrap"><h1>' . esc_html( ucfirst( $hub_id ) . ' - ' . __( 'Help', 'plugin-wp-support-thisismyurl' ) ) . '</h1>';
			echo '<p>' . esc_html__( 'Help content will be added here.', 'plugin-wp-support-thisismyurl' ) . '</p></div>';
			break;
		case 'settings':
			wp_support_render_settings( $hub_id );
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
			echo '<div class="wrap"><h1>' . esc_html( strtoupper( $spoke_id ) . ' - ' . __( 'Help', 'plugin-wp-support-thisismyurl' ) ) . '</h1>';
			echo '<p>' . esc_html__( 'Help content will be added here.', 'plugin-wp-support-thisismyurl' ) . '</p></div>';
			break;
		case 'dashboard':
		default:
			// Route to unified dashboard renderer.
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
function wp_support_render_dashboard( string $hub_id = '', string $spoke_id = '' ): void {
	if ( ! wps_can_access_dashboard() ) {
		wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'plugin-wp-support-thisismyurl' ) );
	}

	// Route to appropriate dashboard renderer.
	// All levels (core, hub, spoke) show the same core dashboard content.
	if ( ! empty( $spoke_id ) && ! empty( $hub_id ) ) {
		// Spoke-level displays core dashboard content.
	} elseif ( ! empty( $hub_id ) ) {
		// Hub-level displays core dashboard content.
	}

	// Core-level dashboard (shown for all levels).
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

	$activity_logs     = WPS_Vault::get_logs( 0, 10 );
	$pending_uploads   = WPS_Vault::get_pending_contributor_uploads( 5 );
	$schedule_snapshot = WPS_Module_Registry::get_schedule_snapshot();
	$run_now_nonce     = wp_create_nonce( 'wps_run_task_now' );

	// Setup metaboxes for dashboard rendering.
	wp_support_setup_dashboard_screen( $hub_id, $spoke_id );
	$screen = get_current_screen();

	// Determine dashboard title based on context.
	$dashboard_title = __( 'Support Dashboard', 'plugin-wp-support-thisismyurl' );
	if ( ! empty( $spoke_id ) && ! empty( $hub_id ) ) {
		$dashboard_title = ucfirst( $spoke_id ) . ' ' . __( 'Dashboard', 'plugin-wp-support-thisismyurl' );
	} elseif ( ! empty( $hub_id ) ) {
		$dashboard_title = ucfirst( $hub_id ) . ' ' . __( 'Dashboard', 'plugin-wp-support-thisismyurl' );
	}

	// Render metabox-based dashboard.
	?>
	<div class="wrap">
		<h1><?php echo esc_html( $dashboard_title ); ?></h1>
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
 * Render modules view.
 *
 * @return void
 */
function wp_support_render_modules(): void {
	if ( ! current_user_can( 'manage_options' ) && ! current_user_can( 'manage_network_options' ) ) {
		wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'plugin-wp-support-thisismyurl' ) );
	}

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
function wps_ajax_toggle_module(): void {
	check_ajax_referer( 'WPS_toggle_module', 'nonce' );

	if ( ! current_user_can( 'manage_options' ) && ! current_user_can( 'manage_network_options' ) ) {
		wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'plugin-wp-support-thisismyurl' ) ) );
	}

	$slug    = sanitize_text_field( wp_unslash( $_POST['slug'] ?? '' ) );
	$enabled = isset( $_POST['enabled'] ) && 'true' === $_POST['enabled'];
	$network = isset( $_POST['network'] ) && 'true' === $_POST['network'] && is_multisite();

	if ( empty( $slug ) ) {
		wp_send_json_error( array( 'message' => __( 'Invalid module slug.', 'plugin-wp-support-thisismyurl' ) ) );
	}

	// Update WPS_module_toggles array (unified toggle system).
	$toggles = get_option( 'WPS_module_toggles', array() );
	$toggles[ $slug ] = $enabled ? 1 : 0;

	$deactivated = array();
	$remembered  = array();

	// Handle cascade deactivation of dependents.
	if ( ! $enabled ) {
		$deactivated = WPS_Module_Toggles::cascade_deactivate( $slug );
		if ( ! empty( $deactivated ) ) {
			// Reload toggles after cascade.
			$toggles = get_option( 'WPS_module_toggles', array() );
		}
	}

	// Handle restoration check on activation.
	if ( $enabled ) {
		$remembered = WPS_Module_Toggles::get_remembered_deactivated( $slug );
	}

	$success = update_option( 'WPS_module_toggles', $toggles );

	// Clear catalog cache so submenu regenerates.
	WPS_Module_Registry::clear_cache();

	$user      = wp_get_current_user();
	$user_name = $user && $user->exists() ? $user->display_name : __( 'System', 'plugin-wp-support-thisismyurl' );

	if ( $success ) {
		// If module is being enabled, inherit parent dashboard layout.
		if ( $enabled ) {
			WPS_Dashboard_Layout::on_module_activated( $slug, $network );
		}

		WPS_Vault::add_log(
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

		// Prepare response data.
		$response_data = array( 'message' => __( 'Module settings updated.', 'plugin-wp-support-thisismyurl' ) );

		// Add cascade info if modules were auto-deactivated.
		if ( ! empty( $deactivated ) ) {
			$response_data['deactivated'] = $deactivated;
		}

		// Add restoration prompt if remembered dependents exist.
		if ( ! empty( $remembered ) ) {
			$response_data['remembered'] = $remembered;
		}

		wp_send_json_success( $response_data );
	} else {
		WPS_Vault::add_log(
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
function wps_ajax_install_module(): void {
	check_ajax_referer( 'WPS_module_action', 'nonce' );

	if ( ! wps_can_install_modules() ) {
		wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'plugin-wp-support-thisismyurl' ) ) );
	}

	$slug      = sanitize_text_field( wp_unslash( $_POST['slug'] ?? '' ) );
	$user      = wp_get_current_user();
	$user_name = $user && $user->exists() ? $user->display_name : __( 'System', 'plugin-wp-support-thisismyurl' );

	if ( empty( $slug ) ) {
		WPS_Vault::add_log( 'error', 0, __( 'Install failed: empty slug.', 'plugin-wp-support-thisismyurl' ), 'module_install' );
		wp_send_json_error( array( 'message' => __( 'Invalid module slug.', 'plugin-wp-support-thisismyurl' ) ) );
	}

	$catalog = WPS_Module_Registry::get_catalog_with_status();
	$module  = $catalog[ $slug ] ?? null;

	if ( empty( $module ) || empty( $module['download_url'] ) ) {
		WPS_Vault::add_log(
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
		WPS_Vault::add_log(
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
	$download = wp_support_resolve_download_url( $module );
	$result   = $upgrader->install( $download );

	if ( is_wp_error( $result ) || ! $result ) {
		$message = is_wp_error( $result ) ? $result->get_error_message() : __( 'Installation failed.', 'plugin-wp-support-thisismyurl' );
		WPS_Vault::add_log(
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
	$plugin_file = wp_support_find_plugin_file_by_slug( $slug );
	if ( $plugin_file ) {
		$network_wide = is_multisite() && is_network_admin();
		$activation   = activate_plugin( $plugin_file, '', $network_wide, false );
		if ( is_wp_error( $activation ) ) {
			do_action(
				'WPS_catalog_install_warning',
				array(
					'slug'    => $slug,
					'message' => $activation->get_error_message(),
				)
			);
		} else {
			// Module activated successfully, inherit dashboard layout.
			WPS_Dashboard_Layout::on_module_activated( $slug, $network_wide );
		}
	}

	WPS_Module_Registry::clear_cache();
	WPS_Module_Registry::discover_modules();
	WPS_Module_Registry::load_catalog();

	WPS_Vault::add_log(
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
function wps_ajax_update_module(): void {
	check_ajax_referer( 'WPS_module_action', 'nonce' );

	if ( ! wps_can_update_modules() ) {
		wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'plugin-wp-support-thisismyurl' ) ) );
	}

	$slug      = sanitize_text_field( wp_unslash( $_POST['slug'] ?? '' ) );
	$user      = wp_get_current_user();
	$user_name = $user && $user->exists() ? $user->display_name : __( 'System', 'plugin-wp-support-thisismyurl' );

	if ( empty( $slug ) ) {
		WPS_Vault::add_log( 'error', 0, __( 'Update failed: empty slug.', 'plugin-wp-support-thisismyurl' ), 'module_update' );
		wp_send_json_error( array( 'message' => __( 'Invalid module slug.', 'plugin-wp-support-thisismyurl' ) ) );
	}

	$catalog   = WPS_Module_Registry::get_catalog_with_status();
	$installed = WPS_Module_Registry::get_module( $slug );
	$module    = $catalog[ $slug ] ?? null;

	if ( empty( $module ) || empty( $module['download_url'] ) || empty( $installed['file'] ) ) {
		WPS_Vault::add_log(
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
		WPS_Vault::add_log(
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

	$download = wp_support_resolve_download_url( $module );
	$result   = $upgrader->install( $download );

	// Remove filter after run.
	remove_filter( 'upgrader_package_options', $filter );

	if ( is_wp_error( $result ) || ! $result ) {
		$message = is_wp_error( $result ) ? $result->get_error_message() : __( 'Update failed.', 'plugin-wp-support-thisismyurl' );
		WPS_Vault::add_log(
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

	WPS_Module_Registry::clear_cache();
	WPS_Module_Registry::discover_modules();
	WPS_Module_Registry::load_catalog();

	WPS_Vault::add_log(
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
function wps_ajax_broadcast_license(): void {
	if ( empty( $_POST['nonce'] ) ) {
		wp_send_json_error( array( 'message' => __( 'Nonce failed.', 'plugin-wp-support-thisismyurl' ) ) );
	}

	if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'WPS_broadcast_license' ) ) {
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
	$result = WPS_License::broadcast_network_key( $key, $site_ids, (bool) $auto_broadcast );

	wp_send_json_success( $result );
}

/**
 * AJAX handler to save metabox state (order and collapsed state).
 *
 * @return void
 */
function wps_ajax_save_metabox_state(): void {
	if ( empty( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'WPS_metabox_state' ) ) {
		wp_send_json_error( array( 'message' => __( 'Nonce verification failed.', 'plugin-wp-support-thisismyurl' ) ) );
	}

	if ( ! current_user_can( 'manage_options' ) && ! current_user_can( 'manage_network_options' ) ) {
		wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'plugin-wp-support-thisismyurl' ) ) );
	}

	$state = isset( $_POST['state'] ) ? sanitize_text_field( wp_unslash( $_POST['state'] ) ) : '';
	
	if ( empty( $state ) ) {
		wp_send_json_error( array( 'message' => __( 'Invalid state data.', 'plugin-wp-support-thisismyurl' ) ) );
	}

	update_user_meta( get_current_user_id(), 'WPS_metabox_state', $state );
	
	wp_send_json_success();
}

/**
 * AJAX handler to save postbox order.
 *
 * @return void
 */
function wps_ajax_save_postbox_order(): void {
	check_ajax_referer( 'WPS_postbox_state', 'nonce' );

	if ( ! current_user_can( 'manage_options' ) && ! current_user_can( 'manage_network_options' ) ) {
		wp_send_json_error( array( 'message' => 'Insufficient permissions' ) );
	}

	$page  = isset( $_POST['page'] ) ? sanitize_key( $_POST['page'] ) : '';
	$order = isset( $_POST['order'] ) ? wp_unslash( $_POST['order'] ) : array();

	if ( empty( $page ) ) {
		wp_send_json_error( array( 'message' => 'Invalid page parameter' ) );
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

	error_log( 'SAVED POSTBOX ORDER: user=' . $user_id . ', page=' . $page . ', order=' . json_encode( $order ) );

	wp_send_json_success( array(
		'message' => 'Order saved',
		'page' => $page,
		'order' => $order
	) );
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

	error_log( 'SAVED POSTBOX STATE: user=' . $user_id . ', page=' . $page . ', closed=' . json_encode( $closed ) );

	wp_send_json_success( array(
		'message' => 'State saved',
		'page' => $page,
		'closed' => $closed
	) );
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
			esc_url( admin_url( 'admin.php?page=wp-support&WPS_tab=settings' ) ),
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
	$context = WPS_Tab_Navigation::get_current_context();
	$hub_id  = $context['hub'] ?? '';
	$state_key = 'wp-support' . ( $hub_id ? '-' . $hub_id : '' );
	
	// Get all states from JSON store
	$user_id    = get_current_user_id();
	$all_states = get_user_meta( $user_id, 'WPS_postbox_states', true );

	if ( ! is_array( $all_states ) || ! isset( $all_states[ $state_key ]['order'] ) ) {
		error_log( 'LOADING METABOX ORDER: page=' . $state_key . ', no data found' );
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
		error_log( 'LOADING METABOX ORDER: page=' . $state_key . ' invalid data, purging entry' );
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

	error_log( 'LOADING METABOX ORDER: page=' . $state_key . ', order=' . json_encode( $normalized ) );

	return $normalized;
}

/**
 * Get closed postboxes from custom state key.
 *
 * @param mixed $result Default result.
 * @return mixed
 */
function wp_support_get_closed_postboxes( $result ) {
	$context = WPS_Tab_Navigation::get_current_context();
	$hub_id  = $context['hub'] ?? '';
	$state_key = 'wp-support' . ( $hub_id ? '-' . $hub_id : '' );
	
	// Get all states from JSON store
	$user_id    = get_current_user_id();
	$all_states = get_user_meta( $user_id, 'WPS_postbox_states', true );

	if ( ! is_array( $all_states ) || ! isset( $all_states[ $state_key ]['closed'] ) ) {
		error_log( 'LOADING CLOSED POSTBOXES: page=' . $state_key . ', no data found' );
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
		error_log( 'LOADING CLOSED POSTBOXES: page=' . $state_key . ' invalid data, purging entry' );
		unset( $all_states[ $state_key ] );
		update_user_meta( $user_id, 'WPS_postbox_states', $all_states );
		return false;
	}

	// Convert to string for WordPress explode()
	$closed_str = implode( ',', $closed );

	error_log( 'LOADING CLOSED POSTBOXES: page=' . $state_key . ', closed=' . $closed_str );

	return $closed_str;
}

/**
 * Enqueue admin scripts and styles.
 *
 * @param string $hook The current admin page hook.
 * @return void
 */
function wp_support_admin_enqueue( string $hook ): void {
	// Load on all wp-support related pages (core, hubs, spokes).
	// Hooks can be: toplevel_page_wp-support, support-hub_page_wp-support-hub-media, etc.
	if ( false === strpos( $hook, 'wp-support' ) ) {
		return;
	}

	error_log( 'wp_support_admin_enqueue: Hook=' . $hook );

	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	error_log( 'wp_support_admin_enqueue: Screen ID=' . ( $screen ? $screen->id : 'null' ) );

	// Cache-bust using current timestamp to force reload for testing.
	$cache_bust = time();

	// Enqueue modern design system (shared across all WPS plugins).
	wp_enqueue_style(
		'wps-ui-system',
		wp_support_URL . 'assets/css/wps-ui-system.css',
		array(),
		$cache_bust
	);

	wp_enqueue_style(
		'wps-core-admin',
		wp_support_URL . 'assets/css/admin.css',
		array( 'wps-ui-system' ),
		$cache_bust
	);

	wp_enqueue_style(
		'wps-tab-navigation',
		wp_support_URL . 'assets/css/tab-navigation.css',
		array( 'wps-ui-system' ),
		$cache_bust
	);

	// Enable drag and drop for dashboard metaboxes on all wp-support pages using WordPress native postboxes.
	if ( $screen && false !== strpos( $screen->id, 'wp-support' ) ) {
		error_log( 'wp_support_admin_enqueue: Loading dashboard assets for screen=' . $screen->id );
		
		// Use WordPress's built-in postbox drag and drop.
		wp_enqueue_script( 'postbox' );
		
		// Add custom script to handle context-specific state saving.
		wp_enqueue_script(
			'wps-postbox-state',
			wp_support_URL . 'assets/js/postbox-state.js',
			array( 'jquery', 'postbox' ),
			$cache_bust,
			true
		);
		
		// Get current context for unique state key.
		$context = WPS_Tab_Navigation::get_current_context();
		$hub_id  = $context['hub'] ?? '';
		$state_key = 'wp-support' . ( $hub_id ? '-' . $hub_id : '' );
		
		wp_localize_script(
			'wps-postbox-state',
			'wpsPostboxState',
			array(
				'stateKey' => $state_key,
				'nonce'    => wp_create_nonce( 'WPS_postbox_state' ),
			)
		);
		
		wp_enqueue_style(
			'wps-dashboard-drag',
			wp_support_URL . 'assets/css/dashboard-drag.css',
			array(),
			$cache_bust
		);
	} else {
		error_log( 'wp_support_admin_enqueue: NOT loading dashboard assets. Screen=' . ( $screen ? $screen->id : 'null' ) );
	}

	wp_enqueue_script(
		'wps-core-admin',
		wp_support_URL . 'assets/js/admin.js',
		array( 'jquery' ),
		$cache_bust,
		true
	);

	// Localize script for AJAX and i18n.
	wp_localize_script(
		'wps-core-admin',
		'wpsAdminData',
		array(
			'toggleNonce' => wp_create_nonce( 'WPS_toggle_module' ),
			'actionNonce' => wp_create_nonce( 'WPS_module_action' ),
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

	// Enqueue module actions script (install/update/activate).
	wp_enqueue_script(
		'wps-module-actions',
		wp_support_URL . 'assets/js/module-actions.js',
		array(),
		$cache_bust,
		true
	);

	// Localize module actions script with nonce and AJAX URL.
	wp_localize_script(
		'wps-module-actions',
		'wpsModuleActions',
		array(
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'WPS_module_actions' ),
		)
	);
}

/**
 * Register the Vault exporter with WordPress Personal Data Export.
 *
 * @param array $exporters Existing exporters.
 * @return array Modified exporters.
 */
function wp_support_register_privacy_exporters( array $exporters ): array {
	$exporters['wps-vault-exporter'] = array(
		'exporter_friendly_name' => __( 'WPS Vault', 'plugin-wp-support-thisismyurl' ),
		'callback'               => __NAMESPACE__ . '\\wp_support_vault_exporter_callback',
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
function wp_support_vault_exporter_callback( string $email_address, int $page = 1 ): array {
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
					'key'   => '_WPS_vault_uploader_user_id',
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
			$vault_path     = (string) get_post_meta( $attachment_id, '_WPS_vault_path', true );
			$vault_mode     = (string) get_post_meta( $attachment_id, '_WPS_vault_mode', true );
			$vault_created  = (string) get_post_meta( $attachment_id, '_WPS_vault_created', true );
			$hash_raw       = (string) get_post_meta( $attachment_id, '_WPS_vault_sha256_raw', true );
			$hash_store     = (string) get_post_meta( $attachment_id, '_WPS_vault_sha256_store', true );
			$anonymized_at  = (string) get_post_meta( $attachment_id, '_WPS_vault_anonymized', true );
			$encrypted_flag = (string) get_post_meta( $attachment_id, '_WPS_vault_encrypted', true );

			$items[] = array(
				'group_id'    => 'wps-vault',
				'group_label' => __( 'WPS Vault', 'plugin-wp-support-thisismyurl' ),
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
function wp_support_register_privacy_erasers( array $erasers ): array {
	$erasers['wps-vault-eraser'] = array(
		'eraser_friendly_name' => __( 'WPS Vault (anonymize originals & derivatives)', 'plugin-wp-support-thisismyurl' ),
		'callback'             => __NAMESPACE__ . '\\wp_support_vault_eraser_callback',
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
function wp_support_vault_eraser_callback( string $email_address, int $page = 1 ): array {
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
	$result = WPS_Vault::erase_user_personal_data( (int) $user->ID, max( 1, $page ), 50 );

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
	<form method="post" class=\"wps-settings-form\" data-settings-group="module_registry" style="max-width: 600px;">
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
		<div class=\"wps-settings-save-status\" style="margin-top: 10px; font-size: 13px; color: #666;"></div>
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
	<form method="post" class=\"wps-settings-form\" data-settings-group="capabilities" style="max-width: 600px;">
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
		<div class=\"wps-settings-save-status\" style="margin-top: 10px; font-size: 13px; color: #666;"></div>
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
	<form method="post" class=\"wps-settings-form\" data-settings-group="dashboard" style="max-width: 600px;">
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
		<div class=\"wps-settings-save-status\" style="margin-top: 10px; font-size: 13px; color: #666;"></div>
	</form>
	<?php
}

/**
 * Render License & Updates settings widget.
 *
 * @return void
 */
function render_settings_license(): void {
	$license_key     = get_option( 'WPS_license_key', '' );
	$is_licensed     = ! empty( $license_key );
	$auto_update     = (array) get_option( 'WPS_license_auto_update_types', array( 'minor', 'patch' ) );
	$update_channel  = get_option( 'WPS_license_update_channel', 'stable' );
	?>
	<form method="post" class=\"wps-settings-form\" data-settings-group="license" style="max-width: 600px;">
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
		<div class=\"wps-settings-save-status\" style="margin-top: 10px; font-size: 13px; color: #666;"></div>
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
	?>
	<form method="post" class=\"wps-settings-form\" data-settings-group="privacy" style="max-width: 600px;">
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
		<div class=\"wps-settings-save-status\" style="margin-top: 10px; font-size: 13px; color: #666;"></div>
	</form>
	<?php
}
