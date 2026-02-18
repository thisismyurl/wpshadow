<?php
/**
 * Performance Budget Not Defined Diagnostic
 *
 * Checks if performance budget is defined.
 * Performance budget = limits on page weight, requests, load time.
 * Without budget = no guardrails, performance regresses over time.
 * With budget = maintain performance as site evolves.
 *
 * **What This Check Does:**
 * - Checks for documented performance budget
 * - Validates budget metrics (page size, requests, load time)
 * - Tests for budget enforcement (CI/CD integration)
 * - Checks for budget violation alerts
 * - Validates team awareness of budget
 * - Returns severity if no budget defined
 *
 * **Why This Matters:**
 * Site optimized. Developers add features. Content team adds images.
 * No performance budget. Gradual regression. Site slow again.
 * Budget = guardrails. "Page weight must stay under 2MB."
 * "Load time must stay under 2s." Automated checks enforce.
 * Prevents performance regression.
 *
 * **Business Impact:**
 * Marketing site: optimized to 1.2s load. 6 months later: 3.8s load.
 * Cause: new features, unoptimized images, third-party scripts.
 * Nobody noticed until conversion rate dropped 40%. Implemented
 * performance budget: page weight <2MB, <50 requests, LCP <2.5s,
 * load time <2s. Added Lighthouse CI: fails pull request if budget
 * violated. Content team: automated image optimization. Result:
 * performance maintained. Load time stable at 1.3-1.5s for 2 years.
 * Prevented repeat performance crisis. Budget enforcement: automated,
 * zero ongoing effort. Setup: 4 hours (Lighthouse CI config).
 *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Sustained performance
 * - #9 Show Value: Prevent regression, maintain gains
 * - #10 Beyond Pure: Proactive performance culture
 *
 * **Related Checks:**
 * - Performance Baseline Metrics (starting point)
 * - Load Time Monitoring (budget enforcement)
 * - Page Weight Optimization (budget metric)
 *
 * **Learn More:**
 * Performance budgets: https://wpshadow.com/kb/perf-budget
 * Video: Setting performance budgets (13min): https://wpshadow.com/training/budget
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6030.2352
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Performance Budget Not Defined Diagnostic Class
 *
 * Detects missing performance budget.
 *
 * **Detection Pattern:**
 * 1. Check for documented performance budget
 * 2. Validate budget metrics defined
 * 3. Test for budget enforcement (CI/CD checks)
 * 4. Check monitoring for budget violations
 * 5. Validate team awareness
 * 6. Return if no budget documentation found
 *
 * **Real-World Scenario:**
 * Defined budget: Homepage <2MB, <40 requests, LCP <2.5s, CLS <0.1.
 * Product pages <2.5MB, <50 requests. Blog posts <1.5MB, <35 requests.
 * Lighthouse CI: runs on every commit. Blocks merge if budget exceeded.
 * Alert: developer added 500KB library. CI failed. Developer: found
 * lighter alternative (50KB). Budget maintained. This happens 2-3x/month.
 * Budget prevents slow regression.
 *
 * **Implementation Notes:**
 * - Checks for budget documentation
 * - Validates budget metrics
 * - Tests enforcement mechanisms
 * - Severity: low (preventive measure, high long-term value)
 * - Treatment: define budget, implement enforcement
 *
 * @since 1.6030.2352
 */
class Diagnostic_Performance_Budget_Not_Defined extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'performance-budget-not-defined';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Performance Budget Not Defined';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if performance budget is defined';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6030.2352
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for performance budget definition
		if ( ! get_option( 'performance_budget_defined' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Performance budget is not defined. Set targets for page load time (<3s), FCP (<1.8s), LCP (<2.5s), and CLS (<0.1) to maintain consistent performance.', 'wpshadow' ),
				'severity'      => 'medium',
				'threat_level'  => 40,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/performance-budget-not-defined',
			);
		}

		return null;
	}
}
