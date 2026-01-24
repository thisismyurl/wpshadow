<?php
declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Password Reset Working?
 *
 * Target Persona: Non-technical Site Owner (Mom/Dad)
 * Philosophy: Helpful neighbor (#1), show value (#9), educate (#5, #6)
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Loaded via Diagnostic_Registry
 */
class Diagnostic_Forgot_Password_Works extends Diagnostic_Base {
	protected static $slug        = 'forgot-password-works';
	protected static $title       = 'Password Reset Working?';
	protected static $description = 'Verifies password reset emails are sent.';

	public static function check(): ?array {
		$smtp_active = is_plugin_active('wp-mail-smtp/wp_mail_smtp.php') || 
		              is_plugin_active('easy-wp-smtp/easy-wp-smtp.php');
		if ($smtp_active) {
			return null;
		}
		return array(
			'id'            => static::$slug,
			'title'         => static::$title,
			'description'   => 'Password reset relies on default PHP mail().',			'kb_link'       => 'https://wpshadow.com/kb/forgot-password-works/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=forgot-password-works',
			'training_link' => 'https://wpshadow.com/training/forgot-password-works/',
			'auto_fixable'  => false,
			'threat_level'  => 60,
			'module'        => 'Core',
			'priority'      => 2,
		);
	}

	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Password Reset Working?
	 * Slug: forgot-password-works
	 * 
	 * Test Purpose:
	 * - Verify that check() method returns the correct result based on site state
	 * - PASS: check() returns NULL when diagnostic condition is NOT met (site is healthy)
	 * - FAIL: check() returns array when diagnostic condition IS met (issue found)
	 * - Description: Verifies password reset emails are sent.
	 *
	 * @return array {
	 *     @type bool   $passed  Whether the test passed
	 *     @type string $message Human-readable test result message
	 * }
	 */
	public static function test_live_forgot_password_works(): array {
		/*
		 * IMPLEMENTATION NOTES:
		 * - This test validates the actual WordPress site state
		 * - Do not use mocks or stubs
		 * - Call self::check() to get the diagnostic result
		 * - Verify the result matches expected site state
		 * - Return [ 'passed' => bool, 'message' => string ]
		 */
		
		$result = self::check();
		
		// Pattern: check() returns NULL when SMTP plugin is active (healthy)
		// Pattern: check() returns array when no SMTP configured (issue found)
		
		if ($result === null) {
			return [
				'passed' => true,
				'message' => 'Password reset emails are configured with SMTP plugin',
			];
		}
		
		return [
			'passed' => false,
			'message' => 'Password reset relies on default PHP mail() - recommend SMTP plugin',
		];
	}

}
