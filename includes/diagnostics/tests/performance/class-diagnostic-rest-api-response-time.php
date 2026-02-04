<?php
/**
 * REST API Response Time Diagnostic
 *
 * Measures WordPress REST API performance.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6033.2064
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * REST API Response Time Diagnostic Class
 *
 * Tests REST API endpoint performance. Slow REST API impacts
 * Gutenberg editor, mobile apps, and API integrations.
 *
 * @since 1.6033.2064
 */
class Diagnostic_REST_API_Response_Time extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'rest-api-response-time';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'REST API Response Time';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Measures REST API endpoint performance';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * Tests REST API by timing internal request.
	 * Threshold: <500ms good, >1000ms slow
	 *
	 * @since  1.6033.2064
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if REST API is available
		if ( ! function_exists( 'rest_url' ) ) {
			return array(
				'id'           => 'rest-api-disabled',
				'title'        => __( 'REST API Disabled', 'wpshadow' ),
				'description'  => __( 'WordPress REST API is disabled. This may break Gutenberg editor, mobile apps, and plugin functionality.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/enable-rest-api',
			);
		}
		
		// Test a simple endpoint
		$start_time = microtime( true );
		
		$response = wp_remote_get(
			rest_url( 'wp/v2/types' ),
			array(
				'timeout'   => 5,
				'sslverify' => false, // Internal request
			)
		);
		
		$elapsed_time = microtime( true ) - $start_time;
		$elapsed_ms   = round( $elapsed_time * 1000 );
		
		// Check for errors
		if ( is_wp_error( $response ) ) {
			return array(
				'id'           => 'rest-api-error',
				'title'        => __( 'REST API Error', 'wpshadow' ),
				'description'  => sprintf(
					/* translators: %s: error message */
					__( 'REST API request failed: %s', 'wpshadow' ),
					$response->get_error_message()
				),
				'severity'     => 'high',
				'threat_level' => 75,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/rest-api-troubleshooting',
				'meta'         => array(
					'error_code'    => $response->get_error_code(),
					'error_message' => $response->get_error_message(),
				),
			);
		}
		
		// Check response time
		if ( $elapsed_ms > 1000 ) {
			$severity = 'high';
			$threat_level = 70;
		} elseif ( $elapsed_ms > 500 ) {
			$severity = 'medium';
			$threat_level = 50;
		} else {
			return null; // Fast enough
		}
		
		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %d: response time in milliseconds */
				__( 'REST API response time is %dms (should be <500ms). Slow REST API impacts Gutenberg editor performance and API integrations.', 'wpshadow' ),
				$elapsed_ms
			),
			'severity'     => $severity,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/optimize-rest-api',
			'meta'         => array(
				'response_time_ms' => $elapsed_ms,
				'threshold_good'   => 500,
				'threshold_slow'   => 1000,
				'endpoint_tested'  => 'wp/v2/types',
				'http_code'        => wp_remote_retrieve_response_code( $response ),
			),
		);
	}
}
