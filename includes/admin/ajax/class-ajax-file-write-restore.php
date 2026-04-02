<?php
/**
 * File Write Restore AJAX Handler
 *
 * Reads the backup stored by Ajax_File_Write_Backup and writes it back to
 * the original file path. Provides a one-click rollback for file-write
 * treatments that have gone wrong.
 *
 * The restore requires:
 *   - A backup to exist in the options table
 *   - The target file to be writable (or not yet exist)
 *   - The requesting user to have manage_options capability
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
 * Restores a file from its stored backup.
 */
class Ajax_File_Write_Restore extends AJAX_Handler_Base {

	/**
	 * Register WordPress AJAX action.
	 *
	 * @return void
	 */
	public static function register(): void {
		add_action( 'wp_ajax_wpshadow_file_write_restore', [ __CLASS__, 'handle' ] );
	}

	/**
	 * Handle restore request.
	 *
	 * POST params:
	 *   nonce       (string) wpshadow_file_write_restore nonce
	 *   finding_id  (string) treatment finding ID
	 *
	 * @return void
	 */
	public static function handle(): void {
		self::verify_request( 'wpshadow_file_write_restore', 'manage_options', 'nonce' );

		$finding_id = self::get_post_param( 'finding_id', 'text', '', true );

		$info = self::get_treatment_info( $finding_id );
		if ( ! $info ) {
			self::send_error( __( 'Unknown treatment.', 'wpshadow' ) );
		}

		$file_path  = $info['target_file'];
		$backup_key = 'wpshadow_file_backup_' . md5( $file_path );
		$backup     = get_option( $backup_key, null );

		if ( ! is_array( $backup ) || empty( $backup['content'] ) ) {
			self::send_error( __( 'No backup found for this file. Create a backup first.', 'wpshadow' ) );
		}

		// Verify the stored file path matches (prevent path substitution).
		if ( isset( $backup['file_path'] ) && $backup['file_path'] !== $file_path ) {
			self::send_error( __( 'Backup file path mismatch. Aborting restore for safety.', 'wpshadow' ) );
		}

		if ( file_exists( $file_path ) && ! is_writable( $file_path ) ) {
			self::send_error( __( 'The target file is not writable. Please check file permissions.', 'wpshadow' ) );
		}

		// Write backup content back to disk.
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_file_put_contents
		$result = file_put_contents( $file_path, $backup['content'] );

		if ( false === $result ) {
			self::send_error( __( 'Could not write to the file. Please check file permissions.', 'wpshadow' ) );
		}

		// Also call treatment undo() if available, to clean up any option flags.
		$class = $info['class'];
		if ( method_exists( $class, 'undo' ) ) {
			$class::undo();
		}

		\WPShadow\Core\Activity_Logger::log(
			'file_write_restored',
			/* translators: %s: finding ID */
			sprintf( __( 'File restored from backup: %s', 'wpshadow' ), $finding_id ),
			'security',
			[ 'finding_id' => $finding_id, 'file_path' => $file_path ]
		);

		self::send_success( [
			'message'  => __( 'File restored from backup successfully.', 'wpshadow' ),
			'finding_id' => $finding_id,
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
}
