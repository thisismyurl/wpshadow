<?php
/**
 * Feature: Real-Time Traffic Monitor
 *
 * Real-time traffic monitoring and analytics.
 *
 * @package WPShadow\CoreSupport
 * @since 1.2601.75000
 */

declare(strict_types=1);

namespace WPShadow\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WPSHADOW_Feature_Traffic_Monitor
 *
 * Monitor live traffic with real-time request logging, visitor analytics, and bot detection.
 */
final class WPSHADOW_Feature_Traffic_Monitor extends WPSHADOW_Abstract_Feature {

	/**
	 * Database table name.
	 */
	private const TABLE_NAME = 'wpshadow_traffic_log';

	/**
	 * Maximum log retention (days).
	 */
	private const LOG_RETENTION_DAYS = 30;

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			array(
				'id'                 => 'traffic-monitor',
				'name'               => __( 'Traffic Monitor', 'plugin-wpshadow' ),
			'description'        => __( 'Logs live traffic with lightweight request details, highlights bots and suspicious spikes, and shows simple analytics so you can see who is hitting your site. Helps spot attacks or misbehaving crawlers quickly, keeps retention limited to reduce storage, and provides filters so you can focus on meaningful events.', 'plugin-wpshadow' ),
				'scope'              => 'core',
				'default_enabled'    => false,
				'version'            => '1.0.0',
				'widget_group'       => 'security',
				'widget_label'       => __( 'Security', 'plugin-wpshadow' ),
				'widget_description' => __( 'Advanced security features', 'plugin-wpshadow' ),
				'license_level'      => 3,
				'minimum_capability' => 'manage_options',
				'icon'               => 'dashicons-networking',
				'category'           => 'security',
				'priority'           => 20,
				'dashboard'          => 'overview',
				'widget_column'      => 'left',
				'widget_priority'    => 20,
			)
		);
	}

	/**
	 * Register hooks when feature is enabled.
	 *
	 * @return void
	 */
	public function register(): void {
		if ( ! $this->is_enabled() ) {
			return;
		}

		// Request logging.
		add_action( 'wp_loaded', array( $this, 'log_request' ), 99999 );

		// AJAX handlers for live updates.
		add_action( 'wp_ajax_WPSHADOW_get_live_traffic', array( $this, 'ajax_get_live_traffic' ) );
		add_action( 'wp_ajax_WPSHADOW_get_traffic_stats', array( $this, 'ajax_get_traffic_stats' ) );

		// Log cleanup.
		if ( ! wp_next_scheduled( 'wpshadow_traffic_cleanup' ) ) {
			wp_schedule_event( time(), 'daily', 'wpshadow_traffic_cleanup' );
		}
		add_action( 'wpshadow_traffic_cleanup', array( $this, 'cleanup_old_logs' ) );

		// Create database table if needed.
		$this->maybe_create_table();
	}

	/**
	 * Log current request.
	 *
	 * @return int|false Log entry ID or false on failure.
	 */
	public function log_request(): int|false {
		global $wpdb;

		// Skip logging for admin AJAX requests.
		if ( wp_doing_ajax() ) {
			return false;
		}

		// Skip logging for heartbeat requests.
		if ( isset( $_POST['action'] ) && 'heartbeat' === $_POST['action'] ) {
			return false;
		}

		$table_name = $wpdb->prefix . self::TABLE_NAME;
		$ip         = $this->get_client_ip();
		$user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
		$request_uri = $_SERVER['REQUEST_URI'] ?? '';
		$method     = $_SERVER['REQUEST_METHOD'] ?? 'GET';
		$bot_info   = $this->detect_bot( $user_agent );

		$data = array(
			'timestamp'    => current_time( 'mysql' ),
			'ip'           => $ip,
			'user_id'      => get_current_user_id(),
			'url'          => $request_uri,
			'method'       => $method,
			'user_agent'   => $user_agent,
			'request_type' => $bot_info['is_bot'] ? 'bot' : 'human',
			'bot_type'     => $bot_info['type'],
		);

		$result = $wpdb->insert( $table_name, $data, array( '%s', '%s', '%d', '%s', '%s', '%s', '%s', '%s' ) );

		return $result ? $wpdb->insert_id : false;
	}

	/**
	 * Get client IP address.
	 *
	 * @return string Client IP.
	 */
	private function get_client_ip(): string {
		$ip_keys = array(
			'HTTP_CF_CONNECTING_IP',
			'HTTP_X_FORWARDED_FOR',
			'HTTP_X_REAL_IP',
			'REMOTE_ADDR',
		);

		foreach ( $ip_keys as $key ) {
			if ( ! empty( $_SERVER[ $key ] ) ) {
				$ip = sanitize_text_field( wp_unslash( $_SERVER[ $key ] ) );
				if ( filter_var( $ip, FILTER_VALIDATE_IP ) ) {
					return $ip;
				}
			}
		}

		return '0.0.0.0';
	}

	/**
	 * Detect if request is from a bot.
	 *
	 * @param string $user_agent User agent string.
	 * @return array Bot detection result.
	 */
	private function detect_bot( string $user_agent ): array {
		$bot_patterns = array(
			'googlebot'       => 'search_engine',
			'bingbot'         => 'search_engine',
			'yahoo'           => 'search_engine',
			'duckduckbot'     => 'search_engine',
			'baiduspider'     => 'search_engine',
			'yandexbot'       => 'search_engine',
			'facebookexternalhit' => 'social',
			'twitterbot'      => 'social',
			'linkedinbot'     => 'social',
			'slackbot'        => 'monitoring',
			'pingdom'         => 'monitoring',
			'uptimerobot'     => 'monitoring',
		);

		$user_agent_lower = strtolower( $user_agent );

		foreach ( $bot_patterns as $pattern => $category ) {
			if ( strpos( $user_agent_lower, $pattern ) !== false ) {
				return array(
					'is_bot'   => true,
					'type'     => $pattern,
					'category' => $category,
				);
			}
		}

		return array(
			'is_bot'   => false,
			'type'     => '',
			'category' => '',
		);
	}

	/**
	 * Get live traffic (last N requests).
	 *
	 * @param int $limit Number of requests to retrieve.
	 * @return array Traffic log entries.
	 */
	private function get_live_traffic( int $limit = 50 ): array {
		global $wpdb;

		$table_name = $wpdb->prefix . self::TABLE_NAME;

		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$table_name} ORDER BY timestamp DESC LIMIT %d",
				$limit
			),
			ARRAY_A
		);

		return $results ?? array();
	}

	/**
	 * Get traffic statistics.
	 *
	 * @param string $period Time period (hour, day, week, month).
	 * @return array Traffic statistics.
	 */
	private function get_traffic_statistics( string $period = 'day' ): array {
		global $wpdb;

		$table_name = $wpdb->prefix . self::TABLE_NAME;

		// Calculate time range.
		$intervals = array(
			'hour'  => '-1 hour',
			'day'   => '-1 day',
			'week'  => '-1 week',
			'month' => '-1 month',
		);

		$start_time = isset( $intervals[ $period ] ) ? date( 'Y-m-d H:i:s', strtotime( $intervals[ $period ] ) ) : date( 'Y-m-d H:i:s', strtotime( '-1 day' ) );

		// Total requests.
		$total_requests = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$table_name} WHERE timestamp >= %s",
				$start_time
			)
		);

		// Unique visitors.
		$unique_visitors = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(DISTINCT ip) FROM {$table_name} WHERE timestamp >= %s",
				$start_time
			)
		);

		// Human vs bot.
		$human_requests = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$table_name} WHERE timestamp >= %s AND request_type = 'human'",
				$start_time
			)
		);

		$bot_requests = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$table_name} WHERE timestamp >= %s AND request_type = 'bot'",
				$start_time
			)
		);

		// Top pages.
		$top_pages = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT url, COUNT(*) as hits FROM {$table_name} WHERE timestamp >= %s GROUP BY url ORDER BY hits DESC LIMIT 10",
				$start_time
			),
			ARRAY_A
		);

		return array(
			'total_requests'   => (int) $total_requests,
			'unique_visitors'  => (int) $unique_visitors,
			'human_requests'   => (int) $human_requests,
			'bot_requests'     => (int) $bot_requests,
			'top_pages'        => $top_pages,
			'period'           => $period,
		);
	}

	/**
	 * AJAX handler to get live traffic.
	 *
	 * @return void
	 */
	public function ajax_get_live_traffic(): void {
		\WPShadow\WPSHADOW_verify_ajax_request( 'wps-traffic' );

		$limit  = \WPShadow\WPSHADOW_get_post_int( 'limit', 50 );
		$traffic = $this->get_live_traffic( $limit );

		wp_send_json_success( array(
			'traffic' => $traffic,
			'count'   => count( $traffic ),
		) );
	}

	/**
	 * AJAX handler to get traffic stats.
	 *
	 * @return void
	 */
	public function ajax_get_traffic_stats(): void {
		\WPShadow\WPSHADOW_verify_ajax_request( 'wps-traffic' );

		$period = \WPShadow\WPSHADOW_get_post_text( 'period', 'day' );
		$stats  = $this->get_traffic_statistics( $period );

		wp_send_json_success( array( 'stats' => $stats ) );
	}

	/**
	 * Cleanup old log entries.
	 *
	 * @return void
	 */
	public function cleanup_old_logs(): void {
		global $wpdb;

		$table_name = $wpdb->prefix . self::TABLE_NAME;
		$cutoff     = date( 'Y-m-d H:i:s', strtotime( '-' . self::LOG_RETENTION_DAYS . ' days' ) );

		$wpdb->query(
			$wpdb->prepare(
				"DELETE FROM {$table_name} WHERE timestamp < %s",
				$cutoff
			)
		);
	}

	/**
	 * Create database table if needed.
	 *
	 * @return void
	 */
	private function maybe_create_table(): void {
		global $wpdb;

		$table_name      = $wpdb->prefix . self::TABLE_NAME;
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE IF NOT EXISTS {$table_name} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			timestamp datetime NOT NULL,
			ip varchar(45) NOT NULL,
			user_id bigint(20) unsigned DEFAULT 0,
			url varchar(500) NOT NULL,
			method varchar(10) NOT NULL,
			user_agent text NOT NULL,
			request_type varchar(20) NOT NULL,
			bot_type varchar(50) DEFAULT '',
			PRIMARY KEY  (id),
			KEY timestamp (timestamp),
			KEY ip (ip),
			KEY request_type (request_type)
		) {$charset_collate};";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}
}
