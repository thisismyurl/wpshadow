<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Diagnostics\Diagnostic_Base;

/**
 * Diagnostic: CSS Asset Versions
 * Checks for version query strings (?ver=) on CSS files that can be removed
 */
class Test_Performance_Asset_Versions_CSS extends Diagnostic_Base {


	/**
	 * Run the diagnostic check
	 *
	 * @return array|null Array with issue details or null if healthy
	 */
	public static function check(): ?array {
		if ( ! did_action( 'wp_enqueue_scripts' ) && ! did_action( 'admin_enqueue_scripts' ) ) {
			return null;
		}

		global $wp_styles;

		if ( ! isset( $wp_styles ) || ! ( $wp_styles instanceof \WP_Styles ) ) {
			return null;
		}

		$versioned_assets = 0;
		foreach ( $wp_styles->registered as $handle => $style ) {
			if ( is_string( $style->src ) && strpos( $style->src, '?ver=' ) !== false ) {
				++$versioned_assets;
			}
		}

		if ( $versioned_assets === 0 ) {
			return null;
		}

		return array(
			'id'           => 'asset-versions-css',
			'title'        => 'CSS Asset Version Strings Found',
			'threat_level' => 8,
			'description'  => sprintf(
				'Found %d CSS files with version query strings (?ver=) that could be removed.',
				$versioned_assets
			),
		);
	}

	/**
	 * Test the diagnostic check
	 *
	 * @return array Test result with passed status and message
	 */
	public static function test_live_asset_versions_css(): array {
		$result = self::check();
		return array(
			'passed'  => $result === null,
			'message' => $result === null ? 'CSS asset versions are clean' : 'CSS version query strings detected',
		);
	}
}
