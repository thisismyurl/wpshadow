<?php
/**
 * Disaster Recovery RTO Diagnostic
 *
 * Checks if Recovery Time Objective (RTO) is achievable and tested.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Tests
 * @since      1.6035.1445
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Disaster Recovery RTO Diagnostic Class
 *
 * Detects if Recovery Time Objective is properly defined,
 * configured, and validated through testing.
 *
 * @since 1.6035.1445
 */
class Diagnostic_Disaster_Recovery_Rto extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'disaster-recovery-rto';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Disaster Recovery RTO';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if Recovery Time Objective is achievable in testing';

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
	 * @since  1.6035.1445
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check for RTO configuration.
		$rto_target = get_option( 'wpshadow_disaster_recovery_rto_hours', 0 );
		$rto_last_test = get_option( 'wpshadow_disaster_recovery_rto_last_test', 0 );
		$rto_test_result = get_option( 'wpshadow_disaster_recovery_rto_test_result', '' );
		$rto_actual_hours = get_option( 'wpshadow_disaster_recovery_rto_actual_hours', 0 );

		// Check for recovery automation.
		$has_automated_recovery = false;
		$recovery_method = 'manual';

		// Check for disaster recovery plugins/services.
		$dr_solutions = array(
			'jetpack/jetpack.php'                         => 'Jetpack Backup & Restore',
			'blogvault-real-time-backup/backup.php'       => 'BlogVault',
			'updraftplus/updraftplus.php'                 => 'UpdraftPlus',
			'duplicator-pro/duplicator-pro.php'           => 'Duplicator Pro',
			'wp-staging/wp-staging.php'                   => 'WP Staging',
			'backup-guard-platinum/backup-guard-pro.php'  => 'Backup Guard',
		);

		$active_dr_solution = '';
		foreach ( $dr_solutions as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$active_dr_solution = $plugin_name;
				$has_automated_recovery = true;
				$recovery_method = 'automated';
				break;
			}
		}

		// Check for staging environment.
		$has_staging = defined( 'WP_STAGING_SITE' ) || 
		               get_option( 'wpshadow_staging_url', '' ) !== '' ||
		               is_plugin_active( 'wp-staging/wp-staging.php' );

		// Check for load balancer / failover.
		$has_failover = defined( 'WP_LOAD_BALANCER' ) || 
		                defined( 'DB_FAILOVER_HOST' ) ||
		                get_option( 'wpshadow_failover_configured', false );

		// Estimate RTO based on configuration.
		$estimated_rto = 24; // Default: 24 hours manual recovery.

		if ( $has_failover ) {
			$estimated_rto = 0.1; // 6 minutes with automatic failover.
		} elseif ( $has_automated_recovery && $has_staging ) {
			$estimated_rto = 2; // 2 hours with automated recovery and staging.
		} elseif ( $has_automated_recovery ) {
			$estimated_rto = 4; // 4 hours with automated recovery only.
		} elseif ( $has_staging ) {
			$estimated_rto = 8; // 8 hours with staging but manual recovery.
		}

		// Check recovery runbook.
		$has_runbook = get_option( 'wpshadow_disaster_recovery_runbook_url', '' ) !== '' ||
		               get_option( 'wpshadow_disaster_recovery_procedure_documented', false );

		// Check team training.
		$team_last_trained = get_option( 'wpshadow_disaster_recovery_team_last_trained', 0 );
		$days_since_training = $team_last_trained > 0 
			? ( time() - $team_last_trained ) / DAY_IN_SECONDS 
			: 9999;

		// Evaluate issues.
		if ( $rto_target === 0 || $rto_target === false ) {
			$issues[] = __( 'No Recovery Time Objective (RTO) target defined', 'wpshadow' );
		}

		if ( ! $has_automated_recovery && ! $has_failover ) {
			$issues[] = __( 'No automated disaster recovery solution detected', 'wpshadow' );
		}

		if ( $rto_target > 0 && $estimated_rto > $rto_target ) {
			$issues[] = sprintf(
				/* translators: 1: estimated RTO in hours 2: target RTO in hours */
				__( 'Estimated recovery time (%.1f hours) exceeds RTO target (%.1f hours)', 'wpshadow' ),
				$estimated_rto,
				(float) $rto_target
			);
		}

		$days_since_test = $rto_last_test > 0 
			? ( time() - $rto_last_test ) / DAY_IN_SECONDS 
			: 9999;

		if ( $days_since_test > 180 ) {
			$issues[] = sprintf(
				/* translators: %d: number of days */
				__( 'RTO not tested in %d+ days (recommend semi-annual testing)', 'wpshadow' ),
				floor( $days_since_test )
			);
		} elseif ( $days_since_test > 90 ) {
			$issues[] = sprintf(
				/* translators: %d: number of days */
				__( 'RTO not tested in %d+ days (recommend quarterly testing)', 'wpshadow' ),
				floor( $days_since_test )
			);
		}

		if ( $rto_test_result === 'failed' ) {
			$issues[] = __( 'Last RTO test failed - cannot achieve target RTO', 'wpshadow' );
		}

		if ( $rto_actual_hours > 0 && $rto_target > 0 && $rto_actual_hours > $rto_target ) {
			$issues[] = sprintf(
				/* translators: 1: actual RTO 2: target RTO */
				__( 'Last test showed %.1f hour recovery time, exceeding %.1f hour target', 'wpshadow' ),
				(float) $rto_actual_hours,
				(float) $rto_target
			);
		}

		if ( ! $has_runbook ) {
			$issues[] = __( 'No disaster recovery runbook or procedure documented', 'wpshadow' );
		}

		if ( $days_since_training > 365 ) {
			$issues[] = __( 'Disaster recovery team not trained in past year', 'wpshadow' );
		}

		if ( ! $has_staging ) {
			$issues[] = __( 'No staging environment for recovery testing', 'wpshadow' );
		}

		if ( empty( $issues ) ) {
			return null;
		}

		$description = sprintf(
			/* translators: 1: estimated RTO 2: recovery method 3: DR solution name */
			__( 'Recovery Time Objective (RTO) configuration incomplete. Estimated RTO: %.1f hours (%s recovery). %s', 'wpshadow' ),
			$estimated_rto,
			$recovery_method,
			$active_dr_solution 
				? sprintf( __( 'Using %s for disaster recovery.', 'wpshadow' ), $active_dr_solution )
				: __( 'No automated DR solution detected.', 'wpshadow' )
		);

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => $description,
			'severity'     => 'critical',
			'threat_level' => 90,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/disaster-recovery-rto',
			'issues'       => $issues,
			'persona'      => self::$persona,
			'context'      => array(
				'rto_target'             => $rto_target,
				'estimated_rto'          => $estimated_rto,
				'has_automated_recovery' => $has_automated_recovery,
				'recovery_method'        => $recovery_method,
				'active_dr_solution'     => $active_dr_solution,
				'days_since_test'        => floor( $days_since_test ),
				'test_result'            => $rto_test_result,
				'actual_rto_hours'       => $rto_actual_hours,
				'has_runbook'            => $has_runbook,
				'days_since_training'    => floor( $days_since_training ),
				'has_staging'            => $has_staging,
				'has_failover'           => $has_failover,
			),
		);
	}
}
