<?php
/**
 * Envira Gallery Performance Diagnostic
 *
 * Envira Gallery slowing page loads.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.488.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Envira Gallery Performance Diagnostic Class
 *
 * @since 1.488.0000
 */
class Diagnostic_EnviraGalleryPerformance extends Diagnostic_Base {

	protected static $slug = 'envira-gallery-performance';
	protected static $title = 'Envira Gallery Performance';
	protected static $description = 'Envira Gallery slowing page loads';
	protected static $family = 'performance';

	public static function check() {
		if ( ! class_exists( 'Envira_Gallery' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Image optimization
		$img_opt = get_option( 'envira_image_optimization_enabled', 0 );
		if ( ! $img_opt ) {
			$issues[] = 'Image optimization not enabled';
		}
		
		// Check 2: Lazy loading
		$lazy = get_option( 'envira_lazy_loading_enabled', 0 );
		if ( ! $lazy ) {
			$issues[] = 'Lazy loading not enabled';
		}
		
		// Check 3: Lightbox performance
		$lightbox = get_option( 'envira_lightbox_optimized', 0 );
		if ( ! $lightbox ) {
			$issues[] = 'Lightbox performance not optimized';
		}
		
		// Check 4: Caching enabled
		$cache = get_option( 'envira_gallery_caching_enabled', 0 );
		if ( ! $cache ) {
			$issues[] = 'Gallery caching not enabled';
		}
		
		// Check 5: CDN integration
		$cdn = get_option( 'envira_cdn_enabled', 0 );
		if ( ! $cdn ) {
			$issues[] = 'CDN integration not configured';
		}
		
		// Check 6: JavaScript optimization
		$js = get_option( 'envira_js_optimization_enabled', 0 );
		if ( ! $js ) {
			$issues[] = 'JavaScript optimization not enabled';
		}
		
		$issue_count = count( $issues );
		if ( $issue_count > 0 ) {
			$base_threat = 35;
			$threat_multiplier = 6;
			$max_threat = 65;
			$threat_level = min( $max_threat, $base_threat + ( $issue_count * $threat_multiplier ) );
			
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					'Found %d gallery performance issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/envira-gallery-performance',
			);
		}
		
		return null;
	}
}
