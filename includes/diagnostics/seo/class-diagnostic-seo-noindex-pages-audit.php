<?php
declare(strict_types=1);
/**
 * Noindex Pages Audit Diagnostic
 *
 * Philosophy: SEO visibility - accidentally noindexed pages lose traffic
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check for important pages set to noindex.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_SEO_Noindex_Pages_Audit extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		global $wpdb;
		
		$noindex_posts = $wpdb->get_results(
			"SELECT p.ID, p.post_title 
			FROM {$wpdb->posts} p
			LEFT JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
			WHERE p.post_status = 'publish'
			AND p.post_type IN ('post', 'page')
			AND pm.meta_key = '_yoast_wpseo_meta-robots-noindex'
			AND pm.meta_value = '1'
			LIMIT 5"
		);
		
		if ( ! empty( $noindex_posts ) ) {
			return array(
				'id'          => 'seo-noindex-pages-audit',
				'title'       => 'Important Pages Set to Noindex',
				'description' => sprintf( '%d published pages/posts are noindexed. Accidentally noindexed pages won\'t rank. Review and remove noindex from important content.', count( $noindex_posts ) ),
				'severity'    => 'high',
				'category'    => 'seo',
				'kb_link'     => 'https://wpshadow.com/kb/fix-noindex-issues/',
				'training_link' => 'https://wpshadow.com/training/indexation-control/',
				'auto_fixable' => false,
				'threat_level' => 75,
			);
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
