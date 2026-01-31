<?php
/**
 * Generatepress Premium Mobile Header Diagnostic
 *
 * Generatepress Premium Mobile Header needs optimization.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1299.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Generatepress Premium Mobile Header Diagnostic Class
 *
 * @since 1.1299.0000
 */
class Diagnostic_GeneratepressPremiumMobileHeader extends Diagnostic_Base {

	protected static $slug = 'generatepress-premium-mobile-header';
	protected static $title = 'Generatepress Premium Mobile Header';
	protected static $description = 'Generatepress Premium Mobile Header needs optimization';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! defined( 'GP_PREMIUM_VERSION' ) ) {
			return null;
		}
		
		$issues = array();

		// Check 1: Verify mobile menu is enabled
		$mobile_menu_enabled = get_option( 'generate_mobile_menu_enabled', '' );
		if ( 'enabled' !== $mobile_menu_enabled ) {
			$issues[] = __( 'Mobile menu not enabled', 'wpshadow' );
		}

		// Check 2: Check mobile breakpoint configuration
		$mobile_breakpoint = get_option( 'generate_mobile_header_breakpoint', 0 );
		if ( $mobile_breakpoint > 768 || $mobile_breakpoint === 0 ) {
			$issues[] = __( 'Mobile header breakpoint not optimally configured', 'wpshadow' );
		}

		// Check 3: Verify sticky mobile header
		$sticky_mobile = get_option( 'generate_sticky_mobile_header', '' );
		if ( 'enabled' !== $sticky_mobile ) {
			$issues[] = __( 'Sticky mobile header not enabled', 'wpshadow' );
		}

		// Check 4: Check mobile menu animation performance
		$menu_animation = get_option( 'generate_mobile_menu_animation', 'slide' );
		if ( 'slide' !== $menu_animation && 'fade' !== $menu_animation ) {
			$issues[] = __( 'Mobile menu animation not performance-optimized', 'wpshadow' );
		}

		// Check 5: Verify hamburger icon optimization
		$icon_type = get_option( 'generate_mobile_menu_icon_type', '' );
		if ( 'svg' !== $icon_type ) {
			$issues[] = __( 'Hamburger icon not using SVG for performance', 'wpshadow' );
		}

		// Check 6: Check mobile header caching
		$mobile_cache = get_transient( 'generate_mobile_header_cache' );
		if ( false === $mobile_cache ) {
			$issues[] = __( 'Mobile header caching not active', 'wpshadow' );
		}
		return null;
	}
}
