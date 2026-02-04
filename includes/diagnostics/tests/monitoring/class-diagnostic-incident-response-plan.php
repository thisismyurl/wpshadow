<?php
/**
 * Incident Response Plan Diagnostic
 *
 * Checks whether an incident response plan and escalation contacts are documented.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6035.0900
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
 * Detects missing incident response documentation and contacts.
 *
 * @since 1.6035.0900
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
	protected static $description = 'Checks if incident response procedures and escalation contacts are documented';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'monitoring';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6035.0900
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$plan_url = get_option( 'wpshadow_incident_response_plan_url', '' );
		$plan_text = get_option( 'wpshadow_incident_response_plan', '' );
		$contacts = get_option( 'wpshadow_incident_response_contacts', array() );
		$backup_plan = get_option( 'wpshadow_backup_restore_plan', '' );

		$has_plan = ! empty( $plan_url ) || ! empty( $plan_text );
		$has_contacts = is_array( $contacts ) && ! empty( $contacts );
		$has_backup_plan = ! empty( $backup_plan );

		if ( $has_plan && $has_contacts && $has_backup_plan ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'Incident response plan is incomplete or missing. Document escalation contacts and recovery steps to reduce downtime impact.', 'wpshadow' ),
			'severity'     => 'medium',
			'threat_level' => 60,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/incident-response-plan',
			'meta'         => array(
				'has_plan'        => $has_plan,
				'has_contacts'    => $has_contacts,
				'has_backup_plan' => $has_backup_plan,
			),
		);
	}
}
