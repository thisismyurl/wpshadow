<?php
/**
 * No Annual Business Plan Documented Diagnostic
 *
 * Checks if annual business plan or strategy document exists.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6035.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Annual Business Plan Documented Diagnostic
 *
 * Businesses with written annual plans grow 30% faster than those without.
 * A documented plan provides clarity, alignment, and accountability.
 *
 * @since 1.6035.0000
 */
class Diagnostic_No_Annual_Business_Plan_Documented extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'no-annual-business-plan-documented';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Annual Business Plan Documented';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks if annual business plan or strategy document exists';

	/**
	 * Diagnostic family.
	 *
	 * @var string
	 */
	protected static $family = 'marketing';

	/**
	 * Run diagnostic check.
	 *
	 * @since  1.6035.0000
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		if ( ! self::has_business_plan() ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'No annual business plan documented. You\'re operating reactively instead of strategically. Businesses with written annual plans grow 30% faster and hit goals 2x more often. Your plan should include: 1) Revenue/growth targets, 2) Key initiatives (3-5 major projects), 3) Budget allocation, 4) Team hiring plan, 5) Product roadmap, 6) Marketing strategy, 7) Monthly milestones, 8) Risk mitigation. A plan creates clarity, alignment, and accountability. Strategic beats reactive every time.', 'wpshadow' ),
				'severity'    => 'medium',
				'threat_level' => 60,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/annual-business-plan',
				'details'     => array(
					'issue'               => __( 'No annual business plan or strategy document detected', 'wpshadow' ),
					'recommendation'      => __( 'Create written annual business plan with goals, initiatives, and milestones', 'wpshadow' ),
					'business_impact'     => __( 'Growing 30% slower and missing 50% of goals due to lack of strategic planning', 'wpshadow' ),
					'plan_components'     => self::get_plan_components(),
					'planning_process'    => self::get_planning_process(),
				),
			);
		}

		return null;
	}

	/**
	 * Check if business plan exists.
	 *
	 * @since  1.6035.0000
	 * @return bool True if plan detected, false otherwise.
	 */
	private static function has_business_plan() {
		// Check for business plan content
		$plan_posts = self::count_posts_by_keywords(
			array(
				'business plan',
				'annual plan',
				'strategic plan',
				'annual goals',
				'business strategy',
				'strategic roadmap',
				'annual objectives',
			)
		);

		if ( $plan_posts > 0 ) {
			return true;
		}

		// Check for project management/planning plugins that might host plans
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$all_plugins = get_plugins();
		$planning_keywords = array(
			'project management',
			'roadmap',
			'planning',
			'objectives',
			'okr',
			'goals',
			'gantt',
		);

		foreach ( $all_plugins as $plugin_file => $plugin_data ) {
			$plugin_name = strtolower( $plugin_data['Name'] );
			foreach ( $planning_keywords as $keyword ) {
				if ( false !== strpos( $plugin_name, $keyword ) ) {
					if ( is_plugin_active( $plugin_file ) ) {
						return true;
					}
				}
			}
		}

		return false;
	}

	/**
	 * Count posts containing specific keywords.
	 *
	 * @since  1.6035.0000
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
	 * Get business plan components.
	 *
	 * @since  1.6035.0000
	 * @return array Plan components with descriptions.
	 */
	private static function get_plan_components() {
		return array(
			'vision_mission'      => __( 'Vision (where you\'re going) and Mission (why you exist)', 'wpshadow' ),
			'goals_targets'       => __( 'Specific, measurable annual goals (revenue, customers, growth)', 'wpshadow' ),
			'initiatives'         => __( '3-5 key initiatives to achieve goals (major projects)', 'wpshadow' ),
			'budget'              => __( 'Annual budget allocation by department/initiative', 'wpshadow' ),
			'team_plan'           => __( 'Hiring plan (roles, timing, budget)', 'wpshadow' ),
			'product_roadmap'     => __( 'Product development roadmap (features, launches)', 'wpshadow' ),
			'marketing_strategy'  => __( 'Marketing strategy (channels, campaigns, budget)', 'wpshadow' ),
			'sales_plan'          => __( 'Sales strategy (targets, territories, compensation)', 'wpshadow' ),
			'milestones'          => __( 'Quarterly/monthly milestones to track progress', 'wpshadow' ),
			'risks'               => __( 'Risk assessment and mitigation strategies', 'wpshadow' ),
		);
	}

	/**
	 * Get annual planning process.
	 *
	 * @since  1.6035.0000
	 * @return array Planning process steps.
	 */
	private static function get_planning_process() {
		return array(
			'step_1' => __( 'Review: Analyze last year (what worked, what didn\'t, why)', 'wpshadow' ),
			'step_2' => __( 'Research: Market trends, competitor moves, customer feedback', 'wpshadow' ),
			'step_3' => __( 'Goals: Set 3-5 measurable annual goals (SMART format)', 'wpshadow' ),
			'step_4' => __( 'Initiatives: Identify 3-5 key initiatives to hit goals', 'wpshadow' ),
			'step_5' => __( 'Resources: Budget, team, tools needed for each initiative', 'wpshadow' ),
			'step_6' => __( 'Timeline: Break down into quarterly/monthly milestones', 'wpshadow' ),
			'step_7' => __( 'Document: Write it down (shared doc, not your head)', 'wpshadow' ),
			'step_8' => __( 'Review: Monthly check-ins to track progress and adjust', 'wpshadow' ),
		);
	}
}
