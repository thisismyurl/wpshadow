<?php
/**
 * Business Continuity Plan Diagnostic
 *
 * Checks if BC plan with RTO/RPO is defined.
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
 * Business Continuity Plan Diagnostic Class
 *
 * Verifies that a Business Continuity (BC) plan exists with
 * defined Recovery Time Objective (RTO) and Recovery Point
 * Objective (RPO) metrics.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Business_Continuity_Plan extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'business-continuity-plan';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Business Continuity Plan';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'BC plan with RTO/RPO defined';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'compliance';

	/**
	 * Run the business continuity plan check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if BC plan missing, null otherwise.
	 */
	public static function check() {
		$stats = array();
		$issues = array();

		// Check if BC plan exists.
		$bc_plan_exists = get_option( 'wpshadow_bc_plan_exists' );
		$stats['plan_exists'] = boolval( $bc_plan_exists );

		if ( ! $bc_plan_exists ) {
			$issues[] = __( 'No business continuity plan documented', 'wpshadow' );
		}

		// Check for RTO (Recovery Time Objective).
		$rto = get_option( 'wpshadow_bc_rto_minutes' );
		$stats['rto_defined'] = ! empty( $rto );
		$stats['rto_minutes'] = intval( $rto );

		if ( empty( $rto ) ) {
			$issues[] = __( 'Recovery Time Objective (RTO) not defined', 'wpshadow' );
		} else {
			// Validate RTO is reasonable.
			if ( intval( $rto ) > 1440 ) { // More than 24 hours.
				$issues[] = sprintf(
					/* translators: %d: minutes */
					__( 'RTO of %d minutes may be too long for business continuity', 'wpshadow' ),
					intval( $rto )
				);
			}
		}

		// Check for RPO (Recovery Point Objective).
		$rpo = get_option( 'wpshadow_bc_rpo_minutes' );
		$stats['rpo_defined'] = ! empty( $rpo );
		$stats['rpo_minutes'] = intval( $rpo );

		if ( empty( $rpo ) ) {
			$issues[] = __( 'Recovery Point Objective (RPO) not defined', 'wpshadow' );
		} else {
			// Validate RPO makes sense relative to backup frequency.
			$backup_frequency = get_option( 'wpshadow_backup_frequency_minutes' );
			if ( ! empty( $backup_frequency ) && intval( $backup_frequency ) > intval( $rpo ) ) {
				$issues[] = sprintf(
					/* translators: %d: minutes */
					__( 'Backup frequency (%d min) exceeds RPO target', 'wpshadow' ),
					intval( $backup_frequency )
				);
			}
		}

		// Check for alternate site/failover.
		$alternate_site = get_option( 'wpshadow_bc_alternate_site_url' );
		$stats['alternate_site_configured'] = ! empty( $alternate_site );

		if ( empty( $alternate_site ) ) {
			$issues[] = __( 'No alternate site or failover configured', 'wpshadow' );
		} else {
			$stats['alternate_site'] = esc_url( $alternate_site );
		}

		// Check for backup location.
		$backup_location = get_option( 'wpshadow_bc_backup_location' );
		$stats['backup_location_configured'] = ! empty( $backup_location );

		if ( empty( $backup_location ) ) {
			$issues[] = __( 'No secondary backup location configured', 'wpshadow' );
		} else {
			$stats['backup_location'] = sanitize_text_field( $backup_location );
		}

		// Check for regular testing.
		$bc_tested = get_option( 'wpshadow_bc_last_test_date' );
		$stats['tested'] = ! empty( $bc_tested );

		if ( empty( $bc_tested ) ) {
			$issues[] = __( 'Business continuity plan has never been tested', 'wpshadow' );
		} else {
			$test_timestamp = strtotime( $bc_tested );
			$current_time = current_time( 'timestamp' );
			$days_since_test = ( $current_time - $test_timestamp ) / ( 60 * 60 * 24 );

			$stats['last_test_date'] = $bc_tested;
			$stats['days_since_test'] = round( $days_since_test );

			if ( $days_since_test > 180 ) {
				$issues[] = sprintf(
					/* translators: %d: days */
					__( 'BC plan last tested %d days ago - recommend semi-annual testing', 'wpshadow' ),
					round( $days_since_test )
				);
			}
		}

		// Check for staff awareness/training.
		$staff_trained = get_option( 'wpshadow_bc_staff_trained' );
		$stats['staff_trained'] = boolval( $staff_trained );

		if ( ! $staff_trained ) {
			$issues[] = __( 'BC plan team has not been trained', 'wpshadow' );
		}

		// Check for documentation currency.
		$bc_plan_date = get_option( 'wpshadow_bc_plan_updated' );
		$stats['last_updated'] = $bc_plan_date;

		if ( ! empty( $bc_plan_date ) ) {
			$plan_timestamp = strtotime( $bc_plan_date );
			$current_time = current_time( 'timestamp' );
			$days_old = ( $current_time - $plan_timestamp ) / ( 60 * 60 * 24 );

			$stats['days_since_update'] = round( $days_old );

			if ( $days_old > 365 ) {
				$issues[] = sprintf(
					/* translators: %d: days */
					__( 'BC plan last updated %d days ago - recommend annual review', 'wpshadow' ),
					round( $days_old )
				);
			}
		}

		// If critical issues found.
		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Business continuity issues: ', 'wpshadow' ) . implode( ', ', $issues ),
				'severity'     => 'high',
				'threat_level' => 85,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/business-continuity',
				'context'      => array(
					'stats'  => $stats,
					'issues' => $issues,
				),
			);
		}

		return null; // BC plan properly configured.
	}
}
