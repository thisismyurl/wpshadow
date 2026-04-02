<?php
/**
 * File Backup Working Diagnostic
 *
 * Verifies file backups include wp-content and recent uploads.
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
 * File Backup Working Diagnostic Class
 *
 * Ensures file backups are recent and include critical directories.
 *
 * @since 1.6093.1200
 */
class Diagnostic_File_Backup_Working extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'file-backup-working';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'File Backup Working';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks that file backups are recent and include critical directories';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'backup';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$backups = apply_filters( 'wpshadow_get_backups', array() );
		$file_backups = self::filter_backups_by_type( $backups, 'files' );

		if ( empty( $file_backups ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'No file backups detected. Ensure backups include wp-content and uploads.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 75,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/file-backup-working',
			);
		}

		$latest = self::get_latest_backup( $file_backups );
		$timestamp = (int) ( $latest['timestamp'] ?? 0 );
		$file = $latest['file'] ?? '';
		$includes = $latest['includes'] ?? array();

		if ( $timestamp > 0 ) {
			$days_since = (int) floor( ( time() - $timestamp ) / DAY_IN_SECONDS );
			if ( $days_since > 7 ) {
				return array(
					'id'           => self::$slug,
					'title'        => self::$title,
					'description'  => sprintf(
						/* translators: %d: days since backup */
						__( 'File backup is %d days old. Back up files at least weekly.', 'wpshadow' ),
						$days_since
					),
					'severity'     => 'high',
					'threat_level' => 70,
					'auto_fixable' => false,
					'kb_link'      => 'https://wpshadow.com/kb/file-backup-working',
					'meta'         => array(
						'days_since' => $days_since,
					),
				);
			}
		}

		if ( $file && file_exists( $file ) ) {
			$size_mb = filesize( $file ) / ( 1024 * 1024 );
			if ( $size_mb < 5 ) {
				return array(
					'id'           => self::$slug,
					'title'        => self::$title,
					'description'  => __( 'File backup size appears unusually small. Verify that wp-content and uploads are included.', 'wpshadow' ),
					'severity'     => 'medium',
					'threat_level' => 60,
					'auto_fixable' => false,
					'kb_link'      => 'https://wpshadow.com/kb/file-backup-working',
					'meta'         => array(
						'file_size_mb' => round( $size_mb, 2 ),
					),
				);
			}
		}

		if ( is_array( $includes ) && ! empty( $includes ) ) {
			$missing = array();
			if ( ! in_array( 'wp-content', $includes, true ) ) {
				$missing[] = 'wp-content';
			}
			if ( ! in_array( 'uploads', $includes, true ) ) {
				$missing[] = 'uploads';
			}

			if ( ! empty( $missing ) ) {
				return array(
					'id'           => self::$slug,
					'title'        => self::$title,
					'description'  => sprintf(
						/* translators: %s: missing directories */
						__( 'File backups are missing critical directories: %s', 'wpshadow' ),
						implode( ', ', $missing )
					),
					'severity'     => 'high',
					'threat_level' => 75,
					'auto_fixable' => false,
					'kb_link'      => 'https://wpshadow.com/kb/file-backup-working',
					'meta'         => array(
						'missing' => $missing,
					),
				);
			}
		}

		return null;
	}

	/**
	 * Filter backups by type.
	 *
	 * @since 1.6093.1200
	 * @param  array $backups Backup list.
	 * @param  string $type Backup type.
	 * @return array Filtered backups.
	 */
	private static function filter_backups_by_type( array $backups, string $type ): array {
		$filtered = array();
		foreach ( $backups as $backup ) {
			if ( ( $backup['type'] ?? '' ) === $type ) {
				$filtered[] = $backup;
			}
		}

		return $filtered;
	}

	/**
	 * Get latest backup from list.
	 *
	 * @since 1.6093.1200
	 * @param  array $backups Backup list.
	 * @return array Latest backup.
	 */
	private static function get_latest_backup( array $backups ): array {
		usort(
			$backups,
			static function ( $a, $b ) {
				return (int) ( $b['timestamp'] ?? 0 ) <=> (int) ( $a['timestamp'] ?? 0 );
			}
		);

		return $backups[0] ?? array();
	}
}
