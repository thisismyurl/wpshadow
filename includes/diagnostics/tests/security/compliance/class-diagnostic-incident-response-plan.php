<?php
/**
 * Incident Response Plan Diagnostic
 *
 * Checks if IR plan is documented and tested.
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
 * Incident Response Plan Diagnostic Class
 *
 * Verifies that an Incident Response (IR) plan exists, is documented,
 * and has been tested through drills or simulations.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Incident_Response_Plan extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'incident-response-plan';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Incident Response Plan';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'IR plan documented and tested';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'compliance';

	/**
	 * Run the incident response plan check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if IR plan missing, null otherwise.
	 */
	public static function check() {
		$stats = array();
		$issues = array();

		// Check if IR plan exists.
		$ir_plan_exists = get_option( 'wpshadow_ir_plan_exists' );
		$stats['plan_exists'] = boolval( $ir_plan_exists );

		if ( ! $ir_plan_exists ) {
			$issues[] = __( 'No incident response plan documented', 'wpshadow' );
		}

		// Check if plan is current.
		$ir_plan_date = get_option( 'wpshadow_ir_plan_updated' );
		$stats['last_updated'] = $ir_plan_date;

		if ( ! empty( $ir_plan_date ) ) {
			$plan_timestamp = strtotime( $ir_plan_date );
			$current_time = current_time( 'timestamp' );
			$days_old = ( $current_time - $plan_timestamp ) / ( 60 * 60 * 24 );

			$stats['days_since_update'] = round( $days_old );

			if ( $days_old > 365 ) {
				$issues[] = sprintf(
					/* translators: %d: days */
					__( 'IR plan last updated %d days ago - recommend annual review', 'wpshadow' ),
					round( $days_old )
				);
			}
		}

		// Check for IR team contacts.
		$ir_contacts = get_option( 'wpshadow_ir_team_contacts', array() );
		$stats['team_contacts_defined'] = is_array( $ir_contacts ) && count( $ir_contacts ) > 0;
		$stats['contact_count'] = is_array( $ir_contacts ) ? count( $ir_contacts ) : 0;

		if ( empty( $ir_contacts ) ) {
			$issues[] = __( 'No incident response team contacts defined', 'wpshadow' );
		}

		// Check for escalation procedures.
		$escalation_procedures = get_option( 'wpshadow_ir_escalation_procedure' );
		$stats['escalation_documented'] = boolval( $escalation_procedures );

		if ( ! $escalation_procedures ) {
			$issues[] = __( 'No escalation procedures documented', 'wpshadow' );
		}

		// Check for communication plan.
		$communication_plan = get_option( 'wpshadow_ir_communication_plan' );
		$stats['communication_plan_exists'] = boolval( $communication_plan );

		if ( ! $communication_plan ) {
			$issues[] = __( 'No incident communication plan defined', 'wpshadow' );
		}

		// Check for containment procedures.
		$containment_procedures = get_option( 'wpshadow_ir_containment_procedures' );
		$stats['containment_procedures_defined'] = boolval( $containment_procedures );

		if ( ! $containment_procedures ) {
			$issues[] = __( 'No containment procedures defined', 'wpshadow' );
		}

		// Check for recovery procedures.
		$recovery_procedures = get_option( 'wpshadow_ir_recovery_procedures' );
		$stats['recovery_procedures_defined'] = boolval( $recovery_procedures );

		if ( ! $recovery_procedures ) {
			$issues[] = __( 'No recovery procedures defined', 'wpshadow' );
		}

		// Check for tests/drills.
		$ir_drills = get_option( 'wpshadow_ir_drills' );
		$stats['drills_performed'] = boolval( $ir_drills );

		if ( ! $ir_drills ) {
			$issues[] = __( 'No incident response drills or tests performed', 'wpshadow' );
		} else {
			// Check if drills are recent.
			$last_drill_date = get_option( 'wpshadow_ir_last_drill_date' );
			if ( ! empty( $last_drill_date ) ) {
				$drill_timestamp = strtotime( $last_drill_date );
				$current_time = current_time( 'timestamp' );
				$days_since_drill = ( $current_time - $drill_timestamp ) / ( 60 * 60 * 24 );

				$stats['last_drill_date'] = $last_drill_date;
				$stats['days_since_drill'] = round( $days_since_drill );

				if ( $days_since_drill > 365 ) {
					$issues[] = sprintf(
						/* translators: %d: days */
						__( 'Last IR drill was %d days ago - recommend annual testing', 'wpshadow' ),
						round( $days_since_drill )
					);
				}
			}
		}

		// Check for after-action reviews.
		$aar_process = get_option( 'wpshadow_ir_after_action_reviews' );
		$stats['aar_process_exists'] = boolval( $aar_process );

		if ( ! $aar_process ) {
			$issues[] = __( 'No after-action review process defined', 'wpshadow' );
		}

		// If critical issues found.
		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Incident response plan issues: ', 'wpshadow' ) . implode( ', ', $issues ),
				'severity'     => 'high',
				'threat_level' => 80,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/incident-response',
				'context'      => array(
					'stats'  => $stats,
					'issues' => $issues,
				),
			);
		}

		return null; // IR plan properly documented.
	}
}
