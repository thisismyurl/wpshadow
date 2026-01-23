<?php
declare(strict_types=1);
/**
 * Asset Versions - JavaScript Diagnostic
 *
 * @package WPShadow
 */


namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Check for JavaScript asset version query strings (?ver=).
 *
 * Family: asset-versions
 * Related: asset-versions-css
 * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Registered in Diagnostic_Registry quick_diagnostics
 */
class Diagnostic_Asset_Versions_JS extends Diagnostic_Base {

	protected static $slug = 'asset-versions-js';
	protected static $title = 'JavaScript Asset Version Strings';
	protected static $description = 'Checks for version query strings (?ver=) on JavaScript files that can be removed to improve caching.';
	protected static $family = 'asset-versions';
	protected static $family_label = 'Asset Optimization';

	public static function check(): ?array {
		if ( get_option( 'wpshadow_asset_version_removal_enabled', false ) ) {
			return null;
		}

		// Ensure WordPress assets are loaded
		if ( ! did_action( 'wp_enqueue_scripts' ) && ! did_action( 'admin_enqueue_scripts' ) ) {
			// Not in proper context, skip check
			return null;
		}

		global $wp_scripts;

		if ( ! isset( $wp_scripts ) || ! ( $wp_scripts instanceof \WP_Scripts ) ) {
			return null;
		}

		$versioned_assets = 0;
		$sample_assets    = array();

		foreach ( $wp_scripts->registered as $handle => $script ) {
			if ( is_string( $script->src ) && strpos( $script->src, '?ver=' ) !== false ) {
				$versioned_assets++;
				if ( count( $sample_assets ) < 3 ) {
					$sample_assets[] = $handle;
				}
			}
		}

		if ( $versioned_assets === 0 ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'finding_id'   => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				__( 'Found %d JavaScript files with version query strings (?ver=) that could be removed. Examples: %s', 'wpshadow' ),
				$versioned_assets,
				implode( ', ', $sample_assets )
			),
			'category'     => 'performance',
			'severity'     => 'low',
			'threat_level' => 7,
			'auto_fixable' => true,
			'family'       => self::$family,
			'family_label' => self::$family_label,
			'timestamp'    => current_time( 'mysql' ),
		);
	}

	/**
	 * Test: No versioned JS assets should return null (pass).
	 *
	 * @return array Test result with 'passed', 'message' keys.
	 */
	public static function test_no_versioned_assets(): array {
		global $wp_scripts;

		// Setup: Create WP_Scripts object with non-versioned JS
		$wp_scripts = new \WP_Scripts();
		$wp_scripts->add( 'test-clean-1', 'https://example.com/script1.js' );
		$wp_scripts->add( 'test-clean-2', 'https://example.com/script2.js' );

		// Mock that enqueue_scripts fired
		do_action( 'wp_enqueue_scripts' );

		$result = self::check();

		if ( null === $result ) {
			return array(
				'passed'  => true,
				'message' => 'Correctly returned null when no versioned JS found',
			);
		}

		return array(
			'passed'  => false,
			'message' => 'Expected null for clean JS, got array: ' . wp_json_encode( $result ),
		);
	}

	/**
	 * Test: With versioned JS assets, should return array with correct fields.
	 *
	 * @return array Test result with 'passed', 'message' keys.
	 */
	public static function test_versioned_assets_detected(): array {
		global $wp_scripts;

		// Setup: Create WP_Scripts with versioned JS
		$wp_scripts = new \WP_Scripts();
		$wp_scripts->add( 'test-versioned-1', 'https://example.com/script1.js?ver=1.0' );
		$wp_scripts->add( 'test-versioned-2', 'https://example.com/script2.js?ver=2.0' );
		$wp_scripts->add( 'test-versioned-3', 'https://example.com/script3.js?ver=3.0' );
		$wp_scripts->add( 'test-versioned-4', 'https://example.com/script4.js?ver=4.0' );

		do_action( 'wp_enqueue_scripts' );

		$result = self::check();

		if ( ! is_array( $result ) ) {
			return array(
				'passed'  => false,
				'message' => 'Expected array when versioned JS found, got: ' . gettype( $result ),
			);
		}

		// Verify required fields
		$required_fields = array( 'id', 'title', 'description', 'category', 'severity', 'threat_level' );
		foreach ( $required_fields as $field ) {
			if ( ! isset( $result[ $field ] ) ) {
				return array(
					'passed'  => false,
					'message' => "Missing required field: $field",
				);
			}
		}

		// Verify values
		if ( 'asset-versions-js' !== $result['id'] ) {
			return array(
				'passed'  => false,
				'message' => "Expected id 'asset-versions-js', got '{$result['id']}'",
			);
		}

		if ( 'performance' !== $result['category'] ) {
			return array(
				'passed'  => false,
				'message' => "Expected category 'performance', got '{$result['category']}'",
			);
		}

		if ( 'low' !== $result['severity'] ) {
			return array(
				'passed'  => false,
				'message' => "Expected severity 'low', got '{$result['severity']}'",
			);
		}

		// Verify threat level is 7 (not 8 like CSS)
		if ( 7 !== $result['threat_level'] ) {
			return array(
				'passed'  => false,
				'message' => "Expected threat_level 7, got {$result['threat_level']}",
			);
		}

		// Verify description mentions the count
		if ( strpos( $result['description'], '4' ) === false ) {
			return array(
				'passed'  => false,
				'message' => "Description should mention asset count, got: {$result['description']}",
			);
		}

		// Verify examples are in description
		if ( strpos( $result['description'], 'test-versioned-' ) === false ) {
			return array(
				'passed'  => false,
				'message' => "Description should include asset examples, got: {$result['description']}",
			);
		}

		return array(
			'passed'  => true,
			'message' => 'Correctly detected versioned JS and returned proper array structure',
		);
	}

	/**
	 * Test: When removal is enabled, should return null (skip).
	 *
	 * @return array Test result with 'passed', 'message' keys.
	 */
	public static function test_when_removal_enabled(): array {
		global $wp_scripts;

		// Setup: Enable the option
		update_option( 'wpshadow_asset_version_removal_enabled', true );

		// Create WP_Scripts with versioned JS (would normally fail)
		$wp_scripts = new \WP_Scripts();
		$wp_scripts->add( 'test-versioned', 'https://example.com/script.js?ver=1.0' );

		do_action( 'wp_enqueue_scripts' );

		$result = self::check();

		// Clean up
		delete_option( 'wpshadow_asset_version_removal_enabled' );

		if ( null === $result ) {
			return array(
				'passed'  => true,
				'message' => 'Correctly returned null when removal is already enabled',
			);
		}

		return array(
			'passed'  => false,
			'message' => 'Expected null when removal enabled, got: ' . wp_json_encode( $result ),
		);
	}

	/**
	 * Test: With mixed (versioned and non-versioned) JS, should count only versioned.
	 *
	 * @return array Test result with 'passed', 'message' keys.
	 */
	public static function test_mixed_assets(): array {
		global $wp_scripts;

		// Setup: Mix of versioned and non-versioned
		$wp_scripts = new \WP_Scripts();
		$wp_scripts->add( 'clean-1', 'https://example.com/clean1.js' );
		$wp_scripts->add( 'versioned-1', 'https://example.com/versioned1.js?ver=1.0' );
		$wp_scripts->add( 'clean-2', 'https://example.com/clean2.js' );
		$wp_scripts->add( 'versioned-2', 'https://example.com/versioned2.js?ver=2.0' );

		do_action( 'wp_enqueue_scripts' );

		$result = self::check();

		if ( ! is_array( $result ) ) {
			return array(
				'passed'  => false,
				'message' => 'Expected array, got: ' . gettype( $result ),
			);
		}

		// Should mention 2 versioned assets
		if ( strpos( $result['description'], '2' ) === false ) {
			return array(
				'passed'  => false,
				'message' => "Expected description to mention '2' assets, got: {$result['description']}",
			);
		}

		// Should include examples
		if ( strpos( $result['description'], 'versioned-' ) === false ) {
			return array(
				'passed'  => false,
				'message' => "Expected versioned examples in description, got: {$result['description']}",
			);
		}

		return array(
			'passed'  => true,
			'message' => 'Correctly counted only versioned JS in mixed set',
		);
	}

	/**
	 * Test: Sample limit - should only include first 3 examples, not all.
	 *
	 * @return array Test result with 'passed', 'message' keys.
	 */
	public static function test_sample_limit(): array {
		global $wp_scripts;

		// Setup: Create 10 versioned JS files
		$wp_scripts = new \WP_Scripts();
		for ( $i = 1; $i <= 10; $i++ ) {
			$wp_scripts->add( "test-$i", "https://example.com/script$i.js?ver=$i.0" );
		}

		do_action( 'wp_enqueue_scripts' );

		$result = self::check();

		if ( ! is_array( $result ) ) {
			return array(
				'passed'  => false,
				'message' => 'Expected array, got: ' . gettype( $result ),
			);
		}

		// Should mention 10 assets found
		if ( strpos( $result['description'], '10' ) === false ) {
			return array(
				'passed'  => false,
				'message' => "Description should mention '10' assets found, got: {$result['description']}",
			);
		}

		// Should include some examples
		$example_count = 0;
		for ( $i = 1; $i <= 10; $i++ ) {
			if ( strpos( $result['description'], "test-$i" ) !== false ) {
				$example_count++;
			}
		}

		if ( $example_count !== 3 ) {
			return array(
				'passed'  => false,
				'message' => "Expected exactly 3 examples, found $example_count in: {$result['description']}",
			);
		}

		return array(
			'passed'  => true,
			'message' => 'Correctly limited examples to first 3 of 10 assets',
		);
	}
}
