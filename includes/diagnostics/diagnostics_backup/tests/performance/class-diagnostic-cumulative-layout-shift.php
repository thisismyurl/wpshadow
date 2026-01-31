<?php
/**
 * Diagnostic: Cumulative Layout Shift (CLS) Measurement
 *
 * Measures Cumulative Layout Shift (CLS) to detect unexpected content movement.
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
 * Class Diagnostic_Cumulative_Layout_Shift
 *
 * Monitors Cumulative Layout Shift (CLS), a Core Web Vital that measures
 * visual stability. CLS should be less than 0.1 for good user experience.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Cumulative_Layout_Shift extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'cumulative-layout-shift';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Cumulative Layout Shift (CLS) Measurement';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Measure Cumulative Layout Shift (CLS) to detect unexpected content movement';

	/**
	 * Good CLS threshold (dimensionless).
	 *
	 * @var float
	 */
	private const CLS_GOOD = 0.1;

	/**
	 * Needs improvement CLS threshold (dimensionless).
	 *
	 * @var float
	 */
	private const CLS_NEEDS_IMPROVEMENT = 0.25;

	/**
	 * Run the diagnostic check.
	 *
	 * Note: This diagnostic requires JavaScript measurement of layout shifts.
	 * This implementation provides a placeholder that can be enhanced with
	 * actual CLS data from real user monitoring or lab testing.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if CLS is poor, null otherwise.
	 */
	public static function check() {
		// Check if CLS data is available from previous measurements
		$cls_data = get_transient( 'wpshadow_cls_measurement' );

		if ( false === $cls_data || ! isset( $cls_data['cls'] ) ) {
			// No CLS data available - return info message
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'Cumulative Layout Shift (CLS) measurement not yet available. CLS is a Core Web Vital that measures visual stability. Enable JavaScript-based performance monitoring to collect CLS data. Good: <0.1, Needs improvement: 0.1-0.25, Poor: >0.25.', 'wpshadow' ),
				'severity'    => 'info',
				'threat_level' => 20,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/performance-cumulative-layout-shift',
				'meta'        => array(
					'measurement_available' => false,
				),
			);
		}

		$cls_score = floatval( $cls_data['cls'] );

		// Determine severity based on CLS value
		if ( $cls_score < self::CLS_GOOD ) {
			// Good CLS - no issue
			return null;
		}

		if ( $cls_score < self::CLS_NEEDS_IMPROVEMENT ) {
			// Needs improvement
			$severity = 'low';
			$threat_level = 20;
			$status = __( 'needs improvement', 'wpshadow' );
		} else {
			// Poor CLS
			$severity = 'medium';
			$threat_level = 40;
			$status = __( 'poor', 'wpshadow' );
		}

		$description = sprintf(
			/* translators: 1: CLS score, 2: status (needs improvement/poor) */
			__( 'Cumulative Layout Shift (CLS) score is %1$s, which is %2$s. CLS measures visual stability - how much content unexpectedly shifts during page load. Google recommends CLS under 0.1. Common causes: images without dimensions, ads/embeds/iframes without reserved space, dynamically injected content, or web fonts causing layout shifts.', 'wpshadow' ),
			number_format( $cls_score, 3 ),
			$status
		);

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => $description,
			'severity'    => $severity,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/performance-cumulative-layout-shift',
			'meta'        => array(
				'cls_score' => $cls_score,
				'threshold_good' => self::CLS_GOOD,
				'threshold_needs_improvement' => self::CLS_NEEDS_IMPROVEMENT,
			),
		);
	}
}
