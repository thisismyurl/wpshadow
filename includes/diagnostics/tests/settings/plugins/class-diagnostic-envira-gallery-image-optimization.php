<?php
/**
 * Envira Gallery Image Optimization Diagnostic
 *
 * Envira Gallery images not optimized.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.489.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Envira Gallery Image Optimization Diagnostic Class
 *
 * @since 1.489.0000
 */
class Diagnostic_EnviraGalleryImageOptimization extends Diagnostic_Base {

	protected static $slug = 'envira-gallery-image-optimization';
	protected static $title = 'Envira Gallery Image Optimization';
	protected static $description = 'Envira Gallery images not optimized';
	protected static $family = 'performance';

	public static function check() {
		if ( ! class_exists( 'Envira_Gallery' ) ) {
			return null;
		}
		
		$issues = array();
		$threat_level = 0;

		// Get Envira settings
		$settings = get_option( 'envira_gallery_settings', array() );

		// Check lazy loading
		$lazy_load = isset( $settings['lazy_loading'] ) ? $settings['lazy_loading'] : false;
		if ( ! $lazy_load ) {
			$issues[] = 'lazy_loading_disabled';
			$threat_level += 15;
		}

		// Check responsive images
		$responsive = isset( $settings['responsive_images'] ) ? $settings['responsive_images'] : false;
		if ( ! $responsive ) {
			$issues[] = 'responsive_images_disabled';
			$threat_level += 15;
		}

		// Check image compression
		$compression = isset( $settings['image_quality'] ) ? $settings['image_quality'] : 100;
		if ( $compression > 85 ) {
			$issues[] = 'high_image_quality';
			$threat_level += 10;
		}

		// Check thumbnail size configuration
		global $wpdb;
		$galleries = $wpdb->get_results( "SELECT * FROM {$wpdb->posts} WHERE post_type = 'envira'" );
		if ( $galleries ) {
			$large_images = 0;
			foreach ( $galleries as $gallery ) {
				$config = get_post_meta( $gallery->ID, '_eg_gallery_data', true );
				if ( isset( $config['config']['dimensions_width'] ) && $config['config']['dimensions_width'] > 1920 ) {
					$large_images++;
				}
			}
			if ( $large_images > 0 ) {
				$issues[] = 'oversized_images';
				$threat_level += 15;
			}
		}

		// Check CDN usage
		$cdn = isset( $settings['cdn_url'] ) ? $settings['cdn_url'] : '';
		if ( empty( $cdn ) ) {
			$issues[] = 'cdn_not_configured';
			$threat_level += 10;
		}

		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %s: list of image optimization issues */
				__( 'Envira Gallery image optimization needs improvement: %s. This causes slower page loads and higher bandwidth usage.', 'wpshadow' ),
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
				'kb_link'     => 'https://wpshadow.com/kb/envira-gallery-image-optimization',
			);
		}
		
		return null;
	}
}
