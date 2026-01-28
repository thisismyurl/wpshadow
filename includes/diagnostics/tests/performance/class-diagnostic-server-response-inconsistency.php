<?php
/**
 * Server Response Time Inconsistency Diagnostic
 *
 * Detects variance in server response times (TTFB). High variance indicates
 * server instability, resource contention, database issues, or load balancing
 * problems. Consistent response times are critical for reliable user experience.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Performance
 * @since      1.6028.1520
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Server Response Time Inconsistency Diagnostic Class
 *
 * Samples TTFB multiple times to calculate standard deviation and
 * identify response time stability issues.
 *
 * @since 1.6028.1520
 */
class Diagnostic_Server_Response_Inconsistency extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @since 1.6028.1520
	 * @var   string
	 */
	protected static $slug = 'server-response-inconsistency';

	/**
	 * The diagnostic title
	 *
	 * @since 1.6028.1520
	 * @var   string
	 */
	protected static $title = 'Server Response Time Inconsistency';

	/**
	 * The diagnostic description
	 *
	 * @since 1.6028.1520
	 * @var   string
	 */
	protected static $description = 'Detects variance in server response times indicating instability';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @since 1.6028.1520
	 * @var   string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check
	 *
	 * Samples TTFB 10 times and calculates standard deviation.
	 * Benchmarks:
	 * - Variance ≤200ms: Good
	 * - 200-500ms: Warning
	 * - >500ms: Critical
	 *
	 * @since  1.6028.1520
	 * @return array|null Null if consistent, array if high variance.
	 */
	public static function check() {
		$response_data = self::measure_response_variance();

		if ( is_null( $response_data ) ) {
			return null; // Cannot measure.
		}

		$variance_ms = $response_data['variance_ms'];

		// Only report if variance >200ms.
		if ( $variance_ms <= 200 ) {
			return null;
		}

		$severity = $variance_ms > 500 ? 'high' : 'medium';
		$threat_level = min( 70, 30 + ( $variance_ms * 0.08 ) );

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %d: variance in milliseconds */
				__( 'Server response times vary by %dms, indicating instability or resource contention', 'wpshadow' ),
				$variance_ms
			),
			'severity'     => $severity,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'family'       => self::$family,
			'kb_link'      => 'https://wpshadow.com/kb/server-response-stability',
			'meta'         => array(
				'variance_ms'      => $variance_ms,
				'avg_response_ms'  => $response_data['avg_response_ms'],
				'min_response_ms'  => $response_data['min_response_ms'],
				'max_response_ms'  => $response_data['max_response_ms'],
				'samples_taken'    => $response_data['samples'],
				'immediate_actions' => array(
					__( 'Monitor server CPU and memory usage', 'wpshadow' ),
					__( 'Check database query performance', 'wpshadow' ),
					__( 'Review server logs for errors', 'wpshadow' ),
					__( 'Consider upgrading hosting plan', 'wpshadow' ),
				),
			),
			'details'      => array(
				'why_important' => __(
					'Consistent server response times indicate healthy infrastructure. High variance suggests resource contention (CPU/memory/database), unstable hosting, network issues, or inefficient code. Users experience unpredictable performance - sometimes fast, sometimes slow. This hurts user experience and indicates underlying problems that may worsen under load.',
					'wpshadow'
				),
				'user_impact'   => __(
					'Users never know if your site will load quickly or slowly, creating frustration and unreliability. During peak variance, site may timeout completely. High variance often precedes complete outages. This indicates your hosting is at capacity or has fundamental performance issues requiring immediate attention.',
					'wpshadow'
				),
				'solution_options' => array(
					'free'     => array(
						__( 'Monitor with free UptimeRobot or Pingdom', 'wpshadow' ),
						__( 'Check server logs for error patterns', 'wpshadow' ),
						__( 'Optimize database with WP-Optimize', 'wpshadow' ),
					),
					'premium'  => array(
						__( 'Upgrade to dedicated or managed WordPress hosting', 'wpshadow' ),
						__( 'Install Query Monitor to identify slow queries', 'wpshadow' ),
						__( 'Implement Redis object caching', 'wpshadow' ),
					),
					'advanced' => array(
						__( 'Deploy load balancer for traffic distribution', 'wpshadow' ),
						__( 'Migrate to cloud hosting (AWS, GCP) with autoscaling', 'wpshadow' ),
						__( 'Implement application performance monitoring (APM)', 'wpshadow' ),
					),
				),
				'best_practices' => array(
					__( 'Monitor TTFB variance daily with uptime monitoring', 'wpshadow' ),
					__( 'Set up alerts for response time spikes (>500ms)', 'wpshadow' ),
					__( 'Keep variance under 200ms for consistent UX', 'wpshadow' ),
					__( 'Investigate spikes immediately - they worsen over time', 'wpshadow' ),
					__( 'Test during peak traffic hours for realistic variance', 'wpshadow' ),
					__( 'Maintain server CPU usage <70% average', 'wpshadow' ),
					__( 'Use dedicated hosting for high-traffic sites', 'wpshadow' ),
					__( 'Implement database query caching', 'wpshadow' ),
				),
				'testing_steps' => array(
					__( 'Run multiple curl requests: for i in {1..10}; do curl -w "%{time_starttransfer}\\n" -o /dev/null -s yoursite.com; done', 'wpshadow' ),
					__( 'Calculate standard deviation of response times', 'wpshadow' ),
					__( 'Use tools.pingdom.com to measure from multiple locations', 'wpshadow' ),
					__( 'Check server CPU/memory during testing', 'wpshadow' ),
					__( 'Monitor database query times with Query Monitor', 'wpshadow' ),
					__( 'Test during peak and off-peak hours', 'wpshadow' ),
				),
			),
		);
	}

	/**
	 * Measure response variance
	 *
	 * Samples TTFB 10 times and calculates statistical variance to
	 * identify inconsistent server performance.
	 *
	 * @since  1.6028.1520
	 * @return array|null Response time statistics or null if failed.
	 */
	private static function measure_response_variance() {
		$home_url = home_url( '/' );
		$samples = array();
		$sample_count = 10;

		// Take 10 samples with small delays.
		for ( $i = 0; $i < $sample_count; $i++ ) {
			$start_time = microtime( true );

			$response = wp_remote_head(
				$home_url,
				array(
					'timeout'   => 10,
					'sslverify' => false,
				)
			);

			$end_time = microtime( true );

			if ( ! is_wp_error( $response ) ) {
				$samples[] = ( $end_time - $start_time ) * 1000; // Convert to ms.
			}

			// Small delay between samples.
			if ( $i < $sample_count - 1 ) {
				usleep( 500000 ); // 0.5 second delay.
			}
		}

		// Need at least 5 successful samples.
		if ( count( $samples ) < 5 ) {
			return null;
		}

		// Calculate statistics.
		$avg = array_sum( $samples ) / count( $samples );
		$min = min( $samples );
		$max = max( $samples );

		// Calculate standard deviation.
		$variance = 0;
		foreach ( $samples as $sample ) {
			$variance += pow( $sample - $avg, 2 );
		}
		$std_dev = sqrt( $variance / count( $samples ) );

		return array(
			'variance_ms'     => round( $std_dev ),
			'avg_response_ms' => round( $avg ),
			'min_response_ms' => round( $min ),
			'max_response_ms' => round( $max ),
			'samples'         => count( $samples ),
		);
	}
}
