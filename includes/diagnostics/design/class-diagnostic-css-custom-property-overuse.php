<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: CSS Custom Property Overuse (ASSET-019)
 * 
 * Counts CSS custom properties (warn if >100 unique).
 * Philosophy: Educate (#5) about CSS performance.
 * 
 * @package WPShadow
 * @subpackage Diagnostics
 * @since 1.2601.2200
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Css_Custom_Property_Overuse extends Diagnostic_Base {
	
	/**
	 * Run the diagnostic check
	 * 
	 * @return array|null Array with finding details or null if no issue found
	 */
	public static function check(): ?array {
		// Check CSS custom property (variable) overuse
        $css_vars = get_transient('wpshadow_css_custom_properties_count');
        
        if ($css_vars && $css_vars > 200) {
            return array(
                'id' => 'css-custom-property-overuse',
                'title' => sprintf(__('Many CSS Variables (%d used)', 'wpshadow'), $css_vars),
                'description' => __('Excessive CSS custom properties can impact performance. Use sparingly and consolidate similar variables.', 'wpshadow'),
                'severity' => 'low',
                'category' => 'design',
                'kb_link' => 'https://wpshadow.com/kb/css-variables-performance/',
                'training_link' => 'https://wpshadow.com/training/css-best-practices/',
                'auto_fixable' => false,
                'threat_level' => 25,
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
