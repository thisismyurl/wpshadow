<?php
/**
 * Restore Local Backup Handler.
 *
 * Handles confirmed restore requests from the Vault Lite page.
 *
 * @package WPShadow
 * @since   0.6095
 */

declare(strict_types=1);

namespace WPShadow\Admin\Ajax;

use WPShadow\Core\AJAX_Handler_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handle local backup restore requests.
 */
class Restore_Local_Backup_Handler extends AJAX_Handler_Base {

	/**
	 * Register the admin-post hook for confirmed restore actions.
	 *
	 * @since  0.6095
	 * @return void
	 */
	public static function register(): void {
		if ( ! has_action( 'admin_post_wpshadow_restore_local_backup', array( __CLASS__, 'handle_admin_post' ) ) ) {
			add_action( 'admin_post_wpshadow_restore_local_backup', array( __CLASS__, 'handle_admin_post' ) );
		}
	}

	/**
	 * Handle a confirmed restore request from the Vault Lite modal.
	 *
	 * @since  0.6095
	 * @return void Redirects back to the Vault Lite page with the result.
	 */
	public static function handle_admin_post(): void {
		self::verify_admin_request( 'wpshadow_restore_local_backup', 'manage_options' );

		$backup_file = self::get_request_param( 'backup_file', 'file', '', true );

		$result = class_exists( '\\WPShadow\\Guardian\\Backup_Manager' )
			? \WPShadow\Guardian\Backup_Manager::restore_backup( $backup_file )
			: array(
				'success' => false,
				'message' => __( 'The local backup manager is not available.', 'wpshadow' ),
			);

		$redirect = wp_get_referer();
		if ( ! $redirect ) {
			$redirect = admin_url( 'admin.php?page=wpshadow-vault-lite' );
		}

		$redirect = add_query_arg(
			array(
				'wpshadow_backup_restored' => ! empty( $result['success'] ) ? 'success' : 'error',
				'wpshadow_restored_file'   => $backup_file,
				'wpshadow_restore_message' => isset( $result['message'] ) ? (string) $result['message'] : '',
			),
			$redirect
		);

		wp_safe_redirect( $redirect );
		exit;
	}
}
