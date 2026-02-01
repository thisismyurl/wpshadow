<?php
declare(strict_types=1);

namespace WPShadow\Guardian;

/**
 * Dashboard Performance Analyzer
 *
 * Monitors WordPress admin dashboard load times and performance metrics.
 * Tracks slow dashboard widgets, heavy admin pages, and overall admin performance.
 *
 * Philosophy: Show value (#9) - Improve admin user experience.
 *
 * @package WPShadow
 * @subpackage Guardian
 * @since 1.2601.2200
 */
class Dashboard_Performance_Analyzer {

	/**
	 * @var float Request start time
	 */
	private static $start_time = 0;

	/**
	 * Initialize performance tracking
	 *
	 * @return void
	 */
	public static function init(): void {
		// Track admin page loads
		add_action( 'admin_init', array( __CLASS__, 'start_tracking' ) );
		add_action( 'admin_footer', array( __CLASS__, 'end_tracking' ) );

		// Track dashboard widgets
		add_action( 'wp_dashboard_setup', array( __CLASS__, 'track_dashboard_widgets' ), 999 );
	}

	/**
	 * Start tracking page load
	 *
	 * @return void
	 */
	public static function start_tracking(): void {
		self::$start_time = microtime( true );
	}

	/**
	 * End tracking and record metrics
	 *
	 * @return void
	 */
	public static function end_tracking(): void {
		if ( self::$start_time === 0 ) {
			return;
		}

		$load_time_ms = (int) ( ( microtime( true ) - self::$start_time ) * 1000 );

		// Only track dashboard pages
		if ( ! \function_exists( 'get_current_screen' ) ) {
			return;
		}

		$screen = \get_current_screen();
		if ( ! $screen || ! isset( $screen->id ) || $screen->id !== 'dashboard' ) {
			return;
		}

		// Get current metrics
		$metrics = \WPShadow\Core\Cache_Manager::get(
			'dashboard_metrics',
			'wpshadow_dashboard'
		);
		if ( ! is_array( $metrics ) ) {
			$metrics = array(
				'loads'       => array(),
				'total_loads' => 0,
			);
		}

		// Add this load time
		$metrics['loads'][] = $load_time_ms;
		++$metrics['total_loads'];

		// Keep only last 50 loads
		if ( count( $metrics['loads'] ) > 50 ) {
			$metrics['loads'] = array_slice( $metrics['loads'], -50 );
		}

		// Calculate average
		$avg_load_ms = (int) ( array_sum( $metrics['loads'] ) / count( $metrics['loads'] ) );

		// Set cache for diagnostics
		\WPShadow\Core\Cache_Manager::set(
			'dashboard_load_ms',
			$avg_load_ms,
			WEEK_IN_SECONDS,
			'wpshadow_dashboard'
		);
		\WPShadow\Core\Cache_Manager::set(
			'dashboard_metrics',
			$metrics,
			WEEK_IN_SECONDS,
			'wpshadow_dashboard'
		);
	}

	/**
	 * Track dashboard widgets
	 *
	 * @return void
	 */
	public static function track_dashboard_widgets(): void {
		global $wp_meta_boxes;

		$widget_count = 0;
		if ( isset( $wp_meta_boxes['dashboard'] ) ) {
			foreach ( $wp_meta_boxes['dashboard'] as $context => $priority_boxes ) {
				foreach ( $priority_boxes as $priority => $boxes ) {
					$widget_count += count( $boxes );
				}
			}
		}

		\WPShadow\Core\Cache_Manager::set(
			'dashboard_widget_count',
			$widget_count,
			DAY_IN_SECONDS,
			'wpshadow_dashboard'
		);
	}

	/**
	 * Get analysis summary
	 *
	 * @return array Analysis data
	 */
	public static function get_summary(): array {
		$avg_load_ms  = (int) \WPShadow\Core\Cache_Manager::get(
			'dashboard_load_ms',
			'wpshadow_dashboard',
			0
		);
		$widget_count = (int) \WPShadow\Core\Cache_Manager::get(
			'dashboard_widget_count',
			'wpshadow_dashboard',
			0
		);
		$metrics      = \WPShadow\Core\Cache_Manager::get(
			'dashboard_metrics',
			'wpshadow_dashboard'
		);

		return array(
			'avg_load_ms'  => $avg_load_ms,
			'widget_count' => $widget_count,
			'total_loads'  => is_array( $metrics ) ? $metrics['total_loads'] : 0,
			'is_slow'      => $avg_load_ms > 2000, // Slower than 2 seconds
		);
	}

	/**
	 * Clear cached data
	 *
	 * @return void
	 */
	public static function clear_cache(): void {
		\WPShadow\Core\Cache_Manager::delete( 'dashboard_load_ms', 'wpshadow_dashboard' );
		\WPShadow\Core\Cache_Manager::delete( 'dashboard_widget_count', 'wpshadow_dashboard' );
		\WPShadow\Core\Cache_Manager::delete( 'dashboard_metrics', 'wpshadow_dashboard' );
	}
}
