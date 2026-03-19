<?php
/**
 * Performance Budget Not Defined Treatment
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
 * Marketing site: optimized to1.0s load. 6 months later: 3.8s load.
 * Cause: new features, unoptimized images, third-party scripts.
 * Nobody noticed until conversion rate dropped 40%. Implemented
 * performance budget: page weight <2MB, <50 requests, LCP <2.5s,
 * load time <2s. Added Lighthouse CI: fails pull request if budget
 * violated. Content team: automated image optimization. Result:
 * performance maintained. Load time stable at1.0-1.5s for 2 years.
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
 * @subpackage Treatments
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Performance Budget Not Defined Treatment Class
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
 * @since 1.6093.1200
 */
class Treatment_Performance_Budget_Not_Defined extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'performance-budget-not-defined';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Performance Budget Not Defined';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if performance budget is defined';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the treatment check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Performance_Budget_Not_Defined' );
	}
}
