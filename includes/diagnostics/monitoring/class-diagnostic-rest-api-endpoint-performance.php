<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: REST API Endpoint Performance Profiling (WORDPRESS-006)
 * 
 * Profiles WordPress REST API endpoints to identify slow responses.
 * Philosophy: Show value (#9) - Optimize API for headless/mobile apps.
 * 
 * @package WPShadow
 * @subpackage Diagnostics
 * @since 1.2601.2200
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_REST_API_Endpoint_Performance extends Diagnostic_Base {
    
    /**
     * Run the diagnostic check
     * 
     * @return array|null Array with finding details or null if no issue found
     */
    public static function check(): ?array {
		// Monitor REST API endpoint performance
        $rest_api_time = get_transient('wpshadow_rest_api_avg_time_ms');
        
        if ($rest_api_time && $rest_api_time > 500) { // 500ms
            return array(
                'id' => 'rest-api-endpoint-performance',
                'title' => sprintf(__('Slow REST API (%dms average)', 'wpshadow'), $rest_api_time),
                'description' => __('Your REST API endpoints are responding slowly. Profile and optimize the slowest endpoints.', 'wpshadow'),
                'severity' => 'medium',
                'category' => 'monitoring',
                'kb_link' => 'https://wpshadow.com/kb/rest-api-optimization/',
                'training_link' => 'https://wpshadow.com/training/api-performance-tuning/',
                'auto_fixable' => false,
                'threat_level' => 45,
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
