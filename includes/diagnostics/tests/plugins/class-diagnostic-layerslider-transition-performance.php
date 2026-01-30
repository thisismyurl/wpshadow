<?php
/**
 * LayerSlider Transitions Diagnostic
 *
 * LayerSlider transitions causing lag.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.288.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * LayerSlider Transitions Diagnostic Class
 *
 * @since 1.288.0000
 */
class Diagnostic_LayersliderTransitionPerformance extends Diagnostic_Base {

	protected static $slug = 'layerslider-transition-performance';
	protected static $title = 'LayerSlider Transitions';
	protected static $description = 'LayerSlider transitions causing lag';
	protected static $family = 'performance';

	public static function check() {
		if ( ! defined( 'LS_PLUGIN_VERSION' ) ) {
			return null;
		}
		
		// Check if LayerSlider is active
		if ( ! defined( 'LS_PLUGIN_VERSION' ) && ! class_exists( 'LS_Sliders' ) ) {
			return null;
		}

		$issues = array();
		$threat_level = 0;

		global $wpdb;

		// Check sliders table
		$sliders_table = $wpdb->prefix . 'layerslider';
		$table_exists = $wpdb->get_var( "SHOW TABLES LIKE '{$sliders_table}'" );
		
		if ( ! $table_exists ) {
			return null;
		}

		// Check slider count and complexity
		$sliders = $wpdb->get_results( "SELECT id, data FROM {$sliders_table}" );
		
		if ( ! empty( $sliders ) ) {
			foreach ( $sliders as $slider ) {
				$data = maybe_unserialize( $slider->data );
				$layers = isset( $data['layers'] ) ? $data['layers'] : array();
				
				// Check layer count per slide
				foreach ( $layers as $layer ) {
					$sublayers = isset( $layer['sublayers'] ) ? $layer['sublayers'] : array();
					if ( count( $sublayers ) > 15 ) {
						$issues[] = 'excessive_layers_per_slide';
						$threat_level += 20;
						break 2;
					}
				}
			}
		}

		// Check performance settings
		$lazy_load = get_option( 'layerslider_lazy_load', 0 );
		if ( ! $lazy_load ) {
			$issues[] = 'lazy_loading_disabled';
			$threat_level += 20;
		}

		// Check GPU acceleration
		$use_srcset = get_option( 'layerslider_use_srcset', 0 );
		if ( ! $use_srcset ) {
			$issues[] = 'responsive_images_disabled';
			$threat_level += 15;
		}

		// Check script optimization
		$optimize_code = get_option( 'layerslider_optimize_code', 0 );
		if ( ! $optimize_code ) {
			$issues[] = 'code_optimization_disabled';
			$threat_level += 15;
		}

		// Check conditional loading
		$conditional_script = get_option( 'layerslider_conditional_script_loading', 0 );
		if ( ! $conditional_script ) {
			$issues[] = 'scripts_load_on_all_pages';
			$threat_level += 15;
		}

		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %s: list of performance issues */
				__( 'LayerSlider transitions have performance problems: %s. This causes lag and slow page loads.', 'wpshadow' ),
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
				'kb_link'     => 'https://wpshadow.com/kb/layerslider-transition-performance',
			);
		}
		
		return null;
	}
}
