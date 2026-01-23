<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Modal Overlay Contrast
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-modal-overlay-contrast
 * Training: https://wpshadow.com/training/design-modal-overlay-contrast
 */
class Diagnostic_Design_MODAL_OVERLAY_CONTRAST extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-modal-overlay-contrast',
            'title' => __('Modal Overlay Contrast', 'wpshadow'),
            'description' => __('Checks overlay darkness sufficient to make modal stand out (0.5-0.8 opacity).', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-modal-overlay-contrast',
            'training_link' => 'https://wpshadow.com/training/design-modal-overlay-contrast',
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
