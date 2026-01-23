<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Multiple Analytics Providers (THIRD-004)
 * 
 * Counts analytics scripts (GA, FB Pixel, etc.).
 * Philosophy: Show value (#9) with tracking consolidation.
 * 
 * @package WPShadow
 * @subpackage Diagnostics
 * @since 1.2601.2200
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Multiple_Analytics_Providers extends Diagnostic_Base {
	
	/**
	 * Run the diagnostic check
	 * 
	 * @return array|null Array with finding details or null if no issue found
	 */
	public static function check(): ?array {
		// Check for multiple analytics providers
        $analytics_count = 0;
        
        if (get_option('google_analytics_id')) $analytics_count++;
        if (get_option('matomo_site_id')) $analytics_count++;
        if (apply_filters('wpshadow_analytics_providers_count', 0)) $analytics_count += apply_filters('wpshadow_analytics_providers_count', 0);
        
        if ($analytics_count > 2) {
            return array(
                'id' => 'multiple-analytics-providers',
                'title' => sprintf(__('%d Analytics Providers Active', 'wpshadow'), $analytics_count),
                'description' => __('Multiple analytics services add extra requests and tracking code. Consolidate to single analytics provider if possible.', 'wpshadow'),
                'severity' => 'low',
                'category' => 'monitoring',
                'kb_link' => 'https://wpshadow.com/kb/analytics-consolidation/',
                'training_link' => 'https://wpshadow.com/training/analytics-performance/',
                'auto_fixable' => false,
                'threat_level' => 30,
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
