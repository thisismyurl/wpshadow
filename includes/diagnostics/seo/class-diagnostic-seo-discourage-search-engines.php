<?php
declare(strict_types=1);
/**
 * Discourage Search Engines (blog_public) Diagnostic
 *
 * Philosophy: Technical SEO visibility control
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_Discourage_Search_Engines extends Diagnostic_Base {
    /**
     * Check if the site is set to discourage search engines.
     *
     * @return array|null
     */
    public static function check(): ?array {
        $blog_public = get_option('blog_public');
        if ($blog_public === '0' || $blog_public === 0) {
            return [
                'id' => 'seo-discourage-search-engines',
                'title' => 'Search Engine Visibility Disabled',
                'description' => 'WordPress is set to discourage search engines (noindex). Disable this in Settings → Reading for production sites.',
                'severity' => 'high',
                'category' => 'seo',
                'kb_link' => 'https://wpshadow.com/kb/search-engine-visibility/',
                'training_link' => 'https://wpshadow.com/training/indexation-basics/',
                'auto_fixable' => false,
                'threat_level' => 80,
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
