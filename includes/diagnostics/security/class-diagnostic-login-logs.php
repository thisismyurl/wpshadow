<?php
declare(strict_types=1);
/**
 * Login Attempt Logging Diagnostic
 *
 * Philosophy: Forensics and monitoring - track login attempts
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if failed login attempts are logged.
 */
class Diagnostic_Login_Logs extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		// Check for login logging plugins or features
		$logging_plugins = array(
			'wordfence/wordfence.php',
			'simple-login-log/simple-login-log.php',
			'wp-security-audit-log/wp-security-audit-log.php',
			'user-activity-log/user-activity-log.php',
		);
		
		$active = get_option( 'active_plugins', array() );
		foreach ( $logging_plugins as $plugin ) {
			if ( in_array( $plugin, $active, true ) ) {
				return null; // Login logging active
			}
		}
		
		return array(
			'id'          => 'login-logs',
			'title'       => 'Login Attempts Not Logged',
			'description' => 'Failed login attempts are not being logged, preventing forensic analysis of security incidents. Enable login logging via a security plugin.',
			'severity'    => 'medium',
			'category'    => 'security',
			'kb_link'     => 'https://wpshadow.com/kb/enable-login-logging/',
			'training_link' => 'https://wpshadow.com/training/login-monitoring/',
			'auto_fixable' => false,
			'threat_level' => 50,
		);
	}
}
