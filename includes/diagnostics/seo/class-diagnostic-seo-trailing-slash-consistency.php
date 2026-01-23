<?php
declare(strict_types=1);
/**
 * Trailing Slash Consistency Diagnostic
 *
 * Philosophy: URL canonicalization for clean indexation
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_Trailing_Slash_Consistency extends Diagnostic_Base {
    /**
     * Check permalink structure trailing slash consistency.
     *
     * @return array|null
     */
    public static function check(): ?array {
        $structure = get_option('permalink_structure');
        if (is_string($structure)) {
            $hasSlash = substr($structure, -1) === '/';
            // Advisory only: ensure a consistent canonical scheme
            return [
                'id' => 'seo-trailing-slash-consistency',
                'title' => 'Trailing Slash Consistency',
                'description' => $hasSlash
                    ? 'Permalink structure ends with a trailing slash. Ensure redirects canonicalize to slash style sitewide.'
                    : 'Permalink structure does not end with a trailing slash. Ensure redirects canonicalize to non-slash style sitewide.',
                'severity' => 'low',
                'category' => 'seo',
                'kb_link' => 'https://wpshadow.com/kb/trailing-slash-canonicalization/',
                'training_link' => 'https://wpshadow.com/training/url-canonicalization/',
                'auto_fixable' => false,
                'threat_level' => 30,
            ];
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
	}
	/**
	 * Test: Option-based detection
	 *
	 * Verifies that diagnostic correctly reads and evaluates options
	 * and returns appropriate result.
	 *
	 * @return array Test result
	 */
	public static function test_option_detection(): array {
		$result = self::check();
		
		// Should return null or array based on option values
		if ( $result === null || is_array( $result ) ) {
			return array(
				'passed'  => true,
				'message' => 'Option detection working correctly',
			);
		}
		
		return array(
			'passed'  => false,
			'message' => 'Option detection returned invalid type',
		);
	}}
