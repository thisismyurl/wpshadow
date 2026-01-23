<?php
declare(strict_types=1);
/**
 * Slow Page Speed Diagnostic
 *
 * Philosophy: SEO Core Web Vitals - speed is ranking factor
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check for slow page speed.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_SEO_Slow_Page_Speed extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		$home_url = home_url( '/' );
		$start = microtime( true );
		$response = wp_remote_get( $home_url, array( 'timeout' => 10 ) );
		$load_time = microtime( true ) - $start;
		
		if ( ! is_wp_error( $response ) && $load_time > 3 ) {
			return array(
				'id'          => 'seo-slow-page-speed',
				'title'       => 'Slow Page Speed',
				'description' => sprintf( 'Homepage loads in %.2f seconds. Google recommends under 2.5s. Page speed is a ranking factor. Optimize images, enable caching, minify CSS/JS.', $load_time ),
				'severity'    => 'high',
				'category'    => 'seo',
				'kb_link'     => 'https://wpshadow.com/kb/improve-page-speed/',
				'training_link' => 'https://wpshadow.com/training/core-web-vitals/',
				'auto_fixable' => false,
				'threat_level' => 70,
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
