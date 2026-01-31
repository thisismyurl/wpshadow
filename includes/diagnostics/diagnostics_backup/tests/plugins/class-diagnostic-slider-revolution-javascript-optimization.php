<?php
/**
 * Slider Revolution JavaScript Diagnostic
 *
 * Slider Revolution JavaScript not optimized.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.283.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Slider Revolution JavaScript Diagnostic Class
 *
 * @since 1.283.0000
 */
class Diagnostic_SliderRevolutionJavascriptOptimization extends Diagnostic_Base {

	protected static $slug = 'slider-revolution-javascript-optimization';
	protected static $title = 'Slider Revolution JavaScript';
	protected static $description = 'Slider Revolution JavaScript not optimized';
	protected static $family = 'performance';

	public static function check() {
		if ( ! defined( 'RS_REVISION' ) ) {
			return null;
		}
		
		// Check if Slider Revolution is active
		if ( ! defined( 'RS_REVISION' ) && ! class_exists( 'RevSliderFront' ) ) {
			return null;
		}

		$issues = array();
		$threat_level = 0;

		global $wpdb;

		// Check sliders
		$sliders = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}revslider_sliders" );
		
		if ( empty( $sliders ) ) {
			return null;
		}

		// Check script minification
		$minify_js = get_option( 'revslider-minify-js', 'off' );
		if ( $minify_js === 'off' ) {
			$issues[] = 'javascript_minification_disabled';
			$threat_level += 25;
		}

		// Check conditional loading
		$load_all_pages = get_option( 'revslider-output-type', 'footer' );
		if ( $load_all_pages === 'footer' ) {
			// Scripts load on all pages
			$issues[] = 'scripts_load_globally';
			$threat_level += 20;
		}

		// Check async loading
		$async_js = get_option( 'revslider-js-async', 'off' );
		if ( $async_js === 'off' ) {
			$issues[] = 'async_loading_disabled';
			$threat_level += 15;
		}

		// Check inline scripts
		$wait_for_fonts = get_option( 'revslider-wait-for-fonts', 'on' );
		if ( $wait_for_fonts === 'on' ) {
			$issues[] = 'blocking_font_loading';
			$threat_level += 15;
		}

		// Check dependency loading
		$jquery_cdn = get_option( 'revslider-jquery-cdn', 'off' );
		if ( $jquery_cdn === 'off' ) {
			$issues[] = 'not_using_cdn_for_dependencies';
			$threat_level += 10;
		}

		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %s: list of optimization issues */
				__( 'Slider Revolution JavaScript has performance issues: %s. This slows page loads and reduces performance scores.', 'wpshadow' ),
				implode( ', ', array_map( function( $issue ) {
					return ucwords( str_replace( '_', ' ', $issue ) );
				}, $issues ) )
			);

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => $description,
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/slider-revolution-javascript-optimization',
			);
		}
		
		return null;
	}
}
