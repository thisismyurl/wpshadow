<?php
/**
 * Autoptimize Javascript Optimization Diagnostic
 *
 * Autoptimize Javascript Optimization not optimized.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.912.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Autoptimize Javascript Optimization Diagnostic Class
 *
 * @since 1.912.0000
 */
class Diagnostic_AutoptimizeJavascriptOptimization extends Diagnostic_Base {

	protected static $slug = 'autoptimize-javascript-optimization';
	protected static $title = 'Autoptimize Javascript Optimization';
	protected static $description = 'Autoptimize Javascript Optimization not optimized';
	protected static $family = 'performance';

	public static function check() {
		if ( ! defined( 'AUTOPTIMIZE_PLUGIN_VERSION' ) ) {
			return null;
		}
		$issues = array();
		$optimize_js = get_option( 'autoptimize_optimize_js', 0 );
		if ( '0' === $optimize_js ) { $issues[] = 'JavaScript optimization disabled'; }
		$minify_js = get_option( 'autoptimize_minify_js', 0 );
		if ( '0' === $minify_js ) { $issues[] = 'JS minification disabled'; }
		$remove_unused = get_option( 'autoptimize_remove_unused_js', 0 );
		if ( '0' === $remove_unused ) { $issues[] = 'unused JavaScript not removed'; }
		$defer_js = get_option( 'autoptimize_defer_js', 0 );
		if ( '0' === $defer_js ) { $issues[] = 'defer attribute not used'; }
		$exclude_js = get_option( 'autoptimize_js_exclude', '' );
		if ( empty( $exclude_js ) ) { $issues[] = 'no JS exclusion rules'; }
		$aggregate_js = get_option( 'autoptimize_aggregate_js', 0 );
		if ( '0' === $aggregate_js ) { $issues[] = 'JS file aggregation disabled'; }
		if ( ! empty( $issues ) ) {
			return array( 'id' => self::$slug, 'title' => self::$title, 'description' => implode( ', ', $issues ), 'severity' => self::calculate_severity( min( 75, 50 + ( count( $issues ) * 4 ) ) ), 'threat_level' => min( 75, 50 + ( count( $issues ) * 4 ) ), 'auto_fixable' => false, 'kb_link' => 'https://wpshadow.com/kb/autoptimize-javascript-optimization' );
		}
		return null;
	}
}
