<?php
/**
 * Run Local Backup Handler.
 *
 * Triggers an immediate Vault Lite local-only backup from the Vault Lite page
 * or via authenticated AJAX.
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
 * Handle immediate local backup requests.
 */
class Run_Local_Backup_Handler extends AJAX_Handler_Base {

	/**
	 * Register AJAX and admin-post hooks for manual local backups.
	 *
	 * @since  0.6095
	 * @return void
	 */
	public static function register(): void {
		if ( ! has_action( 'wp_ajax_thisismyurl_shadow_run_local_backup', array( __CLASS__, 'handle' ) ) ) {
			add_action( 'wp_ajax_thisismyurl_shadow_run_local_backup', array( __CLASS__, 'handle' ) );
		}

		if ( ! has_action( 'admin_post_thisismyurl_shadow_run_local_backup', array( __CLASS__, 'handle_admin_post' ) ) ) {
			add_action( 'admin_post_thisismyurl_shadow_run_local_backup', array( __CLASS__, 'handle_admin_post' ) );
		}
	}

	/**
	 * Handle authenticated AJAX requests for immediate local backups.
	 *
	 * @since  0.6095
	 * @return void Sends JSON response and exits.
	 */
	public static function handle(): void {
		self::verify_request( 'thisismyurl_shadow_run_local_backup', 'manage_options', 'nonce' );

		$result = class_exists( '\\ThisIsMyURL\\Shadow\\Guardian\\Backup_Manager' )
			? \ThisIsMyURL\Shadow\Guardian\Backup_Manager::create_backup(
				array(
					'trigger' => 'manual',
					'context' => 'ajax',
				)
			)
			: array(
				'success' => false,
				'message' => __( 'The local backup manager is not available.', 'thisismyurl-shadow' ),
			);

		if ( ! empty( $result['success'] ) ) {
			self::send_success( $result );
		}

		self::send_error( $result['message'] ?? __( 'Local backup failed.', 'thisismyurl-shadow' ), $result );
	}

	/**
	 * Handle admin-post requests from the Vault Lite screen.
	 *
	 * @since  0.6095
	 * @return void Redirects back to the Vault Lite page with result query args.
	 */
	public static function handle_admin_post(): void {
		self::verify_admin_request( 'thisismyurl_shadow_run_local_backup', 'manage_options' );

		$result = class_exists( '\\ThisIsMyURL\\Shadow\\Guardian\\Backup_Manager' )
			? \ThisIsMyURL\Shadow\Guardian\Backup_Manager::create_backup(
				array(
					'trigger' => 'manual',
					'context' => 'vault-lite-page',
				)
			)
			: array(
				'success' => false,
				'message' => __( 'The local backup manager is not available.', 'thisismyurl-shadow' ),
			);

		$redirect = wp_get_referer();
		if ( ! $redirect ) {
			$redirect = admin_url( 'admin.php?page=thisismyurl-shadow-vault-lite' );
		}

		$redirect = remove_query_arg(
			array( 'thisismyurl_shadow_backup_run', 'thisismyurl_shadow_backup_file' ),
			$redirect
		);

		if ( empty( $result['success'] ) ) {
			$redirect = add_query_arg(
				array(
					'thisismyurl_shadow_backup_run' => 'error',
				),
				$redirect
			);
		}

		wp_safe_redirect( $redirect );
		exit;
	}
}
