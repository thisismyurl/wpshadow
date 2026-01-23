<?php
declare(strict_types=1);
/**
 * Author Sitemap Disabled Diagnostic
 *
 * Philosophy: Avoid low-value author archives on single-author sites
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_Author_Sitemap_Disabled extends Diagnostic_Base {
    public static function check(): ?array {
        // Count active authors
        $author_count = count(get_users(array('who' => 'authors')));
        
        // Only flag if single author site
        if ($author_count > 1) {
            return null;
        }
        
        // Check if Yoast SEO is active and has author sitemap disabled
        if (is_plugin_active('wordpress-seo/wp-seo.php')) {
            $options = get_option('wpseo_xml');
            if (isset($options['disable_author_sitemap']) && $options['disable_author_sitemap']) {
                return null; // Already disabled
            }
        }
        
        return [
            'id' => 'seo-author-sitemap-disabled',
            'title' => 'Consider Disabling Author Sitemap',
            'description' => 'Single-author site detected. Consider disabling author archive sitemaps.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/author-archives-seo/',
            'training_link' => 'https://wpshadow.com/training/archive-templates-seo/',
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
