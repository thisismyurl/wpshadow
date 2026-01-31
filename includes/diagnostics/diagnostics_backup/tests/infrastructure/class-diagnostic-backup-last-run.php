<?php
/**
 * Backup Last Run Diagnostic
 *
 * Monitors backup plugin activity to ensure regular backups are being created.
 * Critical for disaster recovery and business continuity.
 *
 * @since   1.2601.2148
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Backup_Last_Run Class
 *
 * Verifies that backups are being created on a regular schedule.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Backup_Last_Run extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'backup-last-run';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Backup Last Run Status';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Monitors backup plugin activity and backup recency';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'infrastructure';

	/**
	 * Warning threshold: backup older than 7 days
	 *
	 * @var int
	 */
	const WARNING_DAYS = 7;

	/**
	 * Critical threshold: backup older than 30 days
	 *
	 * @var int
	 */
	const CRITICAL_DAYS = 30;

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if backup issues detected, null otherwise.
	 */
	public static function check() {
		$backup_info = self::get_backup_status();

		if ( ! $backup_info ) {
			// No backup plugin detected
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'No backup plugin detected. Backups are critical for disaster recovery.', 'wpshadow' ),
				'severity'      => 'critical',
				'threat_level'  => 90,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/backup-plugin-setup',
				'family'        => self::$family,
				'meta'          => array(
					'backup_status'        => 'Not Installed',
					'backup_plugin'        => 'None detected',
					'data_at_risk'         => __( 'ALL - No backups exist', 'wpshadow' ),
					'recovery_time'        => __( 'Impossible (no backups)' ),
					'recommended_plugins'  => array(
						'UpdraftPlus' => 'Most popular, cloud storage support',
						'Jetpack Backup' => 'Jetpack managed hosting',
						'BackWPup' => 'Flexible, multiple storage backends',
						'All-in-One WP Migration' => 'Simple, beginner-friendly',
					),
				),
				'details'       => array(
					'critical_issue' => __( 'Without backups, any catastrophic event (hack, data loss, plugin conflict) means permanent data loss.', 'wpshadow' ),
					'typical_costs' => array(
						__( 'Site rebuild' ) => '$1,000-5,000',
						__( 'Lost revenue/downtime' ) => '$500-10,000/day',
						__( 'Customer churn' ) => '20-40% lose trust',
						__( 'Legal liability' ) => 'Potential lawsuits',
					),
					'quick_setup' => array(
						'Step 1' => __( 'Install UpdraftPlus or Jetpack Backup plugin' ),
						'Step 2' => __( 'Configure backup schedule (daily recommended)' ),
						'Step 3' => __( 'Enable cloud storage (Google Drive, Dropbox, AWS)' ),
						'Step 4' => __( 'Run first manual backup immediately' ),
						'Step 5' => __( 'Test restore on staging to verify backup integrity' ),
					),
				),
			);
		}

		$days_since_backup = $backup_info['days_since_backup'];

		if ( $days_since_backup < self::WARNING_DAYS ) {
			// Backups are current
			return null;
		}

		$severity = ( $days_since_backup >= self::CRITICAL_DAYS ) ? 'critical' : 'high';
		$threat_level = ( $severity === 'critical' ) ? 85 : 65;

		return array(
			'id'            => self::$slug,
			'title'         => self::$title,
			'description'   => sprintf(
				/* translators: %d: number of days, %s: plugin name */
				__( 'Last backup was %d days ago using %s. Backups should be fresh.', 'wpshadow' ),
				$days_since_backup,
				$backup_info['plugin_name']
			),
			'severity'      => $severity,
			'threat_level'  => $threat_level,
			'auto_fixable'  => false,
			'kb_link'       => 'https://wpshadow.com/kb/backup-troubleshooting',
			'family'        => self::$family,
			'meta'          => array(
				'backup_plugin'         => $backup_info['plugin_name'],
				'last_backup_date'      => $backup_info['last_backup_date'],
				'days_since_backup'     => $days_since_backup,
				'backup_schedule'       => $backup_info['schedule'],
				'data_at_risk'          => sprintf(
					/* translators: %d: days of data */
					__( '%d days of changes unprotected', 'wpshadow' ),
					$days_since_backup
				),
				'immediate_actions'     => array(
					__( 'Manually trigger backup NOW' ),
					__( 'Check backup plugin settings and cron' ),
					__( 'Verify backup storage has space available' ),
					__( 'Check email alerts from backup plugin' ),
				),
			),
			'details'       => array(
				'risk_assessment' => sprintf(
					/* translators: %d: days of data loss risk */
					__( 'If disaster occurs today, you can only recover to %d days ago. Any changes made since then are lost.', 'wpshadow' ),
					$days_since_backup
				),
				'common_causes'   => array(
					__( 'Cron disabled' ) => __( 'WordPress scheduled tasks not running (check WordPress Cron status)' ),
					__( 'Plugin disabled' ) => __( 'Plugin auto-deactivated due to error' ),
					__( 'Storage full' ) => __( 'Cloud storage (Google Drive, Dropbox) at limit' ),
					__( 'API errors' ) => __( 'Cloud storage API access revoked or limited' ),
					__( 'Server resources' ) => __( 'Not enough memory/time for backup to complete' ),
					__( 'Plugin conflict' ) => __( 'Another plugin interfering with backup schedule' ),
				),
				'troubleshooting' => array(
					'Check 1' => __( 'Manually run backup: Log in to WordPress, go to backup plugin settings, click "Backup Now"' ),
					'Check 2' => __( 'Verify WordPress cron is running: Install WP Control, check "Cron" tab' ),
					'Check 3' => __( 'Check backup logs: Most plugins have activity/debug logs showing why backups fail' ),
					'Check 4' => __( 'Verify storage connection: Check cloud storage permissions and API keys' ),
					'Check 5' => __( 'Check PHP memory: Increase PHP memory limit to 256M in wp-config.php' ),
					'Check 6' => __( 'Review email notifications: Backup plugin should email you on failure' ),
				),
				'backup_best_practices' => array(
					__( 'Daily backups recommended' ),
					__( '3-2-1 rule: 3 copies, 2 different media, 1 offsite' ),
					__( 'Test restore monthly' ),
					__( 'Monitor backup plugin activity logs' ),
					__( 'Use managed hosting with automatic backups' ),
					__( 'Keep multiple backup versions (7-30 days retention)' ),
				),
			),
		);
	}

	/**
	 * Get backup plugin status.
	 *
	 * @since  1.2601.2148
	 * @return array|null Backup info or null if no backup plugin found.
	 */
	private static function get_backup_status() {
		// Check for backup plugins
		$backup_plugins = array(
			'updraftplus/updraftplus.php' => 'UpdraftPlus',
			'jetpack/jetpack.php' => 'Jetpack',
			'backwpup/backwpup.php' => 'BackWPup',
			'all-in-one-wp-migration/all-in-one-wp-migration.php' => 'All-in-One WP Migration',
			'duplicator/duplicator.php' => 'Duplicator',
			'wp-super-backup/wp-super-backup.php' => 'WP Super Backup',
		);

		$active_plugin = null;

		foreach ( $backup_plugins as $plugin_path => $plugin_name ) {
			if ( is_plugin_active( $plugin_path ) ) {
				$active_plugin = array(
					'path' => $plugin_path,
					'name' => $plugin_name,
				);
				break;
			}
		}

		if ( ! $active_plugin ) {
			return null;
		}

		// Get last backup timestamp based on plugin
		$last_backup = self::get_last_backup_time( $active_plugin['name'] );

		if ( ! $last_backup ) {
			return array(
				'plugin_name'       => $active_plugin['name'],
				'schedule'          => 'Unknown',
				'last_backup_date'  => __( 'Never or unable to determine', 'wpshadow' ),
				'days_since_backup' => 999, // Critical: treat as never backed up
			);
		}

		$days_since = ceil( ( time() - $last_backup ) / DAY_IN_SECONDS );

		return array(
			'plugin_name'       => $active_plugin['name'],
			'schedule'          => self::get_backup_schedule( $active_plugin['name'] ),
			'last_backup_date'  => gmdate( 'Y-m-d H:i:s', $last_backup ),
			'days_since_backup' => $days_since,
		);
	}

	/**
	 * Get last backup timestamp for a plugin.
	 *
	 * @since  1.2601.2148
	 * @param  string $plugin_name Plugin name.
	 * @return int|false Backup timestamp or false.
	 */
	private static function get_last_backup_time( $plugin_name ) {
		switch ( $plugin_name ) {
			case 'UpdraftPlus':
				$last_backup = get_option( 'updraft_last_backup_time' );
				if ( $last_backup ) {
					return (int) $last_backup;
				}
				// Fallback: check recent files
				break;

			case 'Jetpack':
				$last_backup = get_option( 'jetpack_backup_last_run' );
				if ( $last_backup ) {
					return (int) $last_backup;
				}
				break;

			case 'BackWPup':
				$last_backup = get_option( 'backwpup_backup_timestamp' );
				if ( $last_backup ) {
					return (int) $last_backup;
				}
				break;

			case 'All-in-One WP Migration':
				$last_backup = get_option( 'ai1wm_backup_timestamp' );
				if ( $last_backup ) {
					return (int) $last_backup;
				}
				break;

			case 'Duplicator':
				$last_backup = get_option( 'duplicator_last_backup' );
				if ( $last_backup ) {
					return (int) $last_backup;
				}
				break;
		}

		return false;
	}

	/**
	 * Get backup schedule for a plugin.
	 *
	 * @since  1.2601.2148
	 * @param  string $plugin_name Plugin name.
	 * @return string Schedule description.
	 */
	private static function get_backup_schedule( $plugin_name ) {
		switch ( $plugin_name ) {
			case 'UpdraftPlus':
				$schedule = get_option( 'updraft_interval' );
				return $schedule ? "UpdraftPlus: {$schedule}" : 'UpdraftPlus: Unknown schedule';

			case 'Jetpack':
				return 'Jetpack: Daily (Managed)';

			case 'BackWPup':
				$schedule = get_option( 'backwpup_schedule' );
				return $schedule ? "BackWPup: {$schedule}" : 'BackWPup: Unknown schedule';

			default:
				return "{$plugin_name}: Unknown schedule";
		}
	}
}
