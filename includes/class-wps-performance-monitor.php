<?php
/**
 * Performance Monitor Class
 *
 * Real-time performance monitoring providing visibility into WordPress performance metrics,
 * query counts, memory usage, and load times. Tracks improvements, identifies bottlenecks,
 * and measures optimization impact.
 *
 * @package WPS_CoreSupport
 * @since 1.0.0
 */

declare(strict_types=1);

namespace WPS\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Performance Monitor Class
 */
class WPS_Performance_Monitor {

	/**
	 * Option key for storing performance history.
	 */
	private const HISTORY_OPTION_KEY = 'wps_performance_history';

	/**
	 * Option key for storing alert thresholds.
	 */
	private const THRESHOLDS_OPTION_KEY = 'wps_performance_thresholds';

	/**
	 * Option key for storing current metrics.
	 */
	private const CURRENT_METRICS_KEY = 'wps_performance_current';

	/**
	 * Query log storage.
	 *
	 * @var array
	 */
	private static $query_log = array();

	/**
	 * Initialize the performance monitor.
	 *
	 * @return void
	 */
	public static function init(): void {
		// Enable query logging if not already enabled.
		if ( ! defined( 'SAVEQUERIES' ) ) {
			define( 'SAVEQUERIES', true );
		}

		// Hook into shutdown to log queries and metrics.
		add_action( 'shutdown', array( __CLASS__, 'log_queries' ), 999 );

		// Register admin menu.
		add_action( 'admin_menu', array( __CLASS__, 'add_admin_menu' ) );

		// Register AJAX handlers.
		add_action( 'wp_ajax_wps_performance_export', array( __CLASS__, 'ajax_export_data' ) );
		add_action( 'wp_ajax_wps_performance_clear_history', array( __CLASS__, 'ajax_clear_history' ) );

		// Schedule cleanup of old data.
		if ( ! wp_next_scheduled( 'wps_performance_cleanup' ) ) {
			wp_schedule_event( time(), 'daily', 'wps_performance_cleanup' );
		}
		add_action( 'wps_performance_cleanup', array( __CLASS__, 'cleanup_old_data' ) );
	}

	/**
	 * Log queries and collect metrics at shutdown.
	 *
	 * @return void
	 */
	public static function log_queries(): void {
		global $wpdb;

		// Skip if not in admin or if this is an AJAX request that we want to skip.
		if ( ! is_admin() || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
			return;
		}

		$total_time = 0;
		$slow_queries = array();
		$query_types = array(
			'SELECT' => 0,
			'INSERT' => 0,
			'UPDATE' => 0,
			'DELETE' => 0,
			'OTHER'  => 0,
		);

		// Process queries if available.
		if ( isset( $wpdb->queries ) && is_array( $wpdb->queries ) ) {
			foreach ( $wpdb->queries as $query ) {
				$sql = $query[0] ?? '';
				$time = (float) ( $query[1] ?? 0 );
				$caller = $query[2] ?? '';

				$total_time += $time;

				// Log slow queries (>100ms).
				if ( $time > 0.1 ) {
					$slow_queries[] = array(
						'sql'    => substr( $sql, 0, 200 ), // Truncate for storage.
						'time'   => $time,
						'caller' => $caller,
					);
				}

				// Count query types.
				$sql_upper = strtoupper( trim( $sql ) );
				if ( strpos( $sql_upper, 'SELECT' ) === 0 ) {
					$query_types['SELECT']++;
				} elseif ( strpos( $sql_upper, 'INSERT' ) === 0 ) {
					$query_types['INSERT']++;
				} elseif ( strpos( $sql_upper, 'UPDATE' ) === 0 ) {
					$query_types['UPDATE']++;
				} elseif ( strpos( $sql_upper, 'DELETE' ) === 0 ) {
					$query_types['DELETE']++;
				} else {
					$query_types['OTHER']++;
				}
			}
		}

		// Collect current metrics.
		$metrics = array(
			'timestamp'       => time(),
			'query_count'     => isset( $wpdb->queries ) ? count( $wpdb->queries ) : 0,
			'query_time'      => round( $total_time, 4 ),
			'slow_queries'    => $slow_queries,
			'query_types'     => $query_types,
			'memory'          => memory_get_peak_usage( true ),
			'memory_mb'       => round( memory_get_peak_usage( true ) / 1024 / 1024, 2 ),
			'load_time'       => timer_stop( 0, 4 ),
			'db_size'         => self::get_database_size(),
			'active_plugins'  => count( get_option( 'active_plugins', array() ) ),
		);

		// Store current metrics.
		update_option( self::CURRENT_METRICS_KEY, $metrics, false );

		// Store in history (rolling window).
		self::store_metrics( $metrics );

		// Check for alerts.
		self::check_alerts( $metrics );
	}

	/**
	 * Store metrics in historical data.
	 *
	 * @param array $metrics Metrics to store.
	 * @return void
	 */
	private static function store_metrics( array $metrics ): void {
		$history = get_option( self::HISTORY_OPTION_KEY, array() );

		// Add new entry.
		$history[ $metrics['timestamp'] ] = $metrics;

		// Keep last 90 days.
		$cutoff = time() - ( 90 * DAY_IN_SECONDS );
		$history = array_filter(
			$history,
			function ( $timestamp ) use ( $cutoff ) {
				return $timestamp > $cutoff;
			},
			ARRAY_FILTER_USE_KEY
		);

		update_option( self::HISTORY_OPTION_KEY, $history, false );
	}

	/**
	 * Get current performance metrics.
	 *
	 * @return array
	 */
	public static function get_current_metrics(): array {
		$metrics = get_option( self::CURRENT_METRICS_KEY, array() );

		// If no metrics, return defaults.
		if ( empty( $metrics ) ) {
			return array(
				'query_count'    => 0,
				'query_time'     => 0,
				'memory_mb'      => 0,
				'load_time'      => 0,
				'db_size'        => 0,
				'active_plugins' => 0,
			);
		}

		return $metrics;
	}

	/**
	 * Calculate performance score (0-100).
	 *
	 * @return array Score and grade information.
	 */
	public static function calculate_performance_score(): array {
		$metrics = self::get_current_metrics();

		// Query count score (target: <20, max penalty at 50).
		$query_count = $metrics['query_count'] ?? 0;
		$query_score = max( 0, 100 - ( ( $query_count - 20 ) * 2 ) );
		$query_score = min( 100, $query_score );

		// Load time score (target: <1s).
		$load_time = (float) ( $metrics['load_time'] ?? 1 );
		$load_score = max( 0, 100 - ( $load_time * 50 ) );
		$load_score = min( 100, $load_score );

		// Memory score (target: <50MB).
		$memory_mb = (float) ( $metrics['memory_mb'] ?? 50 );
		$memory_score = max( 0, 100 - ( ( $memory_mb - 50 ) * 2 ) );
		$memory_score = min( 100, $memory_score );

		// Database size score (target: <100MB).
		$db_size = (float) ( $metrics['db_size'] ?? 100 );
		$db_score = max( 0, 100 - ( ( $db_size - 100 ) * 0.5 ) );
		$db_score = min( 100, $db_score );

		// Plugin count score (target: <15 plugins).
		$plugin_count = (int) ( $metrics['active_plugins'] ?? 0 );
		$plugin_score = max( 0, 100 - ( ( $plugin_count - 15 ) * 3 ) );
		$plugin_score = min( 100, $plugin_score );

		// Weighted average.
		$score = (
			$query_score * 0.30 +
			$load_score * 0.25 +
			$memory_score * 0.20 +
			$db_score * 0.15 +
			$plugin_score * 0.10
		);

		$score = round( $score );

		// Determine grade.
		$grade = 'F';
		if ( $score >= 95 ) {
			$grade = 'A+';
		} elseif ( $score >= 90 ) {
			$grade = 'A';
		} elseif ( $score >= 80 ) {
			$grade = 'B';
		} elseif ( $score >= 70 ) {
			$grade = 'C';
		} elseif ( $score >= 60 ) {
			$grade = 'D';
		}

		// Determine color.
		$color = '#dc3545'; // Red for F.
		if ( $score >= 90 ) {
			$color = '#28a745'; // Green for A+/A.
		} elseif ( $score >= 70 ) {
			$color = '#ffc107'; // Yellow for B/C.
		} elseif ( $score >= 60 ) {
			$color = '#fd7e14'; // Orange for D.
		}

		return array(
			'score'       => $score,
			'grade'       => $grade,
			'color'       => $color,
			'breakdown'   => array(
				'queries' => round( $query_score ),
				'load'    => round( $load_score ),
				'memory'  => round( $memory_score ),
				'db'      => round( $db_score ),
				'plugins' => round( $plugin_score ),
			),
		);
	}

	/**
	 * Get database size in MB.
	 *
	 * @return float Database size in MB.
	 */
	private static function get_database_size(): float {
		global $wpdb;

		$size = $wpdb->get_var(
			$wpdb->prepare(
				'SELECT SUM(data_length + index_length) / 1024 / 1024 as size
				FROM information_schema.TABLES
				WHERE table_schema = %s',
				DB_NAME
			)
		);

		return $size ? round( (float) $size, 2 ) : 0;
	}

	/**
	 * Get database statistics.
	 *
	 * @return array Database statistics.
	 */
	public static function get_database_stats(): array {
		global $wpdb;

		// Get total size.
		$total_size = self::get_database_size();

		// Get largest tables.
		$largest_tables = $wpdb->get_results(
			$wpdb->prepare(
				'SELECT table_name, ROUND((data_length + index_length) / 1024 / 1024, 2) AS size_mb
				FROM information_schema.TABLES
				WHERE table_schema = %s
				ORDER BY (data_length + index_length) DESC
				LIMIT 10',
				DB_NAME
			),
			ARRAY_A
		);

		// Count transients.
		$transient_count = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->options}
			WHERE option_name LIKE '_transient_%'"
		);

		// Count expired transients.
		$expired_transients = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->options} as a
				INNER JOIN {$wpdb->options} as b
				ON a.option_name = CONCAT('_transient_timeout_', SUBSTRING(b.option_name, 12))
				WHERE a.option_value < %d
				AND b.option_name LIKE '_transient_%'",
				time()
			)
		);

		// Count orphaned postmeta.
		$orphaned_postmeta = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->postmeta} pm
			LEFT JOIN {$wpdb->posts} p ON pm.post_id = p.ID
			WHERE p.ID IS NULL"
		);

		return array(
			'total_size'         => $total_size,
			'largest_tables'     => $largest_tables,
			'transient_count'    => (int) $transient_count,
			'expired_transients' => (int) $expired_transients,
			'orphaned_postmeta'  => (int) $orphaned_postmeta,
		);
	}

	/**
	 * Get historical metrics for graphs.
	 *
	 * @param int $days Number of days to retrieve.
	 * @return array Historical metrics.
	 */
	public static function get_historical_metrics( int $days = 7 ): array {
		$history = get_option( self::HISTORY_OPTION_KEY, array() );
		$cutoff = time() - ( $days * DAY_IN_SECONDS );

		// Filter to requested timeframe.
		$filtered = array_filter(
			$history,
			function ( $timestamp ) use ( $cutoff ) {
				return $timestamp > $cutoff;
			},
			ARRAY_FILTER_USE_KEY
		);

		// Sort by timestamp.
		ksort( $filtered );

		return $filtered;
	}

	/**
	 * Get optimization recommendations.
	 *
	 * @return array List of recommendations.
	 */
	public static function get_recommendations(): array {
		$recommendations = array();
		$metrics = self::get_current_metrics();
		$db_stats = self::get_database_stats();

		// Check for high query count.
		if ( isset( $metrics['query_count'] ) && $metrics['query_count'] > 50 ) {
			$recommendations[] = array(
				'type'        => 'warning',
				'title'       => __( 'High Query Count', 'plugin-wp-support-thisismyurl' ),
				'description' => sprintf(
					/* translators: %d: number of queries */
					__( 'Query count is high (%d queries) - consider object caching or query optimization.', 'plugin-wp-support-thisismyurl' ),
					$metrics['query_count']
				),
			);
		}

		// Check for expired transients.
		if ( $db_stats['expired_transients'] > 100 ) {
			$recommendations[] = array(
				'type'        => 'warning',
				'title'       => __( 'Expired Transients', 'plugin-wp-support-thisismyurl' ),
				'description' => sprintf(
					/* translators: %d: number of expired transients */
					__( 'Database has %d expired transients - cleanup recommended.', 'plugin-wp-support-thisismyurl' ),
					$db_stats['expired_transients']
				),
			);
		}

		// Check for high memory usage.
		if ( isset( $metrics['memory_mb'] ) && $metrics['memory_mb'] > 100 ) {
			$memory_limit = ini_get( 'memory_limit' );
			$recommendations[] = array(
				'type'        => 'critical',
				'title'       => __( 'High Memory Usage', 'plugin-wp-support-thisismyurl' ),
				'description' => sprintf(
					/* translators: 1: current memory usage, 2: memory limit */
					__( 'Memory usage is high (%1$s MB). Current limit: %2$s. Consider increasing WP_MEMORY_LIMIT.', 'plugin-wp-support-thisismyurl' ),
					$metrics['memory_mb'],
					$memory_limit
				),
			);
		}

		// Check for orphaned data.
		if ( $db_stats['orphaned_postmeta'] > 0 ) {
			$recommendations[] = array(
				'type'        => 'info',
				'title'       => __( 'Orphaned Data', 'plugin-wp-support-thisismyurl' ),
				'description' => sprintf(
					/* translators: %d: number of orphaned postmeta records */
					__( 'Found %d orphaned postmeta records - database cleanup recommended.', 'plugin-wp-support-thisismyurl' ),
					$db_stats['orphaned_postmeta']
				),
			);
		}

		// Check for slow load time.
		if ( isset( $metrics['load_time'] ) && $metrics['load_time'] > 2 ) {
			$recommendations[] = array(
				'type'        => 'warning',
				'title'       => __( 'Slow Page Load', 'plugin-wp-support-thisismyurl' ),
				'description' => sprintf(
					/* translators: %s: load time in seconds */
					__( 'Page load time is %s seconds - consider enabling caching and optimization features.', 'plugin-wp-support-thisismyurl' ),
					$metrics['load_time']
				),
			);
		}

		return $recommendations;
	}

	/**
	 * Check for performance alerts.
	 *
	 * @param array $metrics Current metrics.
	 * @return void
	 */
	private static function check_alerts( array $metrics ): void {
		$thresholds = self::get_thresholds();

		// Check query count threshold.
		if ( $metrics['query_count'] > $thresholds['query_count'] ) {
			self::trigger_alert(
				'query_count',
				sprintf(
					/* translators: 1: current query count, 2: threshold */
					__( 'Query count (%1$d) exceeds threshold (%2$d)', 'plugin-wp-support-thisismyurl' ),
					$metrics['query_count'],
					$thresholds['query_count']
				)
			);
		}

		// Check load time threshold.
		if ( $metrics['load_time'] > $thresholds['load_time'] ) {
			self::trigger_alert(
				'load_time',
				sprintf(
					/* translators: 1: current load time, 2: threshold */
					__( 'Load time (%1$s s) exceeds threshold (%2$s s)', 'plugin-wp-support-thisismyurl' ),
					$metrics['load_time'],
					$thresholds['load_time']
				)
			);
		}

		// Check memory threshold (80% of limit).
		$memory_limit = ini_get( 'memory_limit' );
		$memory_limit_mb = self::parse_memory_limit( $memory_limit );
		$memory_threshold = $memory_limit_mb * 0.8;

		if ( $metrics['memory_mb'] > $memory_threshold ) {
			self::trigger_alert(
				'memory',
				sprintf(
					/* translators: 1: current memory usage, 2: threshold */
					__( 'Memory usage (%1$s MB) exceeds 80%% of limit (%2$s MB)', 'plugin-wp-support-thisismyurl' ),
					$metrics['memory_mb'],
					round( $memory_threshold, 2 )
				)
			);
		}
	}

	/**
	 * Parse memory limit string to MB.
	 *
	 * @param string $limit Memory limit string (e.g., "128M", "1G").
	 * @return float Memory limit in MB.
	 */
	private static function parse_memory_limit( string $limit ): float {
		$limit = trim( $limit );
		$unit = strtoupper( substr( $limit, -1 ) );
		$value = (float) substr( $limit, 0, -1 );

		switch ( $unit ) {
			case 'G':
				return $value * 1024;
			case 'M':
				return $value;
			case 'K':
				return $value / 1024;
			default:
				return $value / 1024 / 1024;
		}
	}

	/**
	 * Trigger performance alert.
	 *
	 * @param string $type Alert type.
	 * @param string $message Alert message.
	 * @return void
	 */
	private static function trigger_alert( string $type, string $message ): void {
		// Log to WPS Activity Logger if available.
		// TODO: Re-enable when WPS_Activity_Logger::log_event() method exists
		// if ( class_exists( '\\WPS\\CoreSupport\\WPS_Activity_Logger' ) ) {
		// 	WPS_Activity_Logger::log_event(
		// 		'performance_alert',
		// 		$message,
		// 		array(
		// 			'alert_type' => $type,
		// 		)
		// 	);
		// }

		// Store alert in transient to display in admin.
		$alerts = get_transient( 'wps_performance_alerts' );
		if ( ! is_array( $alerts ) ) {
			$alerts = array();
		}

		$alerts[] = array(
			'type'      => $type,
			'message'   => $message,
			'timestamp' => time(),
		);

		set_transient( 'wps_performance_alerts', $alerts, HOUR_IN_SECONDS );
	}

	/**
	 * Get alert thresholds.
	 *
	 * @return array Threshold values.
	 */
	public static function get_thresholds(): array {
		$defaults = array(
			'query_count' => 50,
			'load_time'   => 2,
			'memory'      => 80, // Percentage of limit.
		);

		return get_option( self::THRESHOLDS_OPTION_KEY, $defaults );
	}

	/**
	 * Update alert thresholds.
	 *
	 * @param array $thresholds New threshold values.
	 * @return bool Success status.
	 */
	public static function update_thresholds( array $thresholds ): bool {
		return update_option( self::THRESHOLDS_OPTION_KEY, $thresholds );
	}

	/**
	 * Cleanup old historical data (90+ days).
	 *
	 * @return void
	 */
	public static function cleanup_old_data(): void {
		$history = get_option( self::HISTORY_OPTION_KEY, array() );
		$cutoff = time() - ( 90 * DAY_IN_SECONDS );

		$history = array_filter(
			$history,
			function ( $timestamp ) use ( $cutoff ) {
				return $timestamp > $cutoff;
			},
			ARRAY_FILTER_USE_KEY
		);

		update_option( self::HISTORY_OPTION_KEY, $history, false );
	}

	/**
	 * Add admin menu.
	 *
	 * @return void
	 */
	public static function add_admin_menu(): void {
		// The Performance Dashboard will be added as a tab in the main dashboard.
		// No separate menu item needed.
	}

	/**
	 * AJAX handler for exporting performance data.
	 *
	 * @return void
	 */
	public static function ajax_export_data(): void {
		check_ajax_referer( 'wps_performance_export', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'plugin-wp-support-thisismyurl' ) ) );
		}

		$format = isset( $_POST['format'] ) ? sanitize_key( $_POST['format'] ) : 'json';
		$days = isset( $_POST['days'] ) ? absint( $_POST['days'] ) : 30;

		$data = self::get_historical_metrics( $days );

		if ( 'csv' === $format ) {
			$csv = self::export_csv( $data );
			wp_send_json_success( array( 'data' => $csv, 'format' => 'csv' ) );
		} else {
			wp_send_json_success( array( 'data' => wp_json_encode( $data ), 'format' => 'json' ) );
		}
	}

	/**
	 * Convert data to CSV format.
	 *
	 * @param array $data Historical data.
	 * @return string CSV data.
	 */
	private static function export_csv( array $data ): string {
		$csv = "Timestamp,Query Count,Query Time (s),Memory (MB),Load Time (s),DB Size (MB),Active Plugins\n";

		foreach ( $data as $timestamp => $metrics ) {
			$csv .= sprintf(
				"%s,%d,%s,%s,%s,%s,%d\n",
				gmdate( 'Y-m-d H:i:s', $timestamp ),
				$metrics['query_count'] ?? 0,
				$metrics['query_time'] ?? 0,
				$metrics['memory_mb'] ?? 0,
				$metrics['load_time'] ?? 0,
				$metrics['db_size'] ?? 0,
				$metrics['active_plugins'] ?? 0
			);
		}

		return $csv;
	}

	/**
	 * AJAX handler for clearing historical data.
	 *
	 * @return void
	 */
	public static function ajax_clear_history(): void {
		check_ajax_referer( 'wps_performance_clear', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'plugin-wp-support-thisismyurl' ) ) );
		}

		delete_option( self::HISTORY_OPTION_KEY );
		wp_send_json_success( array( 'message' => __( 'Historical data cleared', 'plugin-wp-support-thisismyurl' ) ) );
	}
}
