<?php
/**
 * Cookiebot Consent Database Diagnostic
 *
 * Cookiebot Consent Database not compliant.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1116.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Cookiebot Consent Database Diagnostic Class
 *
 * @since 1.1116.0000
 */
class Diagnostic_CookiebotConsentDatabase extends Diagnostic_Base {

	protected static $slug = 'cookiebot-consent-database';
	protected static $title = 'Cookiebot Consent Database';
	protected static $description = 'Cookiebot Consent Database not compliant';
	protected static $family = 'security';

	public static function check() {

		$issues = array();

		// Check 1: Verify SSL for consent data
		if ( ! is_ssl() ) {
			$issues[] = __( 'SSL not enabled for consent database', 'wpshadow' );
		}

		// Check 2: Check consent database logging
		$consent_logging = get_option( 'cookiebot_consent_logging', false );
		if ( ! $consent_logging ) {
			$issues[] = __( 'Consent logging not enabled', 'wpshadow' );
		}

		// Check 3: Verify GDPR compliance
		$gdpr_compliance = get_option( 'cookiebot_gdpr_compliance', false );
		if ( ! $gdpr_compliance ) {
			$issues[] = __( 'GDPR compliance mode not enabled', 'wpshadow' );
		}

		// Check 4: Check data retention policy
		$retention_days = get_option( 'cookiebot_retention_days', 0 );
		if ( $retention_days === 0 || $retention_days > 365 ) {
			$issues[] = __( 'Consent data retention period not properly configured', 'wpshadow' );
		}

		// Check 5: Verify consent revocation
		$revocation_enabled = get_option( 'cookiebot_revocation_enabled', false );
		if ( ! $revocation_enabled ) {
			$issues[] = __( 'Consent revocation not enabled', 'wpshadow' );
		}

		// Check 6: Check consent proof audit trail
		$audit_trail = get_option( 'cookiebot_audit_trail', false );
		if ( ! $audit_trail ) {
			$issues[] = __( 'Audit trail for consent proof not enabled', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			$threat_level = min( 100, 70 + ( count( $issues ) * 5 ) );
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %s: Comma-separated list of issues */
					__( 'Cookiebot consent database issues detected: %s', 'wpshadow' ),
					implode( ', ', $issues )
				),
				'severity'     => 'high',
				'threat_level' => $threat_level,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/cookiebot-consent-database',
			);
		}

		return null;
	}
}
