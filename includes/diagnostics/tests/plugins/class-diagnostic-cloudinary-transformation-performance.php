<?php
/**
 * Cloudinary Transformation Performance Diagnostic
 *
 * Cloudinary Transformation Performance detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.785.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Cloudinary Transformation Performance Diagnostic Class
 *
 * @since 1.785.0000
 */
class Diagnostic_CloudinaryTransformationPerformance extends Diagnostic_Base {

	protected static $slug = 'cloudinary-transformation-performance';
	protected static $title = 'Cloudinary Transformation Performance';
	protected static $description = 'Cloudinary Transformation Performance detected';
	protected static $family = 'performance';

	public static function check() {
		if ( ! class_exists( 'Cloudinary' ) && ! defined( 'CLOUDINARY_VERSION' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Transformation settings optimized
		$auto_format = get_option( 'cloudinary_auto_format', '0' );
		if ( '0' === $auto_format ) {
			$issues[] = 'automatic format optimization disabled (missing WebP/AVIF)';
		}

		// Check 2: Quality settings
		$quality = get_option( 'cloudinary_quality', 'auto' );
		if ( 'auto' !== $quality && ( $quality > 85 || $quality < 60 ) ) {
			$issues[] = "quality set to {$quality}% (recommend 'auto' or 70-85%)";
		}

		// Check 3: Lazy loading enabled
		$lazy_load = get_option( 'cloudinary_lazy_load', '0' );
		if ( '0' === $lazy_load ) {
			$issues[] = 'lazy loading disabled (initial page load slower)';
		}

		// Check 4: Responsive images
		$responsive = get_option( 'cloudinary_responsive', '0' );
		if ( '0' === $responsive ) {
			$issues[] = 'responsive images disabled (mobile users download full-size)';
		}

		// Check 5: CDN delivery
		global $wpdb;
		$local_images = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->postmeta}
			 WHERE meta_key = '_wp_attached_file'
			 AND meta_value NOT LIKE '%cloudinary%'"
		);
		$total_images = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'attachment' AND post_mime_type LIKE 'image/%'"
		);
		if ( $local_images > ( $total_images * 0.3 ) ) {
			$percent = round( ( $local_images / $total_images ) * 100 );
			$issues[] = "{$percent}% of images still served locally (not using CDN)";
		}

		// Check 6: Transformation caching
		$cache_transformations = get_option( 'cloudinary_cache_transformations', '1' );
		if ( '0' === $cache_transformations ) {
			$issues[] = 'transformation caching disabled (regenerating on each request)';
		}

		if ( ! empty( $issues ) ) {
			$threat_level = min( 75, 45 + ( count( $issues ) * 6 ) );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'Cloudinary transformation performance issues: ' . implode( ', ', $issues ),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/cloudinary-transformation-performance',
			);
		}

		return null;
	}
}
