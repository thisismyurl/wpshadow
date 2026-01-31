<?php
/**
 * Age Gate Verification Bypass Diagnostic
 *
 * Age Gate Verification Bypass not compliant.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1121.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Age Gate Verification Bypass Diagnostic Class
 *
 * @since 1.1121.0000
 */
class Diagnostic_AgeGateVerificationBypass extends Diagnostic_Base {

	protected static $slug = 'age-gate-verification-bypass';
	protected static $title = 'Age Gate Verification Bypass';
	protected static $description = 'Age Gate Verification Bypass not compliant';
	protected static $family = 'functionality';

	public static function check() {
		// Check for age gate plugins
		$has_age_gate = defined( 'AGE_GATE_VERSION' ) ||
		                class_exists( 'Age_Gate' ) ||
		                get_option( 'age_gate_enabled', '' ) !== '';
		
		if ( ! $has_age_gate ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Cookie-only verification
		$verification_method = get_option( 'age_gate_verification_method', 'cookie' );
		if ( 'cookie' === $verification_method ) {
			$issues[] = __( 'Cookie-only verification (easily bypassed)', 'wpshadow' );
		}
		
		// Check 2: JavaScript dependency
		$require_js = get_option( 'age_gate_require_js', 'yes' );
		if ( 'yes' === $require_js ) {
			$issues[] = __( 'JavaScript required (bypass if disabled)', 'wpshadow' );
		}
		
		// Check 3: Remember me duration
		$remember_duration = get_option( 'age_gate_remember_duration', 365 );
		if ( $remember_duration > 30 ) {
			$issues[] = sprintf( __( '%d day cookie (long bypass window)', 'wpshadow' ), $remember_duration );
		}
		
		// Check 4: Minimum age validation
		$min_age = get_option( 'age_gate_minimum_age', 18 );
		$validate_date = get_option( 'age_gate_validate_date', 'no' );
		
		if ( 'no' === $validate_date ) {
			$issues[] = __( 'No date validation (fake birthdays accepted)', 'wpshadow' );
		}
		
		// Check 5: Legal compliance
		$show_policy = get_option( 'age_gate_show_privacy_policy', 'no' );
		if ( 'no' === $show_policy ) {
			$issues[] = __( 'No privacy policy link (compliance issue)', 'wpshadow' );
		}
		
		// Check 6: Bot detection
		$bot_protection = get_option( 'age_gate_bot_protection', 'off' );
		if ( 'off' === $bot_protection ) {
			$issues[] = __( 'No bot detection (automated bypass)', 'wpshadow' );
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
				/* translators: %s: list of age gate bypass vulnerabilities */
				__( 'Age gate has %d verification bypass risks: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => $threat_level,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/age-gate-verification-bypass',
		);
	}
}
