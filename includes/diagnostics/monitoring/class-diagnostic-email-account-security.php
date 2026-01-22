<?php
declare(strict_types=1);
/**
 * Email Account Security Diagnostic
 *
 * Philosophy: Account recovery - prevent email account takeover
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check email account recovery security.
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
}
