<?php
/**
 * Litespeed Cache Configuration Diagnostic
 *
 * Litespeed Cache Configuration not optimized.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.900.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Litespeed Cache Configuration Diagnostic Class
 *
 * @since 1.900.0000
 */
class Diagnostic_LitespeedCacheConfiguration extends Diagnostic_Base {

	protected static $slug = 'litespeed-cache-configuration';
	protected static $title = 'Litespeed Cache Configuration';
	protected static $description = 'Litespeed Cache Configuration not optimized';
	protected static $family = 'performance';

	public static function check() {
		if ( ! defined( 'LSCWP_V' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Verify cache is enabled
		$cache_enabled = get_option( 'litespeed.conf.cache', 0 );
		if ( ! $cache_enabled ) {
			$issues[] = 'LiteSpeed cache not enabled';
		}

		// Check 2: Check for guest mode
		$guest_mode = get_option( 'litespeed.conf.guest', 0 );
		if ( ! $guest_mode ) {
			$issues[] = 'Guest mode not enabled';
		}

		// Check 3: Verify browser cache
		$browser_cache = get_option( 'litespeed.conf.browser_cache', 0 );
		if ( ! $browser_cache ) {
			$issues[] = 'Browser cache not enabled';
		}

		// Check 4: Check for CSS/JS minification
		$minify_css = get_option( 'litespeed.conf.optm-css_min', 0 );
		$minify_js = get_option( 'litespeed.conf.optm-js_min', 0 );
		if ( ! $minify_css || ! $minify_js ) {
			$issues[] = 'CSS/JS minification not enabled';
		}

		// Check 5: Verify cache purge settings
		$purge_all = get_option( 'litespeed.conf.purge_all', 0 );
		if ( ! $purge_all ) {
			$issues[] = 'Auto purge not configured';
		}

		// Check 6: Check for page optimization
		$page_opt = get_option( 'litespeed.conf.optm', 0 );
		if ( ! $page_opt ) {
			$issues[] = 'Page optimization not enabled';
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
					'Found %d LiteSpeed cache configuration issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/litespeed-cache-configuration',
			);
		}

		return null;
	}
}
