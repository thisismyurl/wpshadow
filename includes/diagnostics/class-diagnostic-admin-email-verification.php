<?php declare(strict_types=1);
/**
 * Admin Email Verification Diagnostic
 *
 * Philosophy: Account security - verify admin email changes
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

/**
 * Check if admin email changes require verification.
 */
class Diagnostic_Admin_Email_Verification {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check() {
		$has_verification = has_action( 'new_admin_email_approve' );
		
		if ( ! $has_verification ) {
			return array(
				'id'          => 'admin-email-verification',
				'title'       => 'No Admin Email Change Verification',
				'description' => 'Admin email can be changed immediately without verification. Attackers can change the admin email to lock out legitimate admins. Require email verification for admin email changes.',
				'severity'    => 'high',
				'category'    => 'security',
				'kb_link'     => 'https://wpshadow.com/kb/verify-admin-email-changes/',
				'training_link' => 'https://wpshadow.com/training/account-security/',
				'auto_fixable' => false,
				'threat_level' => 70,
			);
		}
		
		return null;
	}
}
