<?php
declare(strict_types=1);
/**
 * Session Timeout Diagnostic
 *
 * Philosophy: Session security - expire inactive sessions
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check admin session timeout configuration.
 */
class Diagnostic_Session_Timeout extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		// Check default session timeout (2 days is WordPress default)
		$timeout = apply_filters( 'auth_cookie_expiration', 2 * DAY_IN_SECONDS );
		
		// If timeout is longer than 1 day
		if ( $timeout > DAY_IN_SECONDS ) {
			return array(
				'id'          => 'session-timeout',
				'title'       => 'Long Admin Session Timeout',
				'description' => sprintf(
					'Admin sessions remain active for %s. Shorter timeouts reduce risk of session hijacking from abandoned computers. Consider setting timeout to 8-12 hours.',
					human_time_diff( 0, $timeout )
				),
				'severity'    => 'medium',
				'category'    => 'security',
				'kb_link'     => 'https://wpshadow.com/kb/configure-session-timeout/',
				'training_link' => 'https://wpshadow.com/training/session-security/',
				'auto_fixable' => false,
				'threat_level' => 55,
			);
		}
		
		return null;
	}
}
