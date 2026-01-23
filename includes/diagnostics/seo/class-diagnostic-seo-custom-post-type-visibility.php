<?php
declare(strict_types=1);
/**
 * Custom Post Type Visibility Diagnostic
 *
 * Philosophy: CPTs should be in sitemaps
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_Custom_Post_Type_Visibility extends Diagnostic_Base {
    public static function check(): ?array {
        $post_types = get_post_types(['public' => true, '_builtin' => false], 'names');
        if (count($post_types) > 0) {
            return [
                'id' => 'seo-custom-post-type-visibility',
                'title' => 'Custom Post Type Sitemap Visibility',
                'description' => 'Verify custom post types are included in XML sitemaps and properly indexed.',
                'severity' => 'low',
                'category' => 'seo',
                'kb_link' => 'https://wpshadow.com/kb/custom-post-types-seo/',
                'training_link' => 'https://wpshadow.com/training/cpt-optimization/',
                'auto_fixable' => false,
                'threat_level' => 25,
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
	}}
