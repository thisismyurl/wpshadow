<?php
declare(strict_types=1);
/**
 * Asset Versions - CSS Diagnostic
 *
 * @package WPShadow
 */


namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Check for CSS asset version query strings (?ver=).
 *
 * Family: asset-versions
 * Related: asset-versions-js
 * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Registered in Diagnostic_Registry quick_diagnostics
 */
class Diagnostic_Asset_Versions_CSS extends Diagnostic_Base {

	protected static $slug = 'asset-versions-css';
	protected static $title = 'CSS Asset Version Strings';
	protected static $description = 'Checks for version query strings (?ver=) on CSS files that can be removed to improve caching.';
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

		global $wp_styles;

		if ( ! isset( $wp_styles ) || ! ( $wp_styles instanceof \WP_Styles ) ) {
			return null;
		}

		$versioned_assets = 0;
		$sample_assets    = array();

		foreach ( $wp_styles->registered as $handle => $style ) {
			if ( is_string( $style->src ) && strpos( $style->src, '?ver=' ) !== false ) {
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
				__( 'Found %d CSS files with version query strings (?ver=) that could be removed. Examples: %s', 'wpshadow' ),
				$versioned_assets,
				implode( ', ', $sample_assets )
			),
			'category'     => 'performance',
			'severity'     => 'low',
			'threat_level' => 8,
			'auto_fixable' => true,
			'family'       => self::$family,
			'family_label' => self::$family_label,
			'timestamp'    => current_time( 'mysql' ),
		);
	}

	/**
	 * Test: No versioned CSS assets should return null (pass).
	 *
	 * @return array Test result with 'passed', 'message' keys.
	 */
	public static function test_no_versioned_assets(): array {
		global $wp_styles;

		// Setup: Create WP_Styles object with non-versioned CSS
		$wp_styles = new \WP_Styles();
		$wp_styles->add( 'test-clean-1', 'https://example.com/style1.css' );
		$wp_styles->add( 'test-clean-2', 'https://example.com/style2.css' );

		// Mock that enqueue_scripts fired
		do_action( 'wp_enqueue_scripts' );

		$result = self::check();

		if ( null === $result ) {
			return array(
				'passed'  => true,
				'message' => 'Correctly returned null when no versioned CSS found',
			);
		}

		return array(
			'passed'  => false,
			'message' => 'Expected null for clean CSS, got array: ' . wp_json_encode( $result ),
		);
	}

	/**
	 * Test: With versioned CSS assets, should return array with correct fields.
	 *
	 * @return array Test result with 'passed', 'message' keys.
	 */
	public static function test_versioned_assets_detected(): array {
		global $wp_styles;

		// Setup: Create WP_Styles with versioned CSS
		$wp_styles = new \WP_Styles();
		$wp_styles->add( 'test-versioned-1', 'https://example.com/style1.css?ver=1.0' );
		$wp_styles->add( 'test-versioned-2', 'https://example.com/style2.css?ver=2.0' );
		$wp_styles->add( 'test-versioned-3', 'https://example.com/style3.css?ver=3.0' );
		$wp_styles->add( 'test-versioned-4', 'https://example.com/style4.css?ver=4.0' );

		do_action( 'wp_enqueue_scripts' );

		$result = self::check();

		if ( ! is_array( $result ) ) {
			return array(
				'passed'  => false,
				'message' => 'Expected array when versioned CSS found, got: ' . gettype( $result ),
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
		if ( 'asset-versions-css' !== $result['id'] ) {
			return array(
				'passed'  => false,
				'message' => "Expected id 'asset-versions-css', got '{$result['id']}'",
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
			'message' => 'Correctly detected versioned CSS and returned proper array structure',
		);
	}

	/**
	 * Test: When removal is enabled, should return null (skip).
	 *
	 * @return array Test result with 'passed', 'message' keys.
	 */
	public static function test_when_removal_enabled(): array {
		global $wp_styles;

		// Setup: Enable the option
		update_option( 'wpshadow_asset_version_removal_enabled', true );

		// Create WP_Styles with versioned CSS (would normally fail)
		$wp_styles = new \WP_Styles();
		$wp_styles->add( 'test-versioned', 'https://example.com/style.css?ver=1.0' );

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
	 * Test: With mixed (versioned and non-versioned) CSS, should count only versioned.
	 *
	 * @return array Test result with 'passed', 'message' keys.
	 */
	public static function test_mixed_assets(): array {
		global $wp_styles;

		// Setup: Mix of versioned and non-versioned
		$wp_styles = new \WP_Styles();
		$wp_styles->add( 'clean-1', 'https://example.com/clean1.css' );
		$wp_styles->add( 'versioned-1', 'https://example.com/versioned1.css?ver=1.0' );
		$wp_styles->add( 'clean-2', 'https://example.com/clean2.css' );
		$wp_styles->add( 'versioned-2', 'https://example.com/versioned2.css?ver=2.0' );

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
			'message' => 'Correctly counted only versioned CSS in mixed set',
		);
	}
}
