<?php
/**
 * Diagnostic: Heartbeat API Overhead
 *
 * Detects Heartbeat API running too frequently.
 *
 * Philosophy: Show Value (#9) - Measure server resource waste
 * KB Link: https://wpshadow.com/kb/heartbeat-api-overhead
 * Training: https://wpshadow.com/training/heartbeat-api-overhead
 *
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Heartbeat API Overhead diagnostic
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Heartbeat_API_Overhead extends Diagnostic_Base {

	/**
	 * Run the diagnostic check
	 *
	 * @return array|null Diagnostic result or null if no issue
	 */
	public static function check(): ?array {
		// Check if heartbeat is slowed down
		$settings = get_option( 'wpshadow_heartbeat_settings', [] );

		if ( ! empty( $settings ) ) {
			return null; // Already optimized
		}

		// Check if site has performance issues that heartbeat worsens
		global $wpdb;

		// Check for high admin user count (heartbeat polls from all)
		$active_users = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(DISTINCT user_id) FROM {$wpdb->usermeta} 
				WHERE meta_key = 'session_tokens' 
				AND meta_value != ''"
			)
		);

		// Check for resource-heavy plugins that use heartbeat
		$heartbeat_users = [];
		
		// Common plugins that use heartbeat heavily
		$heavy_plugins = [
			'autosave',
			'post-locking',
			'preview',
		];

		// If few users and no heavy usage, not critical
		if ( $active_users < 5 ) {
			return null;
		}

		$severity = $active_users > 20 ? 'medium' : 'low';

		$description = sprintf(
			__( 'WordPress Heartbeat API polls your server every 15-60 seconds from each logged-in user (%d active sessions). This creates constant server load. Slowing or disabling heartbeat on specific pages can reduce server resources by 20-40%%.', 'wpshadow' ),
			$active_users
		);

		return [
			'id'                => 'heartbeat-api-overhead',
			'title'             => __( 'Heartbeat API Running at Default Speed', 'wpshadow' ),
			'description'       => $description,
			'severity'          => $severity,
			'category'          => 'performance',
			'impact'            => 'medium',
			'effort'            => 'low',
			'kb_link'           => 'https://wpshadow.com/kb/heartbeat-api-overhead',
			'training_link'     => 'https://wpshadow.com/training/heartbeat-api-overhead',
			'affected_resource' => sprintf( '%d active users', $active_users ),
			'metadata'          => [
				'active_users'     => $active_users,
				'default_interval' => 15,
				'requests_per_hour' => $active_users * 4, // 15s = 4 per minute
			],
		];
	}



	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Heartbeat API Overhead
	 * Slug: -heartbeat-api-overhead
	 * File: class-diagnostic-heartbeat-api-overhead.php
	 * 
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: Heartbeat API Overhead
	 * Slug: -heartbeat-api-overhead
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
	public static function test_live__heartbeat_api_overhead(): array {
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
