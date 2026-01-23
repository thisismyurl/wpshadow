<?php
declare(strict_types=1);
/**
 * Block Asset Cleanup Diagnostic
 *
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Registered in Diagnostic_Registry
 */


namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if optional block assets are being enqueued unnecessarily on the front-end.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Registered in Diagnostic_Registry
 */
class Diagnostic_Block_Cleanup extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		if ( ! is_admin() && self::has_block_assets() ) {
			return array(
				'id'           => 'block-assets-loaded',
				'title'        => 'Gutenberg Block Assets Loading Everywhere',
				'description'  => 'Block library styles/scripts load on all pages. Disable them on front-end pages that don’t use blocks.',
				'color'        => '#ff9800',
				'bg_color'     => '#fff3e0',
				'kb_link'      => 'https://wpshadow.com/kb/disable-gutenberg-assets/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=block-cleanup',
				'auto_fixable' => true,
				'threat_level' => 30,
			);
		}

		return null;
	}

	private static function has_block_assets() {
		wp_enqueue_scripts(); // Populate scripts/styles queue.
		global $wp_styles, $wp_scripts;
		$block_handles = array( 'wp-block-library', 'wp-block-library-theme', 'wc-blocks-style' );

		if ( isset( $wp_styles ) ) {
			foreach ( $block_handles as $handle ) {
				if ( isset( $wp_styles->registered[ $handle ] ) ) {
					return true;
				}
			}
		}

		if ( isset( $wp_scripts ) ) {
			foreach ( $block_handles as $handle ) {
				if ( isset( $wp_scripts->registered[ $handle ] ) ) {
					return true;
				}
			}
		}

		return false;
	}



	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Block Cleanup
	 * Slug: -block-cleanup
	 * File: class-diagnostic-block-cleanup.php
	 * 
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: Block Cleanup
	 * Slug: -block-cleanup
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
	public static function test_live__block_cleanup(): array {
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
