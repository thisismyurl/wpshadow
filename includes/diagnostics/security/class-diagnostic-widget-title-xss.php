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
