<?php
/**
 * Autoptimize Html Optimization Diagnostic
 *
 * Autoptimize Html Optimization not optimized.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.914.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Autoptimize Html Optimization Diagnostic Class
 *
 * @since 1.914.0000
 */
class Diagnostic_AutoptimizeHtmlOptimization extends Diagnostic_Base {

	protected static $slug = 'autoptimize-html-optimization';
	protected static $title = 'Autoptimize Html Optimization';
	protected static $description = 'Autoptimize Html Optimization not optimized';
	protected static $family = 'performance';

	public static function check() {
		if ( ! defined( 'AUTOPTIMIZE_PLUGIN_VERSION' ) ) {
			return null;
		}
		$issues = array();
		$html_optimize = get_option( 'autoptimize_html_enabled', 0 );
		if ( '0' === $html_optimize ) { $issues[] = 'HTML optimization disabled'; }
		$remove_comments = get_option( 'autoptimize_remove_comments', 0 );
		if ( '0' === $remove_comments ) { $issues[] = 'HTML comments not removed'; }
		$strip_whitespace = get_option( 'autoptimize_strip_whitespace', 0 );
		if ( '0' === $strip_whitespace ) { $issues[] = 'whitespace not stripped'; }
		$minify_html = get_option( 'autoptimize_minify_html', 0 );
		if ( '0' === $minify_html ) { $issues[] = 'HTML minification disabled'; }
		$cache_enabled = get_option( 'autoptimize_cache_enabled', 0 );
		if ( '0' === $cache_enabled ) { $issues[] = 'caching disabled'; }
		$cdn_url = get_option( 'autoptimize_cdn_url', '' );
		if ( empty( $cdn_url ) ) { $issues[] = 'CDN not configured'; }
		if ( ! empty( $issues ) ) {
			return array( 'id' => self::$slug, 'title' => self::$title, 'description' => implode( ', ', $issues ), 'severity' => self::calculate_severity( min( 75, 50 + ( count( $issues ) * 4 ) ) ), 'threat_level' => min( 75, 50 + ( count( $issues ) * 4 ) ), 'auto_fixable' => false, 'kb_link' => 'https://wpshadow.com/kb/autoptimize-html-optimization' );
		}
		return null;
	}
}
