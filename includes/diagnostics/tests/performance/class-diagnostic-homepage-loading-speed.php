<?php
/**
 * Homepage Loading Speed Diagnostic
 *
 * Checks homepage load speed, core performance metrics, and payload size.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Homepage Loading Speed Diagnostic Class
 *
 * Evaluates homepage speed metrics that impact user experience and conversion.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Homepage_Loading_Speed extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'homepage-loading-speed';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Homepage Loading Speed Loses Customers';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks homepage speed, Core Web Vitals, and payload size';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance-optimization';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues  = array();
		$metrics = array();
		$home_url = home_url( '/' );

		$timings = self::measure_homepage_timing( $home_url );
		if ( ! $timings ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'We could not measure homepage speed because the homepage did not respond. Check that the site is online and publicly reachable.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/homepage-loading-speed?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'meta'         => array(
					'homepage_url' => $home_url,
				),
			);
		}

		$metrics['homepage_load_time'] = $timings['load_time'];
		$metrics['server_response_time'] = $timings['server_response_time'];
		$metrics['page_size_mb'] = $timings['page_size_mb'];

		if ( $timings['load_time'] > 3.0 ) {
			$issues[] = sprintf(
				/* translators: %s: load time in seconds */
				__( 'Homepage load time is %ss (target: under 3s).', 'wpshadow' ),
				number_format_i18n( $timings['load_time'], 2 )
			);
		}

		if ( $timings['server_response_time'] > 0.6 ) {
			$issues[] = sprintf(
				/* translators: %s: response time in seconds */
				__( 'Server response time is %ss (target: under 0.6s).', 'wpshadow' ),
				number_format_i18n( $timings['server_response_time'], 2 )
			);
		}

		if ( $timings['page_size_mb'] > 2 ) {
			$issues[] = sprintf(
				/* translators: %s: page size in MB */
				__( 'Homepage payload is %sMB (target: under 2MB).', 'wpshadow' ),
				number_format_i18n( $timings['page_size_mb'], 2 )
			);
		}

		$psi_metrics = self::get_pagespeed_metrics( $home_url );
		if ( $psi_metrics ) {
			$metrics = array_merge( $metrics, $psi_metrics );

			if ( isset( $psi_metrics['lcp_ms'] ) && $psi_metrics['lcp_ms'] > 2500 ) {
				$issues[] = sprintf(
					/* translators: %s: LCP in seconds */
					__( 'Largest Contentful Paint is %ss (target: under 2.5s).', 'wpshadow' ),
					number_format_i18n( $psi_metrics['lcp_ms'] / 1000, 2 )
				);
			}

			if ( isset( $psi_metrics['tti_ms'] ) && $psi_metrics['tti_ms'] > 3500 ) {
				$issues[] = sprintf(
					/* translators: %s: TTI in seconds */
					__( 'Time to Interactive is %ss (target: under 3.5s).', 'wpshadow' ),
					number_format_i18n( $psi_metrics['tti_ms'] / 1000, 2 )
				);
			}
		} else {
			$metrics['pagespeed_api'] = 'unavailable';
		}

		if ( empty( $issues ) ) {
			return null;
		}

		$severity = 'medium';
		$threat_level = 60;
		if ( $timings['load_time'] > 4.0 || $timings['server_response_time'] >1.0 ) {
			$severity = 'critical';
			$threat_level = 85;
		} elseif ( $timings['load_time'] > 3.0 || $timings['page_size_mb'] > 2.5 ) {
			$severity = 'high';
			$threat_level = 75;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %d: number of speed issues */
				__( 'We found %d homepage speed issue(s) that can reduce conversions.', 'wpshadow' ),
				count( $issues )
			),
			'severity'     => $severity,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/homepage-loading-speed?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			'meta'         => array(
				'issues'  => $issues,
				'metrics' => $metrics,
			),
		);
	}

	/**
	 * Measure homepage load time, response time, and payload size.
	 *
	 * @since 0.6093.1200
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
	 * @since 0.6093.1200
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
