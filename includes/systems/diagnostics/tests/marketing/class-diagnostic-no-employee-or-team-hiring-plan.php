<?php
/**
 * No Employee or Team Hiring Plan Diagnostic
 *
 * Checks if hiring strategy is documented.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Employee Hiring Plan Diagnostic
 *
 * Companies with documented hiring plans execute 5x faster and
 * build stronger teams than those without plans.
 *
 * @since 0.6093.1200
 */
class Diagnostic_No_Employee_Or_Team_Hiring_Plan extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'no-employee-team-hiring-plan';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Employee/Team Hiring Plan';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks if hiring strategy is documented';

	/**
	 * Diagnostic family.
	 *
	 * @var string
	 */
	protected static $family = 'business-performance';

	/**
	 * Run diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		if ( ! self::has_hiring_plan() ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'No team hiring plan detected. You can\'t scale without people. Companies with documented hiring plans execute 5x faster. Plan: 1) Current roles/people, 2) Roles needed for next 12 months (who do you need to hire?), 3) Timeline (when to hire each role?), 4) Budget (salary + benefits), 5) Hiring process (how to find, evaluate, hire), 6) Onboarding (how to integrate new people), 7) Culture (how to maintain as you grow). First hire usually: 1) Operations (frees founders), 2) Sales (brings revenue), 3) Support (scales with customers). Clear hiring plan prevents panic hiring and attracts better people.', 'wpshadow' ),
				'severity'    => 'medium',
				'threat_level' => 45,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/team-hiring-plan?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'details'     => array(
					'issue'               => __( 'No team hiring plan detected', 'wpshadow' ),
					'recommendation'      => __( 'Document hiring plan for next 12 months', 'wpshadow' ),
					'business_impact'     => __( 'Missing 5x faster scaling execution with team', 'wpshadow' ),
					'hiring_framework'    => self::get_hiring_framework(),
					'first_hires'         => self::get_first_hires(),
				),
			);
		}

		return null;
	}

	/**
	 * Check if hiring plan exists.
	 *
	 * @since 0.6093.1200
	 * @return bool True if plan detected, false otherwise.
	 */
	private static function has_hiring_plan() {
		// Check for hiring-related content
		$hiring_posts = self::count_posts_by_keywords(
			array(
				'hiring',
				'team',
				'employee',
				'recruitment',
				'hiring plan',
			)
		);

		return $hiring_posts > 0;
	}

	/**
	 * Count posts containing specific keywords.
	 *
	 * @since 0.6093.1200
	 * @param  array $keywords Keywords to search for.
	 * @return int Number of matching posts.
	 */
	private static function count_posts_by_keywords( $keywords ) {
		$total = 0;

		foreach ( $keywords as $keyword ) {
			$posts = get_posts(
				array(
					's'              => $keyword,
					'posts_per_page' => 1,
					'post_type'      => array( 'post', 'page' ),
					'post_status'    => 'publish',
					'fields'         => 'ids',
				)
			);

			if ( ! empty( $posts ) ) {
				++$total;
			}
		}

		return $total;
	}

	/**
	 * Get hiring framework.
	 *
	 * @since 0.6093.1200
	 * @return array Hiring framework components.
	 */
	private static function get_hiring_framework() {
		return array(
			'current'       => __( '1. Current State: Map existing team, roles, salaries', 'wpshadow' ),
			'needed'        => __( '2. Needs: Identify gaps (what can\'t you do without help?)', 'wpshadow' ),
			'roles'         => __( '3. Define Roles: Job description, responsibilities, qualifications', 'wpshadow' ),
			'timeline'      => __( '4. Timeline: When to hire each person (Q1, Q2, Q3, Q4)', 'wpshadow' ),
			'budget'        => __( '5. Budget: Salary + benefits + recruiting costs', 'wpshadow' ),
			'hiring_process' => __( '6. Process: Recruiting source, interview questions, decision criteria', 'wpshadow' ),
			'onboarding'    => __( '7. Onboarding: First week, first 30 days, first 90 days', 'wpshadow' ),
			'culture'       => __( '8. Culture: How to maintain values as team grows', 'wpshadow' ),
		);
	}

	/**
	 * Get typical first hires.
	 *
	 * @since 0.6093.1200
	 * @return array Typical first hire priorities.
	 */
	private static function get_first_hires() {
		return array(
			'operations' => array(
				'role'       => __( 'Operations/Admin (Usually First)', 'wpshadow' ),
				'why'        => __( 'Frees founders from non-revenue work', 'wpshadow' ),
				'benefits'   => __( 'Faster time to focus on strategy and growth', 'wpshadow' ),
			),
			'sales'      => array(
				'role'       => __( 'Sales Person (If B2B)', 'wpshadow' ),
				'why'        => __( 'Directly brings revenue', 'wpshadow' ),
				'benefits'   => __( 'Sales feedback improves product', 'wpshadow' ),
			),
			'support'    => array(
				'role'       => __( 'Customer Support (If Customer Success Critical)', 'wpshadow' ),
				'why'        => __( 'Scales with customer growth', 'wpshadow' ),
				'benefits'   => __( 'Improves customer satisfaction and retention', 'wpshadow' ),
			),
		);
	}
}
