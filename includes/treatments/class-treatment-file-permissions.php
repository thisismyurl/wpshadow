<?php
/**
 * Treatment: File Permissions
 *
 * Scans the same three targets that the diagnostic checks (wp-config.php,
 * .htaccess, uploads directory) and applies safe permission modes to any
 * that are found to be overly permissive:
 *
 *   - wp-config.php world-readable or world-writable → chmod to 0640
 *   - .htaccess world-writable                       → chmod to 0644
 *   - uploads/ directory at 0777                     → chmod to 0755
 *
 * Each original mode is saved to an option so that undo() can restore exactly
 * what was there before.
 *
 * Requirement: PHP must own (or have write access to) each target file.
 *
 * @package ThisIsMyURL\Shadow
 * @subpackage Treatments
 * @since 0.6095
 */

declare(strict_types=1);

namespace ThisIsMyURL\Shadow\Treatments;

use ThisIsMyURL\Shadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Corrects unsafe file permissions on wp-config.php, .htaccess, and uploads/.
 */
class Treatment_File_Permissions extends Treatment_Base {

	/** @var string */
	protected static $slug = 'file-permissions';

	const OPTION_KEY = 'thisismyurl_shadow_file_perms_backup';

	// =========================================================================
	// Treatment_Base contract
	// =========================================================================

	public static function get_finding_id(): string {
		return self::$slug;
	}

	public static function get_risk_level(): string {
		return 'medium';
	}

	/**
	 * Scan and fix unsafe permissions on known WordPress paths.
	 *
	 * @return array{success:bool, message:string}
	 */
	public static function apply(): array {
		$targets  = self::get_targets();
		$backup   = [];
		$fixed    = [];
		$failures = [];

		foreach ( $targets as $label => $target ) {
			$path          = $target['path'];
			$expected_mode = $target['expected_mode'];

			if ( ! file_exists( $path ) ) {
				continue;
			}

			$current = (int) ( fileperms( $path ) & 0777 );

			// Only change when the path violates the expected condition.
			if ( ! $target['is_unsafe']( $current ) ) {
				continue;
			}

			$backup[ $path ] = $current;

			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_chmod
			$ok = chmod( $path, $expected_mode );
			clearstatcache( true, $path );

			if ( $ok ) {
				$fixed[] = sprintf( '%s: %04o → %04o', $label, $current, $expected_mode );
			} else {
				$failures[] = sprintf( '%s (chmod failed)', $label );
			}
		}

		if ( ! empty( $backup ) ) {
			update_option( self::OPTION_KEY, $backup, false );
		}

		if ( empty( $fixed ) && empty( $failures ) ) {
			return [
				'success' => true,
				'message' => __( 'All checked paths already have safe permissions — no changes were necessary.', 'thisismyurl-shadow' ),
			];
		}

		if ( ! empty( $failures ) ) {
			return [
				'success' => false,
				'message' => sprintf(
					/* translators: 1: changes made, 2: failures */
					__( 'Some permissions were fixed (%1$s) but chmod() failed for: %2$s. The PHP process may not own those files.', 'thisismyurl-shadow' ),
					implode( ', ', $fixed ),
					implode( ', ', $failures )
				),
			];
		}

		return [
			'success' => true,
			'message' => sprintf(
				/* translators: %s: list of fixed items */
				__( 'File permissions corrected: %s.', 'thisismyurl-shadow' ),
				implode( '; ', $fixed )
			),
		];
	}

	/**
	 * Restore original permissions saved during apply().
	 *
	 * @return array{success:bool, message:string}
	 */
	public static function undo(): array {
		$backup = get_option( self::OPTION_KEY, null );

		if ( empty( $backup ) || ! is_array( $backup ) ) {
			return [
				'success' => false,
				'message' => __( 'No backup permissions found — cannot restore. Check original permissions manually.', 'thisismyurl-shadow' ),
			];
		}

		$restored = [];
		$failures = [];

		foreach ( $backup as $path => $old_mode ) {
			if ( ! file_exists( $path ) ) {
				continue;
			}
			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_chmod
			if ( chmod( $path, (int) $old_mode ) ) {
				$restored[] = sprintf( '%s: %04o', basename( $path ), (int) $old_mode );
				clearstatcache( true, $path );
			} else {
				$failures[] = basename( $path );
			}
		}

		delete_option( self::OPTION_KEY );

		if ( ! empty( $failures ) ) {
			return [
				'success' => false,
				'message' => sprintf(
					/* translators: %s: list of failed files */
					__( 'Restore failed for: %s. Please set permissions manually via SFTP.', 'thisismyurl-shadow' ),
					implode( ', ', $failures )
				),
			];
		}

		return [
			'success' => true,
			'message' => sprintf(
				/* translators: %s: list of restored items */
				__( 'File permissions restored: %s.', 'thisismyurl-shadow' ),
				implode( '; ', $restored )
			),
		];
	}

	// =========================================================================
	// Internal helpers
	// =========================================================================

	/**
	 * Build the array of paths to check, along with their safe modes and unsafe
	 * condition callbacks.
	 *
	 * @return array<string, array{path:string, expected_mode:int, is_unsafe:callable}>
	 */
	private static function get_targets(): array {
		$upload_dir   = wp_upload_dir();
		$uploads_base = $upload_dir['basedir'];

		return [
			'wp-config.php' => [
				'path'          => ABSPATH . 'wp-config.php',
				'expected_mode' => 0640,
				// Flag if world-readable OR world-writable.
				'is_unsafe'     => static function ( int $mode ): bool {
					return (bool) ( $mode & 0004 ) || (bool) ( $mode & 0002 );
				},
			],
			'.htaccess' => [
				'path'          => ABSPATH . '.htaccess',
				'expected_mode' => 0644,
				// Flag only if world-writable.
				'is_unsafe'     => static function ( int $mode ): bool {
					return (bool) ( $mode & 0002 );
				},
			],
			'uploads/' => [
				'path'          => $uploads_base,
				'expected_mode' => 0755,
				// Flag only if fully open (0777).
				'is_unsafe'     => static function ( int $mode ): bool {
					return 0777 === $mode;
				},
			],
		];
	}
}
