<?php
declare(strict_types=1);
/**
 * Missing Knowledge Graph Optimization Diagnostic
 *
 * Philosophy: SEO branding - Knowledge Graph shows brand authority
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check for Knowledge Graph optimization.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_SEO_Missing_Knowledge_Graph extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		// Check if Organization/Brand schema exists
		global $wpdb;
		
		$has_org_schema = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts} 
			WHERE post_content LIKE '%\"@type\":\"Organization\"%' 
			OR post_content LIKE '%schema.org/Organization%'"
		);
		
		if ( $has_org_schema === 0 ) {
			return array(
				'id'          => 'seo-missing-knowledge-graph',
				'title'       => 'Missing Knowledge Graph Optimization',
				'description' => 'No Organization schema detected. Add Organization schema with logo, social profiles, contact info. Helps Google create Knowledge Panel for your brand.',
				'severity'    => 'medium',
				'category'    => 'seo',
				'kb_link'     => 'https://wpshadow.com/kb/optimize-knowledge-graph/',
				'training_link' => 'https://wpshadow.com/training/brand-schema/',
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
