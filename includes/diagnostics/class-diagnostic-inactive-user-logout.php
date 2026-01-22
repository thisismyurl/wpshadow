<?php declare(strict_types=1);
/**
 * Inactive User Auto-Logout Diagnostic
 *
 * Philosophy: Session security - terminate idle sessions
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

/**
 * Check if inactive users are automatically logged out.
 */
class Diagnostic_Inactive_User_Logout {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check() {
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
