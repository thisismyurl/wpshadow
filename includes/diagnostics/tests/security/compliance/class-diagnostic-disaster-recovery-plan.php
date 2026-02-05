<?php
/**
 * Disaster Recovery Plan Diagnostic
 *
 * Checks if DR plan is tested and validated.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6035.1415
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Disaster Recovery Plan Diagnostic Class
 *
 * Verifies that a Disaster Recovery (DR) plan exists, has been
 * tested, and is regularly validated to ensure recovery capability.
 *
 * @since 1.6035.1415
 */
class Diagnostic_Disaster_Recovery_Plan extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'disaster-recovery-plan';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Disaster Recovery Plan';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'DR plan tested and validated';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'compliance';

	/**
	 * Run the disaster recovery plan check.
	 *
	 * @since  1.6035.1415
	 * @return array|null Finding array if DR plan missing, null otherwise.
	 */
	public static function check() {
		$stats = array();
		$issues = array();

		// Check if DR plan exists.
		$dr_plan_exists = get_option( 'wpshadow_dr_plan_exists' );
		$stats['plan_exists'] = boolval( $dr_plan_exists );

		if ( ! $dr_plan_exists ) {
			$issues[] = __( 'No disaster recovery plan documented', 'wpshadow' );
		}

		// Check for complete data backups.
		$backup_full = get_option( 'wpshadow_backup_full_enabled' );
		$stats['full_backups_enabled'] = boolval( $backup_full );

		if ( ! $backup_full ) {
			$issues[] = __( 'Full data backups not enabled', 'wpshadow' );
		}

		// Check backup frequency.
		$backup_frequency = get_option( 'wpshadow_backup_frequency' );
		$stats['backup_frequency'] = sanitize_text_field( $backup_frequency );

		if ( empty( $backup_frequency ) ) {
			$issues[] = __( 'Backup frequency not configured', 'wpshadow' );
		} elseif ( 'daily' !== $backup_frequency && 'hourly' !== $backup_frequency ) {
			$issues[] = sprintf(
				/* translators: %s: frequency */
				__( 'Backup frequency %s may be insufficient for DR', 'wpshadow' ),
				$backup_frequency
			);
		}

		// Check for off-site backups.
		$offsite_backup = get_option( 'wpshadow_offsite_backup_enabled' );
		$stats['offsite_backup'] = boolval( $offsite_backup );

		if ( ! $offsite_backup ) {
			$issues[] = __( 'Off-site backups not configured - at risk of total loss', 'wpshadow' );
		}

		// Check backup locations.
		$backup_locations = get_option( 'wpshadow_backup_locations', array() );
		$stats['backup_location_count'] = is_array( $backup_locations ) ? count( $backup_locations ) : 0;

		if ( is_array( $backup_locations ) && count( $backup_locations ) < 2 ) {
			$issues[] = __( 'Backups stored in less than 2 locations - single point of failure', 'wpshadow' );
		}

		// Check for disaster recovery failover infrastructure.
		$failover_configured = get_option( 'wpshadow_dr_failover_configured' );
		$stats['failover_configured'] = boolval( $failover_configured );

		if ( ! $failover_configured ) {
			$issues[] = __( 'No disaster recovery failover infrastructure configured', 'wpshadow' );
		}

		// Check for recovery procedures.
		$recovery_procedures = get_option( 'wpshadow_dr_recovery_procedures' );
		$stats['recovery_procedures_documented'] = boolval( $recovery_procedures );

		if ( ! $recovery_procedures ) {
			$issues[] = __( 'No recovery procedures documented', 'wpshadow' );
		}

		// Check for DR testing.
		$last_dr_test = get_option( 'wpshadow_dr_last_test_date' );
		$stats['tested'] = ! empty( $last_dr_test );

		if ( empty( $last_dr_test ) ) {
			$issues[] = __( 'Disaster recovery plan has never been tested', 'wpshadow' );
		} else {
			$test_timestamp = strtotime( $last_dr_test );
			$current_time = current_time( 'timestamp' );
			$days_since_test = ( $current_time - $test_timestamp ) / ( 60 * 60 * 24 );

			$stats['last_test_date'] = $last_dr_test;
			$stats['days_since_test'] = round( $days_since_test );

			if ( $days_since_test > 180 ) {
				$issues[] = sprintf(
					/* translators: %d: days */
					__( 'DR plan last tested %d days ago - recommend semi-annual testing', 'wpshadow' ),
					round( $days_since_test )
				);
			}
		}

		// Check for successful recoveries.
		$successful_recoveries = get_option( 'wpshadow_dr_successful_recoveries', 0 );
		$stats['successful_recoveries'] = intval( $successful_recoveries );

		if ( intval( $successful_recoveries ) === 0 && ! empty( $last_dr_test ) ) {
			$issues[] = __( 'No successful recovery validations recorded', 'wpshadow' );
		}

		// Check for documentation.
		$dr_plan_date = get_option( 'wpshadow_dr_plan_updated' );
		$stats['last_updated'] = $dr_plan_date;

		if ( ! empty( $dr_plan_date ) ) {
			$plan_timestamp = strtotime( $dr_plan_date );
			$current_time = current_time( 'timestamp' );
			$days_old = ( $current_time - $plan_timestamp ) / ( 60 * 60 * 24 );

			$stats['days_since_update'] = round( $days_old );

			if ( $days_old > 365 ) {
				$issues[] = sprintf(
					/* translators: %d: days */
					__( 'DR plan last updated %d days ago - recommend annual review', 'wpshadow' ),
					round( $days_old )
				);
			}
		}

		// Check for staff training.
		$staff_trained = get_option( 'wpshadow_dr_staff_trained' );
		$stats['staff_trained'] = boolval( $staff_trained );

		if ( ! $staff_trained ) {
			$issues[] = __( 'DR recovery team has not been trained', 'wpshadow' );
		}

		// If critical issues found.
		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Disaster recovery issues: ', 'wpshadow' ) . implode( ', ', $issues ),
				'severity'     => 'critical',
				'threat_level' => 95,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/disaster-recovery',
				'context'      => array(
					'stats'  => $stats,
					'issues' => $issues,
				),
			);
		}

		return null; // DR plan properly tested and validated.
	}
}
