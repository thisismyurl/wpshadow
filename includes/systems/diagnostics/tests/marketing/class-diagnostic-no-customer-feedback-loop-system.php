<?php
/**
 * No Customer Feedback Loop System Diagnostic
 *
 * Checks if customer feedback collection and response system exists.
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
 * Customer Feedback Loop System Diagnostic
 *
 * Companies that actively collect and act on feedback see 14% higher
 * customer satisfaction and 10% lower churn.
 *
 * @since 1.6035.0000
 */
class Diagnostic_No_Customer_Feedback_Loop_System extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'no-customer-feedback-loop-system';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Customer Feedback Loop System';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks if customer feedback collection and response system exists';

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
		if ( ! self::has_feedback_system() ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'No customer feedback loop system detected. Companies that actively collect and act on feedback see 14% higher satisfaction and 10% lower churn. Without systematic feedback, you build in the dark. Implement: 1) NPS surveys (quarterly "How likely to recommend?"), 2) Post-purchase surveys (satisfaction, improvements), 3) Feature request tracking (public roadmap voting), 4) Customer interviews (monthly deep dives), 5) Support ticket analysis (common pain points), 6) Close the loop (tell customers what you changed based on their feedback). Listening drives loyalty.', 'wpshadow' ),
				'severity'    => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/customer-feedback-loop',
				'details'     => array(
					'issue'               => __( 'No systematic customer feedback collection detected', 'wpshadow' ),
					'recommendation'      => __( 'Implement feedback collection system across customer journey touchpoints', 'wpshadow' ),
					'business_impact'     => __( 'Missing 14% satisfaction improvement and 10% churn reduction', 'wpshadow' ),
					'feedback_types'      => self::get_feedback_types(),
					'closing_loop'        => self::get_closing_loop_tactics(),
				),
			);
		}

		return null;
	}

	/**
	 * Check if feedback system exists.
	 *
	 * @since  1.6035.0000
	 * @return bool True if system detected, false otherwise.
	 */
	private static function has_feedback_system() {
		// Check for feedback-related content
		$feedback_posts = self::count_posts_by_keywords(
			array(
				'feedback',
				'survey',
				'customer feedback',
				'feature request',
				'product feedback',
				'nps',
				'net promoter',
			)
		);

		if ( $feedback_posts > 0 ) {
			return true;
		}

		// Check for survey/feedback plugins
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$all_plugins = get_plugins();
		$feedback_keywords = array(
			'survey',
			'feedback',
			'poll',
			'nps',
			'typeform',
			'surveymonkey',
			'hotjar',
		);

		foreach ( $all_plugins as $plugin_file => $plugin_data ) {
			$plugin_name = strtolower( $plugin_data['Name'] );
			foreach ( $feedback_keywords as $keyword ) {
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
	 * Get feedback collection types.
	 *
	 * @since  1.6035.0000
	 * @return array Feedback types with descriptions.
	 */
	private static function get_feedback_types() {
		return array(
			'nps_survey'          => __( 'NPS (Net Promoter Score): "How likely are you to recommend us?" (0-10)', 'wpshadow' ),
			'csat'                => __( 'CSAT (Customer Satisfaction): "How satisfied were you?" (1-5 stars)', 'wpshadow' ),
			'post_purchase'       => __( 'Post-purchase survey: Product quality, delivery, experience', 'wpshadow' ),
			'feature_requests'    => __( 'Feature request system: Public roadmap with voting', 'wpshadow' ),
			'exit_surveys'        => __( 'Cancel/churn surveys: Why are you leaving?', 'wpshadow' ),
			'in_app_feedback'     => __( 'In-app feedback widget: Quick thumbs up/down + comment', 'wpshadow' ),
			'customer_interviews' => __( 'Scheduled 1-on-1 interviews with power users', 'wpshadow' ),
			'support_tickets'     => __( 'Analyze support tickets for patterns/themes', 'wpshadow' ),
			'social_listening'    => __( 'Monitor social media mentions and reviews', 'wpshadow' ),
		);
	}

	/**
	 * Get tactics for closing the feedback loop.
	 *
	 * @since  1.6035.0000
	 * @return array Closing loop tactics.
	 */
	private static function get_closing_loop_tactics() {
		return array(
			'acknowledge'     => __( 'Thank customers for feedback within 24 hours', 'wpshadow' ),
			'categorize'      => __( 'Tag and track feedback by theme/priority', 'wpshadow' ),
			'act'             => __( 'Build top-requested features into roadmap', 'wpshadow' ),
			'communicate'     => __( 'Tell customers when you ship their request', 'wpshadow' ),
			'public_changelog' => __( 'Share "You asked, we built" updates', 'wpshadow' ),
			'personal_followup' => __( 'Follow up with specific users who requested', 'wpshadow' ),
			'quantify'        => __( 'Track feedback → action conversion rate', 'wpshadow' ),
			'celebrate'       => __( 'Credit customers publicly when appropriate', 'wpshadow' ),
		);
	}
}
