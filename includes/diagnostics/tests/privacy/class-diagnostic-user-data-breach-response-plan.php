<?php
/**
 * User Data Breach Response Plan Diagnostic
 *
 * Checks whether a documented incident response plan exists.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2240
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * User Data Breach Response Plan Diagnostic
 *
 * Ensures a breach response plan is configured and accessible.
 *
 * @since 1.2601.2240
 */
class Diagnostic_User_Data_Breach_Response_Plan extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'user-data-breach-response-plan';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'User Data Breach Response Plan';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether a documented incident response plan exists';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'privacy';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2240
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();
		$details = array();

		$plan_url     = get_option( 'wpshadow_incident_response_plan_url', '' );
		$plan_contact = get_option( 'wpshadow_incident_response_contact', '' );
		$policy_page  = get_option( 'wp_page_for_privacy_policy' );

		if ( empty( $plan_url ) ) {
			$issues[] = __( 'No documented breach response plan URL configured', 'wpshadow' );
		} else {
			$details['plan_url'] = esc_url_raw( $plan_url );
		}

		if ( empty( $plan_contact ) ) {
			$issues[] = __( 'No incident response contact configured', 'wpshadow' );
		} else {
			$details['plan_contact'] = sanitize_text_field( $plan_contact );
		}

		if ( empty( $policy_page ) ) {
			$issues[] = __( 'Privacy policy page is not configured', 'wpshadow' );
		}

		$drill_date = get_option( 'wpshadow_incident_response_last_drill', '' );
		if ( empty( $drill_date ) ) {
			$issues[] = __( 'No incident response drill date recorded', 'wpshadow' );
		} else {
			$details['last_drill'] = sanitize_text_field( $drill_date );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Data breach response plan is incomplete or missing', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/user-data-breach-response-plan',
				'details'      => array(
					'issues' => $issues,
					'info'   => $details,
				),
			);
		}

		return null;
	}
}
