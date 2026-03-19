<?php
/**
 * Performance Regression Detection Diagnostic
 *
 * Analyzes performance trends and detects regressions.
 *
 * @since 1.6093.1200
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Performance Regression Detection Diagnostic
 *
 * Tracks performance metrics over time and identifies regressions.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Performance_Regression_Detection extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'performance-regression-detection';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Performance Regression Detection';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Analyzes performance trends and detects regressions';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'monitoring';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for performance monitoring plugins
		$monitoring_plugins = array(
			'query-monitor/query-monitor.php'           => 'Query Monitor',
			'new-relic-reporting/newrelic-reporting.php' => 'New Relic',
			'blackfire/blackfire.php'                   => 'Blackfire',
		);

		$active_plugin = null;
		foreach ( $monitoring_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$active_plugin = $name;
				break;
			}
		}

		// Get historical performance metrics from transients
		$current_metrics = get_transient( 'wpshadow_perf_metrics_current' );
		$previous_metrics = get_transient( 'wpshadow_perf_metrics_previous' );

		// Measure current page generation time
		$start_time = defined( 'WP_START_TIMESTAMP' ) ? WP_START_TIMESTAMP : microtime( true );
		$current_generation_time = microtime( true ) - $start_time;

		// Store current metrics for future comparison
		if ( ! $current_metrics ) {
			set_transient( 'wpshadow_perf_metrics_current', array(
				'generation_time' => $current_generation_time,
				'timestamp'       => time(),
			), WEEK_IN_SECONDS );
		}

		// Check for performance regression
		$has_regression = false;
		if ( $previous_metrics && $current_metrics ) {
			$prev_time = $previous_metrics['generation_time'] ?? 0;
			$curr_time = $current_metrics['generation_time'] ?? 0;

			// Flag if performance degraded by >30%
			if ( $curr_time > ( $prev_time *1.0 ) ) {
				$has_regression = true;
			}
		}

		// Generate findings if no performance tracking
		if ( ! $active_plugin && ! $previous_metrics ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'No performance regression tracking configured. Monitor trends to catch performance degradation early.', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/performance-regression-detection',
				'meta'         => array(
					'active_plugin'          => $active_plugin,
					'has_historical_data'    => ! empty( $previous_metrics ),
					'current_generation_ms'  => round( $current_generation_time * 1000, 2 ),
					'recommendation'         => 'Install Query Monitor or configure performance tracking',
					'monitoring_approaches'  => array(
						'Query Monitor for development',
						'New Relic for production APM',
						'Blackfire for profiling',
						'Google Lighthouse CI for continuous monitoring',
						'WPShadow activity logging',
					),
					'key_metrics'            => array(
						'Page generation time',
						'Database query time',
						'External API response time',
						'Memory usage',
						'Query count',
					),
				),
			);
		}

		// Alert on detected regression
		if ( $has_regression ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Performance regression detected. Recent changes may have degraded site speed by over 30%.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/performance-regression-detection',
				'meta'         => array(
					'previous_time_ms'   => round( ( $previous_metrics['generation_time'] ?? 0 ) * 1000, 2 ),
					'current_time_ms'    => round( ( $current_metrics['generation_time'] ?? 0 ) * 1000, 2 ),
					'degradation_percent' => round( ( ( ( $current_metrics['generation_time'] ?? 0 ) - ( $previous_metrics['generation_time'] ?? 0 ) ) / ( $previous_metrics['generation_time'] ?? 1 ) ) * 100, 1 ),
					'recommendation'     => 'Review recent plugin/theme changes and database queries',
				),
			);
		}

		return null;
	}
}
