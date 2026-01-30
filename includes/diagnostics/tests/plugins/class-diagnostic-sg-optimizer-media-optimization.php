<?php
/**
 * Sg Optimizer Media Optimization Diagnostic
 *
 * Sg Optimizer Media Optimization not optimized.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.908.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sg Optimizer Media Optimization Diagnostic Class
 *
 * @since 1.908.0000
 */
class Diagnostic_SgOptimizerMediaOptimization extends Diagnostic_Base {

	protected static $slug = 'sg-optimizer-media-optimization';
	protected static $title = 'Sg Optimizer Media Optimization';
	protected static $description = 'Sg Optimizer Media Optimization not optimized';
	protected static $family = 'performance';

	public static function check() {
		if ( ! defined( 'SG_CACHEPRESS_VERSION' ) && ! class_exists( 'SiteGround_Optimizer\Optimizer' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Verify image optimization is enabled
		$image_opt = get_option( 'siteground_optimizer_optimize_images', 0 );
		if ( ! $image_opt ) {
			$issues[] = 'Image optimization not enabled';
		}
		
		// Check 2: Check for WebP conversion
		$webp = get_option( 'siteground_optimizer_webp', 0 );
		if ( ! $webp ) {
			$issues[] = 'WebP conversion not enabled';
		}
		
		// Check 3: Verify lazy loading
		$lazy_load = get_option( 'siteground_optimizer_lazyload_images', 0 );
		if ( ! $lazy_load ) {
			$issues[] = 'Lazy loading not enabled';
		}
		
		// Check 4: Check for image compression level
		$compression = get_option( 'siteground_optimizer_image_quality', 0 );
		if ( $compression <= 0 ) {
			$issues[] = 'Image compression level not configured';
		}
		
		// Check 5: Verify responsive images
		$responsive = get_option( 'siteground_optimizer_responsive_images', 0 );
		if ( ! $responsive ) {
			$issues[] = 'Responsive image optimization not enabled';
		}
		
		// Check 6: Check for media cleanup
		$cleanup = get_option( 'siteground_optimizer_media_cleanup', 0 );
		if ( ! $cleanup ) {
			$issues[] = 'Media cleanup not enabled';
		}
		
		$issue_count = count( $issues );
		if ( $issue_count > 0 ) {
			$base_threat = 45;
			$threat_multiplier = 6;
			$max_threat = 75;
			$threat_level = min( $max_threat, $base_threat + ( $issue_count * $threat_multiplier ) );
			
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					'Found %d SG Optimizer media optimization issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/sg-optimizer-media-optimization',
			);
		}
		
		return null;
	}
}
