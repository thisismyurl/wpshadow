<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Third-Party API Response Time Monitoring (THIRD-006)
 * 
 * Tracks response times for external API calls that block page loads.
 * Philosophy: Show value (#9) - Identify external bottlenecks.
 * 
 * @package WPShadow
 * @subpackage Diagnostics
 * @since 1.2601.2200
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Third_Party_API_Response_Time extends Diagnostic_Base {
    
    /**
     * Run the diagnostic check
     * 
     * @return array|null Array with finding details or null if no issue found
     */
    public static function check(): ?array {
		// Monitor third-party API response times
        $api_response_times = get_transient('wpshadow_third_party_api_times');
        
        if ($api_response_times && is_array($api_response_times)) {
            $slowest_time = max($api_response_times);
            
            if ($slowest_time > 2000) { // 2 seconds
                return array(
                    'id' => 'third-party-api-response-time',
                    'title' => sprintf(__('Slow Third-Party API (%dms)', 'wpshadow'), $slowest_time),
                    'description' => __('One of your third-party APIs is responding slowly. Consider adding retry logic, caching, or switching providers.', 'wpshadow'),
                    'severity' => 'medium',
                    'category' => 'monitoring',
                    'kb_link' => 'https://wpshadow.com/kb/external-api-optimization/',
                    'training_link' => 'https://wpshadow.com/training/api-reliability/',
                    'auto_fixable' => false,
                    'threat_level' => 50,
                );
            }
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
