<?php
declare(strict_types=1);
/**
 * Search Intent Mismatch Diagnostic
 *
 * Philosophy: SEO relevance - match content to search intent
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if content matches likely search intent.
 */
class Diagnostic_SEO_Search_Intent_Mismatch extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		return array(
			'id'          => 'seo-search-intent-mismatch',
			'title'       => 'Review Search Intent Alignment',
			'description' => 'Ensure content matches search intent: Informational ("what is"), Navigational ("brand name"), Transactional ("buy"), Commercial ("best"). Search Google for target keyword and analyze top 10 results.',
			'severity'    => 'medium',
			'category'    => 'seo',
			'kb_link'     => 'https://wpshadow.com/kb/match-search-intent/',
			'training_link' => 'https://wpshadow.com/training/search-intent/',
			'auto_fixable' => false,
			'threat_level' => 55,
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
	}}
