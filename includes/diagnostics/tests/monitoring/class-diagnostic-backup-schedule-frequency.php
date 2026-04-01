<?php
/**
 * Automated Backup Schedule Configuration
 *
 * Validates backup frequency and scheduling configuration.
 *
 * @since 0.6093.1200
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Backup_Schedule_Frequency Class
 *
 * Checks backup scheduling configuration and frequency adequacy.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Backup_Schedule_Frequency extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'backup-schedule-frequency';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Backup Schedule & Frequency';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates backup scheduling and frequency configuration';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'backup-recovery';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		// Check for active backup plugin
		$backup_plugin = self::get_active_backup_plugin();

		if ( ! $backup_plugin ) {
			return null; // No backup plugin, skip this check
		}

		// Pattern 1: Backup scheduled for less frequent than daily
		$backup_frequency = self::get_backup_frequency( $backup_plugin );

		if ( $backup_frequency && $backup_frequency > 1 ) { // More than daily
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Backup frequency is less than daily', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 75,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/backup-schedule-frequency?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'details'      => array(
					'issue' => 'infrequent_backups',
					'current_frequency' => sprintf(
						/* translators: %d: number of days */
						__( 'Every %d days', 'wpshadow' ),
						$backup_frequency
					),
					'message' => sprintf(
						/* translators: %d: days between backups */
						__( 'Backups scheduled every %d days. Daily backups recommended', 'wpshadow' ),
						$backup_frequency
					),
					'data_loss_risk' => sprintf(
						/* translators: %d: days */
						__( 'If disaster occurs, you could lose up to %d days of content and changes', 'wpshadow' ),
						$backup_frequency
					),
					'recommended_frequency' => 'Daily (best for active sites)',
					'frequency_matrix' => array(
						'High traffic/E-commerce' => 'Multiple daily (every 6-12 hours)',
						'Active blogs' => 'Daily',
						'Static sites' => 'Weekly acceptable if changes infrequent',
						'Development sites' => 'Daily minimum',
					),
					'update_importance' => __( 'If you update content daily, you need daily backups', 'wpshadow' ),
					'restoration_impact' => __( 'Weekly backups mean potential 7-day content loss', 'wpshadow' ),
				),
			);
		}

		// Pattern 2: Backup time during peak hours
		$backup_time = self::get_backup_time( $backup_plugin );

		if ( $backup_time ) {
			$backup_hour = (int) substr( $backup_time, 0, 2 );

			// Peak hours typically 9am-6pm
			if ( $backup_hour >= 9 && $backup_hour <= 18 ) {
				return array(
					'id'           => self::$slug,
					'title'        => self::$title,
					'description'  => __( 'Backups scheduled during peak traffic hours', 'wpshadow' ),
					'severity'     => 'medium',
					'threat_level' => 55,
					'auto_fixable' => false,
					'kb_link'      => 'https://wpshadow.com/kb/backup-schedule-frequency?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
					'details'      => array(
						'issue' => 'backup_during_peak_hours',
						'current_time' => sprintf(
							/* translators: %02d: hour */
							__( '%02d:00 UTC', 'wpshadow' ),
							$backup_hour
						),
						'message' => __( 'Backup scheduled during business hours when site traffic is highest', 'wpshadow' ),
						'performance_impact' => __( 'Backups consume CPU and I/O, slowing site during peak hours', 'wpshadow' ),
						'impact_estimate' => __( 'Could reduce site speed by 20-40% during backup', 'wpshadow' ),
						'best_practice' => 'Schedule backups during off-peak hours (midnight - 6am local time)',
						'timezone_note' => __( 'Consider your timezone and when users are least active', 'wpshadow' ),
						'recommendation' => __( 'Change backup time to 2-4am in your timezone', 'wpshadow' ),
					),
				);
			}
		}

		// Pattern 3: No backup retention policy configured
		$retention_days = self::get_backup_retention( $backup_plugin );

		if ( ! $retention_days || $retention_days < 30 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Backup retention period too short', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 60,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/backup-schedule-frequency?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'details'      => array(
					'issue' => 'insufficient_retention',
					'current_retention' => $retention_days ? sprintf( __( '%d days', 'wpshadow' ), $retention_days ) : __( 'Not configured', 'wpshadow' ),
					'message' => __( 'Backup retention is too short. You might not have backups from when problems occurred', 'wpshadow' ),
					'scenario' => __( 'If malware infected your site 2 weeks ago, you\'d have no clean backup', 'wpshadow' ),
					'retention_guidelines' => array(
						'Minimum' => '30 days (catch recent problems)',
						'Recommended' => '90 days (covers 3 months)',
						'Enterprise' => '6-12 months (compliance, long-term recovery)',
					),
					'cost_benefit' => __( 'Storage cost drops significantly after first month; keeping 90 days inexpensive', 'wpshadow' ),
					'recommendation' => __( 'Set retention to at least 90 days', 'wpshadow' ),
				),
			);
		}

		// Pattern 4: Incremental backups disabled (full backups only)
		$incremental_enabled = self::is_incremental_enabled( $backup_plugin );

		if ( $backup_frequency && $backup_frequency < 1 && ! $incremental_enabled ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Using full backups only (incremental not enabled)', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 35,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/backup-schedule-frequency?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'details'      => array(
					'issue' => 'incremental_disabled',
					'message' => __( 'Backup strategy uses only full backups instead of incremental', 'wpshadow' ),
					'efficiency_impact' => __( 'Full backups take 2-10x longer and use much more storage', 'wpshadow' ),
					'comparison' => array(
						'Full backup' => '1GB site = 1GB backup size every time',
						'Incremental' => '1GB site = 100MB first backup, then 10-50MB daily changes only',
					),
					'storage_savings' => __( 'Incremental backups reduce storage costs by 50-80%', 'wpshadow' ),
					'performance' => __( 'Daily incremental backups 5-10x faster than daily full backups', 'wpshadow' ),
					'recommendation' => __( 'Enable incremental backups in your backup plugin settings', 'wpshadow' ),
				),
			);
		}

		// Pattern 5: No database-only backup configured
		$has_db_backup = self::has_database_backup_option( $backup_plugin );

		if ( ! $has_db_backup ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'No separate database backup option configured', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 40,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/backup-schedule-frequency?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'details'      => array(
					'issue' => 'no_database_only_backup',
					'message' => __( 'Database-only backups not configured', 'wpshadow' ),
					'why_matters' => __( 'Database contains all posts, settings, users (restore-critical)', 'wpshadow' ),
					'use_case' => __( 'Database-only backups let you quickly restore just data without large files', 'wpshadow' ),
					'advantages' => array(
						'Faster to create (only database, no large media files)',
						'Smaller file size (email backup feasible)',
						'Quick recovery of lost posts without file restoration',
						'Can restore data from months ago if media files unchanged',
					),
					'best_practice' => __( 'Run daily full backups + separate hourly database backups', 'wpshadow' ),
					'recommendation' => __( 'Configure an additional frequent database-only backup schedule', 'wpshadow' ),
				),
			);
		}

		// Pattern 6: Backup schedule tests never run
		$last_test = self::get_last_backup_test( $backup_plugin );

		if ( ! $last_test || ( time() - $last_test ) > ( 90 * DAY_IN_SECONDS ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Backups never tested or tested over 90 days ago', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/backup-schedule-frequency?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'details'      => array(
					'issue' => 'backups_not_tested',
					'message' => __( 'You have never tested if your backups can actually be restored', 'wpshadow' ),
					'why_critical' => __( 'Untested backups are useless (50% of companies discover backups fail during disaster)', 'wpshadow' ),
					'test_steps' => array(
						'1. In backup plugin, look for "Test Backup" or "Verify Backup" option',
						'2. Run test on most recent backup',
						'3. Verify test completes successfully',
						'4. Check that test results show no errors',
						'5. Schedule monthly verification tests',
					),
					'disaster_scenario' => __( 'When disaster strikes and you need backup, discovering it won\'t restore is catastrophic', 'wpshadow' ),
					'best_practice' => __( 'Test backups monthly minimum, quarterly is bare minimum', 'wpshadow' ),
					'recommendation' => __( 'Run a backup test immediately, then schedule monthly tests', 'wpshadow' ),
				),
			);
		}

		return null; // No issues found
	}

	/**
	 * Get active backup plugin slug.
	 *
	 * @since 0.6093.1200
	 * @return string Plugin slug or empty string.
	 */
	private static function get_active_backup_plugin() {
		$backup_plugins = array(
			'updraftplus/updraftplus.php',
			'backwpup/backwpup.php',
			'all-in-one-wp-migration/all-in-one-wp-migration.php',
			'jetpack/jetpack.php',
			'wp-staging/wp-staging.php',
			'duplicator/duplicator.php',
		);

		foreach ( $backup_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				return basename( dirname( $plugin ) );
			}
		}

		return '';
	}

	/**
	 * Get backup frequency in days.
	 *
	 * @since 0.6093.1200
	 * @param  string $plugin Plugin slug.
	 * @return int Frequency in days or 1 for daily.
	 */
	private static function get_backup_frequency( $plugin ) {
		$frequency = get_option( $plugin . '_backup_frequency', 1 );
		return absint( $frequency );
	}

	/**
	 * Get scheduled backup time.
	 *
	 * @since 0.6093.1200
	 * @param  string $plugin Plugin slug.
	 * @return string Time in HH:MM format or empty.
	 */
	private static function get_backup_time( $plugin ) {
		return get_option( $plugin . '_backup_time', '' );
	}

	/**
	 * Get backup retention days.
	 *
	 * @since 0.6093.1200
	 * @param  string $plugin Plugin slug.
	 * @return int Retention days or 0.
	 */
	private static function get_backup_retention( $plugin ) {
		return absint( get_option( $plugin . '_retention_days', 0 ) );
	}

	/**
	 * Check if incremental backups enabled.
	 *
	 * @since 0.6093.1200
	 * @param  string $plugin Plugin slug.
	 * @return bool True if incremental enabled.
	 */
	private static function is_incremental_enabled( $plugin ) {
		return (bool) get_option( $plugin . '_incremental_backups', false );
	}

	/**
	 * Check for database-only backup option.
	 *
	 * @since 0.6093.1200
	 * @param  string $plugin Plugin slug.
	 * @return bool True if database backup configured.
	 */
	private static function has_database_backup_option( $plugin ) {
		return (bool) get_option( $plugin . '_database_only_backup', false );
	}

	/**
	 * Get last backup test timestamp.
	 *
	 * @since 0.6093.1200
	 * @param  string $plugin Plugin slug.
	 * @return int Timestamp or 0.
	 */
	private static function get_last_backup_test( $plugin ) {
		return absint( get_option( $plugin . '_last_test', 0 ) );
	}
}
