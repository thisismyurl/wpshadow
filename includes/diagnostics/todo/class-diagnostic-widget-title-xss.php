<?php
declare(strict_types=1);
/**
 * Widget Title XSS Diagnostic
 *
 * Philosophy: Widget security - escape widget titles
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if widget titles are escaped.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Widget_Title_XSS extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		// Check if widget_title filter has escaping
		global $wp_filter;
		
		if ( ! isset( $wp_filter['widget_title'] ) ) {
			return null; // No filters
		}
		
		$has_escaping = false;
		
		foreach ( $wp_filter['widget_title']->callbacks as $priority => $callbacks ) {
			foreach ( $callbacks as $callback ) {
				$function = $callback['function'];
				
				// Check if callback is an escaping function
				if ( is_string( $function ) && 
				     ( strpos( $function, 'esc_' ) === 0 || $function === 'wp_kses_post' ) ) {
					$has_escaping = true;
					break 2;
				}
			}
		}
		
		if ( ! $has_escaping ) {
			return array(
				'id'          => 'widget-title-xss',
				'title'       => 'Widget Titles Not Escaped',
				'description' => 'Widget titles are filtered but not escaped. Malicious users with widget management capability can inject JavaScript via widget titles. Add esc_html() filter to widget_title.',
				'severity'    => 'medium',
				'category'    => 'security',
				'kb_link'     => 'https://wpshadow.com/kb/escape-widget-titles/',
				'training_link' => 'https://wpshadow.com/training/widget-security/',
				'auto_fixable' => true,
				'threat_level' => 70,
			);
		}
		
		return null;
	}



	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Widget Title XSS
	 * Slug: -widget-title-xss
	 * File: class-diagnostic-widget-title-xss.php
	 * 
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: Widget Title XSS
	 * Slug: -widget-title-xss
	 * 
	 * TODO: Review the check() method to understand what constitutes a passing test.
	 * The test should verify that:
	 * - check() returns NULL when the diagnostic condition is NOT met (site is healthy)
	 * - check() returns an array when the diagnostic condition IS met (issue found)
	 *
	 * @return array {
	 *     @type bool   $passed  Whether the test passed
	 *     @type string $message Human-readable test result message
	 * }
	 */
	public static function test_live__widget_title_xss(): array {
		/*
		 * IMPLEMENTATION NOTES:
		 * - This test validates the actual WordPress site state
		 * - Do not use mocks or stubs
		 * - Call self::check() to get the diagnostic result
		 * - Verify the result matches expected site state
		 * - Return [ 'passed' => bool, 'message' => string ]
		 */
		
		$result = self::check();
		
		// TODO: Implement actual test logic
		return array(
			'passed' => false,
			'message' => 'Test not yet implemented',
		);
	}

}
