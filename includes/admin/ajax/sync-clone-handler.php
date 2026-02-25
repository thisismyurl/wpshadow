<?php
/**
 * AJAX: Sync Site Clone
 *
 * @since   1.6030.2200
 * @package WPShadow\Admin
 */

declare(strict_types=1);

namespace WPShadow\Admin;

use WPShadow\Core\AJAX_Handler_Base;
use WPShadow\Core\Activity_Logger;
use WPShadow\Core\Error_Handler;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sync Clone Handler
 */
class AJAX_Sync_Clone extends AJAX_Handler_Base {
	/**
	 * Handle the AJAX request.
	 *
	 * @since 1.6030.2200
	 * @return void
	 */
	public static function handle() {
		self::verify_request( 'wpshadow_site_cloner', 'manage_options' );

		$clone_name = sanitize_key( self::get_post_param( 'clone_name', 'text', '', true ) );
		if ( '' === $clone_name ) {
			self::send_error( __( 'Invalid clone name', 'wpshadow' ) );
			return;
		}

		// Get existing clones
		$existing_clones = get_option( 'wpshadow_site_clones', array() );
		if ( ! is_array( $existing_clones ) ) {
			$existing_clones = array();
		}

		// Check if clone exists
		if ( ! isset( $existing_clones[ $clone_name ] ) ) {
			self::send_error( __( 'Clone not found', 'wpshadow' ) );
			return;
		}

		$clone_data = $existing_clones[ $clone_name ];

		try {
			// Create new snapshot
			$snapshot_result = self::create_vault_snapshot();
			if ( ! $snapshot_result['success'] ) {
				throw new \Exception( $snapshot_result['message'] );
			}

			// Sync files
			$sync_result = self::sync_clone_files( $clone_data );
			if ( ! $sync_result['success'] ) {
				throw new \Exception( $sync_result['message'] );
			}

			// Update sync time
			$existing_clones[ $clone_name ]['last_synced'] = time();
			update_option( 'wpshadow_site_clones', $existing_clones );

			// Log activity
			Activity_Logger::log(
				'site_clone_synced',
				array(
					'clone_name' => $clone_name,
					'clone_url'  => $clone_data['url'],
				)
			);

			self::send_success(
				array(
					'message'    => __( 'Clone synced successfully', 'wpshadow' ),
					'clone_name' => $clone_name,
				)
			);

		} catch ( \Exception $e ) {
			Error_Handler::log_error( $e->getMessage(), $e );
			self::send_error( $e->getMessage() );
		}
	}

	/**
	 * Create Vault Light snapshot.
	 *
	 * @since  1.6030.2200
	 * @return array Result array.
	 */
	private static function create_vault_snapshot() {
		if ( ! class_exists( 'WPShadow\\Backup\\Vault_Light' ) ) {
			return array(
				'success' => false,
				'message' => __( 'Vault Light is not available', 'wpshadow' ),
			);
		}

		try {
			$snapshot_id = \WPShadow\Backup\Vault_Light::create_snapshot(
				array(
					'description' => __( 'Clone sync snapshot', 'wpshadow' ),
				)
			);

			return array(
				'success'     => true,
				'snapshot_id' => $snapshot_id,
			);
		} catch ( \Exception $e ) {
			return array(
				'success' => false,
				'message' => $e->getMessage(),
			);
		}
	}

	/**
	 * Sync clone files.
	 *
	 * @since  1.6030.2200
	 * @param  array $clone_data Clone configuration.
	 * @return array Result array.
	 */
	private static function sync_clone_files( $clone_data ) {
		$clone_path = $clone_data['path'];
		$options    = isset( $clone_data['options'] ) ? $clone_data['options'] : array();

		require_once ABSPATH . 'wp-admin/includes/file.php';
		WP_Filesystem();

		// Sync based on original options
		if ( in_array( 'themes', $options, true ) ) {
			copy_dir( WP_CONTENT_DIR . '/themes', $clone_path . '/wp-content/themes' );
		}

		if ( in_array( 'plugins', $options, true ) ) {
			copy_dir( WP_CONTENT_DIR . '/plugins', $clone_path . '/wp-content/plugins' );
		}

		if ( in_array( 'uploads', $options, true ) ) {
			copy_dir( WP_CONTENT_DIR . '/uploads', $clone_path . '/wp-content/uploads' );
		}

		// Sync WordPress core
		copy_dir( ABSPATH . 'wp-admin', $clone_path . '/wp-admin' );
		copy_dir( ABSPATH . 'wp-includes', $clone_path . '/wp-includes' );

		return array( 'success' => true );
	}

	/**
	 * Sync clone database.
	 *
	 * @since  1.6030.2200
	 * @param  string $clone_name Clone identifier.
	 * @param  string $clone_url  Clone URL.
	 * @return array Result array.
	 */
	private static function sync_clone_database( $clone_name, $clone_url ) {
		return array(
			'success' => false,
			'message' => __( 'Database sync is currently unavailable in this build.', 'wpshadow' ),
		);
	}
}

// Register AJAX action
\add_action( 'wp_ajax_wpshadow_sync_clone', array( '\WPShadow\\Admin\\AJAX_Sync_Clone', 'handle' ) );
