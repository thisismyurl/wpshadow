<?php
/**
 * No Voice of Customer Program Diagnostic
 *
 * Checks if voice of customer program exists.
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
 * Voice of Customer Program Diagnostic
 *
 * Your customers know what they need better than you do.
 * Listen systematically or miss critical insights.
 *
 * @since 0.6093.1200
 */
class Diagnostic_No_Voice_Of_Customer_Program extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'no-voice-of-customer-program';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Voice of Customer Program';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks if voice of customer program exists';

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
		if ( ! self::has_voc_program() ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'No voice of customer program detected. Your customers know what they need better than you do. Implement: 1) Customer interviews (1-on-1, deep dives, paid if needed), 2) Surveys (NPS, CSAT, feature requests), 3) Support ticket analysis (what are they struggling with?), 4) User behavior analysis (what features are used?), 5) Advisory board (quarterly feedback session). Frequency: 4-8 interviews/quarter per segment, survey monthly, ticket review weekly. Document: Key quotes, themes, feedback. Track: Which features/improvements matter most (prioritize roadmap). Share: Monthly customer insights with team. This drives: Better product decisions, faster innovation, stronger customer relationships.', 'wpshadow' ),
				'severity'    => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/voice-of-customer-program?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'details'     => array(
					'issue'          => __( 'No voice of customer program detected', 'wpshadow' ),
					'recommendation' => __( 'Implement voice of customer program', 'wpshadow' ),
					'business_impact' => __( 'Missing customer insights that drive product decisions', 'wpshadow' ),
					'program_components' => self::get_program_components(),
				),
			);
		}

		return null;
	}

	/**
	 * Check if VoC program exists.
	 *
	 * @since 0.6093.1200
	 * @return bool True if program detected, false otherwise.
	 */
	private static function has_voc_program() {
		$voc_posts = self::count_posts_by_keywords(
			array(
				'customer feedback',
				'voice of customer',
				'customer interview',
				'user research',
				'customer insight',
			)
		);

		return $voc_posts > 0;
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
	 * Get program components.
	 *
	 * @since 0.6093.1200
	 * @return array Program components and methodology.
	 */
	private static function get_program_components() {
		return array(
			'interviews' => array(
				'method'    => __( 'Customer Interviews (Deep Insights)', 'wpshadow' ),
				'frequency' => __( '4-8 per quarter, per segment', 'wpshadow' ),
				'duration'  => __( '30-60 minutes', 'wpshadow' ),
				'questions' => __( 'Open-ended: "What\'s your biggest challenge?" "How do you currently solve this?"', 'wpshadow' ),
				'goal'      => __( 'Understand underlying problems and motivations', 'wpshadow' ),
			),
			'surveys'    => array(
				'method'    => __( 'Surveys (Quantitative Validation)', 'wpshadow' ),
				'frequency' => __( 'Monthly or quarterly', 'wpshadow' ),
				'types'     => array(
					__( 'NPS: "How likely to recommend? 0-10" - track over time', 'wpshadow' ),
					__( 'CSAT: "How satisfied? 1-5" - product satisfaction', 'wpshadow' ),
					__( 'Feature requests: "Which feature would help most?"', 'wpshadow' ),
				),
				'goal'      => __( 'Measure satisfaction and validate themes from interviews', 'wpshadow' ),
			),
			'support'    => array(
				'method'    => __( 'Support Ticket Analysis (Real Problems)', 'wpshadow' ),
				'frequency' => __( 'Weekly review', 'wpshadow' ),
				'analyze'   => __( 'What are customers asking for? What\'s hard to use? Where are they struggling?', 'wpshadow' ),
				'goal'      => __( 'Identify product gaps and UX issues', 'wpshadow' ),
			),
			'behavior'   => array(
				'method'    => __( 'User Behavior Analysis (What They Do)', 'wpshadow' ),
				'track'     => __( 'Which features are used? Drop-off points? Usage patterns?', 'wpshadow' ),
				'tools'     => __( 'Analytics, session recordings, heatmaps', 'wpshadow' ),
				'goal'      => __( 'Understand what matters to customers (actions > words)', 'wpshadow' ),
			),
			'advisory'   => array(
				'method'    => __( 'Advisory Board (Structured Feedback)', 'wpshadow' ),
				'frequency' => __( 'Quarterly 90-min session', 'wpshadow' ),
				'members'   => __( '5-10 key customers/power users', 'wpshadow' ),
				'agenda'    => __( 'Product roadmap feedback, feature prioritization, market trends', 'wpshadow' ),
				'goal'      => __( 'Deep strategic input from most valuable customers', 'wpshadow' ),
			),
		);
	}
}
