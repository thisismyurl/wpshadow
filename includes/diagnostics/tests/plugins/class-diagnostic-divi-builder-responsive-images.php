<?php
/**
 * Divi Builder Responsive Images Diagnostic
 *
 * Divi images not responsive.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.355.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Divi Builder Responsive Images Diagnostic Class
 *
 * @since 1.355.0000
 */
class Diagnostic_DiviBuilderResponsiveImages extends Diagnostic_Base {

	protected static $slug = 'divi-builder-responsive-images';
	protected static $title = 'Divi Builder Responsive Images';
	protected static $description = 'Divi images not responsive';
	protected static $family = 'performance';

	public static function check() {
		if ( ! function_exists( 'et_divi_fonts_url' ) ) {
			return null;
		}
		
		$issues = array();
		$threat_level = 0;

		// Get Divi theme options
		$divi_options = get_option( 'et_divi', array() );

		// Check responsive images support
		$responsive_images = isset( $divi_options['divi_responsive_images'] ) ? $divi_options['divi_responsive_images'] : false;
		if ( ! $responsive_images ) {
			$issues[] = 'responsive_images_disabled';
			$threat_level += 25;
		}

		// Check image optimization
		$optimize_images = isset( $divi_options['divi_optimize_images'] ) ? $divi_options['divi_optimize_images'] : false;
		if ( ! $optimize_images ) {
			$issues[] = 'image_optimization_disabled';
			$threat_level += 20;
		}

		// Check lazy loading
		$lazy_load = isset( $divi_options['divi_lazy_load_images'] ) ? $divi_options['divi_lazy_load_images'] : false;
		if ( ! $lazy_load ) {
			$issues[] = 'lazy_loading_disabled';
			$threat_level += 15;
		}

		// Check if using Divi's image sizes
		global $wpdb;
		$posts_with_images = $wpdb->get_var(
			"SELECT COUNT(DISTINCT post_id) FROM {$wpdb->postmeta} 
			 WHERE meta_key = '_et_pb_use_background_color_gradient'"
		);

		if ( $posts_with_images > 0 && ! $responsive_images ) {
			$issues[] = 'posts_using_non_responsive_images';
			$threat_level += 15;
		}

		// Check image sizes registered
		$registered_sizes = wp_get_registered_image_subsizes();
		$has_divi_sizes = isset( $registered_sizes['et-pb-post-main-image'] );
		if ( ! $has_divi_sizes ) {
			$issues[] = 'divi_image_sizes_not_registered';
			$threat_level += 10;
		}

		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %s: list of responsive image issues */
				__( 'Divi Builder responsive images have problems: %s. This causes slow mobile load times and poor Core Web Vitals scores.', 'wpshadow' ),
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
				'kb_link'     => 'https://wpshadow.com/kb/divi-builder-responsive-images',
			);
		}
		
		return null;
	}
}
