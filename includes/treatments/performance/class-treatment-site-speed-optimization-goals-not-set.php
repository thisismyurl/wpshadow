<?php
/**
 * Site Speed Optimization Goals Not Set Treatment
 *
 * Checks if speed goals are set.
 * Speed goals = specific, measurable targets.
 * Without goals = no direction, unclear success.
 * With goals = focus optimization, measure progress.
 *
 * **What This Check Does:**
 * - Checks for documented speed goals
 * - Validates goal specificity (measurable targets)
 * - Tests for goal tracking mechanism
 * - Checks stakeholder awareness of goals
 * - Validates goal alignment with business needs
 * - Returns severity if no goals established
 *
 * **Why This Matters:**
 * Optimization work without goals = shooting in dark.
 * "Make it faster" = vague, unmeasurable.
 * Goals = clear targets. "Load time <2s, Lighthouse >85."
 * Know when optimization sufficient. Demonstrate success.
 * Align team on priorities.
 *
 * **Business Impact:**
 * Agency client: "Optimize our site." Agency: spent 60 hours, improved
 * load time 4.5s → 2.8s (38% faster). Client: "That's it? Still feels
 * slow." Expectations mismatch. No defined success criteria. Next
 * project: established goals first. Client goal: "Match competitor
 * speed (1.5s load, Lighthouse 90+)." Agency: optimized to1.0s load,
 * Lighthouse 92. Client: thrilled, clear success. Approved ongoing
 * optimization retainer ($1.5K/month). Lesson: always define goals
 * before optimization. Goals create accountability, align expectations,
 * demonstrate value. Goal documentation: 15 minutes. Value: saved
 * client relationship, secured ongoing revenue.
 *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Clear expectations
 * - #9 Show Value: Measurable success criteria
 * - #10 Beyond Pure: Accountability through specificity
 *
 * **Related Checks:**
 * - Performance Budget (related concept)
 * - Performance Baseline (starting point)
 * - Load Time Monitoring (goal tracking)
 *
 * **Learn More:**
 * Setting speed goals: https://wpshadow.com/kb/speed-goals
 * Video: Performance goal framework (10min): https://wpshadow.com/training/goals
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
 * Site Speed Optimization Goals Not Set Treatment Class
 *
 * Detects missing speed goals.
 *
 * **Detection Pattern:**
 * 1. Check for documented performance goals
 * 2. Validate goal specificity (measurable)
 * 3. Test for tracking mechanism
 * 4. Check stakeholder awareness
 * 5. Validate goal documentation
 * 6. Return if no goals established
 *
 * **Real-World Scenario:**
 * Documented goals: Homepage load <1.5s (mobile 3G), Lighthouse
 * Performance >85, Core Web Vitals "Good" (LCP <2.5s, FID <100ms,
 * CLS <0.1), TTFB <600ms. Tracked monthly. Team aware. Optimization
 * priorities clear. Success measurable. Result: achieved all goals
 * in 3 months. Stakeholders satisfied, data proves success.
 *
 * **Implementation Notes:**
 * - Checks for goal documentation
 * - Validates goal measurability
 * - Tests tracking implementation
 * - Severity: low (best practice, high long-term value)
 * - Treatment: establish SMART speed goals
 *
 * @since 1.6093.1200
 */
class Treatment_Site_Speed_Optimization_Goals_Not_Set extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'site-speed-optimization-goals-not-set';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Site Speed Optimization Goals Not Set';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if speed goals are set';

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
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Site_Speed_Optimization_Goals_Not_Set' );
	}
}
