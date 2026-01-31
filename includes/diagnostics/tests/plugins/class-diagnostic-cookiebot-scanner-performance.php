<?php
/**
 * Cookiebot Scanner Performance Diagnostic
 *
 * Cookiebot Scanner Performance not compliant.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1115.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Cookiebot Scanner Performance Diagnostic Class
 *
 * @since 1.1115.0000
 */
class Diagnostic_CookiebotScannerPerformance extends Diagnostic_Base {

	protected static $slug = 'cookiebot-scanner-performance';
	protected static $title = 'Cookiebot Scanner Performance';
	protected static $description = 'Cookiebot Scanner Performance not compliant';
	protected static $family = 'performance';

	public static function check() {
		if ( ! get_option( 'cookiebot_cbid', '' ) && ! get_option( 'cookiebot_enabled', '' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Scanner enabled
		$scanner_enabled = get_option( 'cookiebot_scanner_enabled', 0 );
		if ( ! $scanner_enabled ) {
			$issues[] = 'Cookiebot scanner not enabled';
		}

		// Check 2: Scan frequency configured
		$scan_frequency = get_option( 'cookiebot_scan_frequency', '' );
		if ( empty( $scan_frequency ) ) {
			$issues[] = 'Scanner frequency not configured';
		}

		// Check 3: Performance optimization enabled
		$perf_opt = get_option( 'cookiebot_performance_optimization', 0 );
		if ( ! $perf_opt ) {
			$issues[] = 'Performance optimization not enabled';
		}

		// Check 4: Caching enabled
		$cache_enabled = get_option( 'cookiebot_cache_enabled', 0 );
		if ( ! $cache_enabled ) {
			$issues[] = 'Scanner caching not enabled';
		}

		// Check 5: Async loading enabled
		$async_loading = get_option( 'cookiebot_async_loading', 0 );
		if ( ! $async_loading ) {
			$issues[] = 'Async loading not enabled';
		}

		// Check 6: Results reporting
		$reporting = get_option( 'cookiebot_results_reporting', 0 );
		if ( ! $reporting ) {
			$issues[] = 'Results reporting not enabled';
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
					'Found %d Cookiebot scanner issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/cookiebot-scanner-performance',
			);
		}

		return null;
	}
}
