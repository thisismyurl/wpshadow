<?php
declare(strict_types=1);
/**
 * Email Account Security Diagnostic
 *
 * Philosophy: Account recovery - prevent email account takeover
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check email account recovery security.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Email_Account_Security extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		$admin_users = get_users( array( 'role' => 'administrator' ) );
		
		$at_risk = 0;
		foreach ( $admin_users as $user ) {
			// Check for weak email domains or free email
			if ( preg_match( '/@(gmail|hotmail|yahoo|aol)\.com$/', $user->user_email ) ) {
				$at_risk ++;
			}
		}
		
		if ( $at_risk > 0 ) {
			return array(
				'id'          => 'email-account-security',
				'title'       => 'Admin Email Using Consumer Email Service',
				'description' => sprintf(
					'%d admin accounts use consumer email (Gmail, Yahoo, Hotmail). If email account is compromised, attackers can reset WordPress password. Use company email with 2FA.',
					$at_risk
				),
				'severity'    => 'high',
				'category'    => 'security',
				'kb_link'     => 'https://wpshadow.com/kb/secure-admin-email/',
				'training_link' => 'https://wpshadow.com/training/email-security/',
				'auto_fixable' => false,
				'threat_level' => 70,
			);
		}
		
		return null;
	}

	/**
	 * Test: Result structure validation
	 *
	 * Ensures diagnostic returns null (no issues) or array (issues found)
	 * with all required fields populated.
	 *
	 * @return array Test result with 'passed' and 'message'
	 */
	public static function test_result_structure(): array {
		$result = self::check();
		
		// Valid states: null (pass) or array (fail)
		if ( null === $result || is_array( $result ) ) {
			// If array, validate structure
			if ( is_array( $result ) ) {
				$required = array(
					'id', 'title', 'description', 'category', 
					'severity', 'threat_level'
				);
				
				foreach ( $required as $field ) {
					if ( ! isset( $result[ $field ] ) ) {
						return array(
							'passed'  => false,
							'message' => "Missing field: $field",
						);
					}
				}
				
				// Validate field types
				if ( ! is_string( $result['severity'] ) ) {
					return array(
						'passed'  => false,
						'message' => 'severity must be string',
					);
				}
				
				if ( ! is_int( $result['threat_level'] ) || $result['threat_level'] < 0 || $result['threat_level'] > 100 ) {
					return array(
						'passed'  => false,
						'message' => 'threat_level must be int 0-100',
					);
				}
			}
			
			return array(
				'passed'  => true,
				'message' => 'Result structure valid',
			);
		}
		
		return array(
			'passed'  => false,
			'message' => 'Invalid result type: ' . gettype( $result ),
		);
	}}
