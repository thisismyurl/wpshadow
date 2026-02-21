<?php
/**
 * Homepage Loading Speed Treatment
 *
 * Checks homepage load speed, core performance metrics, and payload size.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6035.0900
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Homepage Loading Speed Treatment Class
 *
 * Evaluates homepage speed metrics that impact user experience and conversion.
 *
 * @since 1.6035.0900
 */
class Treatment_Homepage_Loading_Speed extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'homepage-loading-speed';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Homepage Loading Speed Loses Customers';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks homepage speed, Core Web Vitals, and payload size';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance-optimization';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6035.0900
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Homepage_Loading_Speed' );
	}

	/**
	 * Measure homepage load time, response time, and payload size.
	 *
	 * @since  1.6035.0900
	 * @param  string $url Homepage URL.
	 * @return array|null Timing data or null when unavailable.
	 */
	private static function measure_homepage_timing( string $url ): ?array {
		$start_time = microtime( true );
		$response = wp_remote_get(
			$url,
			array(
				'timeout'   => 10,
				'blocking'  => true,
				'sslverify' => false,
			)
		);
		$end_time = microtime( true );

		if ( is_wp_error( $response ) ) {
			return null;
		}

		$body = wp_remote_retrieve_body( $response );
		$length_header = wp_remote_retrieve_header( $response, 'content-length' );
		$page_size = 0;
		if ( $length_header ) {
			$page_size = absint( $length_header );
		} elseif ( '' !== $body ) {
			$page_size = strlen( $body );
		}

		$load_time = max( 0.01, $end_time - $start_time );

		$head_start = microtime( true );
		$head_response = wp_remote_head(
			$url,
			array(
				'timeout'   => 10,
				'blocking'  => true,
				'sslverify' => false,
			)
		);
		$head_end = microtime( true );
		$server_response_time = max( 0.01, $head_end - $head_start );
		if ( is_wp_error( $head_response ) ) {
			$server_response_time = $load_time;
		}

		return array(
			'load_time'           => round( $load_time, 2 ),
			'server_response_time'=> round( $server_response_time, 2 ),
			'page_size_mb'        => round( $page_size / ( 1024 * 1024 ), 2 ),
		);
	}

	/**
	 * Fetch PageSpeed Insights metrics when API key is configured.
	 *
	 * @since  1.6035.0900
	 * @param  string $url Homepage URL.
	 * @return array|null Metrics array or null if unavailable.
	 */
	private static function get_pagespeed_metrics( string $url ): ?array {
		$api_key = get_option( 'wpshadow_pagespeed_api_key', '' );
		if ( ! $api_key ) {
			return null;
		}

		$request_url = add_query_arg(
			array(
				'url'      => $url,
				'key'      => $api_key,
				'strategy' => 'mobile',
			),
			'https://www.googleapis.com/pagespeedonline/v5/runPagespeed'
		);

		$response = wp_remote_get( $request_url, array( 'timeout' => 30 ) );
		if ( is_wp_error( $response ) ) {
			return null;
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );
		if ( ! is_array( $data ) ) {
			return null;
		}

		$audits = $data['lighthouseResult']['audits'] ?? array();
		$metrics = array();

		if ( isset( $audits['largest-contentful-paint']['numericValue'] ) ) {
			$metrics['lcp_ms'] = (int) $audits['largest-contentful-paint']['numericValue'];
		}

		if ( isset( $audits['interactive']['numericValue'] ) ) {
			$metrics['tti_ms'] = (int) $audits['interactive']['numericValue'];
		}

		if ( isset( $audits['total-byte-weight']['numericValue'] ) ) {
			$metrics['total_byte_weight'] = (int) $audits['total-byte-weight']['numericValue'];
		}

		return $metrics ? $metrics : null;
	}
}
