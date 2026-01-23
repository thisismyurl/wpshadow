<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: A/B Test and Experiment Performance Impact (EXPERIMENT-341)
 *
 * Quantifies perf cost of A/B and recommendation scripts.
 * Philosophy: Show value (#9) and educate (#5) with clear, actionable insights.
 *
 * @package WPShadow
 * @subpackage Diagnostics
 * @since 1.2601.2200
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_AbTestPerformanceImpact extends Diagnostic_Base {
	/**
	 * Run the diagnostic check
	 *
	 * @return array|null Array with finding details or null if no issue found
	 */
	public static function check(): ?array {
		$ab_overhead_ms = (int) get_transient( 'wpshadow_ab_test_overhead_ms' );
		$ab_scripts     = (int) get_transient( 'wpshadow_ab_test_script_count' );

		if ( $ab_overhead_ms > 100 || $ab_scripts > 2 ) {
			return array(
				'id'            => 'ab-test-performance-impact',
				'title'         => sprintf( __( 'A/B testing adds %1$dms and %2$d scripts', 'wpshadow' ), $ab_overhead_ms, $ab_scripts ),
				'description'   => __( 'Experiment scripts are adding noticeable overhead. Consider server-side testing, async loading, or consolidating vendors.', 'wpshadow' ),
				'severity'      => 'medium',
				'category'      => 'other',
				'kb_link'       => 'https://wpshadow.com/kb/ab-test-performance/',
				'training_link' => 'https://wpshadow.com/training/experimentation-performance/',
				'auto_fixable'  => false,
				'threat_level'  => 55,
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
