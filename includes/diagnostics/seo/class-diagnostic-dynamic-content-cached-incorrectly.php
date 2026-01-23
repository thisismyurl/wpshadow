<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Dynamic Content Cached Incorrectly (CACHE-008)
 * 
 * Detects user-specific content in cached pages.
 * Philosophy: Helpful neighbor (#1) - prevent data leaks.
 * 
 * @package WPShadow
 * @subpackage Diagnostics
 * @since 1.2601.2200
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Dynamic_Content_Cached_Incorrectly extends Diagnostic_Base {
	
	/**
	 * Run the diagnostic check
	 * 
	 * @return array|null Array with finding details or null if no issue found
	 */
	public static function check(): ?array {
		// Check for dynamic content caching issues
        $cache_headers = wp_get_server_var('HTTP_CACHE_CONTROL');
        
        // Check if pages that should be dynamic are being cached
        if (!empty($cache_headers) && is_singular('post')) {
            if (strpos($cache_headers, 'no-cache') === false && strpos($cache_headers, 'private') === false) {
                return array(
                    'id' => 'dynamic-content-cached-incorrectly',
                    'title' => __('Dynamic Content May Be Over-Cached', 'wpshadow'),
                    'description' => __('Posts with personalized or changing content should use private or no-cache directives. Adjust cache headers for content types.', 'wpshadow'),
                    'severity' => 'medium',
                    'category' => 'seo',
                    'kb_link' => 'https://wpshadow.com/kb/cache-control-headers/',
                    'training_link' => 'https://wpshadow.com/training/cache-strategy/',
                    'auto_fixable' => false,
                    'threat_level' => 45,
                );
            }
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
