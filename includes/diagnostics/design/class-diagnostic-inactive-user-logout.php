<?php
declare(strict_types=1);
/**
 * Inactive User Auto-Logout Diagnostic
 *
 * Philosophy: Session security - terminate idle sessions
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if inactive users are automatically logged out.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Inactive_User_Logout extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		$session_timeout = apply_filters( 'auth_cookie_expiration', DAY_IN_SECONDS );
		
		if ( $session_timeout > ( 12 * HOUR_IN_SECONDS ) ) {
			return array(
				'id'          => 'inactive-user-logout',
				'title'       => 'No Inactivity Auto-Logout',
				'description' => 'User sessions remain active for extended periods. Abandoned admin sessions at public computers can be hijacked. Enable auto-logout after 1-2 hours inactivity.',
				'severity'    => 'medium',
				'category'    => 'security',
				'kb_link'     => 'https://wpshadow.com/kb/enable-inactivity-logout/',
				'training_link' => 'https://wpshadow.com/training/session-timeout/',
				'auto_fixable' => false,
				'threat_level' => 55,
			);
		}
		
		return null;
	}
}
