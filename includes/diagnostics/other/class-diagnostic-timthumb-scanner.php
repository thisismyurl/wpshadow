<?php
declare(strict_types=1);
/**
 * Timthumb Vulnerability Scanner Diagnostic
 *
 * Philosophy: Legacy vulnerability detection - timthumb exploit prevention
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check for vulnerable timthumb.php files.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Timthumb_Scanner extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		// Scan themes directory for timthumb.php
		$themes_dir     = get_theme_root();
		$found_timthumb = array();

		if ( ! is_dir( $themes_dir ) ) {
			return null;
		}

		// Check active theme and parent theme
		$active_theme    = wp_get_theme();
		$themes_to_check = array( $active_theme->get_stylesheet() );

		if ( $active_theme->parent() ) {
			$themes_to_check[] = $active_theme->get_template();
		}

		foreach ( $themes_to_check as $theme_slug ) {
			$theme_dir = $themes_dir . '/' . $theme_slug;
			if ( ! is_dir( $theme_dir ) ) {
				continue;
			}

			// Check for timthumb.php
			$timthumb_path = $theme_dir . '/timthumb.php';
			if ( file_exists( $timthumb_path ) ) {
				// Check if it's an old vulnerable version
				$content = file_get_contents( $timthumb_path );
				// Vulnerable versions lack proper security checks
				if ( strpos( $content, 'define(\'VERSION\'' ) !== false ) {
					preg_match( "/define\('VERSION',\s*'([^']+)'/", $content, $matches );
					if ( ! empty( $matches[1] ) ) {
						$version = $matches[1];
						if ( version_compare( $version, '2.8.14', '<' ) ) {
							$found_timthumb[] = $theme_slug . ' (v' . $version . ')';
						}
					}
				}
			}
		}

		if ( ! empty( $found_timthumb ) ) {
			return array(
				'id'            => 'timthumb-scanner',
				'title'         => 'Vulnerable Timthumb Detected',
				'description'   => sprintf(
					'Your theme(s) contain vulnerable timthumb.php files: %s. This is a critical security risk allowing remote code execution. Update or remove timthumb immediately.',
					implode( ', ', $found_timthumb )
				),
				'severity'      => 'critical',
				'category'      => 'security',
				'kb_link'       => 'https://wpshadow.com/kb/remove-timthumb-vulnerability/',
				'training_link' => 'https://wpshadow.com/training/timthumb-security/',
				'auto_fixable'  => false,
				'threat_level'  => 95,
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
