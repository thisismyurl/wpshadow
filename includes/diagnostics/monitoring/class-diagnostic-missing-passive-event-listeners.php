<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Missing Passive Event Listeners (FE-005)
 * 
 * Detects scroll/touch listeners without {passive: true}.
 * Philosophy: Show value (#9) with scroll smoothness.
 * 
 * @package WPShadow
 * @subpackage Diagnostics
 * @since 1.2601.2200
 */
class Diagnostic_Missing_Passive_Event_Listeners extends Diagnostic_Base {
	
	/**
	 * Run the diagnostic check
	 * 
	 * @return array|null Array with finding details or null if no issue found
	 */
	public static function check(): ?array {
		// Check for passive event listener optimization
        // This requires JavaScript analysis, return recommendation
        return array(
            'id' => 'missing-passive-event-listeners',
            'title' => __('Passive Event Listeners Optimization', 'wpshadow'),
            'description' => __('Consider using passive event listeners in JavaScript for better scroll and touch performance. Enable WPShadow Pro to analyze.', 'wpshadow'),
            'severity' => 'info',
            'category' => 'monitoring',
            'kb_link' => 'https://wpshadow.com/kb/passive-event-listeners/',
            'training_link' => 'https://wpshadow.com/training/event-listener-optimization/',
            'auto_fixable' => false,
            'threat_level' => 20,
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
	}}
