<?php
declare(strict_types=1);
/**
 * HTML lang Consistency Diagnostic
 *
 * Philosophy: Align site locale with declared HTML language
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_HTML_Lang_Consistency extends Diagnostic_Base {
    /**
     * Advisory: ensure <html lang> matches site locale.
     *
     * @return array|null
     */
    public static function check(): ?array {
        $locale = get_locale();
        return [
            'id' => 'seo-html-lang-consistency',
            'title' => 'HTML lang Consistency',
            'description' => sprintf('Ensure the <html lang> attribute matches the site locale (%s) across all templates.', esc_html($locale)),
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/html-lang-attribute/',
            'training_link' => 'https://wpshadow.com/training/international-seo/',
            'auto_fixable' => false,
            'threat_level' => 10,
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
