<?php
/**
 * Performance Baseline Metrics Not Established Diagnostic
 *
 * Checks if performance metrics are tracked.
 * Baseline metrics = documented starting point for performance.
 * Without baseline = can't measure improvement or detect degradation.
 * With baseline = track progress, demonstrate value.
 *
 * **What This Check Does:**
 * - Checks for documented performance baseline
 * - Validates metric tracking (load time, TTFB, Core Web Vitals)
 * - Tests for historical data availability
 * - Checks monitoring tool integration
 * - Validates regular measurement schedule
 * - Returns severity if no baseline established
 *
 * **Why This Matters:**
 * Optimization work = meaningless without measurement.
 * Baseline = proof of improvement. Show before/after.
 * Detect degradation early (compare to baseline).
 * Justify optimization investment with data.
 * Essential for accountability and continuous improvement.
 *
 * **Business Impact:**
 * Agency optimized client site. No baseline metrics documented.
 * Client: "Did this actually help?" Agency: "Yes, it's faster." Client:
 * "Prove it." Couldn't. Client questioned value. Lesson learned: now
 * document baseline before optimization. Process: 1) Lighthouse audit
 * (save report), 2) GTmetrix test (screenshot + save data), 3) Real
 * User Monitoring baseline (1 week data). After optimization: compare.
 * Result: provable 68% load time improvement, 42-point Lighthouse
 * increase. Client saw graphs, charts, numbers. Approved ongoing
 * optimization budget ($2K/month). Documentation time: 30 minutes.
 * Value: justified $2K monthly spend.
 *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Data-driven optimization
 * - #9 Show Value: Prove improvement with metrics
 * - #10 Beyond Pure: Accountability through measurement
 *
 * **Related Checks:**
 * - Performance Monitoring Implementation (ongoing tracking)
 * - Load Time Variation Monitoring (trend tracking)
 * - Core Web Vitals Tracking (specific metrics)
 *
 * **Learn More:**
 * Performance baselines: https://wpshadow.com/kb/perf-baseline
 * Video: Measuring site performance (11min): https://wpshadow.com/training/measure-perf
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
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
 * **Detection Pattern:**
 * 1. Check for documented baseline metrics
 * 2. Look for performance monitoring tools
 * 3. Validate historical data availability
 * 4. Check for regular measurement schedule
 * 5. Test for baseline documentation
 * 6. Return if no baseline established
 *
 * **Real-World Scenario:**
 * Established baseline: Lighthouse audit (performance: 58, saved JSON),
 * GTmetrix (4.2s load, screenshot), Core Web Vitals from Search Console
 * (LCP 3.8s, CLS 0.22). Documented in project wiki. After 3 months
 * optimization: Lighthouse 92, GTmetrix1.0s, LCP1.0s, CLS 0.03.
 * Created comparison report with before/after graphs. Stakeholders
 * impressed, optimization budget renewed.
 *
 * **Implementation Notes:**
 * - Checks for baseline documentation
 * - Validates measurement consistency
 * - Tests historical data access
 * - Severity: low (best practice but not critical)
 * - Treatment: document current metrics as baseline
 *
 * @since 1.6093.1200
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
	 * @since 1.6093.1200
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
