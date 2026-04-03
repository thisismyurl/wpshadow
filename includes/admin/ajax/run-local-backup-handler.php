<?php
/**
 * Run Local Backup Handler.
 *
 * Triggers an immediate Vault Light local-only backup from the settings page
 * or via authenticated AJAX.
 *
 * @package WPShadow
 * @since   0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Admin\Ajax;

use WPShadow\Core\AJAX_Handler_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handle immediate local backup requests.
 */
class Run_Local_Backup_Handler extends AJAX_Handler_Base {

	/**
	 * Register AJAX and admin-post hooks for manual local backups.
	 *
	 * @since  0.6093.1200
	 * @return void
	 */
	public static function register(): void {
		add_action( 'wp_ajax_wpshadow_run_local_backup', array( __CLASS__, 'handle' ) );
		add_action( 'admin_post_wpshadow_run_local_backup', array( __CLASS__, 'handle_admin_post' ) );
	}

	/**
	 * Handle authenticated AJAX requests for immediate local backups.
	 *
	 * @since  0.6093.1200
	 * @return void Sends JSON response and exits.
	 */
	public static function handle(): void {
		self::verify_request( 'wpshadow_run_local_backup', 'manage_options', 'nonce' );

		$result = class_exists( '\\WPShadow\\Guardian\\Backup_Manager' )
			? \WPShadow\Guardian\Backup_Manager::create_backup(
				array(
					'trigger' => 'manual',
					'context' => 'ajax',
				)
			)
			: array(
				'success' => false,
				'message' => __( 'The local backup manager is not available.', 'wpshadow' ),
			);

		if ( ! empty( $result['success'] ) ) {
			self::send_success( $result );
		}

		self::send_error( $result['message'] ?? __( 'Local backup failed.', 'wpshadow' ), $result );
	}

	/**
	 * Handle admin-post requests from the settings screen.
	 *
	 * @since  0.6093.1200
	 * @return void Redirects back to the settings page with result query args.
	 */
	public static function handle_admin_post(): void {
		self::verify_admin_request( 'wpshadow_run_local_backup', 'manage_options' );

		$result = class_exists( '\\WPShadow\\Guardian\\Backup_Manager' )
			? \WPShadow\Guardian\Backup_Manager::create_backup(
				array(
					'trigger' => 'manual',
					'context' => 'settings-page',
				)
			)
			: array(
				'success' => false,
				'message' => __( 'The local backup manager is not available.', 'wpshadow' ),
			);

		$redirect = wp_get_referer();
		if ( ! $redirect ) {
			$redirect = admin_url( 'admin.php?page=wpshadow-settings&tab=backups' );
		}

		$redirect = add_query_arg(
			array(
				'wpshadow_backup_run'  => ! empty( $result['success'] ) ? 'success' : 'error',
				'wpshadow_backup_file' => isset( $result['file'] ) ? sanitize_file_name( (string) $result['file'] ) : '',
			),
			$redirect
		);

		wp_safe_redirect( $redirect );
		exit;
	}
}
