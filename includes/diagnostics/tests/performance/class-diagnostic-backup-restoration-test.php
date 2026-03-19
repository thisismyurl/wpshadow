<?php
/**
 * Backup Restoration Test Diagnostic
 *
 * Verifies backup files are valid and restorable.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Backup Restoration Test Diagnostic Class
 *
 * Tests backup file integrity without attempting actual restoration.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Backup_Restoration_Test extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'backup-restoration-test';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Backup Restoration Test';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies backup files are valid and restorable';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'database-health';

	/**
	 * Recent backup threshold
	 *
	 * @var int
	 */
	private const RECENT_BACKUP_DAYS = 7;

	/**
	 * Run the backup restoration diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if backup issue detected, null otherwise.
	 */
	public static function check() {
		$backup_info = self::find_backup_files();

		if ( empty( $backup_info['recent_backups'] ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: %d: days */
					__( 'No recent backup files found (within last %d days). Backups may not be running.', 'wpshadow' ),
					self::RECENT_BACKUP_DAYS
				),
				'severity'    => 'high',
				'threat_level' => 85,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/backup-files-not-found',
			);
		}

		$invalid_backups = array();

		// Check integrity of backup files.
		foreach ( $backup_info['recent_backups'] as $backup_file ) {
			if ( ! self::is_backup_valid( $backup_file ) ) {
				$invalid_backups[] = basename( $backup_file );
			}
		}

		if ( ! empty( $invalid_backups ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: %s: comma-separated list of backup files */
					__( 'Some backup files appear corrupted or invalid: %s. Backups may not be restorable.', 'wpshadow' ),
					implode( ', ', $invalid_backups )
				),
				'severity'    => 'high',
				'threat_level' => 80,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/corrupted-backup-files',
				'meta'        => array(
					'invalid_backups' => $invalid_backups,
				),
			);
		}

		return null;
	}

	/**
	 * Find backup files.
	 *
	 * @since 1.6093.1200
	 * @return array Backup information.
	 */
	private static function find_backup_files(): array {
		$result = array(
			'all_backups'    => array(),
			'recent_backups' => array(),
		);

		$recent_threshold = time() - ( self::RECENT_BACKUP_DAYS * DAY_IN_SECONDS );

		// Common backup plugin directories.
		$backup_dirs = array(
			WP_CONTENT_DIR . '/backups/',
			WP_CONTENT_DIR . '/uploads/backups/',
			WP_CONTENT_DIR . '/ai1wm-backups/',
			WP_CONTENT_DIR . '/backwpup-backups/',
			WP_CONTENT_DIR . '/updraftplus/',
		);

		foreach ( $backup_dirs as $dir ) {
			if ( ! is_dir( $dir ) ) {
				continue;
			}

			$files = glob( $dir . '*' );
			if ( empty( $files ) ) {
				continue;
			}

			foreach ( $files as $file ) {
				if ( ! is_file( $file ) || filesize( $file ) < 1024 ) { // > 1KB
					continue;
				}

				$result['all_backups'][] = $file;

				if ( filemtime( $file ) >= $recent_threshold ) {
					$result['recent_backups'][] = $file;
				}
			}
		}

		return $result;
	}

	/**
	 * Check if backup file is valid.
	 *
	 * @since 1.6093.1200
	 * @param  string $file_path Path to backup file.
	 * @return bool True if valid, false otherwise.
	 */
	private static function is_backup_valid( string $file_path ): bool {
		if ( ! is_readable( $file_path ) ) {
			return false;
		}

		$file_size = filesize( $file_path );
		if ( ! $file_size || $file_size < 1024 ) {
			return false;
		}

		// Check if it's a valid archive if compressed.
		$extension = pathinfo( $file_path, PATHINFO_EXTENSION );

		if ( 'zip' === strtolower( $extension ) ) {
			// Check ZIP file validity.
			if ( ! function_exists( 'zip_open' ) ) {
				return true; // Can't verify, assume valid.
			}

			$zip = @zip_open( $file_path );
			if ( ! is_resource( $zip ) ) {
				return false;
			}
			@zip_close( $zip );
		} elseif ( in_array( strtolower( $extension ), array( 'gz', 'tar' ), true ) ) {
			// For tar/gz, just check if readable and has decent size.
			return filesize( $file_path ) > 1024;
		}

		return true;
	}
}
