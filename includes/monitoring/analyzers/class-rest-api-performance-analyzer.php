<?php
declare(strict_types=1);

namespace WPShadow\Guardian;

/**
 * REST API Performance Analyzer
 *
 * Monitors WordPress REST API endpoint performance and response times.
 * Tracks slow endpoints and overall API health.
 *
 * Philosophy: Show value (#9) - Optimize API performance for better user experience.
 *
 * @package WPShadow
 * @subpackage Guardian
 * @since 1.2601.2200
 */
class REST_API_Performance_Analyzer {

	/**
	 * Initialize API performance tracking
	 *
	 * @return void
	 */
	public static function init(): void {
		// Track REST API requests
		add_filter( 'rest_pre_dispatch', array( __CLASS__, 'start_tracking' ), 10, 3 );
		add_filter( 'rest_post_dispatch', array( __CLASS__, 'end_tracking' ), 10, 3 );
	}

	/**
	 * Start tracking API request
	 *
	 * @param mixed $result Response to replace
	 * @param \WP_REST_Server $server Server instance
	 * @param \WP_REST_Request $request Request object
	 * @return mixed Unmodified result
	 */
	public static function start_tracking( $result, $server, $request ) {
		// Store start time in request
		$request->set_param( '_wpshadow_start_time', microtime( true ) );
		return $result;
	}

	/**
	 * End tracking and record metrics
	 *
	 * @param mixed $response Response object
	 * @param \WP_REST_Server $server Server instance
	 * @param \WP_REST_Request $request Request object
	 * @return mixed Unmodified response
	 */
	public static function end_tracking( $response, $server, $request ) {
		$start_time = $request->get_param( '_wpshadow_start_time' );
		if ( ! $start_time ) {
			return $response;
		}

		$duration_ms = (int) ( ( microtime( true ) - $start_time ) * 1000 );
		$route       = $request->get_route();

		// Get current metrics
		$metrics = \WPShadow\Core\Cache_Manager::get( 'rest_api_metrics', 'wpshadow_monitoring' );
		if ( ! is_array( $metrics ) ) {
			$metrics = array(
				'requests'       => array(),
				'total_requests' => 0,
				'total_time_ms'  => 0,
			);
		}

		// Add this request
		$metrics['requests'][] = array(
			'route'       => $route,
			'duration_ms' => $duration_ms,
			'timestamp'   => time(),
		);
		++$metrics['total_requests'];
		$metrics['total_time_ms'] += $duration_ms;

		// Keep only last 100 requests
		if ( count( $metrics['requests'] ) > 100 ) {
			$metrics['requests'] = array_slice( $metrics['requests'], -100 );
		}

		// Calculate average
		$avg_time_ms = (int) ( $metrics['total_time_ms'] / $metrics['total_requests'] );

		// Set cache
		\WPShadow\Core\Cache_Manager::set( 'rest_api_avg_time_ms', $avg_time_ms, WEEK_IN_SECONDS , 'wpshadow_monitoring');
		\WPShadow\Core\Cache_Manager::set( 'rest_api_metrics', $metrics, WEEK_IN_SECONDS , 'wpshadow_monitoring');

		return $response;
	}

	/**
	 * Get analysis summary
	 *
	 * @return array Analysis data
	 */
	public static function get_summary(): array {
		$avg_time_ms = (int) \WPShadow\Core\Cache_Manager::get( 'rest_api_avg_time_ms', 'wpshadow_monitoring' );
		$metrics     = \WPShadow\Core\Cache_Manager::get( 'rest_api_metrics', 'wpshadow_monitoring' );

		$summary = array(
			'avg_time_ms'    => $avg_time_ms,
			'total_requests' => 0,
			'slow_endpoints' => array(),
			'is_slow'        => $avg_time_ms > 1000, // Slower than 1 second
		);

		if ( is_array( $metrics ) ) {
			$summary['total_requests'] = $metrics['total_requests'];

			// Find slow endpoints
			$route_times = array();
			foreach ( $metrics['requests'] as $req ) {
				$route = $req['route'];
				if ( ! isset( $route_times[ $route ] ) ) {
					$route_times[ $route ] = array(
						'total' => 0,
						'count' => 0,
					);
				}
				$route_times[ $route ]['total'] += $req['duration_ms'];
				++$route_times[ $route ]['count'];
			}

			foreach ( $route_times as $route => $data ) {
				$avg = $data['total'] / $data['count'];
				if ( $avg > 500 ) { // Slower than 500ms
					$summary['slow_endpoints'][] = array(
						'route'  => $route,
						'avg_ms' => (int) $avg,
						'count'  => $data['count'],
					);
				}
			}
		}

		return $summary;
	}

	/**
	 * Clear cached data
	 *
	 * @return void
	 */
	public static function clear_cache(): void {
		\WPShadow\Core\Cache_Manager::delete( 'rest_api_avg_time_ms', 'wpshadow_monitoring' );
		\WPShadow\Core\Cache_Manager::delete( 'rest_api_metrics', 'wpshadow_monitoring' );
	}
}
