<?php
/**
 * No Cookie Consent Banner Diagnostic
 *
 * Detects when cookie consent is not implemented,
 * violating GDPR and privacy regulations.
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
 * Diagnostic: No Cookie Consent Banner
 *
 * Checks whether cookie consent mechanism
 * is implemented for GDPR compliance.
 *
 * @since 1.6035.2148
 */
class Diagnostic_No_Cookie_Consent_Banner extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-cookie-consent-banner';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Cookie Consent Banner';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether cookie consent exists';

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
		// Check for cookie consent plugins
		$has_consent = is_plugin_active( 'cookie-notice/cookie-notice.php' ) ||
			is_plugin_active( 'cookiebot/cookiebot.php' ) ||
			is_plugin_active( 'complianz-gdpr/complianz-gdpr.php' );

		// Check homepage for consent banner
		if ( ! $has_consent ) {
			$homepage = wp_remote_get( home_url() );
			if ( ! is_wp_error( $homepage ) ) {
				$body = wp_remote_retrieve_body( $homepage );
				$has_consent = preg_match( '/cookie.*consent|cookie.*notice|cookiebot/i', $body );
			}
		}

		if ( ! $has_consent ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __(
					'Cookie consent isn\'t implemented, which violates GDPR and ePrivacy laws. GDPR requires: explicit consent before setting cookies (except essential), granular control (accept/reject categories), ability to withdraw consent. Fines: €20M or 4% revenue. Cookie consent must: appear before setting analytics/marketing cookies, allow rejection, persist choice. Plugins: Cookie Notice (free), Cookiebot, Complianz GDPR. This is non-optional for EU visitors.',
					'wpshadow'
				),
				'severity'      => 'critical',
				'threat_level'  => 90,
				'auto_fixable'  => false,
				'business_impact' => array(
					'metric'         => 'GDPR Compliance & Legal Risk',
					'potential_gain' => 'Avoid €20M GDPR fines for cookie violations',
					'roi_explanation' => 'Cookie consent is legally required by GDPR. Violations result in massive fines and legal prosecution.',
				),
				'kb_link'       => 'https://wpshadow.com/kb/cookie-consent-banner',
			);
		}

		return null;
	}
}
