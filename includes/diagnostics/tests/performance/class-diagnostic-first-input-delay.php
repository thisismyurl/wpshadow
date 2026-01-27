<?php
/**
 * Diagnostic: First Input Delay (FID) Measurement
 *
 * Measures First Input Delay (FID) to detect responsiveness issues.
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
 * Class Diagnostic_First_Input_Delay
 *
 * Monitors First Input Delay (FID), a Core Web Vital that measures
 * interactivity. FID should be less than 100ms for good responsiveness.
 *
 * @since 1.2601.2148
 */
class Diagnostic_First_Input_Delay extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'first-input-delay';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'First Input Delay (FID) Measurement';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Measure First Input Delay (FID) to detect responsiveness issues';

	/**
	 * Good FID threshold in milliseconds.
	 *
	 * @var int
	 */
	private const FID_GOOD = 100;

	/**
	 * Needs improvement FID threshold in milliseconds.
	 *
	 * @var int
	 */
	private const FID_NEEDS_IMPROVEMENT = 300;

	/**
	 * Run the diagnostic check.
	 *
	 * Note: This diagnostic requires JavaScript measurement of user interactions.
	 * This implementation provides a placeholder that can be enhanced with
	 * actual FID data from real user monitoring.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if FID is poor, null otherwise.
	 */
	public static function check() {
		// Check if FID data is available from previous measurements
		$fid_data = get_transient( 'wpshadow_fid_measurement' );

		if ( false === $fid_data || ! isset( $fid_data['fid'] ) ) {
			// No FID data available - return info message
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'First Input Delay (FID) measurement not yet available. FID is a Core Web Vital that measures interactivity - the delay between when a user first interacts with your page and when the browser responds. Enable JavaScript-based performance monitoring to collect FID data. Good: <100ms, Needs improvement: 100-300ms, Poor: >300ms.', 'wpshadow' ),
				'severity'    => 'info',
				'threat_level' => 20,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/performance-first-input-delay',
				'meta'        => array(
					'measurement_available' => false,
				),
			);
		}

		$fid_ms = absint( $fid_data['fid'] );

		// Determine severity based on FID value
		if ( $fid_ms < self::FID_GOOD ) {
			// Good FID - no issue
			return null;
		}

		if ( $fid_ms < self::FID_NEEDS_IMPROVEMENT ) {
			// Needs improvement
			$severity = 'low';
			$threat_level = 20;
			$status = __( 'needs improvement', 'wpshadow' );
		} else {
			// Poor FID
			$severity = 'medium';
			$threat_level = 40;
			$status = __( 'poor', 'wpshadow' );
		}

		$description = sprintf(
			/* translators: 1: FID value in milliseconds, 2: status (needs improvement/poor) */
			__( 'First Input Delay (FID) is %1$sms, which is %2$s. FID measures interactivity - how quickly the browser responds to user interaction. Google recommends FID under 100ms. Common causes: heavy JavaScript execution blocking the main thread, large or poorly optimized JavaScript bundles, third-party scripts, or render-blocking resources.', 'wpshadow' ),
			$fid_ms,
			$status
		);

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => $description,
			'severity'    => $severity,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/performance-first-input-delay',
			'meta'        => array(
				'fid_ms' => $fid_ms,
				'threshold_good' => self::FID_GOOD,
				'threshold_needs_improvement' => self::FID_NEEDS_IMPROVEMENT,
			),
		);
	}
}
