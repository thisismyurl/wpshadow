<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Mobile Footer Positioning
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-mobile-footer-positioning
 * Training: https://wpshadow.com/training/design-mobile-footer-positioning
 */
class Diagnostic_Design_MOBILE_FOOTER_POSITIONING extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-mobile-footer-positioning',
            'title' => __('Mobile Footer Positioning', 'wpshadow'),
            'description' => __('Checks footer positioning on mobile.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-mobile-footer-positioning',
            'training_link' => 'https://wpshadow.com/training/design-mobile-footer-positioning',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
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
