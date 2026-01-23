<?php
declare(strict_types=1);
/**
 * PHP Disable Functions Diagnostic
 *
 * Philosophy: Code execution security - disable dangerous functions
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if dangerous PHP functions are disabled.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_PHP_Disable_Functions extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		$disabled_functions = ini_get( 'disable_functions' );
		$disabled_array     = array_map( 'trim', explode( ',', $disabled_functions ) );

		// Dangerous functions that should be disabled
		$dangerous = array(
			'exec',
			'passthru',
			'shell_exec',
			'system',
			'proc_open',
			'popen',
			'curl_exec',
			'curl_multi_exec',
			'parse_ini_file',
			'show_source',
			'eval',
			'assert',
		);

		$enabled_dangerous = array();

		foreach ( $dangerous as $func ) {
			if ( ! in_array( $func, $disabled_array, true ) && function_exists( $func ) ) {
				$enabled_dangerous[] = $func;
			}
		}

		if ( count( $enabled_dangerous ) > 5 ) {
			return array(
				'id'            => 'php-disable-functions',
				'title'         => 'Dangerous PHP Functions Not Disabled',
				'description'   => sprintf(
					'Your PHP configuration allows dangerous functions: %s. These enable remote code execution if exploited. Disable via php.ini: disable_functions = "%s"',
					implode( ', ', array_slice( $enabled_dangerous, 0, 5 ) ),
					implode( ', ', $enabled_dangerous )
				),
				'severity'      => 'high',
				'category'      => 'security',
				'kb_link'       => 'https://wpshadow.com/kb/disable-dangerous-php-functions/',
				'training_link' => 'https://wpshadow.com/training/php-hardening/',
				'auto_fixable'  => false,
				'threat_level'  => 75,
			);
		}

		return null;
	}



	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: PHP Disable Functions
	 * Slug: -php-disable-functions
	 * File: class-diagnostic-php-disable-functions.php
	 * 
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: PHP Disable Functions
	 * Slug: -php-disable-functions
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
	public static function test_live__php_disable_functions(): array {
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
