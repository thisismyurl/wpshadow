<?php
/**
 * Slider Revolution Lazy Loading Diagnostic
 *
 * Slider Revolution images not lazy loaded.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.281.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Slider Revolution Lazy Loading Diagnostic Class
 *
 * @since 1.281.0000
 */
class Diagnostic_SliderRevolutionLazyLoading extends Diagnostic_Base {

	protected static $slug = 'slider-revolution-lazy-loading';
	protected static $title = 'Slider Revolution Lazy Loading';
	protected static $description = 'Slider Revolution images not lazy loaded';
	protected static $family = 'performance';

	public static function check() {
		if ( ! defined( 'RS_REVISION' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Lazy loading enabled globally
		$lazy_enabled = get_option( 'revslider_lazy_loading', false );
		if ( ! $lazy_enabled ) {
			$issues[] = 'Lazy loading not enabled';
		}
		
		// Check 2: Native lazy load support
		$native_lazy = get_option( 'revslider_native_lazy_load', false );
		if ( ! $native_lazy ) {
			$issues[] = 'Native lazy load not enabled';
		}
		
		// Check 3: Progressive loading enabled
		$progressive = get_option( 'revslider_progressive_loading', false );
		if ( ! $progressive ) {
			$issues[] = 'Progressive loading disabled';
		}
		
		// Check 4: Image preloading configured
		$preloading = get_option( 'revslider_image_preloading', false );
		if ( ! $preloading ) {
			$issues[] = 'Image preloading not configured';
		}
		
		// Check 5: Background lazy loading
		$bg_lazy = get_option( 'revslider_background_lazy_loading', false );
		if ( ! $bg_lazy ) {
			$issues[] = 'Background lazy loading disabled';
		}
		
		// Check 6: Video lazy loading
		$video_lazy = get_option( 'revslider_video_lazy_loading', false );
		if ( ! $video_lazy ) {
			$issues[] = 'Video lazy loading disabled';
		}
		
		if ( ! empty( $issues ) ) {
			$threat_level = min( 60, 30 + ( count( $issues ) * 5 ) );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'Slider Revolution lazy loading issues: ' . implode( ', ', $issues ),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/slider-revolution-lazy-loading',
			);
		}
		// Check transient support
		if ( ! function_exists( 'set_transient' ) ) {
			$issues[] = __( 'Transient functions unavailable', 'wpshadow' );
		}
		return null;
	}
}
