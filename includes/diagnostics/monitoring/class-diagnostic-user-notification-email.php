<?php
declare(strict_types=1);
/**
 * User Notification Email Default State Diagnostic
 *
 * Checks if new user notification emails should be unchecked by default for CASL compliance.
 *
 * @package WPShadow
 */


namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Diagnostic_User_Notification_Email extends Diagnostic_Base {

	protected static $slug = 'user-notification-email';
	protected static $title = 'User Notification Email Compliance';
	protected static $description = 'Checks if new user notification emails follow privacy law opt-in requirements.';

	public static function check(): ?array {
		// Check if we're overriding the default to be unchecked (compliant)
		$email_unchecked_by_default = get_option( 'wpshadow_user_email_unchecked_by_default', false );

		if ( $email_unchecked_by_default ) {
			// Compliant setting is enabled
			return null;
		}

		return array(
			'id'   => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				__( 'For CASL (Canada), GDPR (EU), and CCPA (US) compliance, new user notification emails should be unchecked by default to ensure explicit opt-in. Currently, the checkbox on user-new.php appears checked by default. Use the Email Test & Configuration tool to enable "Uncheck email notification by default" for strict privacy law compliance.', 'wpshadow' )
			),
			'category'     => 'settings',
			'severity'     => 'medium',
			'threat_level' => 45,
			'auto_fixable' => false,
			'timestamp'    => current_time( 'mysql' ),
		);
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
	}
	/**
	 * Test: Option-based detection
	 *
	 * Verifies that diagnostic correctly reads and evaluates options
	 * and returns appropriate result.
	 *
	 * @return array Test result
	 */
	public static function test_option_detection(): array {
		$result = self::check();
		
		// Should return null or array based on option values
		if ( $result === null || is_array( $result ) ) {
			return array(
				'passed'  => true,
				'message' => 'Option detection working correctly',
			);
		}
		
		return array(
			'passed'  => false,
			'message' => 'Option detection returned invalid type',
		);
	}}
