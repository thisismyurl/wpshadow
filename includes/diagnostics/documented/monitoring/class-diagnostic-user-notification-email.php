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
	 * Live test for this diagnostic
	 *
	 * Diagnostic: User Notification Email Compliance
	 * Slug: user-notification-email
	 * 
	 * Test Purpose:
	 * - Verify that check() method returns the correct result based on site state
	 * - PASS: check() returns NULL when diagnostic condition is NOT met (site is healthy)
	 * - FAIL: check() returns array when diagnostic condition IS met (issue found)
	 * - Description: Checks if new user notification emails follow privacy law opt-in requirements.
	 *
	 * @return array {
	 *     @type bool   $passed  Whether the test passed
	 *     @type string $message Human-readable test result message
	 * }
	 */
	public static function test_live_user_notification_email(): array {
		/*
		 * IMPLEMENTATION NOTES:
		 * - This test validates the actual WordPress site state
		 * - Do not use mocks or stubs
		 * - Call self::check() to get the diagnostic result
		 * - Verify the result matches expected site state
		 * - Return [ 'passed' => bool, 'message' => string ]
		 */
		
		$result = self::check();
		
		// TODO: Implement actual test logic
		return array(
			'passed' => false,
			'message' => 'Test not yet implemented for ' . self::$slug,
		);
	}

}
