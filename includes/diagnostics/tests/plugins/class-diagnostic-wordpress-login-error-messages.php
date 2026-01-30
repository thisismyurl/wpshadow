<?php
/**
 * Wordpress Login Error Messages Diagnostic
 *
 * Wordpress Login Error Messages issue detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1270.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Wordpress Login Error Messages Diagnostic Class
 *
 * @since 1.1270.0000
 */
class Diagnostic_WordpressLoginErrorMessages extends Diagnostic_Base {

	protected static $slug = 'wordpress-login-error-messages';
	protected static $title = 'Wordpress Login Error Messages';
	protected static $description = 'Wordpress Login Error Messages issue detected';
	protected static $family = 'functionality';

	public static function check() {
		$issues = array();
		
		// Check 1: Verify login error messages are filtered
		if ( ! has_filter( 'login_errors' ) ) {
			$issues[] = 'Login error messages not filtered (username exposure risk)';
		}
		
		// Check 2: Check for use of generic errors
		$generic_errors = get_option( 'login_generic_errors', 0 );
		if ( ! $generic_errors ) {
			$issues[] = 'Generic login errors not enabled';
		}
		
		// Check 3: Verify login throttling
		$throttling = get_option( 'login_throttle_enabled', 0 );
		if ( ! $throttling ) {
			$issues[] = 'Login throttling not enabled';
		}
		
		// Check 4: Check for account lockout
		$lockout = get_option( 'login_lockout_enabled', 0 );
		if ( ! $lockout ) {
			$issues[] = 'Account lockout not enabled for repeated failures';
		}
		
		// Check 5: Verify login URL obfuscation
		$login_url = get_option( 'login_url_custom', '' );
		if ( empty( $login_url ) ) {
			$issues[] = 'Custom login URL not configured';
		}
		
		// Check 6: Check for failed login alerts
		$failed_alerts = get_option( 'login_failed_alerts', 0 );
		if ( ! $failed_alerts ) {
			$issues[] = 'Failed login alerts not configured';
		}
		
		$issue_count = count( $issues );
		if ( $issue_count > 0 ) {
			$base_threat = 40;
			$threat_multiplier = 6;
			$max_threat = 70;
			$threat_level = min( $max_threat, $base_threat + ( $issue_count * $threat_multiplier ) );
			
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					'Found %d login error message issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/wordpress-login-error-messages',
			);
		}
		
		return null;
	}
}
