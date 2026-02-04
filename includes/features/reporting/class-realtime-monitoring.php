<?php
/**
 * Real-Time Monitoring & Alerting
 *
 * Continuously monitors site health and triggers intelligent alerts
 * for anomalies and higher-severity issues. Proactive incident detection.
 *
 * Philosophy:
 * - #8 Inspire Confidence: Know what's happening in real-time
 * - #9 Show Value: Prevent issues before they become problems
 * - #1 Helpful Neighbor: Smart alerts, not noise
 *
 * @package    WPShadow
 * @subpackage Reports
 * @since      1.6030.2200
 */

declare(strict_types=1);

namespace WPShadow\Reports;

use WPShadow\Core\Activity_Logger;
use WPShadow\Core\Hook_Subscriber_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Real-Time Monitoring Class
 *
 * Monitors site health in real-time and generates intelligent alerts.
 *
 * @since 1.6030.2200
 */
class Realtime_Monitoring extends Hook_Subscriber_Base {

	/**
	 * Monitoring interval in seconds
	 */
	const MONITOR_INTERVAL = 300; // 5 minutes

	/**
	 * Get hook subscriptions.
	 *
	 * @since  1.7035.1400
	 * @return array Hook subscriptions.
	 */
	protected static function get_hooks(): array {
		return array(
			'wpshadow_realtime_monitor'  => 'run_monitoring_cycle',
			'cron_schedules'             => 'add_cron_interval',
		);
	}

	/**
	 * Initialize monitoring (deprecated)
	 *
	 * @deprecated 1.7035.1400 Use Realtime_Monitoring::subscribe() instead
	 * @since      1.6030.2200
	 * @return     void
	 */
	public static function init(): void {
		// Schedule recurring monitoring
		if ( ! wp_next_scheduled( 'wpshadow_realtime_monitor' ) ) {
			wp_schedule_event( time(), 'wpshadow_5min', 'wpshadow_realtime_monitor' );
		}

		// Subscribe to hooks
		self::subscribe();
	}

	/**
	 * Add 5-minute cron interval
	 *
	 * @since  1.6030.2200
	 * @param  array $schedules Existing schedules.
	 * @return array Modified schedules.
	 */
	public static function add_cron_interval( array $schedules ): array {
		$schedules['wpshadow_5min'] = array(
			'interval' => self::MONITOR_INTERVAL,
			'display'  => __( 'Every 5 Minutes', 'wpshadow' ),
		);
		return $schedules;
	}

	/**
	 * Run monitoring cycle
	 *
	 * @since  1.6030.2200
	 * @return void
	 */
	public static function run_monitoring_cycle(): void {
		$start_time = microtime( true );

		// Collect current metrics
		$metrics = self::collect_metrics();

		// Detect anomalies
		$anomalies = self::detect_anomalies( $metrics );

		// Generate alerts if needed
		if ( ! empty( $anomalies ) ) {
			self::process_anomalies( $anomalies );
		}

		// Store metrics history
		self::store_metrics( $metrics );

		// Update live dashboard
		self::update_live_dashboard( $metrics, $anomalies );

		$execution_time = microtime( true ) - $start_time;

		// Log monitoring cycle
		Activity_Logger::log(
			'realtime_monitor_cycle',
			sprintf( 'Monitoring cycle completed in %.2fs', $execution_time ),
			'',
			array(
				'metrics'         => $metrics,
				'anomalies_count' => count( $anomalies ),
				'execution_time'  => $execution_time,
			)
		);
	}

	/**
	 * Collect current metrics
	 *
	 * @since  1.6030.2200
	 * @return array Current site metrics.
	 */
	private static function collect_metrics(): array {
		global $wpdb;

		return array(
			'timestamp'        => time(),
			'health_score'     => self::get_current_health_score(),
			'active_plugins'   => count( get_option( 'active_plugins', array() ) ),
			'db_queries'       => (int) $wpdb->num_queries,
			'memory_usage'     => memory_get_usage( true ),
			'memory_peak'      => memory_get_peak_usage( true ),
			'load_average'     => self::get_server_load(),
			'response_time'    => self::measure_response_time(),
			'error_count'      => self::get_recent_error_count(),
			'failed_logins'    => self::get_recent_failed_logins(),
			'disk_usage'       => self::get_disk_usage(),
			'uptime'           => self::get_uptime_status(),
		);
	}

	/**
	 * Detect anomalies in metrics
	 *
	 * @since  1.6030.2200
	 * @param  array $current_metrics Current metrics.
	 * @return array Detected anomalies.
	 */
	private static function detect_anomalies( array $current_metrics ): array {
		$historical = self::get_historical_metrics();
		$anomalies = array();

		// Health score drop
		if ( ! empty( $historical['health_score'] ) ) {
			$avg_health = array_sum( $historical['health_score'] ) / count( $historical['health_score'] );
			$drop_threshold = $avg_health - 10;
			
			if ( $current_metrics['health_score'] < $drop_threshold ) {
				$anomalies[] = array(
					'type'        => 'health_drop',
					'severity'    => 'high',
					'title'       => __( 'Health Score Drop Detected', 'wpshadow' ),
					'description' => sprintf(
						/* translators: 1: current score, 2: average score */
						__( 'Health score dropped to %1$d (average: %2$d)', 'wpshadow' ),
						$current_metrics['health_score'],
						round( $avg_health )
					),
					'current'     => $current_metrics['health_score'],
					'expected'    => round( $avg_health ),
					'action'      => __( 'Run diagnostic scan immediately', 'wpshadow' ),
				);
			}
		}

		// Memory spike
		if ( ! empty( $historical['memory_usage'] ) ) {
			$avg_memory = array_sum( $historical['memory_usage'] ) / count( $historical['memory_usage'] );
			$spike_threshold = $avg_memory * 1.5;
			
			if ( $current_metrics['memory_usage'] > $spike_threshold ) {
				$anomalies[] = array(
					'type'        => 'memory_spike',
					'severity'    => 'medium',
					'title'       => __( 'Memory Usage Spike', 'wpshadow' ),
					'description' => sprintf(
						/* translators: 1: current memory, 2: average memory */
						__( 'Memory usage spiked to %1$s (average: %2$s)', 'wpshadow' ),
						size_format( $current_metrics['memory_usage'] ),
						size_format( $avg_memory )
					),
					'current'     => $current_metrics['memory_usage'],
					'expected'    => round( $avg_memory ),
					'action'      => __( 'Check for resource-intensive processes', 'wpshadow' ),
				);
			}
		}

		// Response time degradation
		if ( ! empty( $historical['response_time'] ) ) {
			$avg_response = array_sum( $historical['response_time'] ) / count( $historical['response_time'] );
			$slow_threshold = $avg_response * 2;
			
			if ( $current_metrics['response_time'] > $slow_threshold ) {
				$anomalies[] = array(
					'type'        => 'slow_response',
					'severity'    => 'high',
					'title'       => __( 'Slow Response Time Detected', 'wpshadow' ),
					'description' => sprintf(
						/* translators: 1: current time, 2: average time */
						__( 'Response time increased to %.2fs (average: %.2fs)', 'wpshadow' ),
						$current_metrics['response_time'],
						$avg_response
					),
					'current'     => $current_metrics['response_time'],
					'expected'    => $avg_response,
					'action'      => __( 'Check for slow queries or external API issues', 'wpshadow' ),
				);
			}
		}

		// Check for unusual login activity (possible brute force).
		if ( $current_metrics['failed_logins'] > 10 ) {
			$anomalies[] = array(
				'type'        => 'security_threat',
				'severity'    => 'critical',
				'title'       => __( 'Potential Brute Force Attack', 'wpshadow' ),
				'description' => sprintf(
					/* translators: %d: number of failed logins */
					__( '%d failed login attempts in last 5 minutes', 'wpshadow' ),
					$current_metrics['failed_logins']
				),
				'current'     => $current_metrics['failed_logins'],
				'expected'    => 0,
				'action'      => __( 'Enable login throttling and review security logs', 'wpshadow' ),
			);
		}

		// Check for a sudden rise in errors.
		if ( $current_metrics['error_count'] > 5 ) {
			$anomalies[] = array(
				'type'        => 'error_spike',
				'severity'    => 'high',
				'title'       => __( 'Error Rate Increased', 'wpshadow' ),
				'description' => sprintf(
					/* translators: %d: number of errors */
					__( '%d errors logged in last 5 minutes', 'wpshadow' ),
					$current_metrics['error_count']
				),
				'current'     => $current_metrics['error_count'],
				'expected'    => 0,
				'action'      => __( 'Check error logs for details', 'wpshadow' ),
			);
		}

		return $anomalies;
	}

	/**
	 * Process detected anomalies
	 *
	 * @since  1.6030.2200
	 * @param  array $anomalies Detected anomalies.
	 * @return void
	 */
	private static function process_anomalies( array $anomalies ): void {
		foreach ( $anomalies as $anomaly ) {
			// Log anomaly
			Activity_Logger::log(
				'anomaly_detected',
				$anomaly['title'],
				'',
				$anomaly
			);

			// Send alert if enabled and severity is high enough
			if ( self::should_send_alert( $anomaly ) ) {
				self::send_alert( $anomaly );
			}

			// Create incident record
			self::create_incident( $anomaly );

			// Auto-remediate if possible
			if ( self::can_auto_remediate( $anomaly ) ) {
				self::attempt_remediation( $anomaly );
			}
		}
	}

	/**
	 * Check if alert should be sent
	 *
	 * @since  1.6030.2200
	 * @param  array $anomaly Anomaly data.
	 * @return bool True if alert should be sent.
	 */
	private static function should_send_alert( array $anomaly ): bool {
		// Check if alerts are enabled
		if ( ! get_option( 'wpshadow_realtime_alerts_enabled', true ) ) {
			return false;
		}

		// Check severity threshold
		$min_severity = get_option( 'wpshadow_alert_min_severity', 'medium' );
		$severity_levels = array( 'low' => 1, 'medium' => 2, 'high' => 3, 'critical' => 4 );
		
		$anomaly_level = $severity_levels[ $anomaly['severity'] ] ?? 0;
		$threshold_level = $severity_levels[ $min_severity ] ?? 2;

		if ( $anomaly_level < $threshold_level ) {
			return false;
		}

		// Check if we've already alerted for this type recently (avoid alert fatigue)
		$last_alert = \WPShadow\Core\Cache_Manager::get( 'last_alert_' . $anomaly['type'], 'wpshadow_monitoring' );
		if ( $last_alert ) {
			return false; // Don't alert again within cooldown period
		}

		return true;
	}

	/**
	 * Send alert notification
	 *
	 * @since  1.6030.2200
	 * @param  array $anomaly Anomaly data.
	 * @return void
	 */
	private static function send_alert( array $anomaly ): void {
		$admin_email = get_option( 'admin_email' );
		$site_name = get_bloginfo( 'name' );

		$subject = sprintf(
			'[%s] %s - %s',
			$site_name,
			ucfirst( $anomaly['severity'] ),
			$anomaly['title']
		);

		$message = sprintf(
			"%s\n\n%s\n\nAction Required: %s\n\nTime: %s\n\nView Details: %s",
			$anomaly['title'],
			$anomaly['description'],
			$anomaly['action'],
			current_time( 'Y-m-d H:i:s' ),
			admin_url( 'admin.php?page=wpshadow-monitoring' )
		);

		wp_mail( $admin_email, $subject, $message );

		// Set cooldown to avoid alert fatigue (30 minutes)
		\WPShadow\Core\Cache_Manager::set( 'last_alert_' . $anomaly['type'], time(), 1800 , 'wpshadow_monitoring');

		/**
		 * Fires after an alert is sent.
		 *
		 * @since 1.6030.2200
		 *
		 * @param array $anomaly Anomaly data.
		 */
		do_action( 'wpshadow_alert_sent', $anomaly );
	}

	/**
	 * Create incident record
	 *
	 * @since  1.6030.2200
	 * @param  array $anomaly Anomaly data.
	 * @return void
	 */
	private static function create_incident( array $anomaly ): void {
		$incidents = get_option( 'wpshadow_incidents', array() );

		$incident = array(
			'id'          => uniqid( 'incident_' ),
			'timestamp'   => time(),
			'type'        => $anomaly['type'],
			'severity'    => $anomaly['severity'],
			'title'       => $anomaly['title'],
			'description' => $anomaly['description'],
			'status'      => 'open',
			'resolved_at' => null,
		);

		$incidents[] = $incident;

		// Keep only last 100 incidents
		$incidents = array_slice( $incidents, -100 );

		update_option( 'wpshadow_incidents', $incidents );
	}

	/**
	 * Check if anomaly can be auto-remediated
	 *
	 * @since  1.6030.2200
	 * @param  array $anomaly Anomaly data.
	 * @return bool True if auto-remediation possible.
	 */
	private static function can_auto_remediate( array $anomaly ): bool {
		$auto_remediate_types = array( 'memory_spike', 'slow_response' );
		return in_array( $anomaly['type'], $auto_remediate_types, true );
	}

	/**
	 * Attempt automatic remediation
	 *
	 * @since  1.6030.2200
	 * @param  array $anomaly Anomaly data.
	 * @return void
	 */
	private static function attempt_remediation( array $anomaly ): void {
		switch ( $anomaly['type'] ) {
			case 'memory_spike':
				// Clear caches
				wp_cache_flush();
				Activity_Logger::log(
					'auto_remediation',
					'Cleared caches due to memory spike',
					'',
					array( 'anomaly' => $anomaly )
				);
				break;

			case 'slow_response':
				// Clear object cache
				if ( function_exists( 'wp_cache_flush' ) ) {
					wp_cache_flush();
				}
				break;
		}
	}

	/**
	 * Store metrics in history
	 *
	 * @since  1.6030.2200
	 * @param  array $metrics Current metrics.
	 * @return void
	 */
	private static function store_metrics( array $metrics ): void {
		$history = get_option( 'wpshadow_metrics_history', array() );
		$history[] = $metrics;

		// Keep last 288 entries (24 hours at 5-minute intervals)
		$history = array_slice( $history, -288 );

		update_option( 'wpshadow_metrics_history', $history );
	}

	/**
	 * Update live dashboard
	 *
	 * @since  1.6030.2200
	 * @param  array $metrics Current metrics.
	 * @param  array $anomalies Detected anomalies.
	 * @return void
	 */
	private static function update_live_dashboard( array $metrics, array $anomalies ): void {
		update_option( 'wpshadow_live_metrics', $metrics );
		update_option( 'wpshadow_live_anomalies', $anomalies );
		update_option( 'wpshadow_last_monitor_run', time() );
	}

	/**
	 * Get historical metrics
	 *
	 * @since  1.6030.2200
	 * @return array Historical metrics by type.
	 */
	private static function get_historical_metrics(): array {
		$history = get_option( 'wpshadow_metrics_history', array() );
		$organized = array();

		foreach ( $history as $entry ) {
			foreach ( $entry as $key => $value ) {
				if ( $key !== 'timestamp' && is_numeric( $value ) ) {
					if ( ! isset( $organized[ $key ] ) ) {
						$organized[ $key ] = array();
					}
					$organized[ $key ][] = $value;
				}
			}
		}

		return $organized;
	}

	/**
	 * Get current health score
	 *
	 * @since  1.6030.2200
	 * @return float Health score.
	 */
	private static function get_current_health_score(): float {
		$health = get_option( 'wpshadow_health_status', array() );
		return (float) ( $health['health_score'] ?? 75 );
	}

	/**
	 * Get server load average
	 *
	 * @since  1.6030.2200
	 * @return float|null Load average or null if unavailable.
	 */
	private static function get_server_load(): ?float {
		if ( function_exists( 'sys_getloadavg' ) ) {
			$load = sys_getloadavg();
			return $load[0] ?? null;
		}
		return null;
	}

	/**
	 * Measure site response time
	 *
	 * @since  1.6030.2200
	 * @return float Response time in seconds.
	 */
	private static function measure_response_time(): float {
		$start = microtime( true );
		
		// Make internal request
		$response = wp_remote_get( home_url(), array( 'timeout' => 10 ) );
		
		$end = microtime( true );
		
		return $end - $start;
	}

	/**
	 * Get recent error count
	 *
	 * @since  1.6030.2200
	 * @return int Number of recent errors.
	 */
	private static function get_recent_error_count(): int {
		// Placeholder: integrate with error logging.
		// Return 0 until a log source is wired in.
		return 0;
	}

	/**
	 * Get recent failed login attempts
	 *
	 * @since  1.6030.2200
	 * @return int Number of failed logins.
	 */
	private static function get_recent_failed_logins(): int {
		$attempts = \WPShadow\Core\Cache_Manager::get( 'failed_logins_5min', 'wpshadow_monitoring' );
		return (int) ( $attempts ?? 0 );
	}

	/**
	 * Get disk usage percentage
	 *
	 * @since  1.6030.2200
	 * @return float|null Disk usage percentage or null if unavailable.
	 */
	private static function get_disk_usage(): ?float {
		if ( function_exists( 'disk_free_space' ) && function_exists( 'disk_total_space' ) ) {
			$free = disk_free_space( ABSPATH );
			$total = disk_total_space( ABSPATH );
			
			if ( $free && $total ) {
				return ( ( $total - $free ) / $total ) * 100;
			}
		}
		return null;
	}

	/**
	 * Get uptime status
	 *
	 * @since  1.6030.2200
	 * @return bool True if site is up.
	 */
	private static function get_uptime_status(): bool {
		// Site is up if we're running this code
		return true;
	}

	/**
	 * Get live dashboard data
	 *
	 * @since  1.6030.2200
	 * @return array Live monitoring data.
	 */
	public static function get_live_dashboard_data(): array {
		return array(
			'current_metrics' => get_option( 'wpshadow_live_metrics', array() ),
			'active_anomalies' => get_option( 'wpshadow_live_anomalies', array() ),
			'last_updated'    => get_option( 'wpshadow_last_monitor_run', 0 ),
			'incidents_24h'   => self::get_recent_incidents( 24 ),
			'status'          => self::get_overall_status(),
		);
	}

	/**
	 * Get recent incidents
	 *
	 * @since  1.6030.2200
	 * @param  int $hours Hours to look back.
	 * @return array Recent incidents.
	 */
	private static function get_recent_incidents( int $hours ): array {
		$incidents = get_option( 'wpshadow_incidents', array() );
		$cutoff = time() - ( $hours * 3600 );

		return array_filter( $incidents, function( $incident ) use ( $cutoff ) {
			return $incident['timestamp'] >= $cutoff;
		} );
	}

	/**
	 * Get overall system status
	 *
	 * @since  1.6030.2200
	 * @return array Status information.
	 */
	private static function get_overall_status(): array {
		$anomalies = get_option( 'wpshadow_live_anomalies', array() );
		$critical = array_filter( $anomalies, function( $a ) {
			return $a['severity'] === 'critical';
		} );

		if ( ! empty( $critical ) ) {
			return array(
				'status' => 'critical',
				'label'  => __( 'Critical Issues Detected', 'wpshadow' ),
				'color'  => '#dc2626',
			);
		}

		if ( ! empty( $anomalies ) ) {
			return array(
				'status' => 'warning',
				'label'  => __( 'Some Issues Detected', 'wpshadow' ),
				'color'  => '#f59e0b',
			);
		}

		return array(
			'status' => 'healthy',
			'label'  => __( 'All Systems Normal', 'wpshadow' ),
			'color'  => '#10b981',
		);
	}
}
