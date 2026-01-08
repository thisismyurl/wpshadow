<?php
/**
 * AJAX handlers for catalog module install/update/activate actions.
 *
 * @package TIMU_CORE_SUPPORT
 * @since 1.2601.73000
 */

declare(strict_types=1);

namespace TIMU\CoreSupport;

use WP_Error;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin AJAX request handlers for module management.
 */
class TIMU_Module_Actions {

	/**
	 * Initialize AJAX handlers.
	 *
	 * @return void
	 */
	public static function init(): void {
		// Install and activate module.
		add_action( 'wp_ajax_timu_module_install', array( __CLASS__, 'ajax_install_module' ) );

		// Update module.
		add_action( 'wp_ajax_timu_module_update', array( __CLASS__, 'ajax_update_module' ) );

		// Activate module.
		add_action( 'wp_ajax_timu_module_activate', array( __CLASS__, 'ajax_activate_module' ) );

		// Deactivate module (network).
		add_action( 'wp_ajax_timu_module_deactivate', array( __CLASS__, 'ajax_deactivate_module' ) );
	}

	/**
	 * AJAX: Install and activate module from catalog.
	 *
	 * @return void
	 */
	public static function ajax_install_module(): void {
		// Verify nonce.
		check_ajax_referer( 'timu_module_actions', 'nonce' );

		// Check capability.
		$cap = is_multisite() ? 'manage_network_plugins' : 'install_plugins';
		if ( ! current_user_can( $cap ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'You do not have permission to install plugins.', 'core-support-thisismyurl' ),
				),
				403
			);
		}

		// Get and validate inputs.
		$slug = isset( $_POST['slug'] ) ? sanitize_text_field( wp_unslash( $_POST['slug'] ) ) : '';
		if ( empty( $slug ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Module slug is required.', 'core-support-thisismyurl' ),
				),
				400
			);
		}

		// Get catalog entry.
		$catalog      = TIMU_Module_Registry::get_catalog_modules();
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
					'message' => __( 'Module not found in catalog or missing download URL.', 'core-support-thisismyurl' ),
				),
				404
			);
		}

		// Determine if network activation.
		$network_activate = is_multisite() && is_network_admin();

		// Perform installation.
		$upgrader = new TIMU_Plugin_Upgrader();
		$result   = $upgrader->install_plugin(
			$module_entry['download_url'],
			true,
			$network_activate
		);

		if ( is_wp_error( $result ) ) {
			wp_send_json_error(
				array(
					'message' => $result->get_error_message(),
				),
				500
			);
		}

		// Refresh catalog with status.
		$status = TIMU_Module_Registry::get_catalog_with_status();

		wp_send_json_success(
			array(
				'message'     => __( 'Module installed and activated successfully.', 'core-support-thisismyurl' ),
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
		// Verify nonce.
		check_ajax_referer( 'timu_module_actions', 'nonce' );

		// Check capability.
		$cap = is_multisite() ? 'manage_network_plugins' : 'update_plugins';
		if ( ! current_user_can( $cap ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'You do not have permission to update plugins.', 'core-support-thisismyurl' ),
				),
				403
			);
		}

		// Get and validate inputs.
		$slug = isset( $_POST['slug'] ) ? sanitize_text_field( wp_unslash( $_POST['slug'] ) ) : '';
		if ( empty( $slug ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Module slug is required.', 'core-support-thisismyurl' ),
				),
				400
			);
		}

		// Get installed module.
		$installed = TIMU_Module_Registry::get_modules();
		if ( empty( $installed[ $slug ] ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Module not found.', 'core-support-thisismyurl' ),
				),
				404
			);
		}

		// Get catalog entry.
		$catalog      = TIMU_Module_Registry::get_catalog_modules();
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
					'message' => __( 'Module not found in catalog or missing download URL.', 'core-support-thisismyurl' ),
				),
				404
			);
		}

		// Build plugin file path (slug/slug.php).
		$plugin_file = $slug . '/' . $slug . '.php';

		// Perform update.
		$upgrader = new TIMU_Plugin_Upgrader();
		$result   = $upgrader->update_plugin( $plugin_file, $module_entry['download_url'] );

		if ( is_wp_error( $result ) ) {
			wp_send_json_error(
				array(
					'message' => $result->get_error_message(),
				),
				500
			);
		}

		// Refresh catalog with status.
		$status = TIMU_Module_Registry::get_catalog_with_status();

		wp_send_json_success(
			array(
				'message'     => __( 'Module updated successfully.', 'core-support-thisismyurl' ),
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
		// Verify nonce.
		check_ajax_referer( 'timu_module_actions', 'nonce' );

		// Check capability.
		$cap = is_multisite() ? 'manage_network_plugins' : 'activate_plugins';
		if ( ! current_user_can( $cap ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'You do not have permission to activate plugins.', 'core-support-thisismyurl' ),
				),
				403
			);
		}

		// Get and validate inputs.
		$slug = isset( $_POST['slug'] ) ? sanitize_text_field( wp_unslash( $_POST['slug'] ) ) : '';
		if ( empty( $slug ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Module slug is required.', 'core-support-thisismyurl' ),
				),
				400
			);
		}

		// Build plugin file path.
		$plugin_file = $slug . '/' . $slug . '.php';
		$plugin_path = WP_PLUGIN_DIR . '/' . $plugin_file;

		if ( ! file_exists( $plugin_path ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Plugin file not found.', 'core-support-thisismyurl' ),
				),
				404
			);
		}

		// Determine activation scope.
		$network_activate = is_multisite() && is_network_admin();

		if ( $network_activate ) {
			$result = activate_plugin( $plugin_file, '', true );
		} else {
			$result = activate_plugin( $plugin_file );
		}

		if ( is_wp_error( $result ) ) {
			wp_send_json_error(
				array(
					'message' => $result->get_error_message(),
				),
				500
			);
		}

		// Refresh catalog with status.
		$status = TIMU_Module_Registry::get_catalog_with_status();

		wp_send_json_success(
			array(
				'message' => __( 'Module activated successfully.', 'core-support-thisismyurl' ),
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
		// Verify nonce.
		check_ajax_referer( 'timu_module_actions', 'nonce' );

		// Check capability - only network admin can deactivate network plugins.
		if ( ! is_multisite() || ! is_network_admin() || ! current_user_can( 'manage_network_plugins' ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'You do not have permission to deactivate network plugins.', 'core-support-thisismyurl' ),
				),
				403
			);
		}

		// Get and validate inputs.
		$slug = isset( $_POST['slug'] ) ? sanitize_text_field( wp_unslash( $_POST['slug'] ) ) : '';
		if ( empty( $slug ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Module slug is required.', 'core-support-thisismyurl' ),
				),
				400
			);
		}

		// Build plugin file path.
		$plugin_file = $slug . '/' . $slug . '.php';
		$plugin_path = WP_PLUGIN_DIR . '/' . $plugin_file;

		if ( ! file_exists( $plugin_path ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Plugin file not found.', 'core-support-thisismyurl' ),
				),
				404
			);
		}

		// Deactivate network-wide.
		deactivate_plugins( $plugin_file, false, true );

		// Refresh catalog with status.
		$status = TIMU_Module_Registry::get_catalog_with_status();

		wp_send_json_success(
			array(
				'message' => __( 'Module deactivated successfully.', 'core-support-thisismyurl' ),
				'status'  => $status[ $slug ] ?? array(),
			)
		);
	}
}

/* @changelog TIMU_Module_Actions class created for AJAX install/update/activate handlers. */
