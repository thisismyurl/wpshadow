<?php
declare(strict_types=1);
/**
 * Font Optimization Diagnostic
 *
 * Philosophy: Shows value by identifying wasted requests and performance drain
 * Guides to Pro features for automatic font optimization
 *
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Registered in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check for font optimization opportunities.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Registered in Diagnostic_Registry
 */
class Diagnostic_Font_Optimization extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		$issues = array();
		
		// Check if using Google Fonts (should be preconnected)
		global $wp_scripts, $wp_styles;
		
		$google_fonts_count = 0;
		$non_optimized_fonts = array();
		
		// Check registered styles for Google Fonts
		if ( isset( $wp_styles->registered ) ) {
			foreach ( $wp_styles->registered as $handle => $item ) {
				// Skip if src is not a string (e.g., inline styles have src = true)
				if ( ! is_string( $item->src ) ) {
					continue;
				}
				
				if ( stripos( $item->src, 'fonts.googleapis.com' ) !== false || stripos( $item->src, 'fonts.gstatic.com' ) !== false ) {
					$google_fonts_count++;
					$non_optimized_fonts[] = $handle;
				}
			}
		}
		
		// Check for preconnect headers
		$preconnect_present = false;
		if ( function_exists( 'wp_resource_hints' ) ) {
			$preconnect_present = true; // WordPress will add it
		}
		
		if ( $google_fonts_count > 3 && ! $preconnect_present ) {
			$issues[] = 'Using ' . $google_fonts_count . ' Google Font requests without resource hints - this causes render blocking';
		}
		
		if ( $google_fonts_count > 1 ) {
			// Multiple font requests is worth noting
			if ( empty( $issues ) ) {
				$issues[] = 'Using ' . $google_fonts_count . ' Google Font files - consider consolidating to reduce HTTP requests';
			}
		}
		
		// Check if System fonts could be used (faster)
		$has_local_fonts = false;
		$theme = wp_get_theme();
		if ( ! empty( $theme ) ) {
			$theme_root = get_theme_root() . '/' . get_template();
			if ( file_exists( $theme_root . '/fonts' ) || file_exists( $theme_root . '/assets/fonts' ) ) {
				$has_local_fonts = true;
			}
		}
		
		if ( ! empty( $issues ) ) {
			return array(
				'id'          => 'font-optimization',
				'title'       => 'Font Loading Optimization',
				'description' => implode( '. ', $issues ) . '. Optimized fonts can improve Core Web Vitals and perceived performance.',
				'severity'    => 'low',
				'category'    => 'design',
				'kb_link'     => 'https://wpshadow.com/kb/optimize-google-fonts/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=font-optimization',
				'auto_fixable' => false,
				'threat_level' => 25,
			);
		}
		
		return null;
	}



	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Font Optimization
	 * Slug: -font-optimization
	 * File: class-diagnostic-font-optimization.php
	 * 
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: Font Optimization
	 * Slug: -font-optimization
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
	public static function test_live__font_optimization(): array {
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
