<?php
/**
 * Diagnostic: Largest Contentful Paint (LCP) Measurement
 *
 * Measures Largest Contentful Paint (LCP) to identify viewport rendering delays.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Diagnostic_Largest_Contentful_Paint
 *
 * Monitors Largest Contentful Paint (LCP), a Core Web Vital that measures
 * loading performance. LCP should occur within 2.5 seconds of page start.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Largest_Contentful_Paint extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'largest-contentful-paint';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Largest Contentful Paint (LCP) Measurement';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Measure Largest Contentful Paint (LCP) to identify viewport rendering delays';

	/**
	 * Good LCP threshold in milliseconds.
	 *
	 * @var int
	 */
	private const LCP_GOOD = 2500;

	/**
	 * Needs improvement LCP threshold in milliseconds.
	 *
	 * @var int
	 */
	private const LCP_NEEDS_IMPROVEMENT = 4000;

	/**
	 * Run the diagnostic check.
	 *
	 * Note: This diagnostic requires actual page load testing and JavaScript
	 * measurement. This implementation provides a placeholder that can be
	 * enhanced with actual LCP data from real user monitoring or lab testing.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if LCP is poor, null otherwise.
	 */
	public static function check() {
		// Check if LCP data is available from previous measurements
		$lcp_data = get_transient( 'wpshadow_lcp_measurement' );

		if ( false === $lcp_data || ! isset( $lcp_data['lcp'] ) ) {
			// No LCP data available - return info message
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'Largest Contentful Paint (LCP) measurement not yet available. LCP is a Core Web Vital that measures loading performance. Enable JavaScript-based performance monitoring to collect LCP data. Good: <2.5s, Needs improvement: 2.5-4s, Poor: >4s.', 'wpshadow' ),
				'severity'    => 'info',
				'threat_level' => 20,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/performance-largest-contentful-paint',
				'meta'        => array(
					'measurement_available' => false,
				),
			);
		}

		$lcp_ms = absint( $lcp_data['lcp'] );
		$lcp_seconds = round( $lcp_ms / 1000, 2 );

		// Determine severity based on LCP value
		if ( $lcp_ms < self::LCP_GOOD ) {
			// Good LCP - no issue
			return null;
		}

		if ( $lcp_ms < self::LCP_NEEDS_IMPROVEMENT ) {
			// Needs improvement
			$severity = 'low';
			$threat_level = 30;
			$status = __( 'needs improvement', 'wpshadow' );
		} else {
			// Poor LCP
			$severity = 'medium';
			$threat_level = 50;
			$status = __( 'poor', 'wpshadow' );
		}

		$description = sprintf(
			/* translators: 1: LCP value in seconds, 2: status (needs improvement/poor) */
			__( 'Largest Contentful Paint (LCP) is %1$ss, which is %2$s. LCP measures loading performance - the time until the largest content element becomes visible. Google recommends LCP under 2.5 seconds. Common causes: slow server response, render-blocking resources, large images without optimization, or slow resource load times.', 'wpshadow' ),
			$lcp_seconds,
			$status
		);

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => $description,
			'severity'    => $severity,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/performance-largest-contentful-paint',
			'meta'        => array(
				'lcp_ms' => $lcp_ms,
				'lcp_seconds' => $lcp_seconds,
				'threshold_good' => self::LCP_GOOD,
				'threshold_needs_improvement' => self::LCP_NEEDS_IMPROVEMENT,
			),
		);
	}
}
