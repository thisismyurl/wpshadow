<?php
/**
 * Restore Local Backup Handler.
 *
 * Handles confirmed restore requests from the Vault Lite page.
 *
 * @package ThisIsMyURL\Shadow
 * @since   0.6095
 */

declare(strict_types=1);

namespace ThisIsMyURL\Shadow\Admin\Ajax;

use ThisIsMyURL\Shadow\Core\AJAX_Handler_Base;

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
		if ( ! has_action( 'admin_post_thisismyurl_shadow_restore_local_backup', array( __CLASS__, 'handle_admin_post' ) ) ) {
			add_action( 'admin_post_thisismyurl_shadow_restore_local_backup', array( __CLASS__, 'handle_admin_post' ) );
		}
	}

	/**
	 * Handle a confirmed restore request from the Vault Lite modal.
	 *
	 * @since  0.6095
	 * @return void Redirects back to the Vault Lite page with the result.
	 */
	public static function handle_admin_post(): void {
		self::verify_admin_request( 'thisismyurl_shadow_restore_local_backup', 'manage_options' );

		$backup_file = self::get_request_param( 'backup_file', 'file', '', true );

		$result = class_exists( '\\ThisIsMyURL\\Shadow\\Guardian\\Backup_Manager' )
			? \ThisIsMyURL\Shadow\Guardian\Backup_Manager::restore_backup( $backup_file )
			: array(
				'success' => false,
				'message' => __( 'The local backup manager is not available.', 'thisismyurl-shadow' ),
			);

		$redirect = wp_get_referer();
		if ( ! $redirect ) {
			$redirect = admin_url( 'admin.php?page=thisismyurl-shadow-vault-lite' );
		}

		$redirect = add_query_arg(
			array(
				'thisismyurl_shadow_backup_restored' => ! empty( $result['success'] ) ? 'success' : 'error',
				'thisismyurl_shadow_restored_file'   => $backup_file,
				'thisismyurl_shadow_restore_message' => isset( $result['message'] ) ? (string) $result['message'] : '',
			),
			$redirect
		);

		wp_safe_redirect( $redirect );
		exit;
	}
}
