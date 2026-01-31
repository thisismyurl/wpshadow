<?php
/**
 * Beaver Builder Asset Optimization Diagnostic
 *
 * Beaver Builder assets not optimized.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.341.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Beaver Builder Asset Optimization Diagnostic Class
 *
 * @since 1.341.0000
 */
class Diagnostic_BeaverBuilderAssetOptimization extends Diagnostic_Base {

	protected static $slug = 'beaver-builder-asset-optimization';
	protected static $title = 'Beaver Builder Asset Optimization';
	protected static $description = 'Beaver Builder assets not optimized';
	protected static $family = 'performance';

	public static function check() {
		if ( ! class_exists( 'FLBuilder' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: CSS minification.
		$css_minify = get_option( '_fl_builder_css_minify', '0' );
		if ( '0' === $css_minify ) {
			$issues[] = 'CSS minification disabled';
		}
		
		// Check 2: JS minification.
		$js_minify = get_option( '_fl_builder_js_minify', '0' );
		if ( '0' === $js_minify ) {
			$issues[] = 'JS minification disabled';
		}
		
		// Check 3: Combine CSS files.
		$combine_css = get_option( '_fl_builder_combine_css', '0' );
		if ( '0' === $combine_css ) {
			$issues[] = 'CSS files not combined';
		}
		
		// Check 4: Combine JS files.
		$combine_js = get_option( '_fl_builder_combine_js', '0' );
		if ( '0' === $combine_js ) {
			$issues[] = 'JS files not combined';
		}
		
		// Check 5: Defer JS.
		$defer_js = get_option( '_fl_builder_defer_js', '0' );
		if ( '0' === $defer_js ) {
			$issues[] = 'JS not deferred';
		}
		
		// Check 6: Remove unused CSS.
		$remove_unused = get_option( '_fl_builder_remove_unused_css', '0' );
		if ( '0' === $remove_unused ) {
			$issues[] = 'unused CSS not removed';
		}
		
		if ( ! empty( $issues ) ) {
			$threat_level = min( 70, 55 + ( count( $issues ) * 3 ) );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'Beaver Builder asset issues: ' . implode( ', ', $issues ),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/beaver-builder-asset-optimization',
			);
		}
		
		return null;
	}
}
