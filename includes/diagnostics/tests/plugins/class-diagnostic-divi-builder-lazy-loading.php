<?php
/**
 * Divi Builder Lazy Loading Diagnostic
 *
 * Divi lazy loading not enabled.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.356.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Divi Builder Lazy Loading Diagnostic Class
 *
 * @since 1.356.0000
 */
class Diagnostic_DiviBuilderLazyLoading extends Diagnostic_Base {

	protected static $slug = 'divi-builder-lazy-loading';
	protected static $title = 'Divi Builder Lazy Loading';
	protected static $description = 'Divi lazy loading not enabled';
	protected static $family = 'performance';

	public static function check() {
		if ( ! function_exists( 'et_divi_fonts_url' ) ) {
			return null;
		}
		
		$issues = array();

		// Check 1: Verify Divi lazy loading is enabled
		$lazy_load_enabled = get_option( 'et_pb_static_lazy_loading', '' );
		if ( 'on' !== $lazy_load_enabled ) {
			$issues[] = __( 'Divi lazy loading not enabled', 'wpshadow' );
		}

		// Check 2: Check native browser lazy loading
		$native_lazy_load = get_option( 'et_pb_native_lazy_loading', '' );
		if ( 'on' !== $native_lazy_load ) {
			$issues[] = __( 'Native browser lazy loading not enabled', 'wpshadow' );
		}

		// Check 3: Verify lazy load threshold
		$threshold = get_option( 'et_pb_lazy_load_threshold', 200 );
		if ( $threshold < 100 ) {
			$issues[] = __( 'Lazy load threshold too aggressive', 'wpshadow' );
		}

		// Check 4: Check video lazy loading
		$video_lazy_load = get_option( 'et_pb_video_lazy_loading', '' );
		if ( 'on' !== $video_lazy_load ) {
			$issues[] = __( 'Video lazy loading not enabled', 'wpshadow' );
		}

		// Check 5: Verify background image lazy loading
		$bg_lazy_load = get_option( 'et_pb_background_lazy_loading', '' );
		if ( 'on' !== $bg_lazy_load ) {
			$issues[] = __( 'Background image lazy loading not enabled', 'wpshadow' );
		}

		// Check 6: Check LCP image optimization
		$lcp_optimization = get_option( 'et_pb_lcp_lazy_load_skip', '' );
		if ( 'on' !== $lcp_optimization ) {
			$issues[] = __( 'LCP image lazy load skip not configured', 'wpshadow' );
		}
		// Check transient support
		if ( ! function_exists( 'set_transient' ) ) {
			$issues[] = __( 'Transient functions unavailable', 'wpshadow' );
		}
		return null;
	}
}
