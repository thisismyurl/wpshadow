<?php
/**
 * Google Analytics Anonymize Ip Diagnostic
 *
 * Google Analytics Anonymize Ip misconfigured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1339.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Google Analytics Anonymize Ip Diagnostic Class
 *
 * @since 1.1339.0000
 */
class Diagnostic_GoogleAnalyticsAnonymizeIp extends Diagnostic_Base {

	protected static $slug = 'google-analytics-anonymize-ip';
	protected static $title = 'Google Analytics Anonymize Ip';
	protected static $description = 'Google Analytics Anonymize Ip misconfigured';
	protected static $family = 'functionality';

	public static function check() {
		// Check for Google Analytics plugins
		$has_ga = function_exists( 'ga_load_options' ) ||
		          defined( 'MONSTERINSIGHTS_VERSION' ) ||
		          defined( 'GADWP_CURRENT_VERSION' ) ||
		          get_option( 'ga_tracking_id', '' ) !== '';
		
		if ( ! $has_ga ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: IP anonymization enabled
		$anonymize_ip = get_option( 'ga_anonymize_ip', 'no' );
		if ( 'no' === $anonymize_ip || empty( $anonymize_ip ) ) {
			$issues[] = __( 'IP anonymization disabled (GDPR violation)', 'wpshadow' );
		}
		
		// Check 2: Data retention period
		$retention = get_option( 'ga_data_retention', 'indefinite' );
		if ( 'indefinite' === $retention ) {
			$issues[] = __( 'Indefinite data retention (privacy concern)', 'wpshadow' );
		}
		
		// Check 3: Consent mode
		$consent_mode = get_option( 'ga_consent_mode', 'no' );
		if ( 'no' === $consent_mode ) {
			$issues[] = __( 'Consent mode disabled (GDPR issue)', 'wpshadow' );
		}
		
		// Check 4: Cookie configuration
		$cookie_flags = get_option( 'ga_cookie_flags', '' );
		if ( strpos( $cookie_flags, 'secure' ) === false ) {
			$issues[] = __( 'Cookie not secure (HTTPS required)', 'wpshadow' );
		}
		
		// Check 5: User opt-out
		$opt_out_enabled = get_option( 'ga_opt_out', 'no' );
		if ( 'no' === $opt_out_enabled ) {
			$issues[] = __( 'No opt-out mechanism (user rights violation)', 'wpshadow' );
		}
		
		// Check 6: Demographics tracking
		$demographics = get_option( 'ga_enable_demographics', 'yes' );
		if ( 'yes' === $demographics ) {
			$issues[] = __( 'Demographics tracking enabled (PII collection)', 'wpshadow' );
		}
		
		if ( empty( $issues ) ) {
			return null;
		}
		
		$threat_level = 50;
		if ( count( $issues ) >= 4 ) {
			$threat_level = 62;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 56;
		}
		
		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %s: list of IP anonymization issues */
				__( 'Google Analytics has %d privacy issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => self::calculate_severity( $threat_level ),
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/google-analytics-anonymize-ip',
		);
	}
}
