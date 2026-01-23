<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

class Diagnostic_Monitor_Crawl_Efficiency_Score extends Diagnostic_Base { public static function check(): ?array { return ['id' => 'monitor-crawl-score', 'title' => __('Overall Crawl Efficiency Score', 'wpshadow'), 'description' => __('Holistic score: indexation rate, crawl depth, response times, errors. Lower = wasted crawl budget.', 'wpshadow'), 'severity' => 'high', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/crawl-health/', 'training_link' => 'https://wpshadow.com/training/crawl-optimization/', 'auto_fixable' => false, 'threat_level' => 8]; } 
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
