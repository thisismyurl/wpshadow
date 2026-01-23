<?php
declare(strict_types=1);
/**
 * Cornerstone Content Identification Diagnostic
 *
 * Philosophy: SEO strategy - identify and promote best content
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check for cornerstone content strategy.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_SEO_Cornerstone_Content extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		global $wpdb;
		
		$cornerstone = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->postmeta} 
			WHERE meta_key = '_yoast_wpseo_is_cornerstone' 
			AND meta_value = '1'"
		);
		
		if ( $cornerstone === 0 ) {
			return array(
				'id'          => 'seo-cornerstone-content',
				'title'       => 'Identify Cornerstone Content',
				'description' => 'No cornerstone content marked. Identify 3-5 best, most comprehensive articles. Mark as cornerstone, keep updated, link to from other posts, promote heavily.',
				'severity'    => 'low',
				'category'    => 'seo',
				'kb_link'     => 'https://wpshadow.com/kb/cornerstone-content/',
				'training_link' => 'https://wpshadow.com/training/content-hierarchy/',
				'auto_fixable' => false,
				'threat_level' => 45,
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
