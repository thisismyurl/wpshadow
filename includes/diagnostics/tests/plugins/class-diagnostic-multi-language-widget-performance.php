<?php
/**
 * Multi Language Widget Performance Diagnostic
 *
 * Multi Language Widget Performance misconfigured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1183.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Multi Language Widget Performance Diagnostic Class
 *
 * @since 1.1183.0000
 */
class Diagnostic_MultiLanguageWidgetPerformance extends Diagnostic_Base {

	protected static $slug = 'multi-language-widget-performance';
	protected static $title = 'Multi Language Widget Performance';
	protected static $description = 'Multi Language Widget Performance misconfigured';
	protected static $family = 'performance';

	public static function check() {
		if ( ! function_exists( 'pll_the_languages' ) && ! defined( 'WPML_VERSION' ) && ! defined( 'POLYLANG_VERSION' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Widget caching.
		$widget_cache = get_option( 'multilang_widget_cache', '1' );
		if ( '0' === $widget_cache ) {
			$issues[] = 'widget caching disabled';
		}
		
		// Check 2: Flag image optimization.
		$optimize_flags = get_option( 'multilang_optimize_flags', '0' );
		if ( '0' === $optimize_flags ) {
			$issues[] = 'flag images not optimized';
		}
		
		// Check 3: Database queries.
		$cache_queries = get_option( 'multilang_cache_queries', '1' );
		if ( '0' === $cache_queries ) {
			$issues[] = 'query caching disabled';
		}
		
		// Check 4: Lazy load flags.
		$lazy_load = get_option( 'multilang_lazy_load_flags', '0' );
		if ( '0' === $lazy_load ) {
			$issues[] = 'flag lazy loading disabled';
		}
		
		// Check 5: Language list caching.
		$list_cache = get_option( 'multilang_cache_list', '1' );
		if ( '0' === $list_cache ) {
			$issues[] = 'language list not cached';
		}
		
		// Check 6: Widget minification.
		$minify = get_option( 'multilang_minify_widget', '0' );
		if ( '0' === $minify ) {
			$issues[] = 'widget assets not minified';
		}
		
		if ( ! empty( $issues ) ) {
			$threat_level = min( 70, 55 + ( count( $issues ) * 3 ) );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'Multi-language widget issues: ' . implode( ', ', $issues ),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/multi-language-widget-performance',
			);
		}
		
		return null;
	}
}
