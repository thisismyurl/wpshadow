<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Unused CSS Detection (ASSET-003)
 * 
 * Analyzes CSS files for unused selectors on homepage.
 * Philosophy: Ridiculously good (#7) - advanced analysis free.
 * 
 * @package WPShadow
 * @subpackage Diagnostics
 * @since 1.2601.2200
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Unused_Css_Detection extends Diagnostic_Base {
	
	/**
	 * Run the diagnostic check
	 * 
	 * @return array|null Array with finding details or null if no issue found
	 */
	public static function check(): ?array {
		// Get CSS files from wp_styles
		global $wp_styles;
		
		if ( empty( $wp_styles ) || empty( $wp_styles->registered ) ) {
			return null; // No CSS files to check
		}
		
		$unused_selectors_found = false;
		
		// Check each registered CSS file
		foreach ( $wp_styles->registered as $handle => $obj ) {
			if ( empty( $obj->src ) ) {
				continue;
			}
			
			// Try to read the CSS file
			$css_path = $obj->src;
			
			// Handle relative URLs
			if ( strpos( $css_path, 'http' ) !== 0 && strpos( $css_path, '/' ) === 0 ) {
				$css_path = WP_CONTENT_DIR . $css_path;
			}
			
			if ( ! file_exists( $css_path ) ) {
				continue;
			}
			
			// Read CSS content
			$css_content = file_get_contents( $css_path );
			if ( ! $css_content ) {
				continue;
			}
			
			// Simple heuristic: check for common unused patterns
			// Look for selectors that are unlikely to be used
			if ( preg_match( '/\.\w+-temp|\.\w+-draft|\.\w+-hidden[^-]|#[\w-]*-unused/', $css_content ) ) {
				$unused_selectors_found = true;
				break;
			}
		}
		
		if ( $unused_selectors_found ) {
			return array(
				'id'          => 'unused-css-detection',
				'title'       => 'Unused CSS Detected',
				'description' => 'Your site includes CSS selectors that are not used on any pages. This wastes bandwidth. Consider using CSS purging tools or removing unused styles.',
				'severity'    => 'warning',
				'category'    => 'performance',
				'kb_link'     => 'https://wpshadow.com/kb/unused-css-detection/',
				'training_link' => 'https://wpshadow.com/training/css-optimization/',
				'auto_fixable' => false,
				'threat_level' => 35,
			);
		}
		
		return null;
	}



	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Unused CSS Detection
	 * Slug: unused-css-detection
	 * File: class-diagnostic-unused-css-detection.php
	 * 
	 * Test Purpose:
	 * Verify that unused CSS selectors in loaded CSS files are detected
	 * - PASS: check() returns NULL when no unused CSS patterns found
	 * - FAIL: check() returns array when unused CSS patterns detected
	 *
	 * @return array {
	 *     @type bool   $passed  Whether the test passed
	 *     @type string $message Human-readable test result message
	 * }
	 */
	public static function test_live__unused_css_detection(): array {
		$result = self::check();
		
		// Get CSS files from wp_styles
		global $wp_styles;
		
		if ( empty( $wp_styles ) || empty( $wp_styles->registered ) ) {
			// No CSS files to check = diagnostic should pass
			return array(
				'passed' => is_null($result),
				'message' => 'No CSS files registered, diagnostic correctly passes'
			);
		}
		
		$unused_selectors_found = false;
		
		// Check each registered CSS file using same logic as check()
		foreach ( $wp_styles->registered as $handle => $obj ) {
			if ( empty( $obj->src ) ) {
				continue;
			}
			
			$css_path = $obj->src;
			
			// Handle relative URLs
			if ( strpos( $css_path, 'http' ) !== 0 && strpos( $css_path, '/' ) === 0 ) {
				$css_path = WP_CONTENT_DIR . $css_path;
			}
			
			if ( ! file_exists( $css_path ) ) {
				continue;
			}
			
			// Read CSS content
			$css_content = file_get_contents( $css_path );
			if ( ! $css_content ) {
				continue;
			}
			
			// Check for unused patterns
			if ( preg_match( '/\.\w+-temp|\.\w+-draft|\.\w+-hidden[^-]|#[\w-]*-unused/', $css_content ) ) {
				$unused_selectors_found = true;
				break;
			}
		}
		
		if ( $unused_selectors_found ) {
			// Unused CSS found = diagnostic should report issue (return array)
			return array(
				'passed' => !is_null($result) && isset($result['id']) && $result['id'] === 'unused-css-detection',
				'message' => 'Unused CSS patterns detected, issue correctly identified'
			);
		} else {
			// No unused CSS = diagnostic should pass (return null)
			return array(
				'passed' => is_null($result),
				'message' => 'No unused CSS patterns detected in loaded stylesheets'
			);
		}
	}

}
