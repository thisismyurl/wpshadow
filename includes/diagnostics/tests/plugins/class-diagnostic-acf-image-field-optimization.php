<?php
/**
 * ACF Image Field Optimization Diagnostic
 *
 * ACF image fields not optimized.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.453.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * ACF Image Field Optimization Diagnostic Class
 *
 * @since 1.453.0000
 */
class Diagnostic_AcfImageFieldOptimization extends Diagnostic_Base {

	protected static $slug = 'acf-image-field-optimization';
	protected static $title = 'ACF Image Field Optimization';
	protected static $description = 'ACF image fields not optimized';
	protected static $family = 'performance';

	public static function check() {
		if ( ! class_exists( 'ACF' ) ) {
			return null;
		}
		
		global $wpdb;
		$issues = array();
		
		// Check 1: Image fields count
		$image_fields = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->posts} 
				WHERE post_type = 'acf-field' AND post_excerpt = %s",
				'image'
			)
		);
		
		if ( $image_fields === 0 ) {
			return null; // No image fields
		}
		
		// Check 2: Return format
		$array_format_fields = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->posts} 
				WHERE post_type = 'acf-field' 
				AND post_excerpt = %s 
				AND post_content LIKE %s",
				'image',
				'%return_format":"array%'
			)
		);
		
		if ( $array_format_fields < ( $image_fields * 0.5 ) ) {
			$issues[] = __( 'Most fields return URL (no size control)', 'wpshadow' );
		}
		
		// Check 3: Preview size
		$preview_size = get_option( 'acf_image_preview_size', 'thumbnail' );
		if ( 'full' === $preview_size ) {
			$issues[] = __( 'Full-size previews (slow admin)', 'wpshadow' );
		}
		
		// Check 4: Lazy loading
		$lazy_load = get_option( 'acf_enable_lazy_loading', 'no' );
		if ( 'no' === $lazy_load ) {
			$issues[] = __( 'No lazy loading (all images load immediately)', 'wpshadow' );
		}
		
		// Check 5: Image optimization
		$auto_optimize = get_option( 'acf_auto_optimize_images', 'no' );
		if ( 'no' === $auto_optimize ) {
			$issues[] = __( 'No auto-optimization (large file sizes)', 'wpshadow' );
		}
		
		// Check 6: CDN integration
		$cdn_enabled = get_option( 'acf_cdn_enabled', 'no' );
		if ( 'no' === $cdn_enabled && $image_fields > 20 ) {
			$issues[] = __( 'No CDN (slow image delivery)', 'wpshadow' );
		}
		
		if ( empty( $issues ) ) {
			return null;
		}
		
		$threat_level = 45;
		if ( count( $issues ) >= 4 ) {
			$threat_level = 58;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 52;
		}
		
		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %s: list of ACF image optimization issues */
				__( 'ACF image fields have %d optimization issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => $threat_level,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/acf-image-field-optimization',
		);
	}
}
