<?php
declare(strict_types=1);

namespace WPShadow\Guardian;

/**
 * CSP Violation Analyzer
 *
 * Monitors Content Security Policy (CSP) violations to identify security issues.
 * Tracks violations reported by browsers to help tighten security policies.
 *
 * Philosophy: Show value (#9) - Strengthen site security through CSP monitoring.
 *
 * @package WPShadow
 * @subpackage Guardian
 * @since 1.6030.2200
 */
class CSP_Violation_Analyzer {

	/**
	 * Initialize CSP violation tracking
	 *
	 * @return void
	 */
	public static function init(): void {
		// Set up CSP report endpoint
		add_action( 'rest_api_init', array( __CLASS__, 'register_report_endpoint' ) );

		// Add CSP header with report-uri
		add_action( 'send_headers', array( __CLASS__, 'add_csp_header' ) );
	}

	/**
	 * Register REST API endpoint for CSP reports
	 *
	 * @return void
	 */
	public static function register_report_endpoint(): void {
		register_rest_route(
			'wpshadow/v1',
			'/csp-report',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'handle_csp_report' ),
				'permission_callback' => '__return_true', // Public endpoint for browser reports
			)
		);
	}

	/**
	 * Add CSP header in report-only mode
	 *
	 * @return void
	 */
	public static function add_csp_header(): void {
		// Only add if not already set
		if ( headers_sent() ) {
			return;
		}

		$report_uri = rest_url( 'wpshadow/v1/csp-report' );

		// Report-only mode - doesn't block, just reports
		$csp_policy = "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'; style-src 'self' 'unsafe-inline'; img-src 'self' data: https:; font-src 'self' data:; report-uri {$report_uri}";

		header( "Content-Security-Policy-Report-Only: {$csp_policy}" );
	}

	/**
	 * Handle CSP violation report
	 *
	 * @param \WP_REST_Request $request Request object
	 * @return \WP_REST_Response Response object
	 */
	public static function handle_csp_report( $request ): \WP_REST_Response {
		$body   = $request->get_body();
		$report = json_decode( $body, true );

		if ( ! $report || ! isset( $report['csp-report'] ) ) {
			return new \WP_REST_Response( array( 'status' => 'invalid' ), 400 );
		}

		$violation = $report['csp-report'];

		// Get current violations
		$violations = \WPShadow\Core\Cache_Manager::get( 'csp_violations', 'wpshadow_monitoring' );
		if ( ! is_array( $violations ) ) {
			$violations = array(
				'reports' => array(),
				'count'   => 0,
			);
		}

		// Add this violation
		$violations['reports'][] = array(
			'violated_directive' => $violation['violated-directive'] ?? '',
			'blocked_uri'        => $violation['blocked-uri'] ?? '',
			'document_uri'       => $violation['document-uri'] ?? '',
			'timestamp'          => time(),
		);
		++$violations['count'];

		// Keep only last 100 violations
		if ( count( $violations['reports'] ) > 100 ) {
			$violations['reports'] = array_slice( $violations['reports'], -100 );
		}

		// Set cache
		\WPShadow\Core\Cache_Manager::set( 'csp_violation_count', $violations['count'], WEEK_IN_SECONDS , 'wpshadow_monitoring');
		\WPShadow\Core\Cache_Manager::set( 'csp_violations', $violations, WEEK_IN_SECONDS , 'wpshadow_monitoring');

		return new \WP_REST_Response( array( 'status' => 'recorded' ), 204 );
	}

	/**
	 * Get analysis summary
	 *
	 * @return array Analysis data
	 */
	public static function get_summary(): array {
		$count      = (int) \WPShadow\Core\Cache_Manager::get( 'csp_violation_count', 'wpshadow_monitoring' );
		$violations = \WPShadow\Core\Cache_Manager::get( 'csp_violations', 'wpshadow_monitoring' );

		$summary = array(
			'total_violations'    => $count,
			'unique_blocked_uris' => array(),
			'has_violations'      => $count > 0,
		);

		if ( is_array( $violations ) && isset( $violations['reports'] ) ) {
			$blocked_uris = array();
			foreach ( $violations['reports'] as $report ) {
				$uri = $report['blocked_uri'];
				if ( ! isset( $blocked_uris[ $uri ] ) ) {
					$blocked_uris[ $uri ] = 0;
				}
				++$blocked_uris[ $uri ];
			}

			$summary['unique_blocked_uris'] = $blocked_uris;
		}

		return $summary;
	}

	/**
	 * Clear cached data
	 *
	 * @return void
	 */
	public static function clear_cache(): void {
		\WPShadow\Core\Cache_Manager::delete( 'csp_violation_count', 'wpshadow_monitoring' );
		\WPShadow\Core\Cache_Manager::delete( 'csp_violations', 'wpshadow_monitoring' );
	}
}
