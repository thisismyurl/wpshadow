<?php
/**
 * Autoptimize Css Optimization Diagnostic
 *
 * Autoptimize Css Optimization not optimized.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.913.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Autoptimize Css Optimization Diagnostic Class
 *
 * @since 1.913.0000
 */
class Diagnostic_AutoptimizeCssOptimization extends Diagnostic_Base {

	protected static $slug = 'autoptimize-css-optimization';
	protected static $title = 'Autoptimize Css Optimization';
	protected static $description = 'Autoptimize Css Optimization not optimized';
	protected static $family = 'performance';

	public static function check() {
		if ( ! defined( 'AUTOPTIMIZE_PLUGIN_VERSION' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: CSS optimization enabled
		$css_optimize = get_option( 'autoptimize_css', false );
		if ( ! $css_optimize ) {
			return null;
		}
		
		// Check 2: CSS aggregation
		$css_aggregate = get_option( 'autoptimize_css_aggregate', true );
		if ( ! $css_aggregate ) {
			$issues[] = __( 'CSS aggregation disabled (multiple HTTP requests)', 'wpshadow' );
		}
		
		// Check 3: Inline critical CSS
		$inline_critical = get_option( 'autoptimize_css_inline', false );
		if ( ! $inline_critical ) {
			$issues[] = __( 'Critical CSS not inlined (render-blocking)', 'wpshadow' );
		}
		
		// Check 4: Defer non-critical CSS
		$defer_css = get_option( 'autoptimize_css_defer', false );
		if ( ! $defer_css ) {
			$issues[] = __( 'Non-critical CSS not deferred (page speed impact)', 'wpshadow' );
		}
		
		// Check 5: Cache directory writable
		$cache_dir = WP_CONTENT_DIR . '/cache/autoptimize';
		if ( ! is_writable( $cache_dir ) ) {
			$issues[] = __( 'Cache directory not writable (optimization disabled)', 'wpshadow' );
		}
		
		// Check 6: Excessive exclusions
		$exclusions = get_option( 'autoptimize_css_exclude', '' );
		$exclusion_count = empty( $exclusions ) ? 0 : count( explode( ',', $exclusions ) );
		
		if ( $exclusion_count > 10 ) {
			$issues[] = sprintf( __( '%d CSS exclusions (reduced optimization)', 'wpshadow' ), $exclusion_count );
		}
		
		// Check 7: Large cache size
		if ( is_dir( $cache_dir ) ) {
			$cache_size = 0;
			$files = glob( $cache_dir . '/*' );
			if ( is_array( $files ) && count( $files ) > 100 ) {
				$issues[] = sprintf( __( '%d cached files (cleanup recommended)', 'wpshadow' ), count( $files ) );
			}
		}
		
		if ( empty( $issues ) ) {
			return null;
		}
		
		$threat_level = 50;
		if ( count( $issues ) >= 4 ) {
			$threat_level = 62;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 56;
		}
		
		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %s: list of CSS optimization issues */
				__( 'Autoptimize CSS optimization has %d configuration issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => self::calculate_severity( $threat_level ),
			'threat_level' => $threat_level,
			'auto_fixable' => true,
			'kb_link'     => 'https://wpshadow.com/kb/autoptimize-css-optimization',
		);
	}
}
