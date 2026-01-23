<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Diagnostic_Asset_Versions extends Diagnostic_Base {

	protected static $slug = 'asset-versions';
	protected static $title = 'Asset Version Strings';
	protected static $description = 'Checks for version query strings (?ver=) on CSS and JavaScript files that can be removed to improve caching.';

	public static function check(): ?array {
		if ( get_option( 'wpshadow_asset_version_removal_enabled', false ) ) {
			return null;
		}

		global $wp_scripts, $wp_styles;

		if ( ! isset( $wp_scripts, $wp_styles ) ) {
			wp_default_scripts( $wp_scripts );
			wp_default_styles( $wp_styles );
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
			'finding_id'   => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				__( 'Found %d assets with version query strings (?ver=) that could be removed. Examples: %s', 'wpshadow' ),
				$versioned_assets,
				implode( ', ', $sample_assets )
			),
			'category'     => 'performance',
			'severity'     => 'low',
			'threat_level' => 15,
			'auto_fixable' => true,
			'timestamp'    => current_time( 'mysql' ),
		);
	}



	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Asset Version Strings
	 * Slug: asset-versions
	 * 
	 * Test Purpose:
	 * - Verify that check() method returns the correct result based on site state
	 * - PASS: check() returns NULL when diagnostic condition is NOT met (site is healthy)
	 * - FAIL: check() returns array when diagnostic condition IS met (issue found)
	 * - Description: Checks for version query strings (?ver=) on CSS and JavaScript files that can be removed to improve caching.
	 *
	 * @return array {
	 *     @type bool   $passed  Whether the test passed
	 *     @type string $message Human-readable test result message
	 * }
	 */
	public static function test_live_asset_versions(): array {
		global $wp_scripts, $wp_styles;

		$result = self::check();

		if ( ! isset( $wp_scripts, $wp_styles ) ) {
			wp_default_scripts( $wp_scripts );
			wp_default_styles( $wp_styles );
		}

		$versioned_assets = 0;
		if ( isset( $wp_scripts->registered ) ) {
			foreach ( $wp_scripts->registered as $script ) {
				if ( is_string( $script->src ) && false !== strpos( $script->src, '?ver=' ) ) {
					$versioned_assets++;
				}
			}
		}

		if ( isset( $wp_styles->registered ) ) {
			foreach ( $wp_styles->registered as $style ) {
				if ( is_string( $style->src ) && false !== strpos( $style->src, '?ver=' ) ) {
					$versioned_assets++;
				}
			}
		}

		$removal_enabled = (bool) get_option( 'wpshadow_asset_version_removal_enabled', false );
		$has_issue       = ( ! $removal_enabled && $versioned_assets > 0 );

		$diagnostic_found_issue = is_array( $result );
		$test_passes            = ( $has_issue === $diagnostic_found_issue );

		$message = $test_passes
			? 'Asset version string check matches site state'
			: sprintf(
				'Mismatch: expected %s but diagnostic returned %s (versioned assets: %d, removal_enabled: %s)',
				$has_issue ? 'issue' : 'no issue',
				$diagnostic_found_issue ? 'issue' : 'no issue',
				$versioned_assets,
				$removal_enabled ? 'yes' : 'no'
			);

		return array(
			'passed'  => $test_passes,
			'message' => $message,
		);
	}

}
