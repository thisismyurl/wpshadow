<?php
declare(strict_types=1);

namespace WPShadow\Guardian;

/**
 * API Latency Analyzer
 *
 * Monitors latency of third-party API calls to identify slow external services
 * that impact site performance.
 *
 * Philosophy: Show value (#9) - Identify external performance bottlenecks.
 *
 * @package WPShadow
 * @subpackage Guardian
 * @since 1.2601.2200
 */
class API_Latency_Analyzer {

	/**
	 * @var array API call timing data
	 */
	private static $api_calls = array();

	/**
	 * Initialize API monitoring
	 *
	 * @return void
	 */
	public static function init(): void {
		// Hook into HTTP API requests
		add_action( 'http_api_debug', array( __CLASS__, 'track_http_request' ), 10, 5 );

		// Save data on shutdown
		add_action( 'shutdown', array( __CLASS__, 'save_api_data' ) );

		// Run hourly analysis
		if ( ! wp_next_scheduled( 'wpshadow_analyze_api_latency' ) ) {
			wp_schedule_event( time(), 'hourly', 'wpshadow_analyze_api_latency' );
		}
		add_action( 'wpshadow_analyze_api_latency', array( __CLASS__, 'analyze' ) );
	}

	/**
	 * Track HTTP request
	 *
	 * @param array|WP_Error $response HTTP response
	 * @param string $context Context (response or request)
	 * @param string $class HTTP transport class
	 * @param array $args Request arguments
	 * @param string $url Request URL
	 * @return void
	 */
	public static function track_http_request( $response, string $context, string $class, array $args, string $url ): void {
		// Only track responses
		if ( $context !== 'response' ) {
			return;
		}

		// Extract domain
		$parsed = parse_url( $url );
		if ( ! isset( $parsed['host'] ) ) {
			return;
		}

		$domain      = $parsed['host'];
		$site_domain = parse_url( get_site_url(), PHP_URL_HOST );

		// Skip internal calls
		if ( $domain === $site_domain ) {
			return;
		}

		// Calculate response time from headers if available
		$response_time_ms = 0;
		if ( is_array( $response ) && isset( $response['http_response'] ) ) {
			$http_response = $response['http_response'];
			if ( method_exists( $http_response, 'get_response_object' ) ) {
				$response_obj = $http_response->get_response_object();
				if ( isset( $response_obj->total_time ) ) {
					$response_time_ms = (int) ( $response_obj->total_time * 1000 );
				}
			}
		}

		// Fallback: estimate based on response size
		if ( $response_time_ms === 0 && is_array( $response ) && isset( $response['body'] ) ) {
			$body_size = strlen( $response['body'] );
			// Rough estimate: 1ms per KB + 100ms base
			$response_time_ms = 100 + (int) ( $body_size / 1024 );
		}

		self::$api_calls[] = array(
			'url'       => $url,
			'domain'    => $domain,
			'time_ms'   => $response_time_ms,
			'success'   => ! is_wp_error( $response ),
			'timestamp' => time(),
		);
	}

	/**
	 * Save API data
	 *
	 * @return void
	 */
	public static function save_api_data(): void {
		if ( empty( self::$api_calls ) ) {
			return;
		}

		$stored = get_transient( 'wpshadow_api_latency_data' );
		if ( ! is_array( $stored ) ) {
			$stored = array();
		}

		// Merge with existing data
		$stored = array_merge( $stored, self::$api_calls );

		// Keep only last 24 hours
		$one_day_ago = time() - DAY_IN_SECONDS;
		$stored      = array_filter(
			$stored,
			function ( $item ) use ( $one_day_ago ) {
				return $item['timestamp'] > $one_day_ago;
			}
		);

		// Limit to 1000 entries
		if ( count( $stored ) > 1000 ) {
			$stored = array_slice( $stored, -1000 );
		}

		set_transient( 'wpshadow_api_latency_data', $stored, DAY_IN_SECONDS );
	}

	/**
	 * Analyze API latency patterns
	 *
	 * @return array Analysis results
	 */
	public static function analyze(): array {
		$data = get_transient( 'wpshadow_api_latency_data' );

		$results = array(
			'total_calls'    => 0,
			'unique_apis'    => 0,
			'avg_latency_ms' => 0,
			'slow_apis'      => array(),
			'failed_apis'    => array(),
			'most_called'    => array(),
		);

		if ( ! is_array( $data ) || empty( $data ) ) {
			set_transient( 'wpshadow_api_latency', $results, HOUR_IN_SECONDS );
			return $results;
		}

		$results['total_calls'] = count( $data );

		// Group by domain
		$by_domain = array();
		foreach ( $data as $item ) {
			$domain = $item['domain'];
			if ( ! isset( $by_domain[ $domain ] ) ) {
				$by_domain[ $domain ] = array(
					'count'      => 0,
					'total_time' => 0,
					'max_time'   => 0,
					'failed'     => 0,
				);
			}

			++$by_domain[ $domain ]['count'];
			$by_domain[ $domain ]['total_time'] += $item['time_ms'];
			$by_domain[ $domain ]['max_time']    = max( $by_domain[ $domain ]['max_time'], $item['time_ms'] );

			if ( ! $item['success'] ) {
				++$by_domain[ $domain ]['failed'];
			}
		}

		$results['unique_apis'] = count( $by_domain );

		// Calculate averages
		foreach ( $by_domain as $domain => $stats ) {
			$by_domain[ $domain ]['avg_time'] = (int) ( $stats['total_time'] / $stats['count'] );
		}

		// Find slow APIs (avg > 1000ms or max > 5000ms)
		foreach ( $by_domain as $domain => $stats ) {
			if ( $stats['avg_time'] > 1000 || $stats['max_time'] > 5000 ) {
				$results['slow_apis'][ $domain ] = array(
					'avg_time_ms' => $stats['avg_time'],
					'max_time_ms' => $stats['max_time'],
					'calls'       => $stats['count'],
				);
			}
		}

		// Find failed APIs (>10% failure rate)
		foreach ( $by_domain as $domain => $stats ) {
			$failure_rate = $stats['failed'] / $stats['count'];
			if ( $failure_rate > 0.1 ) {
				$results['failed_apis'][ $domain ] = array(
					'failure_rate' => round( $failure_rate * 100, 1 ),
					'failed_calls' => $stats['failed'],
					'total_calls'  => $stats['count'],
				);
			}
		}

		// Most called APIs
		uasort(
			$by_domain,
			function ( $a, $b ) {
				return $b['count'] - $a['count'];
			}
		);
		$results['most_called'] = array_slice( $by_domain, 0, 10, true );

		// Overall average
		$total_time                = array_sum( array_column( $data, 'time_ms' ) );
		$results['avg_latency_ms'] = (int) ( $total_time / count( $data ) );

		// Set transient for diagnostic
		set_transient( 'wpshadow_api_latency', $results, HOUR_IN_SECONDS );

		return $results;
	}

	/**
	 * Get summary
	 *
	 * @return array Summary data
	 */
	public static function get_summary(): array {
		$results = get_transient( 'wpshadow_api_latency' );
		return is_array( $results ) ? $results : array(
			'total_calls'    => 0,
			'unique_apis'    => 0,
			'avg_latency_ms' => 0,
			'slow_apis'      => array(),
		);
	}

	/**
	 * Clear cached data
	 *
	 * @return void
	 */
	public static function clear_cache(): void {
		delete_transient( 'wpshadow_api_latency_data' );
		delete_transient( 'wpshadow_api_latency' );
	}
}
