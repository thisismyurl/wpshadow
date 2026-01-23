<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Inventory/Availability API Latency (COMMERCE-348)
 *
 * Tracks stock/availability API impact on pages.
 * Philosophy: Show value (#9) and educate (#5) with clear, actionable insights.
 *
 * @package WPShadow
 * @subpackage Diagnostics
 * @since 1.2601.2200
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_InventoryApiLatency extends Diagnostic_Base {
    /**
     * Run the diagnostic check
     *
     * @return array|null Array with finding details or null if no issue found
     */
    public static function check(): ?array {
		// Monitor third-party inventory API latency
        $api_latency = get_transient('wpshadow_inventory_api_latency_ms');
        
        if ($api_latency && $api_latency > 1000) { // 1 second
            return array(
                'id' => 'inventory-api-latency',
                'title' => sprintf(__('Slow Inventory API (%dms)', 'wpshadow'), $api_latency),
                'description' => __('Your inventory/stock API is responding slowly. Consider caching results or using a faster API endpoint.', 'wpshadow'),
                'severity' => 'medium',
                'category' => 'monitoring',
                'kb_link' => 'https://wpshadow.com/kb/api-performance-monitoring/',
                'training_link' => 'https://wpshadow.com/training/api-optimization/',
                'auto_fixable' => false,
                'threat_level' => 50,
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
