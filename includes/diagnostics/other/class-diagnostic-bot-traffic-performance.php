<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Bot Traffic Detection and Impact (SECURITY-PERF-002)
 *
 * Identifies bot traffic consuming server resources unnecessarily.
 * Philosophy: Show value (#9) - Optimize server for real users, not bots.
 *
 * @package WPShadow
 * @subpackage Diagnostics
 * @since 1.2601.2200
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Bot_Traffic_Performance extends Diagnostic_Base {

	/**
	 * Run the diagnostic check
	 *
	 * @return array|null Array with finding details or null if no issue found
	 */
	public static function check(): ?array {
		$bot_ratio    = (float) get_transient( 'wpshadow_bot_traffic_ratio' ); // percent of requests
		$bot_requests = (int) get_transient( 'wpshadow_bot_request_count' );

		if ( $bot_ratio > 30 || $bot_requests > 1000 ) {
			return array(
				'id'            => 'bot-traffic-performance',
				'title'         => sprintf( __( 'High bot traffic detected (%.1f%%)', 'wpshadow' ), $bot_ratio ),
				'description'   => __( 'Bots are consuming server resources. Add bot rate limiting, robots.txt tuning, or CDN-level bot mitigation.', 'wpshadow' ),
				'severity'      => 'medium',
				'category'      => 'other',
				'kb_link'       => 'https://wpshadow.com/kb/bot-traffic-performance/',
				'training_link' => 'https://wpshadow.com/training/bot-mitigation/',
				'auto_fixable'  => false,
				'threat_level'  => 55,
				'bot_requests'  => $bot_requests,
			);
		}

		return null;
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
	}}
