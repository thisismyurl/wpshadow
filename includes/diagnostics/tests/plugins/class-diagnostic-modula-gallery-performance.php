<?php
/**
 * Modula Gallery Performance Diagnostic
 *
 * Modula Gallery slowing frontend.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.498.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Modula Gallery Performance Diagnostic Class
 *
 * @since 1.498.0000
 */
class Diagnostic_ModulaGalleryPerformance extends Diagnostic_Base {

	protected static $slug = 'modula-gallery-performance';
	protected static $title = 'Modula Gallery Performance';
	protected static $description = 'Modula Gallery slowing frontend';
	protected static $family = 'performance';

	public static function check() {
		if ( ! defined( 'MODULA_VERSION' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Image optimization
		$opt = get_option( 'modula_image_optimization_enabled', 0 );
		if ( ! $opt ) {
			$issues[] = 'Image optimization not enabled';
		}

		// Check 2: Lazy loading
		$lazy = get_option( 'modula_lazy_loading_enabled', 0 );
		if ( ! $lazy ) {
			$issues[] = 'Lazy loading not enabled';
		}

		// Check 3: Lightbox performance
		$lightbox = get_option( 'modula_lightbox_performance_optimized', 0 );
		if ( ! $lightbox ) {
			$issues[] = 'Lightbox performance not optimized';
		}

		// Check 4: Gallery caching
		$cache = get_option( 'modula_gallery_caching_enabled', 0 );
		if ( ! $cache ) {
			$issues[] = 'Gallery caching not enabled';
		}

		// Check 5: CSS minification
		$css = get_option( 'modula_css_minification_enabled', 0 );
		if ( ! $css ) {
			$issues[] = 'CSS minification not enabled';
		}

		// Check 6: JavaScript optimization
		$js = get_option( 'modula_js_optimization_enabled', 0 );
		if ( ! $js ) {
			$issues[] = 'JavaScript optimization not enabled';
		}

		$issue_count = count( $issues );
		if ( $issue_count > 0 ) {
			$base_threat = 30;
			$threat_multiplier = 6;
			$max_threat = 60;
			$threat_level = min( $max_threat, $base_threat + ( $issue_count * $threat_multiplier ) );

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					'Found %d gallery performance issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/modula-gallery-performance',
			);
		}

		return null;
	}
}
