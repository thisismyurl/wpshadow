<?php
/**
 * Divi Builder Pro Visual Builder Performance Diagnostic
 *
 * Divi Builder Pro Visual Builder Performance issues found.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.807.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Divi Builder Pro Visual Builder Performance Diagnostic Class
 *
 * @since 1.807.0000
 */
class Diagnostic_DiviBuilderProVisualBuilderPerformance extends Diagnostic_Base {

	protected static $slug = 'divi-builder-pro-visual-builder-performance';
	protected static $title = 'Divi Builder Pro Visual Builder Performance';
	protected static $description = 'Divi Builder Pro Visual Builder Performance issues found';
	protected static $family = 'performance';

	public static function check() {
		if ( ! function_exists( 'et_setup_theme' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Visual builder enabled
		$vb_enabled = get_option( 'et_visual_builder_enabled', 0 );
		if ( ! $vb_enabled ) {
			$issues[] = 'Visual builder not enabled';
		}
		
		// Check 2: Lazy load enabled
		$vb_lazy = get_option( 'et_visual_builder_lazy_load', 0 );
		if ( ! $vb_lazy ) {
			$issues[] = 'Lazy loading not enabled';
		}
		
		// Check 3: CSS optimization
		$css_opt = get_option( 'et_visual_builder_css_optimization', 0 );
		if ( ! $css_opt ) {
			$issues[] = 'CSS optimization not enabled';
		}
		
		// Check 4: JavaScript optimization
		$js_opt = get_option( 'et_visual_builder_js_optimization', 0 );
		if ( ! $js_opt ) {
			$issues[] = 'JavaScript optimization not enabled';
		}
		
		// Check 5: Editor performance mode
		$perf_mode = get_option( 'et_visual_builder_performance_mode', 0 );
		if ( ! $perf_mode ) {
			$issues[] = 'Performance mode not enabled';
		}
		
		// Check 6: Preview optimization
		$preview_opt = get_option( 'et_visual_builder_preview_optimization', 0 );
		if ( ! $preview_opt ) {
			$issues[] = 'Preview optimization not enabled';
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
					'Found %d visual builder performance issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/divi-builder-pro-visual-builder-performance',
			);
		}
		
		return null;
	}
}
