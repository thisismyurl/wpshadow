<?php
/**
 * Page Load Time Monitoring Diagnostic
 *
 * Tracks page load time performance metrics.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6035.1560
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Page Load Time Monitoring Diagnostic Class
 *
 * Monitors page load time and alerts on degradation.
 *
 * @since 1.6035.1560
 */
class Diagnostic_Page_Load_Time_Monitoring extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'page-load-time-monitoring';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Page Load Time Monitoring';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tracks page load time performance metrics';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'real-user-monitoring';

	/**
	 * Page load time threshold (seconds)
	 *
	 * @var float
	 */
	private const LOAD_TIME_THRESHOLD = 3.0;

	/**
	 * Run the page load time diagnostic check.
	 *
	 * @since  1.6035.1560
	 * @return array|null Finding array if load time degraded, null otherwise.
	 */
	public static function check() {
		$homepage_load_time = self::measure_homepage_load_time();

		if ( ! $homepage_load_time ) {
			return null; // Unable to measure.
		}

		// Log page load time.
		if ( class_exists( '\WPShadow\Core\Activity_Logger' ) ) {
			\WPShadow\Core\Activity_Logger::log(
				'page_load_time_check',
				array(
					'load_time' => $homepage_load_time,
				)
			);
		}

		// Check for degradation compared to baseline.
		$baseline = self::get_baseline_load_time();

		if ( $baseline ) {
			$degradation = ( ( $homepage_load_time - $baseline ) / $baseline ) * 100;

			if ( $degradation > 20 ) { // > 20% slower.
				return array(
					'id'          => self::$slug,
					'title'       => self::$title,
					'description' => sprintf(
						/* translators: 1: load time, 2: baseline load time, 3: degradation % */
						__( 'Page load time is %1$.1fs (baseline: %2$.1fs). Performance degraded by %3$.1f%%.', 'wpshadow' ),
						$homepage_load_time,
						$baseline,
						$degradation
					),
					'severity'    => 'medium',
					'threat_level' => 55,
					'auto_fixable' => false,
					'kb_link'     => 'https://wpshadow.com/kb/improve-page-load-performance',
					'meta'        => array(
						'current_load_time'  => round( $homepage_load_time, 2 ),
						'baseline_load_time' => round( $baseline, 2 ),
						'degradation_pct'    => round( $degradation, 1 ),
					),
				);
			}
		}

		if ( $homepage_load_time > self::LOAD_TIME_THRESHOLD ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: 1: load time, 2: threshold */
					__( 'Page load time is %1$.1fs (recommended: < %2$.1fs). Optimize performance.', 'wpshadow' ),
					$homepage_load_time,
					self::LOAD_TIME_THRESHOLD
				),
				'severity'    => 'low',
				'threat_level' => 40,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/optimize-wordpress-performance',
				'meta'        => array(
					'current_load_time' => round( $homepage_load_time, 2 ),
					'threshold'         => self::LOAD_TIME_THRESHOLD,
				),
			);
		}

		return null;
	}

	/**
	 * Measure homepage load time.
	 *
	 * @since  1.6035.1560
	 * @return float|null Load time in seconds or null.
	 */
	private static function measure_homepage_load_time(): ?float {
		$home_url = home_url( '/' );

		$start_time = microtime( true );

		$response = wp_remote_get( $home_url, array(
			'timeout' => 10,
			'blocking' => true,
		) );

		$end_time = microtime( true );

		if ( is_wp_error( $response ) ) {
			return null;
		}

		return $end_time - $start_time;
	}

	/**
	 * Get baseline load time from Activity Logger.
	 *
	 * @since  1.6035.1560
	 * @return float|null Baseline load time or null.
	 */
	private static function get_baseline_load_time(): ?float {
		global $wpdb;

		if ( ! class_exists( '\WPShadow\Core\Activity_Logger' ) ) {
			return null;
		}

		$activity_table = $wpdb->prefix . 'wpshadow_activity';

		if ( $wpdb->get_var( "SHOW TABLES LIKE '{$activity_table}'" ) !== $activity_table ) {
			return null;
		}

		// Get average from past 7 days.
		$week_ago = time() - ( 7 * DAY_IN_SECONDS );

		$result = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT AVG(CAST(JSON_EXTRACT(meta, '$.load_time') AS DECIMAL(10,2))) 
				FROM {$activity_table} 
				WHERE action = %s AND created_at > %d",
				'page_load_time_check',
				$week_ago
			)
		);

		return $result ? (float) $result : null;
	}
}
