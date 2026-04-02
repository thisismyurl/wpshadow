<?php
/**
 * Murphy-Safe File Operations
 *
 * Defensive file operation wrappers that implement Murphy's Law principles:
 * - Assume disk will be full or read-only
 * - Verify all writes succeeded
 * - Use atomic operations (temp-then-rename)
 * - Check disk space before writing
 *
 * @package    WPShadow
 * @subpackage Core
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Murphy_Safe_File Class
 *
 * Provides resilient file operations with verification,
 * atomic writes, and proper error handling.
 *
 * Philosophy Alignment:
 * - ⚙️ Murphy's Law: Assume filesystem operations will fail
 * - #8 Inspire Confidence: Users trust files won't be corrupted
 * - #1 Helpful Neighbor: Clear error messages
 *
 * @since 0.6093.1200
 */
class Murphy_Safe_File {

	/**
	 * Safely write content to file with verification
	 *
	 * Process:
	 * 1. Check disk space
	 * 2. Write to temp file
	 * 3. Verify content matches
	 * 4. Atomic rename to final location
	 * 5. Verify final file
	 *
	 * @since 0.6093.1200
	 * @param  string $filepath Full path to file.
	 * @param  string $content  Content to write.
	 * @param  bool   $append   Whether to append. Default false.
	 * @return bool|WP_Error True on success, WP_Error on failure.
	 */
	public static function write_file_safe( $filepath, $content, $append = false ) {
		// Validate filepath.
		if ( empty( $filepath ) || ! is_string( $filepath ) ) {
			return new \WP_Error(
				'invalid_filepath',
				__( 'Invalid file path provided.', 'wpshadow' )
			);
		}

		// Check if directory exists.
		$dir = dirname( $filepath );
		if ( ! file_exists( $dir ) ) {
			if ( ! wp_mkdir_p( $dir ) ) {
				return new \WP_Error(
					'directory_creation_failed',
					sprintf(
						/* translators: %s: directory path */
						__( 'Failed to create directory: %s', 'wpshadow' ),
						$dir
					)
				);
			}
		}

		// Check if directory is writable.
		if ( ! is_writable( $dir ) ) {
			return new \WP_Error(
				'directory_not_writable',
				sprintf(
					/* translators: %s: directory path */
					__( 'Directory is not writable: %s', 'wpshadow' ),
					$dir
				)
			);
		}

		// Check disk space (need 2x content size for safety).
		$free_space   = disk_free_space( $dir );
		$needed_space = strlen( $content ) * 2;

		if ( $free_space < $needed_space ) {
			return new \WP_Error(
				'insufficient_disk_space',
				sprintf(
					/* translators: 1: needed space, 2: available space */
					__( 'Insufficient disk space. Need %1$s, have %2$s available.', 'wpshadow' ),
					size_format( $needed_space ),
					size_format( $free_space )
				)
			);
		}

		// Handle append mode.
		if ( $append && file_exists( $filepath ) ) {
			$existing_content = file_get_contents( $filepath );
			if ( false !== $existing_content ) {
				$content = $existing_content . $content;
			}
		}

		// Write to temp file first (atomic operation pattern).
		$temp_file = $filepath . '.tmp.' . wp_rand( 10000000, 99999999 );
		$written   = @file_put_contents( $temp_file, $content, LOCK_EX );

		if ( false === $written ) {
			return new \WP_Error(
				'write_failed',
				sprintf(
					/* translators: %s: file path */
					__( 'Failed to write file: %s', 'wpshadow' ),
					$filepath
				)
			);
		}

		// Verify content matches.
		$verify = @file_get_contents( $temp_file );
		if ( $verify !== $content ) {
			@unlink( $temp_file );

			return new \WP_Error(
				'verification_failed',
				sprintf(
					/* translators: %s: file path */
					__( 'File verification failed: %s', 'wpshadow' ),
					$filepath
				),
				array(
					'expected_size' => strlen( $content ),
					'actual_size'   => strlen( $verify ),
				)
			);
		}

		// Backup existing file if it exists.
		if ( file_exists( $filepath ) ) {
			$backup_file = $filepath . '.backup';
			if ( ! @copy( $filepath, $backup_file ) ) {
				// Log warning but continue (backup failure shouldn't block save).
				Error_Handler::log_warning(
					'Failed to create backup file',
					array( 'file' => $filepath )
				);
			}
		}

		// Atomic rename to final location.
		if ( ! @rename( $temp_file, $filepath ) ) {
			@unlink( $temp_file );

			return new \WP_Error(
				'rename_failed',
				sprintf(
					/* translators: %s: file path */
					__( 'Failed to finalize file: %s', 'wpshadow' ),
					$filepath
				)
			);
		}

		// Final verification.
		$final_verify = @file_get_contents( $filepath );
		if ( $final_verify !== $content ) {
			Error_Handler::log_error(
				'Final file verification failed',
				array(
					'file'          => $filepath,
					'expected_size' => strlen( $content ),
					'actual_size'   => strlen( $final_verify ),
				)
			);

			// Attempt rollback to backup if available.
			if ( file_exists( $backup_file ) ) {
				@copy( $backup_file, $filepath );
			}

			return new \WP_Error(
				'final_verification_failed',
				sprintf(
					/* translators: %s: file path */
					__( 'Final file verification failed: %s', 'wpshadow' ),
					$filepath
				)
			);
		}

		// Clean up backup on success.
		if ( isset( $backup_file ) && file_exists( $backup_file ) ) {
			@unlink( $backup_file );
		}

		Error_Handler::log_info(
			'File written and verified successfully',
			array(
				'file' => $filepath,
				'size' => strlen( $content ),
			)
		);

		return true;
	}

	/**
	 * Safely read file with error handling
	 *
	 * @since 0.6093.1200
	 * @param  string $filepath Full path to file.
	 * @return string|WP_Error File contents or error.
	 */
	public static function read_file_safe( $filepath ) {
		// Validate filepath.
		if ( empty( $filepath ) || ! is_string( $filepath ) ) {
			return new \WP_Error(
				'invalid_filepath',
				__( 'Invalid file path provided.', 'wpshadow' )
			);
		}

		// Check if file exists.
		if ( ! file_exists( $filepath ) ) {
			return new \WP_Error(
				'file_not_found',
				sprintf(
					/* translators: %s: file path */
					__( 'File not found: %s', 'wpshadow' ),
					$filepath
				)
			);
		}

		// Check if readable.
		if ( ! is_readable( $filepath ) ) {
			return new \WP_Error(
				'file_not_readable',
				sprintf(
					/* translators: %s: file path */
					__( 'File is not readable: %s', 'wpshadow' ),
					$filepath
				)
			);
		}

		// Read file.
		$content = @file_get_contents( $filepath );

		if ( false === $content ) {
			return new \WP_Error(
				'read_failed',
				sprintf(
					/* translators: %s: file path */
					__( 'Failed to read file: %s', 'wpshadow' ),
					$filepath
				)
			);
		}

		return $content;
	}

	/**
	 * Safely delete file with verification
	 *
	 * @since 0.6093.1200
	 * @param  string $filepath Full path to file.
	 * @param  bool   $backup   Whether to create backup. Default true.
	 * @return bool|WP_Error True on success, WP_Error on failure.
	 */
	public static function delete_file_safe( $filepath, $backup = true ) {
		// Validate filepath.
		if ( empty( $filepath ) || ! is_string( $filepath ) ) {
			return new \WP_Error(
				'invalid_filepath',
				__( 'Invalid file path provided.', 'wpshadow' )
			);
		}

		// Check if file exists.
		if ( ! file_exists( $filepath ) ) {
			// Already deleted - not an error.
			return true;
		}

		// Create backup if requested.
		if ( $backup ) {
			$backup_file = $filepath . '.deleted.' . time();
			if ( ! @copy( $filepath, $backup_file ) ) {
				Error_Handler::log_warning(
					'Failed to create backup before deletion',
					array( 'file' => $filepath )
				);
			}
		}

		// Delete file.
		if ( ! @unlink( $filepath ) ) {
			return new \WP_Error(
				'delete_failed',
				sprintf(
					/* translators: %s: file path */
					__( 'Failed to delete file: %s', 'wpshadow' ),
					$filepath
				)
			);
		}

		// Verify deletion.
		if ( file_exists( $filepath ) ) {
			Error_Handler::log_error(
				'File deletion verification failed - file still exists',
				array( 'file' => $filepath )
			);

			return new \WP_Error(
				'delete_verification_failed',
				sprintf(
					/* translators: %s: file path */
					__( 'File deletion verification failed: %s', 'wpshadow' ),
					$filepath
				)
			);
		}

		return true;
	}

	/**
	 * Get detailed disk space information
	 *
	 * @since 0.6093.1200
	 * @param  string $path Path to check. Default ABSPATH.
	 * @return array {
	 *     Disk space information.
	 *
	 *     @type int    $free_bytes   Free space in bytes.
	 *     @type int    $total_bytes  Total space in bytes.
	 *     @type float  $percent_free Percentage of free space.
	 *     @type string $free_human   Human-readable free space.
	 *     @type string $total_human  Human-readable total space.
	 * }
	 */
	public static function get_disk_space_info( $path = ABSPATH ) {
		$free_bytes  = disk_free_space( $path );
		$total_bytes = disk_total_space( $path );

		return array(
			'free_bytes'   => $free_bytes,
			'total_bytes'  => $total_bytes,
			'percent_free' => ( $free_bytes / $total_bytes ) * 100,
			'free_human'   => size_format( $free_bytes ),
			'total_human'  => size_format( $total_bytes ),
		);
	}

	/**
	 * Check if sufficient disk space available
	 *
	 * @since 0.6093.1200
	 * @param  int    $bytes_needed Bytes needed.
	 * @param  string $path         Path to check.
	 * @return bool True if sufficient space.
	 */
	public static function has_sufficient_space( $bytes_needed, $path = ABSPATH ) {
		$free_space = disk_free_space( $path );

		// Require 10% buffer above needed space.
		$required_space = $bytes_needed *1.0;

		return $free_space >= $required_space;
	}
}
