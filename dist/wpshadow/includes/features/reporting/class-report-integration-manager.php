<?php
/**
 * Report Integration Manager
 *
 * Handles integrations with external services (Slack, webhooks, APIs).
 *
 * @package    WPShadow
 * @subpackage Reporting
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Reporting;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Report_Integration_Manager Class
 *
 * Manages external integrations for reports.
 *
 * @since 0.6093.1200
 */
class Report_Integration_Manager {

	/**
	 * Send report to Slack
	 *
	 * @since 0.6093.1200
	 * @param  string $webhook_url Slack webhook URL.
	 * @param  string $report_id   Report identifier.
	 * @param  array  $data        Report data.
	 * @return bool|WP_Error Success or error.
	 */
	public static function send_to_slack( $webhook_url, $report_id, $data ) {
		$findings_count = isset( $data['findings'] ) ? count( $data['findings'] ) : 0;
		$site_name = get_bloginfo( 'name' );

		$color = $findings_count === 0 ? 'good' : ( $findings_count > 10 ? 'danger' : 'warning' );

		$payload = array(
			'text'        => sprintf( 'Report Generated: %s', ucwords( str_replace( '-', ' ', $report_id ) ) ),
			'attachments' => array(
				array(
					'color'  => $color,
					'fields' => array(
						array(
							'title' => 'Site',
							'value' => $site_name,
							'short' => true,
						),
						array(
							'title' => 'Issues Found',
							'value' => (string) $findings_count,
							'short' => true,
						),
					),
				),
			),
		);

		$response = wp_remote_post(
			$webhook_url,
			array(
				'body'    => wp_json_encode( $payload ),
				'headers' => array( 'Content-Type' => 'application/json' ),
				'timeout' => 15,
			)
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		return wp_remote_retrieve_response_code( $response ) === 200;
	}

	/**
	 * Send report to Microsoft Teams
	 *
	 * @since 0.6093.1200
	 * @param  string $webhook_url Teams webhook URL.
	 * @param  string $report_id   Report identifier.
	 * @param  array  $data        Report data.
	 * @return bool|WP_Error Success or error.
	 */
	public static function send_to_teams( $webhook_url, $report_id, $data ) {
		$findings_count = isset( $data['findings'] ) ? count( $data['findings'] ) : 0;
		$site_name = get_bloginfo( 'name' );

		$theme_color = $findings_count === 0 ? '00FF00' : ( $findings_count > 10 ? 'FF0000' : 'FFA500' );

		$payload = array(
			'@type'      => 'MessageCard',
			'@context'   => 'http://schema.org/extensions',
			'themeColor' => $theme_color,
			'summary'    => 'WPShadow Report Generated',
			'sections'   => array(
				array(
					'activityTitle' => sprintf( '%s Report', ucwords( str_replace( '-', ' ', $report_id ) ) ),
					'facts'         => array(
						array(
							'name'  => 'Site',
							'value' => $site_name,
						),
						array(
							'name'  => 'Issues Found',
							'value' => (string) $findings_count,
						),
					),
				),
			),
		);

		$response = wp_remote_post(
			$webhook_url,
			array(
				'body'    => wp_json_encode( $payload ),
				'headers' => array( 'Content-Type' => 'application/json' ),
				'timeout' => 15,
			)
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		return wp_remote_retrieve_response_code( $response ) === 200;
	}

	/**
	 * Trigger webhook
	 *
	 * @since 0.6093.1200
	 * @param  string $webhook_url Webhook URL.
	 * @param  string $report_id   Report identifier.
	 * @param  array  $data        Report data.
	 * @param  string $method      HTTP method (POST, GET, PUT).
	 * @return bool|WP_Error Success or error.
	 */
	public static function trigger_webhook( $webhook_url, $report_id, $data, $method = 'POST' ) {
		$payload = array(
			'report_id'   => $report_id,
			'site_url'    => home_url(),
			'site_name'   => get_bloginfo( 'name' ),
			'timestamp'   => time(),
			'findings'    => isset( $data['findings'] ) ? $data['findings'] : array(),
			'summary'     => array(
				'total_findings' => isset( $data['findings'] ) ? count( $data['findings'] ) : 0,
				'high_severity'  => self::count_by_severity( $data, 'high' ),
				'medium_severity' => self::count_by_severity( $data, 'medium' ),
				'low_severity'   => self::count_by_severity( $data, 'low' ),
			),
		);

		$args = array(
			'method'  => $method,
			'body'    => wp_json_encode( $payload ),
			'headers' => array(
				'Content-Type' => 'application/json',
				'User-Agent'   => 'WPShadow/' . WPSHADOW_VERSION,
			),
			'timeout' => 15,
		);

		$response = wp_remote_request( $webhook_url, $args );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$status_code = wp_remote_retrieve_response_code( $response );

		/**
		 * Fires after webhook is triggered.
		 *
		 * @since 0.6093.1200
		 *
		 * @param string $webhook_url Webhook URL.
		 * @param int    $status_code Response status code.
		 * @param string $report_id Report ID.
		 */
		do_action( 'wpshadow_after_webhook_trigger', $webhook_url, $status_code, $report_id );

		return $status_code >= 200 && $status_code < 300;
	}

	/**
	 * Count findings by severity
	 *
	 * @since 0.6093.1200
	 * @param  array  $data     Report data.
	 * @param  string $severity Severity level.
	 * @return int Count.
	 */
	private static function count_by_severity( $data, $severity ) {
		if ( ! isset( $data['findings'] ) ) {
			return 0;
		}

		$count = 0;
		foreach ( $data['findings'] as $finding ) {
			if ( isset( $finding['severity'] ) && $finding['severity'] === $severity ) {
				$count++;
			}
		}

		return $count;
	}

	/**
	 * Register REST API endpoints for reports
	 *
	 * @since 0.6093.1200
	 * @return void
	 */
	public static function register_api_endpoints() {
		register_rest_route(
			'wpshadow/v1',
			'/reports',
			array(
				'methods'             => 'GET',
				'callback'            => array( __CLASS__, 'api_get_reports' ),
				'permission_callback' => array( __CLASS__, 'api_permission_check' ),
			)
		);

		register_rest_route(
			'wpshadow/v1',
			'/reports/(?P<report_id>[a-zA-Z0-9-]+)',
			array(
				'methods'             => 'GET',
				'callback'            => array( __CLASS__, 'api_get_report' ),
				'permission_callback' => array( __CLASS__, 'api_permission_check' ),
			)
		);

		register_rest_route(
			'wpshadow/v1',
			'/reports/(?P<report_id>[a-zA-Z0-9-]+)/run',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'api_run_report' ),
				'permission_callback' => array( __CLASS__, 'api_permission_check' ),
			)
		);
	}

	/**
	 * API permission check
	 *
	 * @since 0.6093.1200
	 * @return bool Permission granted.
	 */
	public static function api_permission_check() {
		return current_user_can( 'manage_options' );
	}

	/**
	 * API endpoint: Get available reports
	 *
	 * @since 0.6093.1200
	 * @return WP_REST_Response Response.
	 */
	public static function api_get_reports() {
		$reports = array(
			'security-report',
			'performance-report',
			'seo-report',
			'database-report',
			'ecommerce-report',
			'plugins-report',
			'compliance-report',
			'email-report',
			'backup-report',
			'multisite-report',
		);

		return rest_ensure_response( array(
			'success' => true,
			'reports' => $reports,
		) );
	}

	/**
	 * API endpoint: Get report details
	 *
	 * @since 0.6093.1200
	 * @param  WP_REST_Request $request Request object.
	 * @return WP_REST_Response Response.
	 */
	public static function api_get_report( $request ) {
		$report_id = $request->get_param( 'report_id' );

		// Get latest snapshot
		$snapshots = Report_Snapshot_Manager::get_snapshots( $report_id, 1 );

		if ( empty( $snapshots ) ) {
			return rest_ensure_response( array(
				'success' => false,
				'message' => 'No snapshots found for this report',
			) );
		}

		return rest_ensure_response( array(
			'success' => true,
			'report'  => $snapshots[0],
		) );
	}

	/**
	 * API endpoint: Run a report
	 *
	 * @since 0.6093.1200
	 * @param  WP_REST_Request $request Request object.
	 * @return WP_REST_Response Response.
	 */
	public static function api_run_report( $request ) {
		$report_id = $request->get_param( 'report_id' );

		// This would integrate with actual report generation
		// For now, return placeholder
		return rest_ensure_response( array(
			'success' => true,
			'message' => 'Report generation queued',
			'report_id' => $report_id,
		) );
	}
}
