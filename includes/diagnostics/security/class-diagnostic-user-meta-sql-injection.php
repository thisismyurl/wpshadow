<?php
declare(strict_types=1);
/**
 * User Meta SQL Injection Diagnostic
 *
 * Philosophy: Code security - detect unsafe user meta queries
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check for SQL injection in user meta queries.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_User_Meta_SQL_Injection extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		// Scan active plugins for dangerous patterns (limited scope)
		$active_plugins = get_option( 'active_plugins', array() );
		$vulnerable_plugins = array();
		
		foreach ( array_slice( $active_plugins, 0, 5 ) as $plugin ) {
			$plugin_file = WP_PLUGIN_DIR . '/' . $plugin;
			if ( file_exists( $plugin_file ) ) {
				$content = file_get_contents( $plugin_file );
				
				// Look for get_user_meta with $_GET/$_POST as meta_key
				if ( preg_match( '/get_user_meta\s*\([^,]+,\s*\$_(GET|POST|REQUEST)\[/i', $content ) ||
				     preg_match( '/update_user_meta\s*\([^,]+,\s*\$_(GET|POST|REQUEST)\[/i', $content ) ) {
					$vulnerable_plugins[] = dirname( $plugin );
				}
			}
		}
		
		if ( ! empty( $vulnerable_plugins ) ) {
			return array(
				'id'          => 'user-meta-sql-injection',
				'title'       => 'User Meta SQL Injection Risk',
				'description' => sprintf(
					'Plugins with potential user meta SQL injection: %s. User-controlled meta_key in get_user_meta() allows SQL injection. Sanitize with sanitize_key() before meta queries.',
					implode( ', ', $vulnerable_plugins )
				),
				'severity'    => 'high',
				'category'    => 'security',
				'kb_link'     => 'https://wpshadow.com/kb/prevent-user-meta-sql-injection/',
				'training_link' => 'https://wpshadow.com/training/wordpress-sql-security/',
				'auto_fixable' => false,
				'threat_level' => 80,
			);
		}
		
		return null;
	}



	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: User Meta SQL Injection
	 * Slug: -user-meta-sql-injection
	 * File: class-diagnostic-user-meta-sql-injection.php
	 * 
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: User Meta SQL Injection
	 * Slug: -user-meta-sql-injection
	 * 
	 * TODO: Review the check() method to understand what constitutes a passing test.
	 * The test should verify that:
	 * - check() returns NULL when the diagnostic condition is NOT met (site is healthy)
	 * - check() returns an array when the diagnostic condition IS met (issue found)
	 *
	 * @return array {
	 *     @type bool   $passed  Whether the test passed
	 *     @type string $message Human-readable test result message
	 * }
	 */
	public static function test_live__user_meta_sql_injection(): array {
		/*
		 * IMPLEMENTATION NOTES:
		 * - This test validates the actual WordPress site state
		 * - Do not use mocks or stubs
		 * - Call self::check() to get the diagnostic result
		 * - Verify the result matches expected site state
		 * - Return [ 'passed' => bool, 'message' => string ]
		 */
		
		$result = self::check();
		
		// TODO: Implement actual test logic
		return array(
			'passed' => false,
			'message' => 'Test not yet implemented',
		);
	}

}
