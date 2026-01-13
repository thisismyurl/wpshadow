<?php
/**
 * AJAX handlers for catalog module install/update/activate actions.
 *
 * @package wp_support_SUPPORT
 * @since 1.2601.73000
 */

declare(strict_types=1);

namespace WPS\CoreSupport;

use WP_Error;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin AJAX request handlers for module management.
 */
class WPS_Module_Actions {
	/**
	 * Verify AJAX nonce and capability, with optional network-awareness.
	 *
	 * @param string $nonce_action Nonce action key.
	 * @param string $site_cap     Capability required on single site.
	 * @param string $network_cap  Capability required on network admin (optional; defaults to $site_cap).
	 * @return array{network_scope:bool} Context data.
	 */
	private static function verify_request( string $nonce_action, string $site_cap, string $network_cap = '' ): array {
		check_ajax_referer( $nonce_action, 'nonce' );

		$network_scope = is_multisite() && is_network_admin();
		$cap           = $network_scope ? ( $network_cap ?: $site_cap ) : $site_cap;
		if ( ! current_user_can( $cap ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'plugin-wp-support-thisismyurl' ) ), 403 );
		}

		return array( 'network_scope' => $network_scope );
	}

	/**
	 * Send a standardized error JSON response.
	 *
	 * @param string $message Error message.
	 * @param int    $code    HTTP status code.
	 * @return void
	 */
	private static function respond_error( string $message, int $code = 400 ): void {
		wp_send_json_error( array( 'message' => $message ), $code );
	}

	/**
	 * Send a standardized success JSON response.
	 *
	 * @param array $data Payload.
	 * @return void
	 */
	private static function respond_success( array $data = array() ): void {
		wp_send_json_success( $data );
	}

	/**
	 * Initialize AJAX handlers.
	 *
	 * @return void
	 */
	public static function init(): void {
		// Install and activate module.
		add_action( 'wp_ajax_wps_module_install', array( __CLASS__, 'ajax_install_module' ) );

		// Update module.
		add_action( 'wp_ajax_wps_module_update', array( __CLASS__, 'ajax_update_module' ) );

		// Activate module.
		add_action( 'wp_ajax_wps_module_activate', array( __CLASS__, 'ajax_activate_module' ) );

		// Deactivate module (network).
		add_action( 'wp_ajax_wps_module_deactivate', array( __CLASS__, 'ajax_deactivate_module' ) );

		// Toggle module enabled setting (for bundled/non-plugin modules).
		add_action( 'wp_ajax_wps_module_toggle', array( __CLASS__, 'ajax_toggle_module_enabled' ) );

		// Clear remembered deactivations after restoration.
		add_action( 'wp_ajax_wps_clear_remembered', array( __CLASS__, 'ajax_clear_remembered' ) );

		// Refresh dashboard widgets dynamically.
		add_action( 'wp_ajax_wps_refresh_health_widget', array( __CLASS__, 'ajax_refresh_health_widget' ) );
		add_action( 'wp_ajax_wps_refresh_events_widget', array( __CLASS__, 'ajax_refresh_events_widget' ) );

		// Download progress polling.
		add_action( 'wp_ajax_wps_module_download_progress', array( __CLASS__, 'ajax_download_progress' ) );
	}
	/**
	 * AJAX: Toggle module enabled setting (site or network scope).
	 *
	 * @return void
	 */
	public static function ajax_toggle_module_enabled(): void {
		$ctx = self::verify_request( 'WPS_module_actions', 'manage_options', 'manage_network_options' );

		$slug    = isset( $_POST['slug'] ) ? sanitize_key( wp_unslash( $_POST['slug'] ) ) : '';
		$enabled = isset( $_POST['enabled'] ) ? (bool) intval( wp_unslash( $_POST['enabled'] ) ) : null;
		if ( empty( $slug ) || null === $enabled ) {
			self::respond_error( __( 'Missing parameters.', 'plugin-wp-support-thisismyurl' ), 400 );
		}

		$ok = WPS_Module_Registry::update_module_settings( $slug, array( 'enabled' => $enabled ), (bool) $ctx['network_scope'] );
		if ( ! $ok ) {
			self::respond_error( __( 'Failed to update settings.', 'plugin-wp-support-thisismyurl' ), 500 );
		}

		// Trigger health check refresh hook.
		do_action( $enabled ? 'WPS_module_enabled' : 'WPS_module_disabled', $slug );

		$status = WPS_Module_Registry::get_catalog_with_status();
		self::respond_success(
			array(
				'message' => __( 'Settings updated.', 'plugin-wp-support-thisismyurl' ),
				'status'  => $status[ $slug ] ?? array(),
			)
		);
	}

	/**
	 * AJAX: Install and activate module from catalog.
	 *
	 * @return void
	 */
	public static function ajax_install_module(): void {
		// Verify nonce.
		check_ajax_referer( 'WPS_module_actions', 'nonce' );

		// Check capability.
		$cap = is_multisite() ? 'manage_network_plugins' : 'install_plugins';
		if ( ! current_user_can( $cap ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'You do not have permission to install plugins.', 'plugin-wp-support-thisismyurl' ),
				),
				403
			);
		}

		// Get and validate inputs.
		$slug = isset( $_POST['slug'] ) ? sanitize_text_field( wp_unslash( $_POST['slug'] ) ) : '';
		if ( empty( $slug ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Module slug is required.', 'plugin-wp-support-thisismyurl' ),
				),
				400
			);
		}

		// Get catalog entry.
		$catalog      = WPS_Module_Registry::get_catalog_modules();
		$module_entry = null;

		foreach ( $catalog as $entry ) {
			if ( isset( $entry['slug'] ) && $entry['slug'] === $slug ) {
				$module_entry = $entry;
				break;
			}
		}

		if ( empty( $module_entry ) || empty( $module_entry['download_url'] ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Module not found in catalog or missing download URL.', 'plugin-wp-support-thisismyurl' ),
				),
				404
			);
		}

		// Determine if network activation.
		$network_activate = is_multisite() && is_network_admin();

		// Extract checksum if provided in catalog.
		$expected_hash = ! empty( $module_entry['checksum'] ) ? sanitize_text_field( wp_unslash( $module_entry['checksum'] ) ) : null;

		// Perform installation.
		$upgrader = new WPS_Plugin_Upgrader();
		$result   = $upgrader->install_plugin(
			$module_entry['download_url'],
			true,
			$network_activate,
			$expected_hash,
			$slug
		);

		if ( is_wp_error( $result ) ) {
			$error_data = $result->get_error_data();
			$guidance   = ! empty( $error_data['guidance'] ) ? $error_data['guidance'] : '';
			$message    = $result->get_error_message();

			if ( ! empty( $guidance ) ) {
				$message .= ' ' . $guidance;
			}

			wp_send_json_error(
				array(
					'message' => $message,
				),
				500
			);
		}

		// Refresh catalog with status.
		$status = WPS_Module_Registry::get_catalog_with_status();

		wp_send_json_success(
			array(
				'message'     => __( 'Module installed and activated successfully.', 'plugin-wp-support-thisismyurl' ),
				'plugin_base' => $upgrader->result,
				'status'      => $status[ $slug ] ?? array(),
			)
		);
	}

	/**
	 * AJAX: Update module from catalog.
	 *
	 * @return void
	 */
	public static function ajax_update_module(): void {
		self::verify_request( 'WPS_module_actions', 'update_plugins', 'manage_network_plugins' );

		// Get and validate inputs.
		$slug = isset( $_POST['slug'] ) ? sanitize_text_field( wp_unslash( $_POST['slug'] ) ) : '';
		if ( empty( $slug ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Module slug is required.', 'plugin-wp-support-thisismyurl' ),
				),
				400
			);
		}

		// Get installed module.
		$installed = WPS_Module_Registry::get_modules();
		if ( empty( $installed[ $slug ] ) ) {
			self::respond_error( __( 'Module not found.', 'plugin-wp-support-thisismyurl' ), 404 );
		}

		// Get catalog entry.
		$catalog      = WPS_Module_Registry::get_catalog_modules();
		$module_entry = null;

		foreach ( $catalog as $entry ) {
			if ( isset( $entry['slug'] ) && $entry['slug'] === $slug ) {
				$module_entry = $entry;
				break;
			}
		}

		if ( empty( $module_entry ) || empty( $module_entry['download_url'] ) ) {
			self::respond_error( __( 'Module not found in catalog or missing download URL.', 'plugin-wp-support-thisismyurl' ), 404 );
		}

		// Build plugin file path (slug/slug.php).
		$plugin_file = $slug . '/' . $slug . '.php';

		// Extract checksum if provided in catalog.
		$expected_hash = ! empty( $module_entry['checksum'] ) ? sanitize_text_field( wp_unslash( $module_entry['checksum'] ) ) : null;

		// Perform update.
		$upgrader = new WPS_Plugin_Upgrader();
		$result   = $upgrader->update_plugin( $plugin_file, $module_entry['download_url'], $expected_hash, $slug );

		if ( is_wp_error( $result ) ) {
			$error_data = $result->get_error_data();
			$guidance   = ! empty( $error_data['guidance'] ) ? $error_data['guidance'] : '';
			$message    = $result->get_error_message();

			if ( ! empty( $guidance ) ) {
				$message .= ' ' . $guidance;
			}

			self::respond_error( $message, 500 );
		}

		// Refresh catalog with status.
		$status = WPS_Module_Registry::get_catalog_with_status();

		self::respond_success(
			array(
				'message'     => __( 'Module updated successfully.', 'plugin-wp-support-thisismyurl' ),
				'plugin_base' => $upgrader->result,
				'status'      => $status[ $slug ] ?? array(),
			)
		);
	}

	/**
	 * AJAX: Activate module.
	 *
	 * @return void
	 */
	public static function ajax_activate_module(): void {
		self::verify_request( 'WPS_module_actions', 'activate_plugins', 'manage_network_plugins' );

		// Get and validate inputs.
		$slug = isset( $_POST['slug'] ) ? sanitize_text_field( wp_unslash( $_POST['slug'] ) ) : '';
		if ( empty( $slug ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Module slug is required.', 'plugin-wp-support-thisismyurl' ),
				),
				400
			);
		}

		// Resolve plugin file path.
		$requested_file = isset( $_POST['plugin_base'] ) ? sanitize_text_field( wp_unslash( $_POST['plugin_base'] ) ) : '';
		$plugin_file    = self::resolve_plugin_file( $slug, $requested_file );

		// Determine activation scope.
		$network_activate = is_multisite() && is_network_admin();

		// If a plugin file exists, activate it; otherwise just mark enabled.
		if ( $plugin_file ) {
			if ( $network_activate ) {
				$result = activate_plugin( $plugin_file, '', true );
			} else {
				$result = activate_plugin( $plugin_file );
			}

			if ( is_wp_error( $result ) ) {
				self::respond_error( $result->get_error_message(), 500 );
			}
		}

		// Ensure registry enabled flag is set even for module-only entries.
		WPS_Module_Registry::update_module_settings( $slug, array( 'enabled' => true ), $network_activate );

		// Trigger health check refresh hook.
		do_action( 'WPS_module_enabled', $slug );

		// Refresh catalog with status.
		$status = WPS_Module_Registry::get_catalog_with_status();

		self::respond_success(
			array(
				'message' => __( 'Module activated successfully.', 'plugin-wp-support-thisismyurl' ),
				'status'  => $status[ $slug ] ?? array(),
			)
		);
	}

	/**
	 * AJAX: Deactivate module (network scope).
	 *
	 * @return void
	 */
	public static function ajax_deactivate_module(): void {
		$ctx = self::verify_request( 'WPS_module_actions', 'activate_plugins', 'manage_network_plugins' );

		// Get and validate inputs.
		$slug = isset( $_POST['slug'] ) ? sanitize_text_field( wp_unslash( $_POST['slug'] ) ) : '';
		if ( empty( $slug ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Module slug is required.', 'plugin-wp-support-thisismyurl' ),
				),
				400
			);
		}

		// Resolve plugin file path (optional for module-only entries).
		$requested_file = isset( $_POST['plugin_base'] ) ? sanitize_text_field( wp_unslash( $_POST['plugin_base'] ) ) : '';
		$plugin_file    = self::resolve_plugin_file( $slug, $requested_file );

		if ( $plugin_file ) {
			// Deactivate plugin (site or network scope).
			deactivate_plugins( $plugin_file, false, (bool) $ctx['network_scope'] );
		}

		// Always disable the module flag (plugin or module-only).
		WPS_Module_Registry::update_module_settings( $slug, array( 'enabled' => false ), (bool) $ctx['network_scope'] );

		// Trigger health check refresh hook.
		do_action( 'WPS_module_disabled', $slug );

		// Refresh catalog with status.
		$status = WPS_Module_Registry::get_catalog_with_status();

		self::respond_success(
			array(
				'message' => __( 'Module deactivated successfully.', 'plugin-wp-support-thisismyurl' ),
				'status'  => $status[ $slug ] ?? array(),
			)
		);
	}

	/**
	 * Resolve plugin file location for a module, tolerating GitHub zip naming.
	 *
	 * @param string $slug Module slug.
	 * @param string $preferred Preferred plugin_base from request (optional).
	 * @return string Plugin base path relative to WP_PLUGIN_DIR or empty string.
	 */
	private static function resolve_plugin_file( string $slug, string $preferred = '' ): string {
		$slug       = sanitize_key( $slug );
		$candidates = array();

		if ( ! empty( $preferred ) ) {
			$candidates[] = ltrim( $preferred, '\\/' );
		}

		$module      = WPS_Module_Registry::get_module( $slug ) ?? array();
		$basename    = (string) ( $module['basename'] ?? '' );
		$module_file = (string) ( $module['file'] ?? '' );
		if ( ! empty( $basename ) ) {
			$candidates[] = ltrim( $basename, '\\/' );
		}
		if ( ! empty( $module_file ) ) {
			$candidates[] = ltrim( $module_file, '\\/' );
		}

		// Default guesses.
		$candidates[] = $slug . '/' . $slug . '.php';
		$candidates[] = 'plugin-' . $slug . '/module.php';

		// Glob for GitHub zip extra suffix (e.g., -main).
		$glob = glob( WP_PLUGIN_DIR . '/*' . $slug . '*/' . $slug . '.php' );
		if ( ! empty( $glob ) ) {
			$candidates[] = ltrim( str_replace( WP_PLUGIN_DIR . '/', '', $glob[0] ), '\\/' );
		}

		foreach ( $candidates as $candidate ) {
			$full_path = WP_PLUGIN_DIR . '/' . $candidate;
			if ( file_exists( $full_path ) ) {
				return $candidate;
			}
		}

		return '';
	}

	/**
	 * AJAX: Clear remembered deactivations for a parent module after restoration.
	 *
	 * @return void
	 */
	public static function ajax_clear_remembered(): void {
		self::verify_request( 'WPS_module_actions', 'manage_options', 'manage_network_options' );

		$parent_slug = isset( $_POST['parent_slug'] ) ? sanitize_key( wp_unslash( $_POST['parent_slug'] ) ) : '';
		if ( empty( $parent_slug ) ) {
			self::respond_error( __( 'Missing parent slug.', 'plugin-wp-support-thisismyurl' ), 400 );
		}

		WPS_Module_Toggles::clear_remembered( $parent_slug );
		self::respond_success( array( 'message' => __( 'Restoration memory cleared.', 'plugin-wp-support-thisismyurl' ) ) );
	}

	/**
	 * AJAX: Refresh health widget with active modules only.
	 *
	 * @return void
	 */
	public static function ajax_refresh_health_widget(): void {
		self::verify_request( 'WPS_module_actions', 'manage_options', 'manage_network_options' );

		// Get active module slugs.
		$active_modules = array();
		$catalog        = WPS_Module_Registry::get_catalog_with_status();
		foreach ( $catalog as $module ) {
			$slug = $module['slug'] ?? '';
			if ( ! empty( $slug ) && WPS_Module_Registry::is_enabled( $slug ) ) {
				$active_modules[] = $slug;
			}
		}

		// Get health data filtered by active modules.
		if ( class_exists( '\\WPS\\CoreSupport\\WPS_Site_Health' ) ) {
			$health_data = WPS_Site_Health::get_health_check_results( $active_modules );
		} else {
			$health_data = array(
				'score'   => 0,
				'status'  => 'unavailable',
				'results' => array(),
				'counts'  => array(
					'good'     => 0,
					'warning'  => 0,
					'critical' => 0,
				),
			);
		}

		// Render widget HTML.
		ob_start();
		WPS_Dashboard_Widgets::render_health_widget_html( $health_data );
		$html = ob_get_clean();

		self::respond_success(
			array(
				'html'           => $html,
				'active_modules' => $active_modules,
				'timestamp'      => current_time( 'timestamp' ),
			)
		);
	}

	/**
	 * AJAX: Refresh events and news widget for active modules.
	 *
	 * @return void
	 */
	public static function ajax_refresh_events_widget(): void {
		self::verify_request( 'WPS_module_actions', 'manage_options', 'manage_network_options' );

		// Get active module slugs and their repos.
		$active_repos = array();
		$catalog      = WPS_Module_Registry::get_catalog_with_status();
		foreach ( $catalog as $module ) {
			$slug = $module['slug'] ?? '';
			if ( ! empty( $slug ) && WPS_Module_Registry::is_enabled( $slug ) ) {
				// Extract repo from slug (e.g., 'vault-support-thisismyurl' → 'plugin-vault-support-thisismyurl').
				$repo           = 'plugin-' . $slug;
				$active_repos[] = array(
					'slug' => $slug,
					'repo' => $repo,
					'name' => $module['name'] ?? ucfirst( str_replace( '-', ' ', $slug ) ),
				);
			}
		}

		// Render widget HTML.
		ob_start();
		WPS_Dashboard_Widgets::render_events_widget_html( $active_repos );
		$html = ob_get_clean();

		self::respond_success(
			array(
				'html'         => $html,
				'active_repos' => $active_repos,
				'timestamp'    => current_time( 'timestamp' ),
			)
		);
	}

	/**
	 * AJAX: Get download progress for module installation/update.
	 *
	 * @return void
	 */
	public static function ajax_download_progress(): void {
		self::verify_request( 'WPS_module_actions', 'install_plugins', 'manage_network_plugins' );

		$session_id = isset( $_POST['session_id'] ) ? sanitize_text_field( wp_unslash( $_POST['session_id'] ) ) : '';
		if ( empty( $session_id ) ) {
			self::respond_error( __( 'Session ID is required.', 'plugin-wp-support-thisismyurl' ), 400 );
			return;
		}

		// Get progress from transient.
		$transient_key = 'wps_dl_progress_' . $session_id;
		$progress      = get_transient( $transient_key );

		if ( false === $progress ) {
			self::respond_success(
				array(
					'percent' => 0,
					'status'  => 'unknown',
					'message' => __( 'No progress data available.', 'plugin-wp-support-thisismyurl' ),
				)
			);
			return;
		}

		self::respond_success( $progress );
	}
}

/* @changelog WPS_Module_Actions class created for AJAX install/update/activate handlers. */
