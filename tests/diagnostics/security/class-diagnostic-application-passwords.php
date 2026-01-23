<?php

declare(strict_types=1);
/**
 * Application Passwords Security Diagnostic
 *
 * Philosophy: Security awareness - manage WP 5.6+ app passwords
 * @package WPShadow
 *
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check for application passwords usage.
 *
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Application_Passwords extends Diagnostic_Base
{
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array
	{
		// Application passwords introduced in WP 5.6
		if (! class_exists('WP_Application_Passwords')) {
			return null;
		}

		// Check if any users have application passwords
		global $wpdb;
		$count = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->usermeta} WHERE meta_key = '_application_passwords'"
		);

		if ($count > 0) {
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



	/**
	 * Live test for this diagnostic
	 *
	 * @return array {
	 *     @type bool   $passed  Whether the test passed
	 *     @type string $message Human-readable test result message
	 * }
	 */
	public static function test_live__application_passwords(): array
	{
		// Check if any users have application passwords
		global $wpdb;
		$count = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->usermeta} WHERE meta_key = '_application_passwords'"
		);

		$has_app_passwords = $count > 0;

		$result = self::check();
		$diagnostic_found_issue = !is_null($result);

		$test_passes = ($has_app_passwords === $diagnostic_found_issue);

		return array(
			'passed' => $test_passes,
			'message' => $test_passes ? 'Application passwords check matches site state' :
				"Mismatch: expected " . ($has_app_passwords ? 'issue' : 'no issue') . " but got " .
				($diagnostic_found_issue ? 'issue' : 'pass'),
		);
	}
}
