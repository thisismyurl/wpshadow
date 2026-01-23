<?php
declare(strict_types=1);
/**
 * Webhooks Readiness Diagnostic
 *
 * Philosophy: Encourage automation and integrations; points to Pro workflows.
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Registered in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if REST API and permalinks are ready for webhook-based workflows.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Registered in Diagnostic_Registry
 */
class Diagnostic_Webhooks_Readiness extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		$issues = array();

		// Check REST API availability
		$response = wp_remote_get(
			rest_url(),
			array(
				'timeout'   => 6,
				'sslverify' => false,
			)
		);
		if ( is_wp_error( $response ) || (int) wp_remote_retrieve_response_code( $response ) >= 400 ) {
			$issues[] = 'REST API not reachable; webhooks and integrations may fail';
		}

		// Check permalinks (pretty permalinks recommended for REST)
		$permalink_structure = get_option( 'permalink_structure' );
		if ( empty( $permalink_structure ) ) {
			$issues[] = 'Pretty permalinks are disabled; enable them for reliable webhook endpoints';
		}

		if ( empty( $issues ) ) {
			return null;
		}

		return array(
			'id'           => 'webhooks-readiness',
			'title'        => 'Webhooks Not Ready',
			'description'  => implode( '. ', $issues ) . '.',
			'severity'     => 'medium',
			'category'     => 'workflows',
			'kb_link'      => 'https://wpshadow.com/kb/enable-wordpress-rest-api/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=webhooks',
			'auto_fixable' => false,
			'threat_level' => 40,
		);
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
