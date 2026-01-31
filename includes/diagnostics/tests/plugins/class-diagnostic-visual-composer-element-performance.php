<?php
/**
 * Visual Composer Element Performance Diagnostic
 *
 * Visual Composer Element Performance issues found.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.831.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Visual Composer Element Performance Diagnostic Class
 *
 * @since 1.831.0000
 */
class Diagnostic_VisualComposerElementPerformance extends Diagnostic_Base {

	protected static $slug = 'visual-composer-element-performance';
	protected static $title = 'Visual Composer Element Performance';
	protected static $description = 'Visual Composer Element Performance issues found';
	protected static $family = 'performance';

	public static function check() {
		if ( ! get_option( 'vc_enabled', '' ) && ! defined( 'VCV_VERSION' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Lazy loading elements
		$lazy = get_option( 'vc_element_lazy_loading', 0 );
		if ( ! $lazy ) {
			$issues[] = 'Element lazy loading not enabled';
		}

		// Check 2: CSS minification
		$css_minify = get_option( 'vc_css_minification', 0 );
		if ( ! $css_minify ) {
			$issues[] = 'CSS minification not enabled';
		}

		// Check 3: JS minification
		$js_minify = get_option( 'vc_js_minification', 0 );
		if ( ! $js_minify ) {
			$issues[] = 'JavaScript minification not enabled';
		}

		// Check 4: Element caching
		$cache = get_option( 'vc_element_caching_enabled', 0 );
		if ( ! $cache ) {
			$issues[] = 'Element caching not enabled';
		}

		// Check 5: Performance optimizations
		$perf_opt = get_option( 'vc_performance_optimizations', 0 );
		if ( ! $perf_opt ) {
			$issues[] = 'Performance optimizations not enabled';
		}

		// Check 6: Element rendering optimization
		$render_opt = get_option( 'vc_element_render_optimization', 0 );
		if ( ! $render_opt ) {
			$issues[] = 'Rendering optimization not enabled';
		}

		$issue_count = count( $issues );
		if ( $issue_count > 0 ) {
			$base_threat = 40;
			$threat_multiplier = 6;
			$max_threat = 70;
			$threat_level = min( $max_threat, $base_threat + ( $issue_count * $threat_multiplier ) );

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					'Found %d element performance issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/visual-composer-element-performance',
			);
		}

		return null;
	}
}
