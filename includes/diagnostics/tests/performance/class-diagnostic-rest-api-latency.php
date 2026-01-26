<?php
/**
 * Diagnostic: REST API Latency Measurement
 *
 * Measures response time of REST API endpoints to identify performance issues.
 * Slow REST API responses can significantly impact frontend user experience.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Performance
 * @since      1.2601.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Diagnostic_Rest_Api_Latency
 *
 * Tests REST API response times and reports latency issues.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Rest_Api_Latency extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'rest-api-latency';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'REST API Latency Measurement';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Measures response time of REST API endpoints';

	/**
	 * Latency thresholds in milliseconds.
	 */
	const THRESHOLD_GOOD = 200;
	const THRESHOLD_SLOW = 500;

	/**
	 * Check REST API latency.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		$rest_url = rest_url();

		// Measure REST API response time.
		$start_time = microtime( true );
		$response   = wp_remote_get(
			$rest_url,
			array(
				'timeout'   => 5,
				'sslverify' => false,
			)
		);
		$end_time   = microtime( true );

		// Calculate latency in milliseconds.
		$latency_ms = ( $end_time - $start_time ) * 1000;

		// Check for HTTP errors.
		if ( is_wp_error( $response ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: %s: Error message */
					__( 'REST API request failed: %s', 'wpshadow' ),
					$response->get_error_message()
				),
				'severity'    => 'medium',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/performance-rest-api-latency',
				'meta'        => array(
					'rest_url' => $rest_url,
					'error'    => $response->get_error_message(),
				),
			);
		}

		// Report slow REST API (>500ms).
		if ( $latency_ms > self::THRESHOLD_SLOW ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: %d: Latency in milliseconds */
					__( 'REST API response is slow (%d ms). Consider optimizing database queries or enabling caching.', 'wpshadow' ),
					round( $latency_ms )
				),
				'severity'    => 'low',
				'threat_level' => 30,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/performance-rest-api-latency',
				'meta'        => array(
					'rest_url'   => $rest_url,
					'latency_ms' => round( $latency_ms ),
					'threshold'  => self::THRESHOLD_SLOW,
				),
			);
		}

		// Informational: Report good but not excellent latency (200-500ms).
		if ( $latency_ms > self::THRESHOLD_GOOD ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: %d: Latency in milliseconds */
					__( 'REST API response time is acceptable (%d ms) but could be improved with caching.', 'wpshadow' ),
					round( $latency_ms )
				),
				'severity'    => 'info',
				'threat_level' => 20,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/performance-rest-api-latency',
				'meta'        => array(
					'rest_url'   => $rest_url,
					'latency_ms' => round( $latency_ms ),
					'threshold'  => self::THRESHOLD_GOOD,
				),
			);
		}

		// REST API latency is good.
		return null;
	}
}
