<?php
/**
 * AddToAny Button Optimization Diagnostic
 *
 * AddToAny buttons not optimized.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.435.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * AddToAny Button Optimization Diagnostic Class
 *
 * @since 1.435.0000
 */
class Diagnostic_AddtoanyButtonOptimization extends Diagnostic_Base {

	protected static $slug = 'addtoany-button-optimization';
	protected static $title = 'AddToAny Button Optimization';
	protected static $description = 'AddToAny buttons not optimized';
	protected static $family = 'performance';

	public static function check() {
		if ( ! function_exists( 'A2A_SHARE_SAVE_init' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Too many buttons displayed.
		$button_count = get_option( 'addtoany_button_count', 0 );
		if ( $button_count > 10 ) {
			$issues[] = "{$button_count} share buttons displayed (consider showing fewer for better performance)";
		}

		// Check 2: Async loading enabled.
		$async_loading = get_option( 'addtoany_async', '1' );
		if ( '0' === $async_loading ) {
			$issues[] = 'AddToAny script loading synchronously (blocks page rendering)';
		}

		// Check 3: Icon style optimization.
		$icon_style = get_option( 'addtoany_icon_style', 'default' );
		if ( 'svg' !== $icon_style ) {
			$issues[] = "using {$icon_style} icons (SVG recommended for better performance)";
		}

		// Check 4: Script caching.
		$cache_enabled = get_option( 'addtoany_cache', '0' );
		if ( '0' === $cache_enabled ) {
			$issues[] = 'AddToAny cache not enabled (scripts loaded fresh each time)';
		}

		// Check 5: Custom button images.
		$custom_icons = get_option( 'addtoany_custom_icons', array() );
		if ( ! empty( $custom_icons ) ) {
			foreach ( $custom_icons as $icon_url ) {
				if ( ! empty( $icon_url ) && false === strpos( $icon_url, '.svg' ) ) {
					$issues[] = 'custom icons using raster images (use SVG for better scaling)';
					break;
				}
			}
		}

		// Check 6: Script position.
		global $wp_scripts;
		if ( isset( $wp_scripts->registered['addtoany'] ) ) {
			$script_data = $wp_scripts->registered['addtoany'];
			if ( empty( $script_data->extra['group'] ) || 1 !== $script_data->extra['group'] ) {
				$issues[] = 'AddToAny script loaded in header (should load in footer)';
			}
		}

		if ( ! empty( $issues ) ) {
			$threat_level = min( 75, 45 + ( count( $issues ) * 6 ) );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'AddToAny button optimization issues: ' . implode( ', ', $issues ),
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/addtoany-button-optimization',
			);
		}

		return null;
	}
}
