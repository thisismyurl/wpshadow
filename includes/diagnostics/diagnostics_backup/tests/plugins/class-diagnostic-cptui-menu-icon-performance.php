<?php
/**
 * CPT UI Menu Icon Performance Diagnostic
 *
 * CPT UI menu icons slowing admin.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.449.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CPT UI Menu Icon Performance Diagnostic Class
 *
 * @since 1.449.0000
 */
class Diagnostic_CptuiMenuIconPerformance extends Diagnostic_Base {

	protected static $slug = 'cptui-menu-icon-performance';
	protected static $title = 'CPT UI Menu Icon Performance';
	protected static $description = 'CPT UI menu icons slowing admin';
	protected static $family = 'performance';

	public static function check() {
		if ( ! defined( 'CPT_VERSION' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Verify menu icon URL format and optimization
		$cptui_post_types = get_option( 'cptui_post_types', array() );
		if ( ! empty( $cptui_post_types ) ) {
			foreach ( $cptui_post_types as $post_type ) {
				if ( ! empty( $post_type['menu_icon'] ) && strpos( $post_type['menu_icon'], 'data:image' ) === false && strpos( $post_type['menu_icon'], 'dashicons' ) === false ) {
					$issues[] = __( 'Non-optimized menu icon format detected', 'wpshadow' );
					break;
				}
			}
		}

		// Check 2: Verify icon caching strategy
		$icon_cache_enabled = get_option( 'cptui_icon_cache_enabled', false );
		if ( ! $icon_cache_enabled ) {
			$issues[] = __( 'Menu icon caching not enabled', 'wpshadow' );
		}

		// Check 3: Check for excessive icon sizes
		$icon_size_limit = 32; // KB
		if ( ! empty( $cptui_post_types ) ) {
			foreach ( $cptui_post_types as $post_type ) {
				if ( ! empty( $post_type['menu_icon'] ) && strpos( $post_type['menu_icon'], 'data:image' ) === 0 ) {
					$icon_size = strlen( base64_decode( substr( $post_type['menu_icon'], strpos( $post_type['menu_icon'], ',' ) + 1 ) ) );
					if ( $icon_size > ( $icon_size_limit * 1024 ) ) {
						$issues[] = __( 'Menu icon exceeds recommended size', 'wpshadow' );
						break;
					}
				}
			}
		}

		// Check 4: Verify SVG usage for scalability
		$svg_icons_count = 0;
		if ( ! empty( $cptui_post_types ) ) {
			foreach ( $cptui_post_types as $post_type ) {
				if ( ! empty( $post_type['menu_icon'] ) && strpos( $post_type['menu_icon'], 'svg' ) !== false ) {
					$svg_icons_count++;
				}
			}
			if ( $svg_icons_count === 0 && count( $cptui_post_types ) > 0 ) {
				$issues[] = __( 'SVG icons not utilized for better performance', 'wpshadow' );
			}
		}

		// Check 5: Check menu icon preload strategy
		$icon_preload_enabled = get_option( 'cptui_icon_preload', false );
		if ( ! $icon_preload_enabled ) {
			$issues[] = __( 'Menu icon preloading not configured', 'wpshadow' );
		}

		// Check 6: Verify admin menu performance optimization
		$admin_menu_cache = get_transient( 'cptui_admin_menu_cache' );
		if ( false === $admin_menu_cache ) {
			$issues[] = __( 'Admin menu caching not active', 'wpshadow' );
		}
		// Check transient support
		if ( ! function_exists( 'set_transient' ) ) {
			$issues[] = __( 'Transient functions unavailable', 'wpshadow' );
		}
		return null;
	}
}
