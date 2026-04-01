<?php
/**
 * No Quarterly Business Reviews Diagnostic
 *
 * Checks if quarterly business reviews are conducted.
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
 * Quarterly Business Reviews Diagnostic
 *
 * Companies that review quarterly execute better.
 * Course correction beats fighting to the end.
 *
 * @since 0.6093.1200
 */
class Diagnostic_No_Quarterly_Business_Reviews extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'no-quarterly-business-reviews';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Quarterly Business Reviews (QBRs)';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks if quarterly business reviews are conducted';

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
		if ( ! self::has_quarterly_reviews() ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'No quarterly business reviews detected. Course correction beats end-of-year surprises. Conduct quarterly (every 3 months): 1) Review against plan (hit targets?), 2) Analyze what worked (double down), 3) Identify what didn\'t (fix or kill), 4) Make adjustments (next quarter), 5) Celebrate wins (morale), 6) Plan next quarter (goals, resources). Meetings typically: Full team 2-hour review + leadership 1-hour decision session. Output: Updated plan, adjusted goals, assigned owners, timeline. Without QBRs: Can\'t course correct, waste time on wrong things, team gets demotivated.', 'wpshadow' ),
				'severity'    => 'medium',
				'threat_level' => 45,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/quarterly-business-reviews?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'details'     => array(
					'issue'          => __( 'No quarterly business reviews detected', 'wpshadow' ),
					'recommendation' => __( 'Implement quarterly business review process', 'wpshadow' ),
					'business_impact' => __( 'Better decision-making and course correction every 90 days', 'wpshadow' ),
					'qbr_structure'  => self::get_qbr_structure(),
				),
			);
		}

		return null;
	}

	/**
	 * Check if quarterly reviews exist.
	 *
	 * @since 0.6093.1200
	 * @return bool True if reviews detected, false otherwise.
	 */
	private static function has_quarterly_reviews() {
		$review_posts = self::count_posts_by_keywords(
			array(
				'quarterly review',
				'QBR',
				'quarterly goals',
				'quarterly report',
			)
		);

		return $review_posts > 0;
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
	 * Get QBR structure.
	 *
	 * @since 0.6093.1200
	 * @return array QBR structure and agenda.
	 */
	private static function get_qbr_structure() {
		return array(
			'timing'  => array(
				'schedule' => __( 'Schedule: First month of each quarter (Jan, Apr, Jul, Oct)', 'wpshadow' ),
				'duration' => __( 'Duration: 3-4 hours total (full team + leadership sessions)', 'wpshadow' ),
			),
			'review'  => array(
				'session'  => __( 'Review Session (2 hours, full team)', 'wpshadow' ),
				'agenda'   => array(
					__( '1. Results: How did we perform vs. plan? (% to goal)', 'wpshadow' ),
					__( '2. What Worked: Which initiatives succeeded? Why?', 'wpshadow' ),
					__( '3. What Didn\'t: Which missed? Lessons learned?', 'wpshadow' ),
					__( '4. Blockers: What got in the way?', 'wpshadow' ),
					__( '5. Team Wins: Celebrate successes (morale boost)', 'wpshadow' ),
				),
			),
			'planning' => array(
				'session'  => __( 'Planning Session (1 hour, leadership)', 'wpshadow' ),
				'agenda'   => array(
					__( '1. Adjust Plan: Based on learnings, change course?', 'wpshadow' ),
					__( '2. Next Quarter Goals: SMART goals for next 90 days', 'wpshadow' ),
					__( '3. Resources: What do we need? (budget, people, tools)', 'wpshadow' ),
					__( '4. Ownership: Assign clear owners/DRIs', 'wpshadow' ),
					__( '5. Timeline: Key milestones/deadlines', 'wpshadow' ),
				),
			),
			'output'  => array(
				'document' => __( 'QBR Document: Record outcomes, updated goals, decisions', 'wpshadow' ),
				'share'    => __( 'Share with team: Communicate decisions and direction', 'wpshadow' ),
				'track'    => __( 'Track progress: Monthly check-ins vs. quarterly goals', 'wpshadow' ),
			),
		);
	}
}
