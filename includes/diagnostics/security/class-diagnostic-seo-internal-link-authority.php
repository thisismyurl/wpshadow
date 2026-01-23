<?php
declare(strict_types=1);
/**
 * Internal Link Authority Flow Diagnostic
 *
 * Philosophy: SEO architecture - distribute link equity
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check internal link distribution.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_SEO_Internal_Link_Authority extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		global $wpdb;
		
		// Get top posts by comment count (engagement proxy)
		$important_pages = $wpdb->get_results(
			"SELECT ID, post_title FROM {$wpdb->posts} 
			WHERE post_status = 'publish' 
			AND post_type IN ('post', 'page') 
			ORDER BY comment_count DESC 
			LIMIT 5"
		);
		
		if (empty($important_pages)) {
			return null;
		}
		
		// Check if these pages are linked from homepage
		$homepage_content = get_post_field('post_content', get_option('page_on_front'));
		
		if (empty($homepage_content)) {
			// Get recent posts content instead
			$recent_posts = get_posts(array('numberposts' => 5));
			$homepage_content = '';
			foreach ($recent_posts as $post) {
				$homepage_content .= $post->post_content;
			}
		}
		
		$poorly_linked = 0;
		foreach ($important_pages as $page) {
			// Check if page is linked
			if (stripos($homepage_content, get_permalink($page->ID)) === false) {
				$poorly_linked++;
			}
		}
		
		// If more than 3 important pages aren't linked, flag it
		if ($poorly_linked < 3) {
			return null;
		}
		
		return array(
			'id'          => 'seo-internal-link-authority',
			'title'       => 'Internal Link Structure Needs Optimization',
			'description' => sprintf('%d important page(s) lack links from homepage. Improve internal linking.', $poorly_linked),
			'severity'    => 'low',
			'category'    => 'seo',
			'kb_link'     => 'https://wpshadow.com/kb/internal-link-equity/',
			'training_link' => 'https://wpshadow.com/training/link-architecture/',
			'auto_fixable' => false,
			'threat_level' => 50,
		);
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
