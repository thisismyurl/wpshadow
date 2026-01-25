<?php

declare(strict_types=1);
/**
 * Admin Email Verification Diagnostic
 *
 * Philosophy: Account security - verify admin email changes
 * @package WPShadow
 *
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Loaded via Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if admin email changes require verification.
 *
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Loaded via Diagnostic_Registry
 */
class Diagnostic_Admin_Email_Verification extends Diagnostic_Base {

	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		$has_verification = has_action( 'new_admin_email_approve' );

		if ( ! $has_verification ) {
			return array(
				'id'            => 'admin-email-verification',
				'title'         => 'No Admin Email Change Verification',
				'description'   => 'Admin email can be changed immediately without verification. Attackers can change the admin email to lock out legitimate admins. Require email verification for admin email changes.',
				'severity'      => 'high',
				'category'      => 'security',
				'kb_link'       => 'https://wpshadow.com/kb/verify-admin-email-changes/',
				'training_link' => 'https://wpshadow.com/training/account-security/',
				'auto_fixable'  => false,
				'threat_level'  => 70,
			);
		}

		return null;
	}

	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Admin Email Verification
	 * Slug: admin-email-verification
	 * File: class-diagnostic-admin-email-verification.php
	 *
	 * Test Purpose:
	 * Verify that admin email changes require verification hook
	 * - PASS: check() returns NULL when new_admin_email_approve hook is active
	 * - FAIL: check() returns array when hook is not registered
	 *
	 * @return array {
	 *     @type bool   $passed  Whether the test passed
	 *     @type string $message Human-readable test result message
	 * }
	 */
	public static function test_live__admin_email_verification(): array {
		$result           = self::check();
		$has_verification = has_action( 'new_admin_email_approve' );

		if ( $has_verification ) {
			// Verification hook registered = diagnostic should pass (return null)
			return array(
				'passed'  => is_null( $result ),
				'message' => 'Admin email verification hook properly registered',
			);
		} else {
			// No verification hook = issue should be detected (return array)
			return array(
				'passed'  => ! is_null( $result ) && isset( $result['id'] ) && $result['id'] === 'admin-email-verification',
				'message' => 'Missing admin email verification, issue correctly identified',
			);
		}
	}
}
