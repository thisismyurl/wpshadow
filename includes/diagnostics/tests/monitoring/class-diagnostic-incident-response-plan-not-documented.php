<?php
/**
 * Incident Response Plan Not Documented Diagnostic
 *
 * Checks incident response.
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
 * Diagnostic_Incident_Response_Plan_Not_Documented Class
 *
 * Performs diagnostic check for Incident Response Plan Not Documented.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Incident_Response_Plan_Not_Documented extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'incident-response-plan-not-documented';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Incident Response Plan Not Documented';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks incident response';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'admin';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		if ( ! get_option( 'incident_response_plan_documented' ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'An incident response plan is not documented yet. A simple written playbook helps your team respond quickly and confidently during outages or security events.', 'wpshadow' ),
				'severity'    => 'medium',
				'threat_level' => 40,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/incident-response-plan-not-documented?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
