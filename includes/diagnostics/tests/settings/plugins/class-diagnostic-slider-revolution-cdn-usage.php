<?php
/**
 * Slider Revolution CDN Usage Diagnostic
 *
 * Slider Revolution not using CDN for assets.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.282.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Slider Revolution CDN Usage Diagnostic Class
 *
 * @since 1.282.0000
 */
class Diagnostic_SliderRevolutionCdnUsage extends Diagnostic_Base {

	protected static $slug = 'slider-revolution-cdn-usage';
	protected static $title = 'Slider Revolution CDN Usage';
	protected static $description = 'Slider Revolution not using CDN for assets';
	protected static $family = 'performance';

	public static function check() {
		if ( ! defined( 'RS_REVISION' ) ) {
			return null;
		}
		
		$issues = array();
		$threat_level = 0;

		// Get Slider Revolution settings
		$settings = get_option( 'revslider-global-settings', array() );

		// Check lazy loading
		$lazy_load = isset( $settings['lazyLoad'] ) ? $settings['lazyLoad'] : 'off';
		if ( $lazy_load === 'off' ) {
			$issues[] = 'lazy_loading_disabled';
			$threat_level += 15;
		}

		// Check if using local assets vs CDN
		global $wpdb;
		$sliders = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}revslider_sliders" );
		if ( $sliders ) {
			$using_external_images = false;
			foreach ( $sliders as $slider ) {
				$params = maybe_unserialize( $slider->params );
				if ( isset( $params['source_type'] ) && $params['source_type'] === 'external' ) {
					$using_external_images = true;
					break;
				}
			}
			if ( ! $using_external_images ) {
				$issues[] = 'not_using_cdn';
				$threat_level += 10;
			}
		}

		// Check image optimization
		$optimize_images = isset( $settings['enable_webp'] ) ? $settings['enable_webp'] : false;
		if ( ! $optimize_images ) {
			$issues[] = 'image_optimization_disabled';
			$threat_level += 15;
		}

		// Check JS/CSS minification
		$load_js = isset( $settings['js_to_footer'] ) ? $settings['js_to_footer'] : 'off';
		if ( $load_js === 'off' ) {
			$issues[] = 'js_not_optimized';
			$threat_level += 10;
		}

		// Check preload
		$use_preload = isset( $settings['use_preload'] ) ? $settings['use_preload'] : false;
		if ( ! $use_preload ) {
			$issues[] = 'preload_disabled';
			$threat_level += 5;
		}

		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %s: list of CDN/optimization issues */
				__( 'Slider Revolution performance optimization has issues: %s. This causes slower page loads and poor mobile performance.', 'wpshadow' ),
				implode( ', ', array_map( function( $issue ) {
					return ucwords( str_replace( '_', ' ', $issue ) );
				}, $issues ) )
			);

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => $description,
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/slider-revolution-cdn-usage',
			);
		}
		
		return null;
	}
}
