<?php
declare(strict_types=1);
/**
 * Keyword Cannibalization Diagnostic
 *
 * Philosophy: SEO focus - multiple pages competing hurts both
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check for keyword cannibalization (multiple pages targeting same keyword).
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_SEO_Keyword_Cannibalization extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		global $wpdb;
		
		// Check for duplicate titles (indicator of cannibalization)
		$duplicates = $wpdb->get_results(
			"SELECT post_title, COUNT(*) as count 
			FROM {$wpdb->posts} 
			WHERE post_status = 'publish' 
			AND post_type IN ('post', 'page') 
			GROUP BY post_title 
			HAVING count > 1"
		);
		
		if ( ! empty( $duplicates ) ) {
			return array(
				'id'          => 'seo-keyword-cannibalization',
				'title'       => 'Keyword Cannibalization Detected',
				'description' => sprintf( '%d duplicate titles found (possible keyword cannibalization). Multiple pages targeting same keyword compete against each other. Consolidate or differentiate content.', count( $duplicates ) ),
				'severity'    => 'medium',
				'category'    => 'seo',
				'kb_link'     => 'https://wpshadow.com/kb/fix-keyword-cannibalization/',
				'training_link' => 'https://wpshadow.com/training/keyword-strategy/',
				'auto_fixable' => false,
				'threat_level' => 60,
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
