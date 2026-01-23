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

}