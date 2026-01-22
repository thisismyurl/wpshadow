<?php declare(strict_types=1);
/**
 * Widget Title XSS Diagnostic
 *
 * Philosophy: Widget security - escape widget titles
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

/**
 * Check if widget titles are escaped.
 */
class Diagnostic_Widget_Title_XSS {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check() {
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
}
