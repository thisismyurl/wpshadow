<?php
/**
 * Time to First Byte (TTFB) Performance Diagnostic
 *
 * Measures server response time (time from request to first byte of response).
 * High TTFB indicates hosting issues or excessive backend processing.
 *
 * @since   1.2601.2148
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Time_To_First_Byte Class
 *
 * Measures server response time to detect hosting issues or backend performance problems.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Time_To_First_Byte extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'time-to-first-byte';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Time to First Byte (TTFB) Performance';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Measures server response time for first byte';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * TTFB threshold for good performance (milliseconds)
	 *
	 * @var int
	 */
	const TTFB_GOOD = 600;

	/**
	 * TTFB threshold for acceptable performance (milliseconds)
	 *
	 * @var int
	 */
	const TTFB_ACCEPTABLE = 1200;

	/**
	 * Number of samples to measure
	 *
	 * @var int
	 */
	const SAMPLE_COUNT = 5;

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Measure TTFB
		$ttfb_results = self::measure_ttfb();

		if ( empty( $ttfb_results ) ) {
			// Unable to measure TTFB
			return null;
		}

		// Calculate average TTFB
		$avg_ttfb = array_sum( $ttfb_results ) / count( $ttfb_results );
		$min_ttfb = min( $ttfb_results );
		$max_ttfb = max( $ttfb_results );

		// Evaluate TTFB performance
		if ( $avg_ttfb <= self::TTFB_GOOD ) {
			// Good performance
			return null;
		}

		if ( $avg_ttfb <= self::TTFB_ACCEPTABLE ) {
			// Acceptable but room for improvement
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: TTFB value in milliseconds */
					__( 'Average TTFB is %dms. Target: under 600ms for good performance.', 'wpshadow' ),
					(int) $avg_ttfb
				),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/time-to-first-byte',
				'family'       => self::$family,
				'meta'         => array(
					'ttfb_avg_ms'        => (int) $avg_ttfb,
					'ttfb_min_ms'        => (int) $min_ttfb,
					'ttfb_max_ms'        => (int) $max_ttfb,
					'samples'            => count( $ttfb_results ),
					'threshold_good'     => self::TTFB_GOOD,
					'threshold_poor'     => self::TTFB_ACCEPTABLE,
					'optimization_tips'  => array(
						__( 'Upgrade hosting plan for more server resources' ),
						__( 'Optimize database queries in themes/plugins' ),
						__( 'Enable caching (Redis, Memcached, or file-based)' ),
						__( 'Use a CDN for static assets' ),
						__( 'Check for heavy background tasks during page load' ),
					),
				),
				'details'      => array(
					'issue'   => __( 'Your web server takes too long to respond to requests.', 'wpshadow' ),
					'impact'  => __( 'Slow TTFB is the foundation for all other performance metrics. This delay adds to LCP, FID, and overall perceived slowness.', 'wpshadow' ),
					'causes'  => array(
						__( 'Inadequate server resources (CPU, RAM, connections)' ) => __( 'Shared hosting with too many sites' ),
						__( 'Slow database queries' ) => __( 'Unoptimized WordPress queries or missing indexes' ),
						__( 'Inefficient code execution' ) => __( 'Heavy plugin operations during page load' ),
						__( 'Geographic distance' ) => __( 'Server far from user location' ),
						__( 'Network bottleneck' ) => __( 'ISP or hosting provider network issues' ),
					),
				),
			);
		}

		// Poor performance (critical)
		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %d: TTFB value in milliseconds */
				__( 'TTFB is critically high at %dms. This severely impacts all user experience metrics.', 'wpshadow' ),
				(int) $avg_ttfb
			),
			'severity'     => 'high',
			'threat_level' => 70,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/time-to-first-byte',
			'family'       => self::$family,
			'meta'         => array(
				'ttfb_avg_ms'        => (int) $avg_ttfb,
				'ttfb_min_ms'        => (int) $min_ttfb,
				'ttfb_max_ms'        => (int) $max_ttfb,
				'samples'            => count( $ttfb_results ),
				'threshold_good'     => self::TTFB_GOOD,
				'threshold_poor'     => self::TTFB_ACCEPTABLE,
				'urgent_recommendations' => array(
					__( 'IMMEDIATE: Check server load and available resources' ),
					__( 'Contact hosting provider about server performance' ),
					__( 'Consider upgrading to better hosting or dedicated server' ),
					__( 'Run database optimization and query analysis' ),
					__( 'Disable or remove heavy plugins' ),
				),
			),
			'details'      => array(
				'issue'       => __( 'Server response time is unacceptably slow.', 'wpshadow' ),
				'impact'      => __( 'Users experience significant delays before page content appears. High bounce rates and lost conversions likely.', 'wpshadow' ),
				'urgency'     => __( 'This must be addressed immediately as it blocks all other performance improvements.', 'wpshadow' ),
				'investigation' => array(
					__( '1. Check server CPU and memory usage during peak traffic' ),
					__( '2. Run WordPress database optimization (WP-CLI: wp db optimize)' ),
					__( '3. Check slow query log in MySQL' ),
					__( '4. Review plugin slow query patterns' ),
					__( '5. Test from multiple geographic locations' ),
				),
			),
		);
	}

	/**
	 * Measure TTFB by making multiple HTTP requests.
	 *
	 * @since  1.2601.2148
	 * @return array Array of TTFB measurements in milliseconds.
	 */
	private static function measure_ttfb() {
		$ttfb_results = array();
		$home_url     = home_url();

		for ( $i = 0; $i < self::SAMPLE_COUNT; $i++ ) {
			$start_time = microtime( true );

			$response = wp_remote_head(
				$home_url,
				array(
					'timeout'   => 10,
					'sslverify' => true,
					'blocking'  => true,
				)
			);

			$end_time = microtime( true );

			if ( is_wp_error( $response ) ) {
				// Request failed, skip this sample
				continue;
			}

			// Calculate TTFB in milliseconds
			$ttfb_ms = ( $end_time - $start_time ) * 1000;
			$ttfb_results[] = $ttfb_ms;

			// Small delay between requests
			usleep( 100000 ); // 100ms
		}

		return $ttfb_results;
	}
}
