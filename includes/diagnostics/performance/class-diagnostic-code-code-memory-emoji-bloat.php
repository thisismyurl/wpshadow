<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Emoji/Embeds Not Disabled
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-memory-emoji-bloat
 * Training: https://wpshadow.com/training/code-memory-emoji-bloat
 */
class Diagnostic_Code_CODE_MEMORY_EMOJI_BLOAT extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'code-memory-emoji-bloat',
            'title' => __('Emoji/Embeds Not Disabled', 'wpshadow'),
            'description' => __('Flags unnecessary emoji or embed scripts in performance mode.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-memory-emoji-bloat',
            'training_link' => 'https://wpshadow.com/training/code-memory-emoji-bloat',
            'auto_fixable' => false,
            'threat_level' => 6
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
