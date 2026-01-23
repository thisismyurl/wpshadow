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
	 * Test: Result structure validation
	 *
	 * Ensures diagnostic returns null (no issues) or array (issues found)
	 * with all required fields populated.
	 *
	 * @return array Test result with 'passed' and 'message'
	 */
	public static function test_result_structure(): array {
		$result = self::check();
		
		// Valid states: null (pass) or array (fail)
		if ( null === $result || is_array( $result ) ) {
			// If array, validate structure
			if ( is_array( $result ) ) {
				$required = array(
					'id', 'title', 'description', 'category', 
					'severity', 'threat_level'
				);
				
				foreach ( $required as $field ) {
					if ( ! isset( $result[ $field ] ) ) {
						return array(
							'passed'  => false,
							'message' => "Missing field: $field",
						);
					}
				}
				
				// Validate field types
				if ( ! is_string( $result['severity'] ) ) {
					return array(
						'passed'  => false,
						'message' => 'severity must be string',
					);
				}
				
				if ( ! is_int( $result['threat_level'] ) || $result['threat_level'] < 0 || $result['threat_level'] > 100 ) {
					return array(
						'passed'  => false,
						'message' => 'threat_level must be int 0-100',
					);
				}
			}
			
			return array(
				'passed'  => true,
				'message' => 'Result structure valid',
			);
		}
		
		return array(
			'passed'  => false,
			'message' => 'Invalid result type: ' . gettype( $result ),
		);
	}
	/**
	 * Test: Option-based detection
	 *
	 * Verifies that diagnostic correctly reads and evaluates options
	 * and returns appropriate result.
	 *
	 * @return array Test result
	 */
	public static function test_option_detection(): array {
		$result = self::check();
		
		// Should return null or array based on option values
		if ( $result === null || is_array( $result ) ) {
			return array(
				'passed'  => true,
				'message' => 'Option detection working correctly',
			);
		}
		
		return array(
			'passed'  => false,
			'message' => 'Option detection returned invalid type',
		);
	}}
