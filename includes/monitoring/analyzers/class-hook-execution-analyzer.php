<?php
declare(strict_types=1);

namespace WPShadow\Guardian;

/**
 * Hook Execution Analyzer
 *
 * Monitors WordPress hook execution patterns to identify slow or excessive hooks
 * that impact performance.
 *
 * Philosophy: Show value (#9) - Identify performance bottlenecks in code.
 *
 * @package WPShadow
 * @subpackage Guardian
 * @since 1.2601.2200
 */
class Hook_Execution_Analyzer {

	/**
	 * @var array Hook execution data
	 */
	private static $hook_data = array();

	/**
	 * @var int Hook execution count
	 */
	private static $hook_count = 0;

	/**
	 * Initialize hook monitoring
	 *
	 * @return void
	 */
	public static function init(): void {
		// Track all hooks
		add_action( 'all', array( __CLASS__, 'track_hook' ), 1 );

		// Save data on shutdown
		add_action( 'shutdown', array( __CLASS__, 'save_hook_data' ), 999 );

		// Run hourly analysis
		if ( ! wp_next_scheduled( 'wpshadow_analyze_hook_execution' ) ) {
			wp_schedule_event( time(), 'hourly', 'wpshadow_analyze_hook_execution' );
		}
		add_action( 'wpshadow_analyze_hook_execution', array( __CLASS__, 'analyze' ) );
	}

	/**
	 * Track hook execution
	 *
	 * @param string $hook_name Hook name
	 * @return void
	 */
	public static function track_hook( string $hook_name ): void {
		// Skip our own hooks to avoid recursion
		if ( strpos( $hook_name, 'wpshadow_' ) === 0 ) {
			return;
		}

		++self::$hook_count;

		// Sample hooks (only track 1% to avoid overhead)
		if ( self::$hook_count % 100 !== 0 ) {
			return;
		}

		if ( ! isset( self::$hook_data[ $hook_name ] ) ) {
			self::$hook_data[ $hook_name ] = 0;
		}
		++self::$hook_data[ $hook_name ];
	}

	/**
	 * Save hook data
	 *
	 * @return void
	 */
	public static function save_hook_data(): void {
		if ( empty( self::$hook_data ) ) {
			return;
		}

		$stored = \WPShadow\Core\Cache_Manager::get( 'hook_execution_data', 'wpshadow_monitoring' );
		if ( ! is_array( $stored ) ) {
			$stored = array(
				'hooks'       => array(),
				'total_count' => 0,
			);
		}

		// Merge hook counts
		foreach ( self::$hook_data as $hook => $count ) {
			if ( ! isset( $stored['hooks'][ $hook ] ) ) {
				$stored['hooks'][ $hook ] = 0;
			}
			// Multiply by 100 since we sampled at 1%
			$stored['hooks'][ $hook ] += ( $count * 100 );
		}

		$stored['total_count'] += ( self::$hook_count );

		// Keep only top 100 hooks
		arsort( $stored['hooks'] );
		$stored['hooks'] = array_slice( $stored['hooks'], 0, 100, true );

		\WPShadow\Core\Cache_Manager::set( 'hook_execution_data', $stored, 'wpshadow_monitoring', DAY_IN_SECONDS );
	}

	/**
	 * Analyze hook execution
	 *
	 * @return array Analysis results
	 */
	public static function analyze(): array {
		$data = \WPShadow\Core\Cache_Manager::get( 'hook_execution_data', 'wpshadow_monitoring' );

		$results = array(
			'total_hook_executions' => 0,
			'unique_hooks'          => 0,
			'most_called_hooks'     => array(),
			'excessive_hooks'       => array(),
			'hooks_per_request'     => 0,
		);

		if ( ! is_array( $data ) ) {
			\WPShadow\Core\Cache_Manager::set( 'hook_execution_overhead', $results, 'wpshadow_monitoring', HOUR_IN_SECONDS );
			return $results;
		}

		$results['total_hook_executions'] = $data['total_count'] ?? 0;
		$results['unique_hooks']          = count( $data['hooks'] ?? array() );

		// Get most called hooks
		if ( ! empty( $data['hooks'] ) ) {
			$results['most_called_hooks'] = array_slice( $data['hooks'], 0, 20, true );

			// Find excessive hooks (called >1000 times per request estimate)
			foreach ( $data['hooks'] as $hook => $count ) {
				if ( $count > 1000 ) {
					$results['excessive_hooks'][ $hook ] = $count;
				}
			}
		}

		// Estimate hooks per request (rough calculation)
		if ( $results['total_hook_executions'] > 0 ) {
			// Assume this data is from ~100 page loads over 24h
			$results['hooks_per_request'] = (int) ( $results['total_hook_executions'] / 100 );
		}

		// Set cache for diagnostic
		\WPShadow\Core\Cache_Manager::set( 'hook_execution_overhead', $results, 'wpshadow_monitoring', HOUR_IN_SECONDS );

		return $results;
	}

	/**
	 * Get summary
	 *
	 * @return array Summary data
	 */
	public static function get_summary(): array {
		$results = \WPShadow\Core\Cache_Manager::get( 'hook_execution_overhead', 'wpshadow_monitoring' );
		return is_array( $results ) ? $results : array(
			'total_hook_executions' => 0,
			'hooks_per_request'     => 0,
			'excessive_hooks'       => array(),
		);
	}

	/**
	 * Clear cached data
	 *
	 * @return void
	 */
	public static function clear_cache(): void {
		\WPShadow\Core\Cache_Manager::delete( 'hook_execution_data', 'wpshadow_monitoring' );
		\WPShadow\Core\Cache_Manager::delete( 'hook_execution_overhead', 'wpshadow_monitoring' );
	}
}
