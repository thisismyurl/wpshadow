<?php
/**
 * Wordfence Login Security Diagnostic
 *
 * Wordfence Login Security misconfiguration.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.841.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Wordfence Login Security Diagnostic Class
 *
 * @since 1.841.0000
 */
class Diagnostic_WordfenceLoginSecurity extends Diagnostic_Base {

	protected static $slug = 'wordfence-login-security';
	protected static $title = 'Wordfence Login Security';
	protected static $description = 'Wordfence Login Security misconfiguration';
	protected static $family = 'security';

	public static function check() {
		if ( ! defined( 'WORDFENCE_VERSION' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Two-factor authentication
		$twofa = get_option( 'wordfence_2fa_enabled', 'no' );
		if ( 'no' === $twofa ) {
			$issues[] = __( '2FA disabled (password-only authentication)', 'wpshadow' );
		}
		
		// Check 2: Login lockout
		$lockout_enabled = get_option( 'wordfence_loginSec_lockoutMins', 0 );
		if ( $lockout_enabled === 0 ) {
			$issues[] = __( 'No login lockout (unlimited brute force)', 'wpshadow' );
		}
		
		// Check 3: CAPTCHA on login
		$captcha = get_option( 'wordfence_loginSec_enableSeparateTwoFactor', 'no' );
		if ( 'no' === $captcha ) {
			$issues[] = __( 'No CAPTCHA (bot attacks)', 'wpshadow' );
		}
		
		// Check 4: Password strength enforcement
		$strong_passwords = get_option( 'wordfence_loginSec_strongPasswds', 'no' );
		if ( 'no' === $strong_passwords ) {
			$issues[] = __( 'Weak passwords allowed (easy cracking)', 'wpshadow' );
		}
		
		// Check 5: Session hijacking protection
		$session_security = get_option( 'wordfence_loginSec_disableAuthorizedSessionManagement', 'yes' );
		if ( 'yes' === $session_security ) {
			$issues[] = __( 'Session management disabled (hijacking risk)', 'wpshadow' );
		}
		
		// Check 6: XML-RPC protection
		$xmlrpc = get_option( 'wordfence_xmlrpc_enabled', 'yes' );
		if ( 'yes' === $xmlrpc ) {
			$issues[] = __( 'XML-RPC enabled (brute force vector)', 'wpshadow' );
		}
		
		// Check 7: Failed login alerts
		$alert_threshold = get_option( 'wordfence_alertOn_loginLockout', 0 );
		if ( $alert_threshold === 0 ) {
			$issues[] = __( 'No failed login alerts (silent attacks)', 'wpshadow' );
		}
		
		if ( empty( $issues ) ) {
			return null;
		}
		
		$threat_level = 70;
		if ( count( $issues ) >= 5 ) {
			$threat_level = 85;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 78;
		}
		
		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %s: list of Wordfence login security issues */
				__( 'Wordfence login security has %d issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => self::calculate_severity( $threat_level ),
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/wordfence-login-security',
		);
	}
}
