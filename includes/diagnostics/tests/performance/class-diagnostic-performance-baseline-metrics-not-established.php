<?php
/**
 * Performance Baseline Metrics Not Established Diagnostic
 *
 * Checks if performance metrics are tracked.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2352
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Performance Baseline Metrics Not Established Diagnostic Class
 *
 * Detects missing performance tracking.
 *
 * @since 1.2601.2352
 */
class Diagnostic_Performance_Baseline_Metrics_Not_Established extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'performance-baseline-metrics-not-established';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Performance Baseline Metrics Not Established';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if performance metrics are tracked';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2352
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if performance metrics are being tracked
		if ( ! get_option( 'wpshadow_performance_baseline' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Performance baseline metrics are not established. Set up performance monitoring to track page speed, server response time, and Core Web Vitals.', 'wpshadow' ),
				'severity'      => 'medium',
				'threat_level'  => 30,
				'auto_fixable'  => true,
				'kb_link'       => 'https://wpshadow.com/kb/performance-baseline-metrics-not-established',
			);
		}

		return null;
	}
}
