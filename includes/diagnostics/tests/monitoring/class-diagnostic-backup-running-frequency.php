<?php
/**
 * Backup Running Frequency Diagnostic
 *
 * Checks if backups are running at appropriate frequency.
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
 * Backup Running Frequency Diagnostic Class
 *
 * Verifies backups are running at recommended frequency.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Backup_Running_Frequency extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'backup-running-frequency';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Backup Running Frequency';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if backups are running at appropriate frequency';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'backup-recovery';

	/**
	 * Recommended backup frequency
	 *
	 * @var int
	 */
	private const RECOMMENDED_FREQUENCY_DAYS = 7;

	/**
	 * Maximum acceptable backup age
	 *
	 * @var int
	 */
	private const MAX_BACKUP_AGE_DAYS = 30;

	/**
	 * Run the backup frequency diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if backup frequency issue detected, null otherwise.
	 */
	public static function check() {
		$cache_key = 'wpshadow_backup_frequency';
		$cached = get_transient( $cache_key );

		if ( false !== $cached ) {
			return $cached;
		}

		$latest_backup = self::find_latest_backup();

		if ( ! $latest_backup ) {
			$result = array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'No recent backups found. Configure your backup plugin and ensure scheduled backups are enabled.', 'wpshadow' ),
				'severity'    => 'high',
				'threat_level' => 85,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/configure-backup-schedule?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		} else {
			$backup_age_days = ( time() - $latest_backup ) / DAY_IN_SECONDS;

			if ( $backup_age_days > self::MAX_BACKUP_AGE_DAYS ) {
				$result = array(
					'id'          => self::$slug,
					'title'       => self::$title,
					'description' => sprintf(
						/* translators: 1: days, 2: max days */
						__( 'Last backup is %1$.0f days old (max: %2$d days). Verify backup schedule is enabled.', 'wpshadow' ),
						$backup_age_days,
						self::MAX_BACKUP_AGE_DAYS
					),
					'severity'    => 'high',
					'threat_level' => 80,
					'auto_fixable' => false,
					'kb_link'     => 'https://wpshadow.com/kb/backup-schedule-not-running?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				);
			} elseif ( $backup_age_days > self::RECOMMENDED_FREQUENCY_DAYS ) {
				$result = array(
					'id'          => self::$slug,
					'title'       => self::$title,
					'description' => sprintf(
						/* translators: 1: days, 2: recommended days */
						__( 'Last backup is %1$.0f days old (recommended: every %2$d days). Consider increasing backup frequency.', 'wpshadow' ),
						$backup_age_days,
						self::RECOMMENDED_FREQUENCY_DAYS
					),
					'severity'    => 'medium',
					'threat_level' => 50,
					'auto_fixable' => false,
					'kb_link'     => 'https://wpshadow.com/kb/increase-backup-frequency?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				);
			} else {
				$result = null;
			}
		}

		set_transient( $cache_key, $result, HOUR_IN_SECONDS );

		return $result;
	}

	/**
	 * Find latest backup timestamp.
	 *
	 * @since 0.6093.1200
	 * @return int|null Timestamp of latest backup or null.
	 */
	private static function find_latest_backup(): ?int {
		$backup_dirs = array(
			WP_CONTENT_DIR . '/backups/',
			WP_CONTENT_DIR . '/uploads/backups/',
			WP_CONTENT_DIR . '/ai1wm-backups/',
			WP_CONTENT_DIR . '/backwpup-backups/',
			WP_CONTENT_DIR . '/updraftplus/',
		);

		$latest_time = null;

		foreach ( $backup_dirs as $dir ) {
			if ( ! is_dir( $dir ) ) {
				continue;
			}

			$files = glob( $dir . '*' );
			if ( empty( $files ) ) {
				continue;
			}

			foreach ( $files as $file ) {
				$mtime = filemtime( $file );
				if ( $mtime && ( ! $latest_time || $mtime > $latest_time ) ) {
					$latest_time = $mtime;
				}
			}
		}

		return $latest_time;
	}
}
