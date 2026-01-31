<?php
/**
 * Age Gate Cookie Management Diagnostic
 *
 * Age Gate Cookie Management not compliant.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1122.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Age Gate Cookie Management Diagnostic Class
 *
 * @since 1.1122.0000
 */
class Diagnostic_AgeGateCookieManagement extends Diagnostic_Base {

	protected static $slug = 'age-gate-cookie-management';
	protected static $title = 'Age Gate Cookie Management';
	protected static $description = 'Age Gate Cookie Management not compliant';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! class_exists( 'Age_Gate' ) && ! defined( 'AGE_GATE_VERSION' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Cookie expiration time.
		$cookie_duration = get_option( 'age_gate_cookie_duration', 0 );
		if ( $cookie_duration === 0 ) {
			$issues[] = 'age verification cookie has no expiration (privacy concern)';
		} elseif ( $cookie_duration > 365 ) {
			$issues[] = "cookie expires in {$cookie_duration} days (GDPR recommends shorter duration)";
		}

		// Check 2: Secure cookie flag.
		$secure_cookie = get_option( 'age_gate_secure_cookie', '0' );
		if ( '0' === $secure_cookie && is_ssl() ) {
			$issues[] = 'age gate cookies not marked secure on HTTPS site';
		}

		// Check 3: HttpOnly flag.
		$httponly_cookie = get_option( 'age_gate_httponly_cookie', '0' );
		if ( '0' === $httponly_cookie ) {
			$issues[] = 'cookies not marked HttpOnly (XSS vulnerability)';
		}

		// Check 4: SameSite attribute.
		$samesite = get_option( 'age_gate_samesite', 'None' );
		if ( 'None' === $samesite ) {
			$issues[] = 'SameSite set to None (CSRF risk)';
		}

		// Check 5: Cookie name conflicts.
		$cookie_name = get_option( 'age_gate_cookie_name', 'age_verified' );
		if ( 'age_verified' === $cookie_name ) {
			$issues[] = 'using default cookie name (potential conflicts with other plugins)';
		}

		// Check 6: Cookie consent integration.
		$consent_required = get_option( 'age_gate_require_consent', '0' );
		if ( '0' === $consent_required ) {
			$issues[] = 'no cookie consent required before age verification (GDPR issue)';
		}

		if ( ! empty( $issues ) ) {
			$threat_level = min( 70, 40 + ( count( $issues ) * 6 ) );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'Age Gate cookie management issues: ' . implode( ', ', $issues ),
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/age-gate-cookie-management',
			);
		}

		return null;
	}
}
