<?php
/**
 * Autoptimize Critical Css Diagnostic
 *
 * Autoptimize Critical Css not optimized.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.917.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Autoptimize Critical Css Diagnostic Class
 *
 * @since 1.917.0000
 */
class Diagnostic_AutoptimizeCriticalCss extends Diagnostic_Base {

	protected static $slug = 'autoptimize-critical-css';
	protected static $title = 'Autoptimize Critical Css';
	protected static $description = 'Autoptimize Critical Css not optimized';
	protected static $family = 'performance';

	public static function check() {
		if ( ! defined( 'AUTOPTIMIZE_PLUGIN_VERSION' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Verify critical CSS is enabled
		$critical_css = get_option( 'autoptimize_css_defer_inline', '' );
		if ( empty( $critical_css ) ) {
			$issues[] = 'Critical CSS not configured';
		}

		// Check 2: Check for CSS optimization enabled
		$optimize_css = get_option( 'autoptimize_css', '' );
		if ( $optimize_css !== 'on' ) {
			$issues[] = 'CSS optimization not enabled';
		}

		// Check 3: Verify inline CSS handling
		$inline_css = get_option( 'autoptimize_css_inline', '' );
		if ( $inline_css !== 'on' ) {
			$issues[] = 'Inline CSS optimization not enabled';
		}

		// Check 4: Check for CSS minification
		$minify_css = get_option( 'autoptimize_css_minify', '' );
		if ( $minify_css !== 'on' ) {
			$issues[] = 'CSS minification not enabled';
		}

		// Check 5: Verify excluded CSS files
		$exclude_css = get_option( 'autoptimize_css_exclude', '' );
		if ( ! empty( $exclude_css ) && strpos( $exclude_css, 'admin-bar' ) === false ) {
			$issues[] = 'Admin bar CSS not excluded (may cause display issues)';
		}

		// Check 6: Check for cache configuration
		$cache_enabled = get_option( 'autoptimize_cache_nogzip', '' );
		if ( $cache_enabled === 'on' ) {
			$issues[] = 'Gzip compression disabled for cache files';
		}

		$issue_count = count( $issues );
		if ( $issue_count > 0 ) {
			$base_threat = 45;
			$threat_multiplier = 6;
			$max_threat = 75;
			$threat_level = min( $max_threat, $base_threat + ( $issue_count * $threat_multiplier ) );

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					'Found %d Autoptimize critical CSS issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/autoptimize-critical-css',
			);
		}

		return null;
	}
}
