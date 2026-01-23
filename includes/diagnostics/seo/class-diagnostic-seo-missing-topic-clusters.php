<?php
declare(strict_types=1);
/**
 * Missing Topic Clusters Diagnostic
 *
 * Philosophy: SEO content strategy - pillar pages build authority
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check for topic cluster/pillar page strategy.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_SEO_Missing_Topic_Clusters extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		global $wpdb;
		
		$total_posts = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts} 
			WHERE post_status = 'publish' 
			AND post_type = 'post'"
		);
		
		// Check for pillar page indicators
		$pillar_pages = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts} 
			WHERE post_status = 'publish' 
			AND (post_title LIKE '%complete guide%' OR post_title LIKE '%ultimate guide%' OR post_title LIKE '%everything you need%')"
		);
		
		if ( $total_posts > 20 && $pillar_pages < 2 ) {
			return array(
				'id'          => 'seo-missing-topic-clusters',
				'title'       => 'No Topic Cluster Strategy',
				'description' => sprintf( '%d posts without clear pillar pages. Create comprehensive pillar pages (2000+ words) linked to related cluster content. Builds topical authority.', $total_posts ),
				'severity'    => 'medium',
				'category'    => 'seo',
				'kb_link'     => 'https://wpshadow.com/kb/create-topic-clusters/',
				'training_link' => 'https://wpshadow.com/training/pillar-page-strategy/',
				'auto_fixable' => false,
				'threat_level' => 55,
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
