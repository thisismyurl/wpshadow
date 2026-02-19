<?php
/**
 * AJAX: Delete Site Clone
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
 * Delete Clone Handler
 */
class AJAX_Delete_Clone extends AJAX_Handler_Base {
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
			// Delete files
			if ( ! empty( $clone_data['path'] ) && is_dir( $clone_data['path'] ) ) {
				self::delete_directory( $clone_data['path'] );
			}

			// Delete database tables
			self::delete_clone_database( $clone_name );

			// Remove from clones list
			unset( $existing_clones[ $clone_name ] );
			update_option( 'wpshadow_site_clones', $existing_clones );

			// Log activity
			Activity_Logger::log(
				'site_clone_deleted',
				array(
					'clone_name' => $clone_name,
					'clone_url'  => $clone_data['url'],
				)
			);

			self::send_success(
				array(
					'message'    => __( 'Clone deleted successfully', 'wpshadow' ),
					'clone_name' => $clone_name,
				)
			);

		} catch ( \Exception $e ) {
			Error_Handler::log_error( $e->getMessage(), $e );
			self::send_error( $e->getMessage() );
		}
	}

	/**
	 * Recursively delete directory.
	 *
	 * @since  1.6030.2200
	 * @param  string $dir Directory path.
	 * @return bool Success status.
	 */
	private static function delete_directory( $dir ) {
		if ( ! is_dir( $dir ) ) {
			return false;
		}

		require_once ABSPATH . 'wp-admin/includes/file.php';
		WP_Filesystem();
		global $wp_filesystem;

		return $wp_filesystem->rmdir( $dir, true );
	}

	/**
	 * Delete clone database tables.
	 *
	 * @since  1.6030.2200
	 * @param  string $clone_name Clone identifier.
	 * @return void
	 */
	private static function delete_clone_database( $clone_name ) {
		global $wpdb;

		$clone_prefix = $wpdb->prefix . $clone_name . '_';
		$tables       = $wpdb->get_col( $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $clone_prefix ) . '%' ) );

		foreach ( $tables as $table ) {
			$wpdb->query( "DROP TABLE IF EXISTS `{$table}`" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		}
	}
}

// Register AJAX action
\add_action( 'wp_ajax_wpshadow_delete_clone', array( '\WPShadow\\Admin\\AJAX_Delete_Clone', 'handle' ) );
