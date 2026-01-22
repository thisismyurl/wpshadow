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
}
