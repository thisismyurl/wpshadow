<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Diagnostic_Interactivity_Cleanup extends Diagnostic_Base {

	protected static $slug        = 'interactivity-cleanup';
	protected static $title       = 'Modern Block Features';
	protected static $description = 'Checks if modern interactive block features are loaded but not being used.';

	public static function check(): ?array {
		if ( get_option( 'wpshadow_interactivity_cleanup_enabled', false ) ) {
			return null;
		}

		if ( version_compare( get_bloginfo( 'version' ), '6.5', '<' ) ) {
			return null;
		}

		$uses_interactive_blocks = false;
		$recent_posts            = get_posts(
			array(
				'post_type'   => array( 'post', 'page' ),
				'numberposts' => 20,
				'post_status' => 'publish',
			)
		);

		foreach ( $recent_posts as $post ) {
			if ( has_block( 'core/navigation', $post ) ||
				has_block( 'core/query', $post ) ||
				strpos( $post->post_content, 'wp-interactivity' ) !== false ) {
				$uses_interactive_blocks = true;
				break;
			}
		}

		if ( $uses_interactive_blocks ) {
			return null;
		}

		return array(
			'id'   => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'WordPress 6.5+ interactive block features (Interactivity API, Block Bindings) are loaded but not used. Disabling saves bandwidth and improves performance.', 'wpshadow' ),
			'category'     => 'performance',
			'severity'     => 'low',
			'threat_level' => 20,
			'auto_fixable' => true,
			'timestamp'    => current_time( 'mysql' ),
		);
	}



	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Modern Block Features
	 * Slug: interactivity-cleanup
	 * 
	 * Test Purpose:
	 * - Verify that check() method returns the correct result based on site state
	 * - PASS: check() returns NULL when diagnostic condition is NOT met (site is healthy)
	 * - FAIL: check() returns array when diagnostic condition IS met (issue found)
	 * - Description: Checks if modern interactive block features are loaded but not being used.
	 *
	 * @return array {
	 *     @type bool   $passed  Whether the test passed
	 *     @type string $message Human-readable test result message
	 * }
	 */
	public static function test_live_interactivity_cleanup(): array {
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
			'message' => 'Test not yet implemented for ' . self::$slug,
		);
	}

}
