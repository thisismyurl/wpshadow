<?php
/**
 * File Write Backup AJAX Handler
 *
 * Creates a backup of the target file by reading its current content and
 * storing it in the WordPress options table. Optionally returns the content
 * as a base64-encoded data URI so the admin can download it.
 *
 * Backup storage key: wpshadow_file_backup_{md5( $file_path )}
 * Backup schema:
 *   array(
 *     'file_path'  => string,
 *     'content'    => string,
 *     'created_at' => int (Unix timestamp),
 *   )
 *
 * @package WPShadow
 * @subpackage Admin\Ajax
 * @since 0.6093.1300
 */

declare(strict_types=1);

namespace WPShadow\Admin\Ajax;

use WPShadow\Core\AJAX_Handler_Base;
use WPShadow\Admin\File_Write_Registry;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Creates and stores a file backup.
 */
class Ajax_File_Write_Backup extends AJAX_Handler_Base {

	/**
	 * Register WordPress AJAX action.
	 *
	 * @return void
	 */
	public static function register(): void {
		add_action( 'wp_ajax_wpshadow_file_write_backup', [ __CLASS__, 'handle' ] );
	}

	/**
	 * Handle backup request.
	 *
	 * POST params:
	 *   nonce       (string) wpshadow_file_write_backup nonce
	 *   finding_id  (string) treatment finding ID
	 *
	 * @return void
	 */
	public static function handle(): void {
		self::verify_request( 'wpshadow_file_write_backup', 'manage_options', 'nonce' );

		$finding_id = self::get_post_param( 'finding_id', 'text', '', true );

		// Resolve the target file from the registered treatment.
		$info = self::get_treatment_info( $finding_id );
		if ( ! $info ) {
			self::send_error( __( 'Unknown treatment.', 'wpshadow' ) );
		}

		$file_path = self::assert_allowed_managed_file_path( (string) $info['target_file'] );

		if ( ! file_exists( $file_path ) ) {
			self::send_error( __( 'Target file does not exist.', 'wpshadow' ) );
		}

		if ( ! is_readable( $file_path ) ) {
			self::send_error( __( 'Target file is not readable. Please check file permissions.', 'wpshadow' ) );
		}

		$content = self::read_wp_filesystem_file( $file_path );
		if ( false === $content ) {
			self::send_error( __( 'Could not read the target file through the WordPress filesystem API.', 'wpshadow' ) );
		}

		$backup = [
			'file_path'  => $file_path,
			'content'    => $content,
			'created_at' => time(),
		];

		$backup_key = 'wpshadow_file_backup_' . md5( $file_path );
		update_option( $backup_key, $backup, false );

		\WPShadow\Core\Activity_Logger::log(
			'file_backup_created',
			/* translators: %s: file path */
			sprintf( __( 'Backup created for file: %s', 'wpshadow' ), $file_path ),
			'security',
			[ 'file_path' => $file_path, 'finding_id' => $finding_id ]
		);

		self::send_success( [
			'created_at'      => $backup['created_at'],
			'created_at_human' => human_time_diff( $backup['created_at'], time() ) . ' ' . __( 'ago', 'wpshadow' ),
			'download_url'     => self::make_download_url( $file_path, $content ),
			'message'          => __( 'Backup created successfully.', 'wpshadow' ),
		] );
	}

	// -------------------------------------------------------------------------
	// Helpers
	// -------------------------------------------------------------------------

	/**
	 * Resolve treatment info from registry by finding ID.
	 *
	 * @param string $finding_id
	 * @return array|null
	 */
	private static function get_treatment_info( string $finding_id ): ?array {
		foreach ( File_Write_Registry::get_all() as $class ) {
			if ( class_exists( $class ) && method_exists( $class, 'get_finding_id' ) ) {
				if ( $class::get_finding_id() === $finding_id ) {
					return File_Write_Registry::get_treatment_info( $class );
				}
			}
		}
		return null;
	}

	/**
	 * Build a data-URI download URL the browser can use to save the backup.
	 *
	 * @param string $file_path  Absolute path (used to derive filename).
	 * @param string $content    File content.
	 * @return string  data: URI.
	 */
	private static function make_download_url( string $file_path, string $content ): string {
		return 'data:text/plain;charset=utf-8;base64,' . base64_encode( $content ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
	}
}
