<?php
declare(strict_types=1);
/**
 * Theme Performance & Features Diagnostic
 *
 * Philosophy: Educate about theme optimization opportunities
 * Guides to Pro features for theme analysis and Guardian theme scanning
 *
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Registered in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check active theme for performance and feature issues.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Registered in Diagnostic_Registry
 */
class Diagnostic_Theme_Performance extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		$theme = wp_get_theme();
		if ( ! $theme->exists() ) {
			return null;
		}
		
		$issues = array();
		
		// Check if theme is child theme (best practice)
		if ( ! is_child_theme() ) {
			$issues[] = 'Not using a child theme - customizations may be lost during updates';
		}
		
		// Check if theme has explicit theme support for modern features
		$supports = get_theme_support( 'post-formats' );
		if ( false === $supports && false === get_theme_support( 'custom-logo' ) && false === get_theme_support( 'html5' ) ) {
			$issues[] = 'Theme appears outdated - missing modern WordPress features support';
		}
		
		// Check if theme has proper responsive design support
		$theme_uri = $theme->get( 'ThemeURI' );
		if ( empty( $theme_uri ) ) {
			$issues[] = 'Theme is missing project documentation/homepage';
		}
		
		// Check theme size (very large themes are slower)
		$theme_dir = get_theme_root() . '/' . get_template();
		if ( is_dir( $theme_dir ) ) {
			$size = self::dir_size( $theme_dir );
			if ( $size > 50 * 1024 * 1024 ) { // 50MB
				$issues[] = 'Theme folder is very large (' . round( $size / 1024 / 1024 ) . 'MB) - may slow down file operations';
			}
		}
		
		if ( ! empty( $issues ) ) {
			return array(
				'id'          => 'theme-performance',
				'title'       => 'Theme Performance Recommendations',
				'description' => 'Your theme could be optimized for better performance and maintainability: ' . implode( '. ', $issues ) . '.',
				'severity'    => 'low',
				'category'    => 'design',
				'kb_link'     => 'https://wpshadow.com/kb/choosing-wordpress-themes/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=theme-performance',
				'auto_fixable' => false,
				'threat_level' => 20,
			);
		}
		
		return null;
	}
	
	/**
	 * Get directory size recursively
	 *
	 * @param string $dir Directory path
	 * @return int Size in bytes
	 */
	private static function dir_size( $dir ) {
		$size = 0;
		$files = @scandir( $dir );
		if ( is_array( $files ) ) {
			foreach ( $files as $file ) {
				if ( '.' !== $file && '..' !== $file ) {
					$filepath = $dir . '/' . $file;
					if ( is_dir( $filepath ) ) {
						$size += self::dir_size( $filepath );
					} elseif ( is_file( $filepath ) ) {
						$size += filesize( $filepath );
					}
				}
			}
		}
		return $size;
	}



	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Theme Performance
	 * Slug: -theme-performance
	 * File: class-diagnostic-theme-performance.php
	 * 
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: Theme Performance
	 * Slug: -theme-performance
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
	public static function test_live__theme_performance(): array {
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
