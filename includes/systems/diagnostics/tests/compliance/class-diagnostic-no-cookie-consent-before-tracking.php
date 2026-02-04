<?php
/**
 * No Cookie Consent Before Tracking Diagnostic
 *
 * Detects when tracking cookies load before user consent,
 * violating GDPR and ePrivacy regulations.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Compliance
 * @since      1.6035.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic: No Cookie Consent Before Tracking
 *
 * Checks whether cookies are blocked until
 * user provides consent.
 *
 * @since 1.6035.2148
 */
class Diagnostic_No_Cookie_Consent_Before_Tracking extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-cookie-consent-before-tracking';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Cookie Consent Before Tracking';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether cookies load only after consent';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'compliance';

	/**
	 * Whether this diagnostic is auto-fixable
	 *
	 * @var bool
	 */
	protected static $auto_fixable = false;

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6035.2148
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if GA/tracking loads immediately
		$homepage = wp_remote_get( home_url() );
		if ( is_wp_error( $homepage ) ) {
			return null;
		}

		$body = wp_remote_retrieve_body( $homepage );

		// Check for tracking scripts
		$has_tracking = strpos( $body, 'google-analytics.com' ) !== false ||
			strpos( $body, 'googletagmanager.com' ) !== false ||
			strpos( $body, 'facebook.net' ) !== false;

		// Check for consent management
		$has_consent_blocker = strpos( $body, 'cookiebot' ) !== false ||
			strpos( $body, 'cookie-consent' ) !== false ||
			strpos( $body, 'gdpr-cookie' ) !== false;

		if ( $has_tracking && ! $has_consent_blocker ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __(
					'Tracking cookies are loading before users consent, which violates GDPR. GDPR requires: no tracking cookies before explicit consent, users must opt-in (not opt-out), consent must be freely given (not forced). Your site loads tracking immediately, which means every EU visitor generates a compliance violation. Fines are real: €20M or 4% of global revenue. Cookie consent blockers prevent tracking until users say yes.',
					'wpshadow'
				),
				'severity'      => 'critical',
				'threat_level'  => 95,
				'auto_fixable'  => false,
				'business_impact' => array(
					'metric'         => 'GDPR Compliance',
					'potential_gain' => 'Avoid €20M GDPR violations',
					'roi_explanation' => 'GDPR requires consent before tracking. Violations carry €20M fines. Cookie consent blockers ensure compliance.',
				),
				'kb_link'       => 'https://wpshadow.com/kb/cookie-consent-before-tracking',
			);
		}

		return null;
	}
}
