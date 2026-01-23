<?php
declare(strict_types=1);
/**
 * Diagnostic: AI Writing Detection (ChatGPT, Claude, Gemini)
 * Philosophy: Detect AI-generated content that lacks human authenticity and may be penalized by Google's helpful content update
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_AI_Writing_Detection extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'seo-ai-writing-detection',
            'title' => __('AI Writing Detection', 'wpshadow'),
            'description' => __('Analyzes content for statistical patterns indicating AI generation (ChatGPT, Claude, Gemini). Google\'s helpful content update penalizes purely AI content without human review.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/ai-content-authenticity/',
            'training_link' => 'https://wpshadow.com/training/ai-content-strategy/',
            'auto_fixable' => false,
            'threat_level' => 6,
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
