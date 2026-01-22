<?php
/**
 * Asset Versions - JavaScript Diagnostic
 *
 * @package WPShadow
 */

declare(strict_types=1);

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

		global $wp_scripts;

		if ( ! isset( $wp_scripts ) ) {
			wp_default_scripts( $wp_scripts );
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
}
