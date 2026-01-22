<?php declare(strict_types=1);
/**
 * Application Passwords Security Diagnostic
 *
 * Philosophy: Security awareness - manage WP 5.6+ app passwords
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

/**
 * Check for application passwords usage.
 */
class Diagnostic_Application_Passwords {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check() {
		// Application passwords introduced in WP 5.6
		if ( ! class_exists( 'WP_Application_Passwords' ) ) {
			return null;
		}
		
		// Check if any users have application passwords
		global $wpdb;
		$count = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->usermeta} WHERE meta_key = '_application_passwords'"
		);
		
		if ( $count > 0 ) {
			return array(
				'id'          => 'application-passwords',
				'title'       => 'Application Passwords in Use',
				'description' => sprintf(
					'%d user(s) have application passwords enabled. Review and revoke unused app passwords to minimize attack vectors.',
					$count
				),
				'severity'    => 'medium',
				'category'    => 'security',
				'kb_link'     => 'https://wpshadow.com/kb/manage-application-passwords/',
				'training_link' => 'https://wpshadow.com/training/application-passwords/',
				'auto_fixable' => false,
				'threat_level' => 55,
			);
		}
		
		return null;
	}
}
