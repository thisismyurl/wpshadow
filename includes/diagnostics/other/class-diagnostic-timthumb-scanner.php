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
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Timthumb Scanner
	 * Slug: -timthumb-scanner
	 * File: class-diagnostic-timthumb-scanner.php
	 * 
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: Timthumb Scanner
	 * Slug: -timthumb-scanner
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
	public static function test_live__timthumb_scanner(): array {
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
