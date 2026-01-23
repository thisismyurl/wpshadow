<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: CSS Selector Complexity Scoring (FE-013)
 * 
 * Analyzes CSS selector efficiency.
 * Philosophy: Educate (#5) - Write performant CSS.
 * 
 * @package WPShadow
 * @subpackage Diagnostics
 * @since 1.2601.2200
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_CSS_Selector_Complexity extends Diagnostic_Base {
    public static function check(): ?array {
        // Check CSS selector complexity
        $complex_selectors = get_transient('wpshadow_complex_selector_count');
        
        if ($complex_selectors && $complex_selectors > 50) {
            return array(
                'id' => 'css-selector-complexity',
                'title' => sprintf(__('%d Complex Selectors Detected', 'wpshadow'), $complex_selectors),
                'description' => __('Complex CSS selectors (>4 descendant levels) slow down rendering. Simplify selectors and use BEM or similar naming conventions.', 'wpshadow'),
                'severity' => 'low',
                'category' => 'design',
                'kb_link' => 'https://wpshadow.com/kb/css-selector-optimization/',
                'training_link' => 'https://wpshadow.com/training/css-architecture/',
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
	}}
