<?php
/**
 * Wordfence Brute Force Protection Diagnostic
 *
 * Wordfence Brute Force Protection misconfiguration.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.848.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Wordfence Brute Force Protection Diagnostic Class
 *
 * @since 1.848.0000
 */
class Diagnostic_WordfenceBruteForceProtection extends Diagnostic_Base {

	protected static $slug = 'wordfence-brute-force-protection';
	protected static $title = 'Wordfence Brute Force Protection';
	protected static $description = 'Wordfence Brute Force Protection misconfiguration';
	protected static $family = 'security';

	public static function check() {
		if ( ! defined( 'WORDFENCE_VERSION' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Brute force protection enabled.
		$brute_force = get_option( 'wordfence_brute_force', '1' );
		if ( '0' === $brute_force ) {
			$issues[] = 'brute force protection disabled';
		}
		
		// Check 2: Login attempt limit.
		$max_attempts = get_option( 'wordfence_max_login_attempts', 5 );
		if ( $max_attempts > 10 ) {
			$issues[] = 'login attempt limit too high';
		}
		
		// Check 3: Lockout duration.
		$lockout = get_option( 'wordfence_lockout_duration', 3600 );
		if ( $lockout < 1800 ) {
			$issues[] = 'lockout duration too short';
		}
		
		// Check 4: Two-factor authentication.
		$two_factor = get_option( 'wordfence_2fa_enabled', '0' );
		if ( '0' === $two_factor ) {
			$issues[] = 'two-factor authentication disabled';
		}
		
		// Check 5: Login captcha.
		$captcha = get_option( 'wordfence_login_captcha', '0' );
		if ( '0' === $captcha ) {
			$issues[] = 'login captcha disabled';
		}
		
		// Check 6: Human detection.
		$human_detect = get_option( 'wordfence_human_detection', '1' );
		if ( '0' === $human_detect ) {
			$issues[] = 'human detection disabled';
		}
		
		if ( ! empty( $issues ) ) {
			$threat_level = min( 90, 75 + ( count( $issues ) * 3 ) );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'Wordfence security issues: ' . implode( ', ', $issues ),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/wordfence-brute-force-protection',
			);
		}
		
		return null;
	}
}
