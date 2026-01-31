<?php
/**
 * Autoptimize Image Optimization Diagnostic
 *
 * Autoptimize Image Optimization not optimized.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.915.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Autoptimize Image Optimization Diagnostic Class
 *
 * @since 1.915.0000
 */
class Diagnostic_AutoptimizeImageOptimization extends Diagnostic_Base {

	protected static $slug = 'autoptimize-image-optimization';
	protected static $title = 'Autoptimize Image Optimization';
	protected static $description = 'Autoptimize Image Optimization not optimized';
	protected static $family = 'performance';

	public static function check() {
		if ( ! defined( 'AUTOPTIMIZE_PLUGIN_VERSION' ) ) {
			return null;
		}
		$issues = array();
		$optimize_images = get_option( 'autoptimize_optimize_images', 0 );
		if ( '0' === $optimize_images ) { $issues[] = 'image optimization disabled'; }
		$webp_enabled = get_option( 'autoptimize_enable_webp', 0 );
		if ( '0' === $webp_enabled ) { $issues[] = 'WebP conversion not enabled'; }
		$lazy_load = get_option( 'autoptimize_lazy_load_images', 0 );
		if ( '0' === $lazy_load ) { $issues[] = 'lazy loading disabled'; }
		$quality_level = get_option( 'autoptimize_image_quality', 82 );
		if ( $quality_level < 75 ) { $issues[] = "image quality too low ({$quality_level}%)"; }
		$optimize_thumbnails = get_option( 'autoptimize_optimize_thumbnails', 0 );
		if ( '0' === $optimize_thumbnails ) { $issues[] = 'thumbnail optimization disabled'; }
		$image_cache = get_option( 'autoptimize_image_cache_interval', 0 );
		if ( 0 === $image_cache ) { $issues[] = 'image cache not configured'; }
		if ( ! empty( $issues ) ) {
			return array( 'id' => self::$slug, 'title' => self::$title, 'description' => implode( ', ', $issues ), 'severity' => self::calculate_severity( min( 75, 50 + ( count( $issues ) * 4 ) ) ), 'threat_level' => min( 75, 50 + ( count( $issues ) * 4 ) ), 'auto_fixable' => false, 'kb_link' => 'https://wpshadow.com/kb/autoptimize-image-optimization' );
		}
		return null;
	}
}
