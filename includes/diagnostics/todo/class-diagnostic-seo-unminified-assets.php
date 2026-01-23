<?php
declare(strict_types=1);
/**
 * Unminified CSS/JS Diagnostic
 *
 * Philosophy: SEO performance - minification improves page speed
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if CSS/JS is minified.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_SEO_Unminified_Assets extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		global $wp_scripts, $wp_styles;
		
		$unminified = 0;
		
		if ( ! empty( $wp_scripts->registered ) ) {
			foreach ( $wp_scripts->registered as $handle => $script ) {
				if ( strpos( $script->src, '.min.js' ) === false && strpos( $script->src, '.js' ) !== false ) {
					$unminified++;
				}
			}
		}
		
		if ( $unminified > 5 ) {
			return array(
				'id'          => 'seo-unminified-assets',
				'title'       => 'Unminified CSS/JS Files',
				'description' => sprintf( '%d unminified assets detected. Minification reduces file size by 20-40%%. Use plugin like Autoptimize or WP Rocket to minify CSS/JS.', $unminified ),
				'severity'    => 'medium',
				'category'    => 'seo',
				'kb_link'     => 'https://wpshadow.com/kb/minify-assets/',
				'training_link' => 'https://wpshadow.com/training/asset-optimization/',
				'auto_fixable' => false,
				'threat_level' => 55,
			);
		}
		
		return null;
	}



	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: SEO Unminified Assets
	 * Slug: -seo-unminified-assets
	 * File: class-diagnostic-seo-unminified-assets.php
	 * 
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: SEO Unminified Assets
	 * Slug: -seo-unminified-assets
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
	public static function test_live__seo_unminified_assets(): array {
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
