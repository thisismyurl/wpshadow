<?php
/**
 * Incident Response Plan Diagnostic
 *
 * Checks if recovery procedures are documented.
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
 * Incident Response Plan Diagnostic Class
 *
 * Verifies recovery procedures are documented and accessible.
 * Like having emergency contact numbers posted where you can find them.
 *
 * @since 0.6093.1200
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
	protected static $description = 'Checks if recovery procedures are documented';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'downtime-prevention';

	/**
	 * Run the incident response plan diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if plan not documented, null otherwise.
	 */
	public static function check() {
		// Check if incident response plan is documented.
		$plan_documented = get_option( 'wpshadow_incident_plan_documented', false );

		if ( ! $plan_documented ) {
			return array(
				'id'           => self::$slug . '-not-documented',
				'title'        => __( 'No Incident Response Plan', 'wpshadow' ),
				'description'  => __( 'You don\'t have a documented recovery plan for when your site goes down (like having no fire escape route posted). When disaster strikes, you need to act fast—but without clear steps, you waste precious time figuring out what to do. Create a simple document with: 1) Emergency contacts (host support, developer, etc.), 2) Login credentials location, 3) Backup restoration steps, 4) Common troubleshooting steps, 5) Communication plan (who tells customers). Keep this somewhere accessible even if your site is down.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/incident-response-plan?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'context'      => array(),
			);
		}

		// Check if plan has been reviewed recently.
		$last_review = get_option( 'wpshadow_incident_plan_last_review', 0 );
		$days_since_review = ( time() - $last_review ) / DAY_IN_SECONDS;

		if ( $days_since_review > 180 || 0 === $last_review ) {
			return array(
				'id'           => self::$slug . '-outdated',
				'title'        => __( 'Incident Response Plan Not Reviewed Recently', 'wpshadow' ),
				'description'  => sprintf(
					/* translators: %d: days since last review */
					__( 'Your incident response plan hasn\'t been reviewed in %d days (like emergency contact numbers that haven\'t been checked in months). Things change: hosting providers, team members, backup locations, login credentials. Review and update your plan at least twice a year. Verify: contact numbers still work, credentials are current, backup restoration steps are accurate, team knows their roles.', 'wpshadow' ),
					(int) $days_since_review
				),
				'severity'     => 'low',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/incident-response-plan?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'context'      => array(
					'days_since_review' => $days_since_review,
					'last_review'       => $last_review,
				),
			);
		}

		// Check if plan includes key elements.
		$plan_elements = get_option( 'wpshadow_incident_plan_elements', array() );
		$required_elements = array(
			'contacts'      => __( 'Emergency contacts', 'wpshadow' ),
			'credentials'   => __( 'Login credentials location', 'wpshadow' ),
			'backups'       => __( 'Backup restoration steps', 'wpshadow' ),
			'troubleshoot'  => __( 'Troubleshooting checklist', 'wpshadow' ),
			'communication' => __( 'Customer communication plan', 'wpshadow' ),
		);

		$missing_elements = array();
		foreach ( $required_elements as $key => $label ) {
			if ( ! in_array( $key, $plan_elements, true ) ) {
				$missing_elements[ $key ] = $label;
			}
		}

		if ( ! empty( $missing_elements ) ) {
			return array(
				'id'           => self::$slug . '-incomplete',
				'title'        => __( 'Incident Response Plan Incomplete', 'wpshadow' ),
				'description'  => sprintf(
					/* translators: %s: list of missing elements */
					__( 'Your incident response plan is missing important elements (like an emergency plan without all the phone numbers). Missing: %s. A complete plan helps you respond faster and more effectively during outages. Update your plan to include these critical pieces. When every minute costs money, preparation matters.', 'wpshadow' ),
					implode( ', ', $missing_elements )
				),
				'severity'     => 'low',
				'threat_level' => 35,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/incident-response-plan?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'context'      => array(
					'missing_elements' => $missing_elements,
					'current_elements' => $plan_elements,
				),
			);
		}

		return null; // Incident response plan is complete and current.
	}
}
