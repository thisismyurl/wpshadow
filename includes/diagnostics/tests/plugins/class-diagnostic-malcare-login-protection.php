<?php
/**
 * Malcare Login Protection Diagnostic
 *
 * Malcare Login Protection misconfiguration.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.890.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Malcare Login Protection Diagnostic Class
 *
 * @since 1.890.0000
 */
class Diagnostic_MalcareLoginProtection extends Diagnostic_Base {

	protected static $slug = 'malcare-login-protection';
	protected static $title = 'Malcare Login Protection';
	protected static $description = 'Malcare Login Protection misconfiguration';
	protected static $family = 'security';

	public static function check() {
		if ( ! defined( 'MALCARE_VERSION' ) && ! class_exists( 'MalCare' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Verify login protection enabled
		$login_protection = get_option( 'malcare_login_protection', 0 );
		if ( ! $login_protection ) {
			$issues[] = 'Login protection not enabled';
		}

		// Check 2: Check for CAPTCHA on login
		$captcha = get_option( 'malcare_login_captcha', 0 );
		if ( ! $captcha ) {
			$issues[] = 'Login CAPTCHA not enabled';
		}

		// Check 3: Verify brute force protection
		$brute_force = get_option( 'malcare_bruteforce_protection', 0 );
		if ( ! $brute_force ) {
			$issues[] = 'Brute force protection not enabled';
		}

		// Check 4: Check for IP lockouts
		$ip_lockout = get_option( 'malcare_ip_lockout', 0 );
		if ( ! $ip_lockout ) {
			$issues[] = 'IP lockout not enabled';
		}

		// Check 5: Verify login alert emails
		$alert_email = get_option( 'malcare_login_alerts', 0 );
		if ( ! $alert_email ) {
			$issues[] = 'Login alert emails not enabled';
		}

		// Check 6: Check for 2FA support
		$two_factor = get_option( 'malcare_2fa', 0 );
		if ( ! $two_factor ) {
			$issues[] = 'Two-factor authentication not enabled';
		}

		$issue_count = count( $issues );
		if ( $issue_count > 0 ) {
			$base_threat = 70;
			$threat_multiplier = 5;
			$max_threat = 95;
			$threat_level = min( $max_threat, $base_threat + ( $issue_count * $threat_multiplier ) );

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					'Found %d MalCare login protection issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/malcare-login-protection',
			);
		}

		return null;
	}
}
