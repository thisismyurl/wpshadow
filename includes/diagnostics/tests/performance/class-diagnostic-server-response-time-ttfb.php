<?php
/**
 * Server Response Time (TTFB) Diagnostic
 *
 * Measures Time To First Byte from PHP execution to identify server response delays.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26033.2048
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Server Response Time (TTFB) Diagnostic Class
 *
 * Measures server response time from request start to identify performance bottlenecks.
 * TTFB (Time To First Byte) is critical for overall page load performance.
 *
 * @since 1.26033.2048
 */
class Diagnostic_Server_Response_Time_TTFB extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'server-response-time-ttfb';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Server Response Time (TTFB)';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Measures Time To First Byte from server';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * Measures TTFB from earliest possible point in WordPress execution.
	 * Thresholds:
	 * - Excellent: <200ms
	 * - Good: 200-600ms
	 * - Slow: 600-1000ms
	 * - Critical: >1000ms
	 *
	 * @since  1.26033.2048
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Get start time - use constant if set in wp-config.php, otherwise estimate
		$start_time = defined( 'WPSHADOW_REQUEST_START' ) ? WPSHADOW_REQUEST_START : $_SERVER['REQUEST_TIME_FLOAT'];
		
		// Current time
		$current_time = microtime( true );
		
		// Calculate TTFB in milliseconds
		$ttfb_seconds = $current_time - $start_time;
		$ttfb_ms      = round( $ttfb_seconds * 1000 );
		
		// Threshold: 600ms is acceptable, >600ms is slow
		if ( $ttfb_ms > 600 ) {
			// Determine severity based on TTFB
			$severity     = 'medium';
			$threat_level = 50;
			
			if ( $ttfb_ms > 1000 ) {
				$severity     = 'critical';
				$threat_level = 90;
			} elseif ( $ttfb_ms > 800 ) {
				$severity     = 'high';
				$threat_level = 70;
			}
			
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: TTFB in milliseconds */
					__( 'Server response time is %dms (should be <600ms, ideally <200ms). Slow server response indicates server-level bottlenecks affecting all pages.', 'wpshadow' ),
					$ttfb_ms
				),
				'severity'     => $severity,
				'threat_level' => min( 100, $threat_level ),
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/server-response-time-ttfb',
				'meta'         => array(
					'ttfb_ms'       => $ttfb_ms,
					'ttfb_seconds'  => round( $ttfb_seconds, 3 ),
					'threshold_ms'  => 600,
					'ideal_ms'      => 200,
					'measurement'   => 'backend',
				),
			);
		}
		
		return null;
	}
}
