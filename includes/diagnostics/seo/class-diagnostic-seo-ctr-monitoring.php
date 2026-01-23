<?php
declare(strict_types=1);
/**
 * Click-Through Rate Monitoring Diagnostic
 *
 * Philosophy: SEO performance - monitor CTR in GSC
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if CTR is monitored.
 */
class Diagnostic_SEO_CTR_Monitoring extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		return array(
			'id'          => 'seo-ctr-monitoring',
			'title'       => 'Monitor Click-Through Rate',
			'description' => 'Review CTR in Google Search Console. Low CTR (<2%) despite good rankings means poor title/meta. Test different titles/descriptions to improve CTR.',
			'severity'    => 'low',
			'category'    => 'seo',
			'kb_link'     => 'https://wpshadow.com/kb/improve-ctr/',
			'training_link' => 'https://wpshadow.com/training/ctr-optimization/',
			'auto_fixable' => false,
			'threat_level' => 45,
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
