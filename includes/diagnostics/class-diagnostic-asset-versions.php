<?php
declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Diagnostic_Asset_Versions extends Diagnostic_Base {

	protected function get_id(): string {
		return 'asset-versions';
	}

	protected function get_title(): string {
		return __( 'Asset Version Strings', 'wpshadow' );
	}

	protected function get_description(): string {
		return __( 'Checks for version query strings (?ver=) on CSS and JavaScript files that can be removed to improve caching.', 'wpshadow' );
	}

	protected function get_category(): string {
		return 'performance';
	}

	protected function get_severity(): string {
		return 'low';
	}

	protected function is_auto_fixable(): bool {
		return true;
	}

	public function check(): ?array {
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
			'finding_id'   => $this->get_id(),
			'title'        => $this->get_title(),
			'description'  => sprintf(
				__( 'Found %d assets with version query strings (?ver=) that could be removed. Examples: %s', 'wpshadow' ),
				$versioned_assets,
				implode( ', ', $sample_assets )
			),
			'category'     => $this->get_category(),
			'severity'     => $this->get_severity(),
			'threat_level' => 15,
			'auto_fixable' => $this->is_auto_fixable(),
			'timestamp'    => current_time( 'mysql' ),
		);
	}
}
