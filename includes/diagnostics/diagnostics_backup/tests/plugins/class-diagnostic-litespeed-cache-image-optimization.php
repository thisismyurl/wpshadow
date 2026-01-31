<?php
/**
 * Litespeed Cache Image Optimization Diagnostic
 *
 * Litespeed Cache Image Optimization not optimized.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.901.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Litespeed Cache Image Optimization Diagnostic Class
 *
 * @since 1.901.0000
 */
class Diagnostic_LitespeedCacheImageOptimization extends Diagnostic_Base {

	protected static $slug = 'litespeed-cache-image-optimization';
	protected static $title = 'Litespeed Cache Image Optimization';
	protected static $description = 'Litespeed Cache Image Optimization not optimized';
	protected static $family = 'performance';

	public static function check() {
		if ( ! defined( 'LSCWP_V' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Verify image optimization is enabled
		$img_optimization = get_option( 'litespeed_img_optm_auto', false );
		if ( ! $img_optimization ) {
			$issues[] = __( 'LiteSpeed image optimization not enabled', 'wpshadow' );
		}

		// Check 2: Check WebP conversion
		$webp_enabled = get_option( 'litespeed_img_optm_webp', false );
		if ( ! $webp_enabled ) {
			$issues[] = __( 'WebP image conversion not enabled', 'wpshadow' );
		}

		// Check 3: Verify lazy loading configuration
		$lazy_load = get_option( 'litespeed_media_lazy', false );
		if ( ! $lazy_load ) {
			$issues[] = __( 'Image lazy loading not enabled', 'wpshadow' );
		}

		// Check 4: Check optimization level
		$optimization_level = get_option( 'litespeed_img_optm_level', 0 );
		if ( $optimization_level < 2 ) {
			$issues[] = __( 'Image optimization level too low', 'wpshadow' );
		}

		// Check 5: Verify CDN integration for images
		$cdn_integration = get_option( 'litespeed_cdn_img', false );
		if ( ! $cdn_integration ) {
			$issues[] = __( 'CDN integration for images not enabled', 'wpshadow' );
		}

		// Check 6: Check optimization queue management
		$queue_management = get_option( 'litespeed_img_optm_queue_limit', 0 );
		if ( $queue_management === 0 || $queue_management > 500 ) {
			$issues[] = __( 'Image optimization queue not optimally configured', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			$threat_level = min( 80, 50 + ( count( $issues ) * 5 ) );
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %s: Comma-separated list of issues */
					__( 'LiteSpeed Cache image optimization issues detected: %s', 'wpshadow' ),
					implode( ', ', $issues )
				),
				'severity'     => 'medium',
				'threat_level' => $threat_level,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/litespeed-cache-image-optimization',
			);
		}

		return null;
	}
}
