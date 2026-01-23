<?php
declare(strict_types=1);
/**
 * Dwell Time Optimization Diagnostic
 *
 * Philosophy: SEO engagement - keep visitors on page
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check for dwell time optimization strategies.
 */
class Diagnostic_SEO_Dwell_Time_Optimization extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		return array(
			'id'          => 'seo-dwell-time-optimization',
			'title'       => 'Optimize for Dwell Time',
			'description' => 'Improve dwell time (time on page before returning to SERPs): Use table of contents, add related posts, embed videos, improve readability, answer questions comprehensively.',
			'severity'    => 'low',
			'category'    => 'seo',
			'kb_link'     => 'https://wpshadow.com/kb/improve-dwell-time/',
			'training_link' => 'https://wpshadow.com/training/engagement-metrics/',
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
	}}
