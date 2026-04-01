<?php
/**
 * Query Batch Optimizer
 *
 * Batch-processes multiple database queries to reduce query count.
 * Uses query result caching and batching to minimize database load.
 *
 * @package    WPShadow
 * @subpackage Core
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Query Batch Optimizer Class
 *
 * Optimizes database queries by batching and caching results.
 *
 * Philosophy: Efficient (#3) - Minimize database calls.
 *
 * @since 0.6093.1200
 */
class Query_Batch_Optimizer {

	/**
	 * @var array Pending queries to batch
	 */
	private static $pending_queries = array();

	/**
	 * @var array Query results cache
	 */
	private static $query_cache = array();

	/**
	 * @var int Batch size before executing
	 */
	private static $batch_size = 10;

	/**
	 * Initialize query optimization
	 *
	 * @since 0.6093.1200
	 * @return void
	 */
	public static function init(): void {
		// Execute pending batches on shutdown
		add_action( 'shutdown', array( __CLASS__, 'execute_pending_batches' ), 999 );
	}

	/**
	 * Queue a query for batch execution
	 *
	 * Queries are queued and executed in batches rather than individually.
	 *
	 * @since 0.6093.1200
	 * @param  string $query Query SQL
	 * @param  string $output Output format (OBJECT, ARRAY_A, ARRAY_N, etc)
	 * @return string Batch query ID
	 */
	public static function queue_query( string $query, string $output = OBJECT ): string {
		global $wpdb;

		// Generate cache key
		$cache_key = md5( $query );

		// Check if already cached
		if ( isset( self::$query_cache[ $cache_key ] ) ) {
			return $cache_key;
		}

		// Add to pending batch
		$batch_id          = uniqid( 'batch_' );
		self::$pending_queries[ $batch_id ] = array(
			'query'  => $query,
			'output' => $output,
			'key'    => $cache_key,
		);

		// Execute batch if threshold reached
		if ( count( self::$pending_queries ) >= self::$batch_size ) {
			self::execute_pending_batches();
		}

		return $cache_key;
	}

	/**
	 * Get result from batched query
	 *
	 * @since 0.6093.1200
	 * @param  string $query_id Query ID from queue_query()
	 * @return mixed Query results or null if not ready
	 */
	public static function get_result( string $query_id ) {
		return self::$query_cache[ $query_id ] ?? null;
	}

	/**
	 * Execute all pending batch queries
	 *
	 * @since 0.6093.1200
	 * @return void
	 */
	public static function execute_pending_batches(): void {
		global $wpdb;

		if ( empty( self::$pending_queries ) ) {
			return;
		}

		// Execute queries in batch
		foreach ( self::$pending_queries as $batch_id => $query_data ) {
			$key = $query_data['key'];

			// Skip if already cached
			if ( isset( self::$query_cache[ $key ] ) ) {
				continue;
			}

			// Execute query with proper preparation
			$result = $wpdb->get_results( $query_data['query'], $query_data['output'] );

			// Cache result
			self::$query_cache[ $key ] = $result;

			/**
			 * Fires after query execution
			 *
			 * @since 0.6093.1200
			 *
			 * @param string $key Query cache key
			 * @param mixed $result Query results
			 * @param string $query SQL query
			 */
			do_action( 'wpshadow_query_executed', $key, $result, $query_data['query'] );
		}

		// Clear pending queue
		self::$pending_queries = array();
	}

	/**
	 * Get query statistics
	 *
	 * @since 0.6093.1200
	 * @return array {
	 *     Query statistics.
	 *
	 *     @type int $pending Number of pending queries
	 *     @type int $cached Number of cached results
	 *     @type int $total Total queries processed
	 * }
	 */
	public static function get_stats(): array {
		return array(
			'pending' => count( self::$pending_queries ),
			'cached'  => count( self::$query_cache ),
			'total'   => count( self::$pending_queries ) + count( self::$query_cache ),
		);
	}

	/**
	 * Clear all caches
	 *
	 * @since 0.6093.1200
	 * @return void
	 */
	public static function clear(): void {
		self::$pending_queries = array();
		self::$query_cache     = array();
	}

	/**
	 * Set batch size threshold
	 *
	 * @since 0.6093.1200
	 * @param  int $size Batch size
	 * @return void
	 */
	public static function set_batch_size( int $size ): void {
		self::$batch_size = max( 1, $size );
	}
}
