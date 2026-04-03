<?php
/**
 * Download Local Backup Handler.
 *
 * Streams a confirmed Vault Lite backup archive to the browser.
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
 * Handle local backup download requests.
 */
class Download_Local_Backup_Handler extends AJAX_Handler_Base {

	/**
	 * Register the admin-post hook for backup downloads.
	 *
	 * @since  0.6093.1200
	 * @return void
	 */
	public static function register(): void {
		if ( ! has_action( 'admin_post_wpshadow_download_local_backup', array( __CLASS__, 'handle_admin_post' ) ) ) {
			add_action( 'admin_post_wpshadow_download_local_backup', array( __CLASS__, 'handle_admin_post' ) );
		}
	}

	/**
	 * Stream a backup archive to the browser.
	 *
	 * @since  0.6093.1200
	 * @return void
	 */
	public static function handle_admin_post(): void {
		self::verify_admin_request( 'wpshadow_download_local_backup', 'manage_options' );

		$backup_file = isset( $_REQUEST['backup_file'] ) ? sanitize_file_name( wp_unslash( $_REQUEST['backup_file'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$entry       = class_exists( '\WPShadow\Guardian\Backup_Manager' )
			? \WPShadow\Guardian\Backup_Manager::get_backup_entry( $backup_file )
			: null;

		$path = is_array( $entry ) && ! empty( $entry['path'] ) ? (string) $entry['path'] : '';

		if ( '' === $path || ! is_readable( $path ) ) {
			$redirect = wp_get_referer();
			if ( ! $redirect ) {
				$redirect = admin_url( 'admin.php?page=wpshadow-vault-lite' );
			}

			$redirect = add_query_arg(
				array(
					'wpshadow_backup_download'  => 'error',
					'wpshadow_backup_file'      => $backup_file,
					'wpshadow_download_message' => rawurlencode( __( 'The selected backup file is not available for download.', 'wpshadow' ) ),
				),
				$redirect
			);

			wp_safe_redirect( $redirect );
			exit;
		}

		nocache_headers();
		status_header( 200 );
		header( 'Content-Description: File Transfer' );
		header( 'Content-Type: application/zip' );
		header( 'Content-Disposition: attachment; filename="' . basename( $path ) . '"' );
		header( 'Content-Length: ' . (string) filesize( $path ) );
		header( 'X-Content-Type-Options: nosniff' );

		readfile( $path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_readfile
		exit;
	}
}