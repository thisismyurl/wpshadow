<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Long Task Clustering Analysis (FE-320)
 *
 * Groups TBT bursts to find root causes and stacks.
 * Philosophy: Show value (#9) and educate (#5) with clear, actionable insights.
 *
 * @package WPShadow
 * @subpackage Diagnostics
 * @since 1.2601.2200
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_LongTaskClustering extends Diagnostic_Base {
    /**
     * Run the diagnostic check
     *
     * @return array|null Array with finding details or null if no issue found
     */
    public static function check(): ?array {
		$cluster_count = (int) get_transient('wpshadow_long_task_cluster_count');
		$worst_cluster_ms = (int) get_transient('wpshadow_long_task_worst_cluster_ms');

		if ($cluster_count > 3 || $worst_cluster_ms > 200) {
			return array(
				'id' => 'long-task-clustering',
				'title' => sprintf(__('Long task clusters detected (%d)', 'wpshadow'), max($cluster_count, 1)),
				'description' => __('Multiple long tasks are grouped together, increasing Total Blocking Time. Break up heavy JS work, defer non-critical scripts, and schedule work in rAF.', 'wpshadow'),
				'severity' => 'medium',
				'category' => 'system',
				'kb_link' => 'https://wpshadow.com/kb/long-task-clusters/',
				'training_link' => 'https://wpshadow.com/training/javascript-performance/',
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
