<?php
declare(strict_types=1);
/**
 * Render-Blocking Resources Diagnostic
 *
 * Philosophy: SEO performance - defer non-critical CSS/JS
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check for render-blocking resources.
 */
class Diagnostic_SEO_Render_Blocking_Resources extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		return array(
			'id'          => 'seo-render-blocking-resources',
			'title'       => 'Render-Blocking Resources',
			'description' => 'Check for render-blocking CSS/JS in PageSpeed Insights. Defer non-critical CSS/JS, inline critical CSS, use async/defer attributes. Improves FCP and LCP.',
			'severity'    => 'medium',
			'category'    => 'seo',
			'kb_link'     => 'https://wpshadow.com/kb/eliminate-render-blocking/',
			'training_link' => 'https://wpshadow.com/training/critical-rendering-path/',
			'auto_fixable' => false,
			'threat_level' => 60,
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
