<?php
/**
 * Gdpr Cookie Compliance Consent Diagnostic
 *
 * Gdpr Cookie Compliance Consent not compliant.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1106.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Gdpr Cookie Compliance Consent Diagnostic Class
 *
 * @since 1.1106.0000
 */
class Diagnostic_GdprCookieComplianceConsent extends Diagnostic_Base {

	protected static $slug = 'gdpr-cookie-compliance-consent';
	protected static $title = 'Gdpr Cookie Compliance Consent';
	protected static $description = 'Gdpr Cookie Compliance Consent not compliant';
	protected static $family = 'security';

	public static function check() {
		// Check for GDPR cookie plugins
		$has_gdpr = defined( 'GDPR_COOKIE_CONSENT_VERSION' ) ||
		            class_exists( 'GDPR_Cookie_Compliance' ) ||
		            function_exists( 'gdpr_cookie_compliance' );

		if ( ! $has_gdpr ) {
			return null;
		}

		$issues = array();

		// Check 1: Cookie consent enabled
		$consent_enabled = get_option( 'gdpr_cookie_consent_enabled', 'no' );
		if ( 'no' === $consent_enabled ) {
			$issues[] = __( 'Cookie consent disabled (GDPR violation)', 'wpshadow' );
		}

		// Check 2: Explicit consent required
		$consent_type = get_option( 'gdpr_cookie_consent_type', 'implied' );
		if ( 'implied' === $consent_type ) {
			$issues[] = __( 'Using implied consent (not GDPR compliant)', 'wpshadow' );
		}

		// Check 3: Cookie policy page
		$policy_page = get_option( 'gdpr_cookie_policy_page', 0 );
		if ( ! $policy_page || get_post_status( $policy_page ) !== 'publish' ) {
			$issues[] = __( 'No published cookie policy (GDPR requirement)', 'wpshadow' );
		}

		// Check 4: Cookie categories
		$categories = get_option( 'gdpr_cookie_categories', array() );
		if ( empty( $categories ) ) {
			$issues[] = __( 'No cookie categories defined (poor transparency)', 'wpshadow' );
		}

		// Check 5: Consent logging
		$log_consent = get_option( 'gdpr_cookie_log_consent', 'no' );
		if ( 'no' === $log_consent ) {
			$issues[] = __( 'Consent not logged (cannot prove compliance)', 'wpshadow' );
		}

		// Check 6: Easy withdrawal
		$allow_withdrawal = get_option( 'gdpr_cookie_allow_withdrawal', 'no' );
		if ( 'no' === $allow_withdrawal ) {
			$issues[] = __( 'Consent withdrawal not allowed (GDPR violation)', 'wpshadow' );
		}

		if ( empty( $issues ) ) {
			return null;
		}

		$threat_level = 70;
		if ( count( $issues ) >= 4 ) {
			$threat_level = 82;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 76;
		}

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				__( 'GDPR cookie compliance has %d issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => $threat_level,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/gdpr-cookie-compliance-consent',
		);
	}
}
