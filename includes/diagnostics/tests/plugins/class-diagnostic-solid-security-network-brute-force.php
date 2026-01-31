<?php
/**
 * Solid Security Network Brute Force Diagnostic
 *
 * Solid Security Network Brute Force misconfiguration.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.881.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Solid Security Network Brute Force Diagnostic Class
 *
 * @since 1.881.0000
 */
class Diagnostic_SolidSecurityNetworkBruteForce extends Diagnostic_Base {

	protected static $slug = 'solid-security-network-brute-force';
	protected static $title = 'Solid Security Network Brute Force';
	protected static $description = 'Solid Security Network Brute Force misconfiguration';
	protected static $family = 'security';

	public static function check() {
		if ( ! function_exists( 'itsec_load_textdomain' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Network brute force protection enabled
		$network_protection = get_option( 'itsec_network_brute_force_enabled', false );
		if ( ! $network_protection ) {
			$issues[] = 'Network brute force protection disabled';
		}
		
		// Check 2: Shared IP blacklist enabled
		$ip_blacklist = get_option( 'itsec_network_ip_blacklist', false );
		if ( ! $ip_blacklist ) {
			$issues[] = 'Shared IP blacklist disabled';
		}
		
		// Check 3: Login attempt tracking
		$login_tracking = get_option( 'itsec_login_attempt_tracking', false );
		if ( ! $login_tracking ) {
			$issues[] = 'Login attempt tracking disabled';
		}
		
		// Check 4: CAPTCHA on login enabled
		$captcha_enabled = get_option( 'itsec_recaptcha_enabled', false );
		if ( ! $captcha_enabled ) {
			$issues[] = 'CAPTCHA not enabled on login';
		}
		
		// Check 5: Lockout duration configured
		$lockout_duration = get_option( 'itsec_lockout_period', 0 );
		if ( $lockout_duration <= 0 ) {
			$issues[] = 'Lockout duration not configured';
		}
		
		// Check 6: Brute force logging enabled
		$logging_enabled = get_option( 'itsec_brute_force_logging', false );
		if ( ! $logging_enabled ) {
			$issues[] = 'Brute force logging disabled';
		}
		
		if ( ! empty( $issues ) ) {
			$threat_level = min( 90, 60 + ( count( $issues ) * 5 ) );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'Solid Security network brute force issues: ' . implode( ', ', $issues ),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/solid-security-network-brute-force',
			);
		}
		// Additional checks
		if ( ! function_exists( 'wp_verify_nonce' ) ) {
			$issues[] = __( 'Nonce verification unavailable', 'wpshadow' );
		}
		return null;
	}
}
