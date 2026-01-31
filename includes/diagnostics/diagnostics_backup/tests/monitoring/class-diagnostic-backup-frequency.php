<?php
/**
 * Backup Frequency Validation Diagnostic
 *
 * Verifies that backups are created regularly and the last backup is recent.
 * Checks popular backup plugin metadata and file timestamps.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6028.1640
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Backup Frequency Validation Diagnostic Class
 *
 * Detects backup issues that could:
 * - Prevent disaster recovery
 * - Lead to data loss
 * - Increase downtime during incidents
 * - Create business continuity risks
 *
 * @since 1.6028.1640
 */
class Diagnostic_Backup_Frequency extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @since 1.6028.1640
	 * @var   string
	 */
	protected static $slug = 'backup-frequency-validation';

	/**
	 * The diagnostic title
	 *
	 * @since 1.6028.1640
	 * @var   string
	 */
	protected static $title = 'Backup Frequency Validation';

	/**
	 * The diagnostic description
	 *
	 * @since 1.6028.1640
	 * @var   string
	 */
	protected static $description = 'Validates that backups are created regularly and recent backups exist';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @since 1.6028.1640
	 * @var   string
	 */
	protected static $family = 'monitoring';

	/**
	 * Cache duration in seconds (1 hour)
	 *
	 * @since 1.6028.1640
	 */
	private const CACHE_DURATION = 3600;

	/**
	 * Maximum days since last backup before flagging
	 *
	 * @since 1.6028.1640
	 */
	private const MAX_DAYS_SINCE_BACKUP = 7;

	/**
	 * Run the diagnostic check
	 *
	 * Analyzes backup system status by:
	 * - Detecting installed backup plugins
	 * - Checking last backup timestamp
	 * - Calculating days since last backup
	 * - Validating backup frequency
	 *
	 * @since  1.6028.1640
	 * @return array|null Finding array if issues detected, null otherwise.
	 */
	public static function check() {
		// Check transient cache first.
		$cache_key = 'wpshadow_diagnostic_backup_frequency';
		$cached    = get_transient( $cache_key );
		if ( false !== $cached ) {
			return self::evaluate_results( $cached );
		}

		// Analyze backup status.
		$analysis = self::analyze_backup_status();

		// Cache results.
		set_transient( $cache_key, $analysis, self::CACHE_DURATION );

		return self::evaluate_results( $analysis );
	}

	/**
	 * Analyze backup system status
	 *
	 * @since  1.6028.1640
	 * @return array Analysis results containing backup status data.
	 */
	private static function analyze_backup_status(): array {
		$analysis = array(
			'backup_plugin_detected' => false,
			'backup_plugin_name'     => '',
			'last_backup_timestamp'  => 0,
			'last_backup_date'       => '',
			'days_since_backup'      => 0,
			'backup_status'          => 'unknown',
			'issues'                 => array(),
		);

		// Detect backup plugins and get last backup data.
		$backup_data = self::detect_backup_plugins();
		$analysis    = array_merge( $analysis, $backup_data );

		// Calculate days since last backup.
		if ( $analysis['last_backup_timestamp'] > 0 ) {
			$analysis['days_since_backup'] = floor( ( time() - $analysis['last_backup_timestamp'] ) / DAY_IN_SECONDS );
			$analysis['last_backup_date']  = date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $analysis['last_backup_timestamp'] );
		}

		// Determine backup status and issues.
		$analysis = self::evaluate_backup_status( $analysis );

		return $analysis;
	}

	/**
	 * Detect installed backup plugins and get last backup data
	 *
	 * @since  1.6028.1640
	 * @return array Backup plugin detection results.
	 */
	private static function detect_backup_plugins(): array {
		$result = array(
			'backup_plugin_detected' => false,
			'backup_plugin_name'     => '',
			'last_backup_timestamp'  => 0,
		);

		// Check for UpdraftPlus.
		if ( class_exists( 'UpdraftPlus' ) ) {
			$result['backup_plugin_detected'] = true;
			$result['backup_plugin_name']     = 'UpdraftPlus';
			$result['last_backup_timestamp']  = self::get_updraftplus_last_backup();
			return $result;
		}

		// Check for BackWPup.
		if ( class_exists( 'BackWPup' ) ) {
			$result['backup_plugin_detected'] = true;
			$result['backup_plugin_name']     = 'BackWPup';
			$result['last_backup_timestamp']  = self::get_backwpup_last_backup();
			return $result;
		}

		// Check for BackupBuddy.
		if ( class_exists( 'pb_backupbuddy' ) ) {
			$result['backup_plugin_detected'] = true;
			$result['backup_plugin_name']     = 'BackupBuddy';
			$result['last_backup_timestamp']  = self::get_backupbuddy_last_backup();
			return $result;
		}

		// Check for All-in-One WP Migration.
		if ( class_exists( 'Ai1wm_Main_Controller' ) ) {
			$result['backup_plugin_detected'] = true;
			$result['backup_plugin_name']     = 'All-in-One WP Migration';
			$result['last_backup_timestamp']  = self::get_ai1wm_last_backup();
			return $result;
		}

		// Check for Duplicator.
		if ( class_exists( 'Duplicator' ) ) {
			$result['backup_plugin_detected'] = true;
			$result['backup_plugin_name']     = 'Duplicator';
			$result['last_backup_timestamp']  = self::get_duplicator_last_backup();
			return $result;
		}

		// Check for WPvivid Backup Plugin.
		if ( class_exists( 'WPvivid' ) ) {
			$result['backup_plugin_detected'] = true;
			$result['backup_plugin_name']     = 'WPvivid Backup';
			$result['last_backup_timestamp']  = self::get_wpvivid_last_backup();
			return $result;
		}

		// Check for BlogVault.
		if ( function_exists( 'bvActivateCallback' ) ) {
			$result['backup_plugin_detected'] = true;
			$result['backup_plugin_name']     = 'BlogVault';
			$result['last_backup_timestamp']  = self::get_blogvault_last_backup();
			return $result;
		}

		// Check for VaultPress (Jetpack Backup).
		if ( class_exists( 'VaultPress' ) || class_exists( 'Jetpack_Backup' ) ) {
			$result['backup_plugin_detected'] = true;
			$result['backup_plugin_name']     = 'VaultPress/Jetpack Backup';
			$result['last_backup_timestamp']  = self::get_vaultpress_last_backup();
			return $result;
		}

		// No backup plugin detected - check for manual backups in wp-content/backups.
		$manual_backup = self::check_manual_backup_directory();
		if ( $manual_backup['found'] ) {
			$result['backup_plugin_detected'] = true;
			$result['backup_plugin_name']     = 'Manual/Unknown';
			$result['last_backup_timestamp']  = $manual_backup['timestamp'];
		}

		return $result;
	}

	/**
	 * Get last UpdraftPlus backup timestamp
	 *
	 * @since  1.6028.1640
	 * @return int Timestamp of last backup, 0 if none found.
	 */
	private static function get_updraftplus_last_backup(): int {
		$history = get_option( 'updraft_backup_history', array() );
		if ( empty( $history ) ) {
			return 0;
		}

		// Get most recent backup timestamp.
		$timestamps = array_keys( $history );
		rsort( $timestamps );

		return isset( $timestamps[0] ) ? (int) $timestamps[0] : 0;
	}

	/**
	 * Get last BackWPup backup timestamp
	 *
	 * @since  1.6028.1640
	 * @return int Timestamp of last backup, 0 if none found.
	 */
	private static function get_backwpup_last_backup(): int {
		global $wpdb;

		// Query BackWPup logs table.
		$table = $wpdb->prefix . 'backwpup_jobs';
		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) !== $table ) {
			return 0;
		}

		$last_runtime = $wpdb->get_var( "SELECT MAX(lastrun) FROM {$wpdb->prefix}backwpup_jobs WHERE lastrun > 0" );

		return $last_runtime ? (int) $last_runtime : 0;
	}

	/**
	 * Get last BackupBuddy backup timestamp
	 *
	 * @since  1.6028.1640
	 * @return int Timestamp of last backup, 0 if none found.
	 */
	private static function get_backupbuddy_last_backup(): int {
		$options = get_option( 'pb_backupbuddy', array() );
		if ( empty( $options ) || ! isset( $options['last_backup'] ) ) {
			return 0;
		}

		return (int) $options['last_backup'];
	}

	/**
	 * Get last All-in-One WP Migration backup timestamp
	 *
	 * @since  1.6028.1640
	 * @return int Timestamp of last backup, 0 if none found.
	 */
	private static function get_ai1wm_last_backup(): int {
		$backup_dir = WP_CONTENT_DIR . '/ai1wm-backups';
		if ( ! is_dir( $backup_dir ) ) {
			return 0;
		}

		$files     = glob( $backup_dir . '/*.wpress' );
		$timestamps = array();

		foreach ( $files as $file ) {
			$timestamps[] = filemtime( $file );
		}

		return ! empty( $timestamps ) ? max( $timestamps ) : 0;
	}

	/**
	 * Get last Duplicator backup timestamp
	 *
	 * @since  1.6028.1640
	 * @return int Timestamp of last backup, 0 if none found.
	 */
	private static function get_duplicator_last_backup(): int {
		global $wpdb;

		// Query Duplicator packages table.
		$table = $wpdb->prefix . 'duplicator_packages';
		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) !== $table ) {
			return 0;
		}

		$last_created = $wpdb->get_var( "SELECT MAX(created) FROM {$wpdb->prefix}duplicator_packages" );

		return $last_created ? strtotime( $last_created ) : 0;
	}

	/**
	 * Get last WPvivid backup timestamp
	 *
	 * @since  1.6028.1640
	 * @return int Timestamp of last backup, 0 if none found.
	 */
	private static function get_wpvivid_last_backup(): int {
		$backup_list = get_option( 'wpvivid_backup_list', array() );
		if ( empty( $backup_list ) ) {
			return 0;
		}

		$timestamps = array();
		foreach ( $backup_list as $backup ) {
			if ( isset( $backup['create_time'] ) ) {
				$timestamps[] = (int) $backup['create_time'];
			}
		}

		return ! empty( $timestamps ) ? max( $timestamps ) : 0;
	}

	/**
	 * Get last BlogVault backup timestamp
	 *
	 * @since  1.6028.1640
	 * @return int Timestamp of last backup, 0 if none found.
	 */
	private static function get_blogvault_last_backup(): int {
		$last_backup = get_option( 'bvLastBackupTime', 0 );
		return (int) $last_backup;
	}

	/**
	 * Get last VaultPress/Jetpack Backup timestamp
	 *
	 * @since  1.6028.1640
	 * @return int Timestamp of last backup, 0 if none found.
	 */
	private static function get_vaultpress_last_backup(): int {
		// Check Jetpack Backup option.
		$jetpack_backup = get_option( 'jetpack_backup_last', 0 );
		if ( $jetpack_backup > 0 ) {
			return (int) $jetpack_backup;
		}

		// Check VaultPress option.
		$vaultpress_data = get_option( 'vaultpress', array() );
		if ( isset( $vaultpress_data['last_backup'] ) ) {
			return (int) $vaultpress_data['last_backup'];
		}

		return 0;
	}

	/**
	 * Check for manual backup files in common directories
	 *
	 * @since  1.6028.1640
	 * @return array Result with 'found' boolean and 'timestamp' if found.
	 */
	private static function check_manual_backup_directory(): array {
		$backup_dirs = array(
			WP_CONTENT_DIR . '/backups',
			WP_CONTENT_DIR . '/uploads/backups',
			ABSPATH . 'backups',
		);

		$newest_timestamp = 0;

		foreach ( $backup_dirs as $dir ) {
			if ( ! is_dir( $dir ) ) {
				continue;
			}

			// Look for common backup file extensions.
			$patterns = array( '*.zip', '*.tar.gz', '*.sql', '*.sql.gz' );
			foreach ( $patterns as $pattern ) {
				$files = glob( $dir . '/' . $pattern );
				foreach ( $files as $file ) {
					$timestamp = filemtime( $file );
					if ( $timestamp > $newest_timestamp ) {
						$newest_timestamp = $timestamp;
					}
				}
			}
		}

		return array(
			'found'     => $newest_timestamp > 0,
			'timestamp' => $newest_timestamp,
		);
	}

	/**
	 * Evaluate backup status and identify issues
	 *
	 * @since  1.6028.1640
	 * @param  array $analysis Current analysis data.
	 * @return array Updated analysis with status and issues.
	 */
	private static function evaluate_backup_status( array $analysis ): array {
		$issues = array();

		// No backup plugin detected.
		if ( ! $analysis['backup_plugin_detected'] ) {
			$issues[]                    = __( 'No backup plugin detected', 'wpshadow' );
			$analysis['backup_status']   = 'no_plugin';
			$analysis['issues']          = $issues;
			return $analysis;
		}

		// Backup plugin detected but no backups found.
		if ( 0 === $analysis['last_backup_timestamp'] ) {
			$issues[] = sprintf(
				/* translators: %s: backup plugin name */
				__( '%s is installed but no backups have been created', 'wpshadow' ),
				$analysis['backup_plugin_name']
			);
			$analysis['backup_status'] = 'no_backups';
			$analysis['issues']        = $issues;
			return $analysis;
		}

		// Check if backup is recent enough.
		if ( $analysis['days_since_backup'] > self::MAX_DAYS_SINCE_BACKUP ) {
			$issues[] = sprintf(
				/* translators: %d: days since last backup */
				_n(
					'Last backup was %d day ago - backups should be more frequent',
					'Last backup was %d days ago - backups should be more frequent',
					$analysis['days_since_backup'],
					'wpshadow'
				),
				$analysis['days_since_backup']
			);
			$analysis['backup_status'] = 'outdated';
		} else {
			$analysis['backup_status'] = 'recent';
		}

		$analysis['issues'] = $issues;
		return $analysis;
	}

	/**
	 * Evaluate analysis results and build finding
	 *
	 * @since  1.6028.1640
	 * @param  array $analysis Analysis results.
	 * @return array|null Finding array if issues detected, null otherwise.
	 */
	private static function evaluate_results( array $analysis ) {
		// No issues found - backup system is healthy.
		if ( empty( $analysis['issues'] ) ) {
			return null;
		}

		// Build finding.
		return self::build_finding( $analysis );
	}

	/**
	 * Build finding array
	 *
	 * @since  1.6028.1640
	 * @param  array $analysis Analysis results.
	 * @return array Finding array with full diagnostic information.
	 */
	private static function build_finding( array $analysis ): array {
		$issue_count  = count( $analysis['issues'] );
		$threat_level = self::calculate_threat_level( $analysis );
		$severity     = ( 'no_plugin' === $analysis['backup_status'] || 'no_backups' === $analysis['backup_status'] ) ? 'critical' : 'high';

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %d: number of backup issues */
				_n(
					'Found %d backup configuration issue',
					'Found %d backup configuration issues',
					$issue_count,
					'wpshadow'
				),
				$issue_count
			),
			'severity'     => $severity,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/monitoring-backup-frequency',
			'family'       => self::$family,
			'meta'         => array(
				'backup_plugin_detected' => $analysis['backup_plugin_detected'],
				'backup_plugin_name'     => $analysis['backup_plugin_name'],
				'last_backup_date'       => $analysis['last_backup_date'],
				'days_since_backup'      => $analysis['days_since_backup'],
				'backup_status'          => $analysis['backup_status'],
				'data_loss_risk'         => self::calculate_data_loss_risk( $analysis['days_since_backup'] ),
			),
			'details'      => self::build_finding_details( $analysis ),
		);
	}

	/**
	 * Calculate threat level based on backup status
	 *
	 * @since  1.6028.1640
	 * @param  array $analysis Analysis results.
	 * @return int Threat level (50-75).
	 */
	private static function calculate_threat_level( array $analysis ): int {
		// No backup plugin = maximum threat.
		if ( 'no_plugin' === $analysis['backup_status'] ) {
			return 75;
		}

		// Backup plugin but no backups = high threat.
		if ( 'no_backups' === $analysis['backup_status'] ) {
			return 70;
		}

		// Outdated backup = medium-high threat based on age.
		$days = $analysis['days_since_backup'];
		if ( $days > 30 ) {
			return 65;
		} elseif ( $days > 14 ) {
			return 60;
		} else {
			return 55;
		}
	}

	/**
	 * Calculate data loss risk message
	 *
	 * @since  1.6028.1640
	 * @param  int $days_since_backup Days since last backup.
	 * @return string Data loss risk description.
	 */
	private static function calculate_data_loss_risk( int $days_since_backup ): string {
		if ( $days_since_backup > 30 ) {
			return __( 'Critical: Over 30 days of data at risk', 'wpshadow' );
		} elseif ( $days_since_backup > 14 ) {
			return __( 'High: Over 2 weeks of data at risk', 'wpshadow' );
		} elseif ( $days_since_backup > 7 ) {
			return __( 'Moderate: Over 1 week of data at risk', 'wpshadow' );
		} else {
			return __( 'Low: Less than 1 week of data at risk', 'wpshadow' );
		}
	}

	/**
	 * Build detailed information for finding
	 *
	 * @since  1.6028.1640
	 * @param  array $analysis Analysis results.
	 * @return array Detailed information array.
	 */
	private static function build_finding_details( array $analysis ): array {
		$details = array(
			'issues_found'           => $analysis['issues'],
			'why_backups_matter'     => __( 'Regular backups are your safety net against data loss from hacking, human error, server failures, or plugin conflicts. Without recent backups, any incident could mean permanent loss of content, customer data, and business critical information.', 'wpshadow' ),
			'recommended_frequency'  => __( 'Daily for active sites, weekly minimum for others', 'wpshadow' ),
			'backup_plugins'         => array(
				'UpdraftPlus'         => 'https://wordpress.org/plugins/updraftplus/',
				'BackWPup'            => 'https://wordpress.org/plugins/backwpup/',
				'Duplicator'          => 'https://wordpress.org/plugins/duplicator/',
				'All-in-One WP Migration' => 'https://wordpress.org/plugins/all-in-one-wp-migration/',
				'WPvivid Backup'      => 'https://wordpress.org/plugins/wpvivid-backuprestore/',
			),
			'next_steps'             => array(),
		);

		// Customize next steps based on status.
		if ( 'no_plugin' === $analysis['backup_status'] ) {
			$details['next_steps'] = array(
				__( 'Install a backup plugin immediately', 'wpshadow' ),
				__( 'Configure automated backups (daily recommended)', 'wpshadow' ),
				__( 'Test backup restoration to verify it works', 'wpshadow' ),
				__( 'Store backups off-site (cloud storage recommended)', 'wpshadow' ),
				__( 'Set up backup monitoring and alerts', 'wpshadow' ),
			);
		} elseif ( 'no_backups' === $analysis['backup_status'] ) {
			$details['next_steps'] = array(
				sprintf(
					/* translators: %s: backup plugin name */
					__( 'Configure %s to run automated backups', 'wpshadow' ),
					$analysis['backup_plugin_name']
				),
				__( 'Create your first backup immediately', 'wpshadow' ),
				__( 'Set backup schedule (daily or weekly)', 'wpshadow' ),
				__( 'Test restoration to verify backups work', 'wpshadow' ),
			);
		} else {
			$details['next_steps'] = array(
				sprintf(
					/* translators: %d: days since last backup */
					__( 'Last backup was %d days ago - run backup now', 'wpshadow' ),
					$analysis['days_since_backup']
				),
				__( 'Increase backup frequency to daily', 'wpshadow' ),
				__( 'Verify automated backups are running', 'wpshadow' ),
				__( 'Test backup restoration regularly', 'wpshadow' ),
			);
		}

		return $details;
	}
}
