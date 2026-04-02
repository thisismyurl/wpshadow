<?php
/**
 * Disaster Recovery RPO Diagnostic
 *
 * Checks if Recovery Point Objective (RPO) is achievable and tested.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Tests
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Disaster Recovery RPO Diagnostic Class
 *
 * Detects if Recovery Point Objective is properly defined,
 * configured, and validated through testing.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Disaster_Recovery_Rpo extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'disaster-recovery-rpo';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Disaster Recovery RPO';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if Recovery Point Objective is achievable in testing';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'disaster-recovery';

	/**
	 * Primary persona
	 *
	 * @var string
	 */
	protected static $persona = 'enterprise-corp';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check for RPO configuration.
		$rpo_target = get_option( 'wpshadow_disaster_recovery_rpo_hours', 0 );
		$rpo_last_test = get_option( 'wpshadow_disaster_recovery_rpo_last_test', 0 );
		$rpo_test_result = get_option( 'wpshadow_disaster_recovery_rpo_test_result', '' );

		// Check for backup frequency.
		$backup_frequency = get_option( 'wpshadow_backup_frequency', '' );
		
		// Check popular backup plugins.
		$backup_plugins = array(
			'updraftplus/updraftplus.php'                 => array( 'name' => 'UpdraftPlus', 'freq' => 'daily' ),
			'backwpup/backwpup.php'                       => array( 'name' => 'BackWPup', 'freq' => 'daily' ),
			'wp-db-backup/wp-db-backup.php'               => array( 'name' => 'WP-DB-Backup', 'freq' => 'daily' ),
			'duplicator/duplicator.php'                   => array( 'name' => 'Duplicator', 'freq' => 'manual' ),
			'jetpack/jetpack.php'                         => array( 'name' => 'Jetpack Backup', 'freq' => 'realtime' ),
			'blogvault-real-time-backup/backup.php'       => array( 'name' => 'BlogVault', 'freq' => 'realtime' ),
			'backup-guard-platinum/backup-guard-pro.php'  => array( 'name' => 'Backup Guard', 'freq' => 'scheduled' ),
			'all-in-one-wp-migration/all-in-one-wp-migration.php' => array( 'name' => 'All-in-One WP Migration', 'freq' => 'manual' ),
		);

		$has_backup_plugin = false;
		$backup_plugin_name = '';
		$estimated_rpo = 24; // Default: 24 hours (daily backup).

		foreach ( $backup_plugins as $plugin_file => $plugin_data ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$has_backup_plugin = true;
				$backup_plugin_name = $plugin_data['name'];
				
				// Estimate RPO based on plugin capabilities.
				switch ( $plugin_data['freq'] ) {
					case 'realtime':
						$estimated_rpo = 0.25; // 15 minutes.
						break;
					case 'hourly':
						$estimated_rpo = 1;
						break;
					case 'scheduled':
						$estimated_rpo = 12; // Assume twice daily.
						break;
					case 'daily':
						$estimated_rpo = 24;
						break;
					case 'manual':
						$estimated_rpo = 168; // Weekly.
						break;
				}
				break;
			}
		}

		// Check for database replication (excellent RPO).
		$has_db_replication = defined( 'DB_REPLICATION_ENABLED' ) && DB_REPLICATION_ENABLED;
		if ( $has_db_replication ) {
			$estimated_rpo = 0.01; // Near-zero.
		}

		// Check for continuous backup services.
		if ( defined( 'VAULTPRESS_API_KEY' ) || 
		     defined( 'BLOGVAULT_API_KEY' ) ||
		     get_option( 'jetpack_backup_active', false ) ) {
			$estimated_rpo = 0.25; // 15 minutes for real-time backups.
		}

		// Check backup retention.
		$backup_retention_days = get_option( 'wpshadow_backup_retention_days', 0 );

		// Evaluate issues.
		if ( $rpo_target === 0 || $rpo_target === false ) {
			$issues[] = __( 'No Recovery Point Objective (RPO) target defined', 'wpshadow' );
		}

		if ( ! $has_backup_plugin && ! $has_db_replication ) {
			$issues[] = __( 'No automated backup solution detected', 'wpshadow' );
			$issues[] = __( 'Cannot achieve any RPO without backups', 'wpshadow' );
		}

		if ( $has_backup_plugin && $rpo_target > 0 && $estimated_rpo > $rpo_target ) {
			$issues[] = sprintf(
				/* translators: 1: estimated RPO in hours 2: target RPO in hours */
				__( 'Current backup frequency (every %1$.1f hours) exceeds RPO target (%2$.1f hours)', 'wpshadow' ),
				$estimated_rpo,
				(float) $rpo_target
			);
		}

		$days_since_test = $rpo_last_test > 0 
			? ( time() - $rpo_last_test ) / DAY_IN_SECONDS 
			: 9999;

		if ( $has_backup_plugin && $days_since_test > 90 ) {
			$issues[] = sprintf(
				/* translators: %d: number of days */
				__( 'RPO not tested in %d+ days (recommend quarterly testing)', 'wpshadow' ),
				floor( $days_since_test )
			);
		}

		if ( $rpo_test_result === 'failed' ) {
			$issues[] = __( 'Last RPO test failed - cannot achieve target RPO', 'wpshadow' );
		}

		if ( $backup_retention_days > 0 && $backup_retention_days < 30 ) {
			$issues[] = sprintf(
				/* translators: %d: number of days */
				__( 'Backup retention only %d days (recommend 30+ for enterprise)', 'wpshadow' ),
				$backup_retention_days
			);
		}

		if ( $has_backup_plugin && empty( get_option( 'wpshadow_backup_offsite_location', '' ) ) ) {
			$issues[] = __( 'No off-site backup location configured', 'wpshadow' );
		}

		if ( empty( $issues ) ) {
			return null;
		}

		$description = sprintf(
			/* translators: 1: estimated RPO 2: backup plugin name */
			__( 'Recovery Point Objective (RPO) configuration incomplete. Estimated RPO: %1$s. %2$s', 'wpshadow' ),
			$estimated_rpo < 1 
				? sprintf( __( '%d minutes', 'wpshadow' ), round( $estimated_rpo * 60 ) )
				: sprintf( __( '%.1f hours', 'wpshadow' ), $estimated_rpo ),
			$has_backup_plugin 
				? sprintf( __( 'Using %s for backups.', 'wpshadow' ), $backup_plugin_name )
				: __( 'No backup solution detected.', 'wpshadow' )
		);

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => $description,
			'severity'     => 'critical',
			'threat_level' => 85,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/disaster-recovery-rpo',
			'issues'       => $issues,
			'persona'      => self::$persona,
			'context'      => array(
				'rpo_target'            => $rpo_target,
				'estimated_rpo'         => $estimated_rpo,
				'has_backup_plugin'     => $has_backup_plugin,
				'backup_plugin_name'    => $backup_plugin_name,
				'days_since_test'       => floor( $days_since_test ),
				'test_result'           => $rpo_test_result,
				'backup_retention_days' => $backup_retention_days,
				'has_db_replication'    => $has_db_replication,
			),
		);
	}
}
