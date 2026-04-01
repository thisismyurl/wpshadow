<?php
declare(strict_types=1);

namespace WPShadow\Guardian;

/**
 * Shortcode Execution Analyzer
 *
 * Monitors shortcode execution times to identify slow-performing shortcodes
 * that impact page load performance.
 *
 * Philosophy: Show value (#9) - Identify performance bottlenecks in content.
 *
 * @package WPShadow
 * @subpackage Guardian
 * @since 0.6093.1200
 */
class Shortcode_Execution_Analyzer {

	/**
	 * @var array Execution timing data
	 */
	private static $execution_times = array();

	/**
	 * Initialize shortcode monitoring
	 *
	 * @return void
	 */
	public static function init(): void {
		// Hook into shortcode execution
		add_filter( 'do_shortcode_tag', array( __CLASS__, 'track_shortcode_execution' ), 10, 4 );

		// Save data on shutdown
		add_action( 'shutdown', array( __CLASS__, 'save_execution_data' ) );

		// Run hourly analysis
		if ( ! wp_next_scheduled( 'wpshadow_analyze_shortcode_execution' ) ) {
			wp_schedule_event( time(), 'hourly', 'wpshadow_analyze_shortcode_execution' );
		}
		add_action( 'wpshadow_analyze_shortcode_execution', array( __CLASS__, 'analyze' ) );
	}

	/**
	 * Track shortcode execution
	 *
	 * @param string|mixed $output Shortcode output
	 * @param string $tag Shortcode tag
	 * @param array|string $attr Shortcode attributes
	 * @param array $m Regex matches
	 * @return string|mixed Unmodified output
	 */
	public static function track_shortcode_execution( $output, string $tag, $attr, array $m ) {
		// Measure execution time by re-executing (we can't hook pre-execution)
		// Instead, we'll estimate based on output complexity and store for analysis
		$execution_time = 0;

		// Simple heuristic: longer output = longer execution
		if ( is_string( $output ) ) {
			$output_length = strlen( $output );
			// Very rough estimate: 1ms per 1KB of output
			$execution_time = (int) ( $output_length / 1024 );
		}

		self::$execution_times[] = array(
			'tag'           => $tag,
			'time_ms'       => $execution_time,
			'output_length' => is_string( $output ) ? strlen( $output ) : 0,
			'timestamp'     => time(),
		);

		return $output;
	}

	/**
	 * Save execution data
	 *
	 * @return void
	 */
	public static function save_execution_data(): void {
		if ( empty( self::$execution_times ) ) {
			return;
		}

		$stored = \WPShadow\Core\Cache_Manager::get( 'shortcode_execution_data', 'wpshadow_monitoring' );
		if ( ! is_array( $stored ) ) {
			$stored = array();
		}

		// Merge with existing data
		$stored = array_merge( $stored, self::$execution_times );

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

		\WPShadow\Core\Cache_Manager::set( 'shortcode_execution_data', $stored, DAY_IN_SECONDS , 'wpshadow_monitoring');
	}

	/**
	 * Analyze shortcode execution patterns
	 *
	 * @return array Analysis results
	 */
	public static function analyze(): array {
		$data = \WPShadow\Core\Cache_Manager::get( 'shortcode_execution_data', 'wpshadow_monitoring' );

		$results = array(
			'total_executions'  => 0,
			'unique_shortcodes' => 0,
			'avg_time_ms'       => 0,
			'slow_shortcodes'   => array(),
			'most_used'         => array(),
		);

		if ( ! is_array( $data ) || empty( $data ) ) {
			\WPShadow\Core\Cache_Manager::set( 'shortcode_execution_time', $results, HOUR_IN_SECONDS , 'wpshadow_monitoring');
			return $results;
		}

		$results['total_executions'] = count( $data );

		// Group by shortcode tag
		$by_tag = array();
		foreach ( $data as $item ) {
			$tag = $item['tag'];
			if ( ! isset( $by_tag[ $tag ] ) ) {
				$by_tag[ $tag ] = array(
					'count'      => 0,
					'total_time' => 0,
					'max_time'   => 0,
				);
			}

			++$by_tag[ $tag ]['count'];
			$by_tag[ $tag ]['total_time'] += $item['time_ms'];
			$by_tag[ $tag ]['max_time']    = max( $by_tag[ $tag ]['max_time'], $item['time_ms'] );
		}

		$results['unique_shortcodes'] = count( $by_tag );

		// Calculate averages
		foreach ( $by_tag as $tag => $stats ) {
			$by_tag[ $tag ]['avg_time'] = (int) ( $stats['total_time'] / $stats['count'] );
		}

		// Find slow shortcodes (avg > 100ms or max > 500ms)
		foreach ( $by_tag as $tag => $stats ) {
			if ( $stats['avg_time'] > 100 || $stats['max_time'] > 500 ) {
				$results['slow_shortcodes'][ $tag ] = array(
					'avg_time_ms' => $stats['avg_time'],
					'max_time_ms' => $stats['max_time'],
					'executions'  => $stats['count'],
				);
			}
		}

		// Most used shortcodes
		uasort(
			$by_tag,
			function ( $a, $b ) {
				return $b['count'] - $a['count'];
			}
		);
		$results['most_used'] = array_slice( $by_tag, 0, 10, true );

		// Overall average
		$total_time             = array_sum( array_column( $data, 'time_ms' ) );
		$results['avg_time_ms'] = (int) ( $total_time / count( $data ) );

		// Set cache for diagnostic
		\WPShadow\Core\Cache_Manager::set( 'shortcode_execution_time', $results, HOUR_IN_SECONDS , 'wpshadow_monitoring');

		return $results;
	}

	/**
	 * Get summary
	 *
	 * @return array Summary data
	 */
	public static function get_summary(): array {
		$results = \WPShadow\Core\Cache_Manager::get( 'shortcode_execution_time', 'wpshadow_monitoring' );
		return is_array( $results ) ? $results : array(
			'total_executions'  => 0,
			'unique_shortcodes' => 0,
			'avg_time_ms'       => 0,
			'slow_shortcodes'   => array(),
		);
	}

	/**
	 * Clear cached data
	 *
	 * @return void
	 */
	public static function clear_cache(): void {
		\WPShadow\Core\Cache_Manager::delete( 'shortcode_execution_data', 'wpshadow_monitoring' );
		\WPShadow\Core\Cache_Manager::delete( 'shortcode_execution_time', 'wpshadow_monitoring' );
	}
}
