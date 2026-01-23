<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Performance Trend Analysis Over Time (HISTORICAL-001)
 * 
 * Tracks performance metrics over time to identify degradation trends.
 * Philosophy: Show value (#9) - Catch performance regressions early.
 * 
 * @package WPShadow
 * @subpackage Diagnostics
 * @since 1.2601.2200
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Performance_Trend_Analysis extends Diagnostic_Base {
    
    /**
     * Run the diagnostic check
     * 
     * @return array|null Array with finding details or null if no issue found
     */
    public static function check(): ?array {
		// Analyze performance trends
        $perf_history = get_transient('wpshadow_performance_trend');
        
        if ($perf_history && is_array($perf_history) && count($perf_history) > 3) {
            $latest = end($perf_history);
            $oldest = reset($perf_history);
            
            $degradation = $latest - $oldest;
            
            // If performance has degraded by more than 500ms over time
            if ($degradation > 500) {
                return array(
                    'id' => 'performance-trend-analysis',
                    'title' => sprintf(__('Performance Degradation (+%dms)', 'wpshadow'), $degradation),
                    'description' => __('Site performance is declining over time. Identify and remove recent plugins/updates causing slowdown.', 'wpshadow'),
                    'severity' => 'medium',
                    'category' => 'monitoring',
                    'kb_link' => 'https://wpshadow.com/kb/performance-trend-monitoring/',
                    'training_link' => 'https://wpshadow.com/training/degradation-analysis/',
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
