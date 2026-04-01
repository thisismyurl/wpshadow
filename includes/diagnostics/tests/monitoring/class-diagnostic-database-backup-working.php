<?php
/**
 * Database Backup Working Diagnostic
 *
 * Verifies that database backups exist, are recent, and non-empty.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Database Backup Working Diagnostic Class
 *
 * Ensures database backups are generated and accessible.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Database_Backup_Working extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'database-backup-working';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Database Backup Working';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks that database backups are recent and non-empty';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'backup';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$backups = apply_filters( 'wpshadow_get_backups', array() );
		$db_backups = self::filter_backups_by_type( $backups, 'database' );

		if ( empty( $db_backups ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'No database backups were detected. Ensure backups include the database.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 75,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/database-backup-working?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		$latest = self::get_latest_backup( $db_backups );
		$timestamp = (int) ( $latest['timestamp'] ?? 0 );
		$file = $latest['file'] ?? '';

		if ( $timestamp > 0 ) {
			$days_since = (int) floor( ( time() - $timestamp ) / DAY_IN_SECONDS );
			if ( $days_since > 7 ) {
				return array(
					'id'           => self::$slug,
					'title'        => self::$title,
					'description'  => sprintf(
						/* translators: %d: days since backup */
						__( 'Database backup is %d days old. Back up databases at least weekly.', 'wpshadow' ),
						$days_since
					),
					'severity'     => 'high',
					'threat_level' => 70,
					'auto_fixable' => false,
					'kb_link'      => 'https://wpshadow.com/kb/database-backup-working?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
					'meta'         => array(
						'days_since' => $days_since,
					),
					);
				}
		}

		if ( $file && file_exists( $file ) ) {
			$size_mb = filesize( $file ) / ( 1024 * 1024 );
			if ( $size_mb < 0.5 ) {
				return array(
					'id'           => self::$slug,
					'title'        => self::$title,
					'description'  => __( 'Database backup file is unusually small. It may be incomplete or corrupted.', 'wpshadow' ),
					'severity'     => 'high',
					'threat_level' => 70,
					'auto_fixable' => false,
					'kb_link'      => 'https://wpshadow.com/kb/database-backup-working?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
					'meta'         => array(
						'file_size_mb' => round( $size_mb, 2 ),
					),
					);
			}
		}

		return null;
	}

	/**
	 * Filter backups by type.
	 *
	 * @since 0.6093.1200
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
	 * @since 0.6093.1200
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
