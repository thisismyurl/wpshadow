<?php
/**
 * Thrive Architect Performance Diagnostic
 *
 * Thrive Architect Performance issues found.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.836.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Thrive Architect Performance Diagnostic Class
 *
 * @since 1.836.0000
 */
class Diagnostic_ThriveArchitectPerformance extends Diagnostic_Base {

	protected static $slug = 'thrive-architect-performance';
	protected static $title = 'Thrive Architect Performance';
	protected static $description = 'Thrive Architect Performance issues found';
	protected static $family = 'performance';

	public static function check() {
		if ( ! defined( 'TVE_VERSION' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: CSS cache enabled
		$css_cache = get_option( 'tve_css_cache_enabled', false );
		if ( ! $css_cache ) {
			$issues[] = 'CSS cache disabled';
		}
		
		// Check 2: JavaScript defer enabled
		$js_defer = get_option( 'tve_defer_scripts', false );
		if ( ! $js_defer ) {
			$issues[] = 'JavaScript defer disabled';
		}
		
		// Check 3: Icon fonts optimized
		$icon_fonts = get_option( 'tve_optimize_icon_fonts', false );
		if ( ! $icon_fonts ) {
			$issues[] = 'Icon fonts not optimized';
		}
		
		// Check 4: Lazy load enabled
		$lazy_load = get_option( 'tve_lazy_load_images', false );
		if ( ! $lazy_load ) {
			$issues[] = 'Lazy load disabled';
		}
		
		// Check 5: Asset preload configured
		$preload = get_option( 'tve_preload_assets', false );
		if ( ! $preload ) {
			$issues[] = 'Asset preload not configured';
		}
		
		// Check 6: CDN enabled for assets
		$cdn = get_option( 'tve_cdn_enabled', false );
		if ( ! $cdn ) {
			$issues[] = 'CDN not enabled';
		}
		
		if ( ! empty( $issues ) ) {
			$threat_level = min( 70, 40 + ( count( $issues ) * 5 ) );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'Thrive Architect performance issues: ' . implode( ', ', $issues ),
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/thrive-architect-performance',
			);
		}
		
		return null;
	}
}
