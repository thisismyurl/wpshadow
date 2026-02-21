<?php
/**
 * Frontend Performance Metrics Not Monitored Treatment
 *
 * Checks if performance metrics are monitored.
 * Performance monitoring = track real user experience metrics.
 * No monitoring = blind to performance issues.
 * With monitoring = see exactly what users experience.
 *
 * **What This Check Does:**
 * - Checks for Real User Monitoring (RUM) setup
 * - Validates Core Web Vitals tracking (LCP, FID, CLS)
 * - Tests performance API usage (Navigation Timing, Resource Timing)
 * - Checks analytics integration (Google Analytics, custom)
 * - Validates metric collection and reporting
 * - Returns severity if no performance monitoring
 *
 * **Why This Matters:**
 * Site feels slow to users. But how slow? Which pages?
 * Desktop or mobile? No data = guessing.
 * With monitoring: see exact metrics. LCP = 4.2s (bad).
 * Mobile = slow, desktop = fast. Target optimization.
 *
 * **Business Impact:**
 * Site has "performance issues" (vague user complaints).
 * No monitoring = can't measure or prioritize. Implement RUM
 * (Real User Monitoring). Discover: mobile LCP = 5.8s (terrible),
 * desktop LCP = 1.2s (good). Mobile bounce rate: 62%. Optimized
 * mobile specifically (image optimization, lazy loading). Mobile
 * LCP improved to 2.1s. Bounce rate dropped to 31%. Mobile revenue
 * increased 180%. Monitoring cost: $20/month. ROI: 900:1.
 *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Data-driven optimization
 * - #9 Show Value: Measure before/after improvements
 * - #10 Beyond Pure: Professional monitoring practices
 *
 * **Related Checks:**
 * - Core Web Vitals Optimization (what to monitor)
 * - Analytics Configuration (monitoring platform)
 * - Performance Budget Enforcement (targets)
 *
 * **Learn More:**
 * Performance monitoring: https://wpshadow.com/kb/performance-monitoring
 * Video: Real User Monitoring setup (13min): https://wpshadow.com/training/rum
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6030.2352
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Frontend Performance Metrics Not Monitored Treatment Class
 *
 * Detects missing performance monitoring.
 *
 * **Detection Pattern:**
 * 1. Check for performance monitoring scripts (RUM, analytics)
 * 2. Test Performance API usage (window.performance)
 * 3. Validate Core Web Vitals tracking
 * 4. Check for custom performance beacons
 * 5. Test metric reporting endpoints
 * 6. Return if no monitoring infrastructure
 *
 * **Real-World Scenario:**
 * Implemented web-vitals library + custom reporting:
 * getCLS(sendToAnalytics);
 * getFID(sendToAnalytics);
 * getLCP(sendToAnalytics);
 * Dashboard shows: LCP=2.8s (75th percentile), FID=85ms, CLS=0.15.
 * Identified problem pages. Targeted optimization. Tracked improvement
 * weekly. LCP improved to 1.8s. User satisfaction increased measurably.
 *
 * **Implementation Notes:**
 * - Checks performance monitoring setup
 * - Validates Core Web Vitals tracking
 * - Tests metric collection
 * - Severity: medium (can't improve what you don't measure)
 * - Treatment: implement RUM or web-vitals library
 *
 * @since 1.6030.2352
 */
class Treatment_Frontend_Performance_Metrics_Not_Monitored extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'frontend-performance-metrics-not-monitored';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Frontend Performance Metrics Not Monitored';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if performance metrics are monitored';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6030.2352
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Frontend_Performance_Metrics_Not_Monitored' );
	}
}
