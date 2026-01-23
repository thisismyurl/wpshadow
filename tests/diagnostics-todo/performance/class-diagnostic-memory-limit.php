<?php
declare(strict_types=1);
/**
 * Memory Limit Diagnostic
 *
 * @package WPShadow
 */


namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check PHP memory limit configuration.
 * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Registered in Diagnostic_Registry quick_diagnostics
 */
class Diagnostic_Memory_Limit extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		$memory_limit = self::get_memory_limit_mb();
		
		if ( $memory_limit < 64 ) {
			return array(
				'id'           => 'memory-limit-low',
				'title'        => 'PHP Memory Limit Too Low',
				'description'  => "Your PHP memory limit is {$memory_limit}MB. Recommended: 64MB+ (256MB ideal). This can cause plugin conflicts and timeouts.",
				'color'        => '#ff9800',
				'bg_color'     => '#fff3e0',
				'kb_link'      => 'https://wpshadow.com/kb/increase-php-memory-limit/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=memory-limit',
				'auto_fixable' => self::can_modify_wp_config(),
				'threat_level' => 60,
			);
		}
		
		return null;
	}
	
	/**
	 * Get memory limit in MB.
	 *
	 * @return int Memory limit in megabytes.
	 */
	private static function get_memory_limit_mb() {
		$limit = ini_get( 'memory_limit' );
		if ( '-1' === $limit ) {
			return 999999;
		}
		return intval( $limit );
	}
	
	/**
	 * Check if we can modify wp-config.php.
	 *
	 * @return bool True if wp-config.php is writable.
	 */
	private static function can_modify_wp_config() {
		$config_file = ABSPATH . 'wp-config.php';
		if ( ! file_exists( $config_file ) ) {
			$config_file = dirname( ABSPATH ) . '/wp-config.php';
		}
		return file_exists( $config_file ) && is_writable( $config_file );
	}



	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Memory Limit
	 * Slug: -memory-limit
	 * File: class-diagnostic-memory-limit.php
	 * 
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: Memory Limit
	 * Slug: -memory-limit
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
	public static function test_live__memory_limit(): array {
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
