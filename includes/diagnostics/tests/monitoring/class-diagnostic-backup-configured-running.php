<?php
/**
 * Backup Configured & Running Diagnostic
 *
 * Validates that backups are configured, scheduled, and running without errors.
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
 * Backup Configured & Running Diagnostic Class
 *
 * Detects missing backups or failed backup runs.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Backup_Configured_Running extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'backup-configured-running';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Backup Configured & Running';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if backups are configured, scheduled, and running without errors';

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
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$backup_plugins = array(
			'updraftplus/updraftplus.php' => 'UpdraftPlus',
			'backwpup/backwpup.php'       => 'BackWPup',
			'jetpack/jetpack.php'         => 'Jetpack Backup',
			'duplicator/duplicator.php'   => 'Duplicator',
		);

		$active_plugin = null;
		foreach ( $backup_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$active_plugin = $name;
				break;
			}
		}

		$backups = apply_filters( 'wpshadow_get_backups', array() );
		$last_backup_time = (int) get_option( 'wpshadow_last_backup_time', 0 );
		$backup_errors = get_option( 'wpshadow_backup_errors', array() );

		if ( ! $active_plugin && empty( $backups ) && 0 === $last_backup_time ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'No backup system detected. Configure automated backups to prevent data loss.', 'wpshadow' ),
				'severity'     => 'critical',
				'threat_level' => 90,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/backup-configured-running',
			);
		}

		if ( ! empty( $backup_errors ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Recent backup attempts reported errors. Resolve failures to ensure recoverable backups.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 75,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/backup-configured-running',
				'meta'         => array(
					'active_plugin' => $active_plugin,
					'error_count'   => count( $backup_errors ),
				),
			);
		}

		$last_backup_time = self::get_latest_backup_time( $backups, $last_backup_time );
		if ( $last_backup_time > 0 ) {
			$days_since = (int) floor( ( time() - $last_backup_time ) / DAY_IN_SECONDS );
			if ( $days_since > 7 ) {
				return array(
					'id'           => self::$slug,
					'title'        => self::$title,
					'description'  => sprintf(
						/* translators: %d: days since last backup */
						__( 'Last backup ran %d days ago. Active sites should back up at least weekly.', 'wpshadow' ),
						$days_since
					),
					'severity'     => 'high',
					'threat_level' => 70,
					'auto_fixable' => false,
					'kb_link'      => 'https://wpshadow.com/kb/backup-configured-running',
					'meta'         => array(
						'active_plugin'   => $active_plugin,
						'days_since_last' => $days_since,
					),
					);
				}
		}

		return null;
	}

	/**
	 * Get latest backup time from backup list and fallback option.
	 *
	 * @since 1.6093.1200
	 * @param  array $backups Backup list from filter.
	 * @param  int   $fallback Fallback timestamp.
	 * @return int Timestamp of latest backup.
	 */
	private static function get_latest_backup_time( array $backups, int $fallback ): int {
		$latest = $fallback;
		foreach ( $backups as $backup ) {
			$timestamp = (int) ( $backup['timestamp'] ?? 0 );
			if ( $timestamp > $latest ) {
				$latest = $timestamp;
			}
		}

		return $latest;
	}
}
