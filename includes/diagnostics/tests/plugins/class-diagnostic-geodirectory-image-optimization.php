<?php
/**
 * GeoDirectory Image Optimization Diagnostic
 *
 * GeoDirectory images not optimized.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.555.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * GeoDirectory Image Optimization Diagnostic Class
 *
 * @since 1.555.0000
 */
class Diagnostic_GeodirectoryImageOptimization extends Diagnostic_Base {

	protected static $slug = 'geodirectory-image-optimization';
	protected static $title = 'GeoDirectory Image Optimization';
	protected static $description = 'GeoDirectory images not optimized';
	protected static $family = 'performance';

	public static function check() {
		// Check for GeoDirectory (uses wpbdp as base directory plugin)
		if ( ! function_exists( 'geodir_get_version' ) && ! defined( 'GEODIRECTORY_VERSION' ) ) {
			return null;
		}
		
		global $wpdb;
		$issues = array();
		
		// Check 1: Lazy loading enabled
		$lazy_loading = get_option( 'geodir_lazy_load', false );
		if ( ! $lazy_loading ) {
			$issues[] = __( 'Image lazy loading not enabled', 'wpshadow' );
		}
		
		// Check 2: Image compression quality
		$compression = get_option( 'geodir_image_quality', 100 );
		if ( $compression > 85 ) {
			$issues[] = sprintf( __( 'Image quality set to %d%% (recommend 75-85%%)', 'wpshadow' ), $compression );
		}
		
		// Check 3: Count GeoDirectory attachments
		$geo_images = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->posts} p
				 INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
				 WHERE p.post_type = %s AND pm.meta_key LIKE %s",
				'attachment',
				'geodir_%'
			)
		);
		
		if ( $geo_images === 0 ) {
			return null;
		}
		
		// Check 4: Thumbnail generation
		$thumbnail_sizes = get_option( 'geodir_image_sizes', array() );
		if ( empty( $thumbnail_sizes ) ) {
			$issues[] = __( 'Custom thumbnail sizes not configured', 'wpshadow' );
		}
		
		// Check 5: WebP support
		$webp_enabled = get_option( 'geodir_webp_enabled', false );
		if ( ! $webp_enabled && $geo_images > 100 ) {
			$issues[] = sprintf( __( '%d images without WebP format (large file sizes)', 'wpshadow' ), $geo_images );
		}
		
		// Check 6: CDN for images
		$cdn_url = get_option( 'geodir_cdn_url', '' );
		if ( empty( $cdn_url ) && $geo_images > 500 ) {
			$issues[] = __( 'CDN not configured for large image library', 'wpshadow' );
		}
		
		if ( empty( $issues ) ) {
			return null;
		}
		
		$threat_level = 50;
		if ( count( $issues ) >= 4 ) {
			$threat_level = 62;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 56;
		}
		
		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %s: list of optimization issues */
				__( 'GeoDirectory images have %d optimization issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => self::calculate_severity( $threat_level ),
			'threat_level' => $threat_level,
			'auto_fixable' => true,
			'kb_link'     => 'https://wpshadow.com/kb/geodirectory-image-optimization',
		);
	}
}
