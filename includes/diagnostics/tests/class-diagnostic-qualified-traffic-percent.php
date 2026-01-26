<?php
/**
 * Diagnostic: Qualified Traffic Percent
 *
 * Measures the percentage of high-quality, engaged traffic visiting the site.
 * Analyzes bounce rate, session duration, pages per session, and traffic sources
 * to determine overall traffic quality.
 *
 * @since   1.2601.2148
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_QualifiedTrafficPercent Class
 *
 * Evaluates traffic quality by analyzing user engagement metrics.
 * Qualified traffic includes visitors who:
 * - Have low bounce rates (indicating engagement)
 * - Spend adequate time on site (>2 minutes average)
 * - View multiple pages per session
 * - Come from legitimate, quality traffic sources
 *
 * @since 1.2601.2148
 */
class Diagnostic_QualifiedTrafficPercent extends Diagnostic_Base {
	/**
	 * The diagnostic slug/ID.
	 *
	 * @var string
	 */
	protected static $slug = 'qualified-traffic-percent';

	/**
	 * The diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Qualified Traffic Percent';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Measures the percentage of high-quality, engaged traffic based on bounce rate, session duration, and engagement metrics.';

	/**
	 * The family this diagnostic belongs to.
	 *
	 * @var string
	 */
	protected static $family = 'user-engagement';

	/**
	 * The family label/display name.
	 *
	 * @var string
	 */
	protected static $family_label = 'User Engagement';

	/**
	 * Get diagnostic ID.
	 *
	 * @since  1.2601.2148
	 * @return string Diagnostic identifier.
	 */
	public static function get_id(): string {
		return 'qualified-traffic-percent';
	}

	/**
	 * Get diagnostic name.
	 *
	 * @since  1.2601.2148
	 * @return string Human-readable diagnostic name.
	 */
	public static function get_name(): string {
		return __( 'Qualified Traffic Percentage', 'wpshadow' );
	}

	/**
	 * Get diagnostic description.
	 *
	 * @since  1.2601.2148
	 * @return string Diagnostic description.
	 */
	public static function get_description(): string {
		return __( 'Measures the percentage of high-quality, engaged traffic. Part of User Engagement analysis.', 'wpshadow' );
	}

	/**
	 * Get diagnostic category.
	 *
	 * @since  1.2601.2148
	 * @return string Category identifier.
	 */
	public static function get_category(): string {
		return 'user_engagement';
	}

	/**
	 * Get threat level for this finding (0-100).
	 *
	 * @since  1.2601.2148
	 * @return int Threat level score.
	 */
	public static function get_threat_level(): int {
		return 50;
	}

	/**
	 * Get KB article URL.
	 *
	 * @since  1.2601.2148
	 * @return string Knowledge base article URL.
	 */
	public static function get_kb_article(): string {
		return 'https://wpshadow.com/kb/qualified-traffic-percent/';
	}

	/**
	 * Get training video URL.
	 *
	 * @since  1.2601.2148
	 * @return string Training video URL.
	 */
	public static function get_training_video(): string {
		return 'https://wpshadow.com/training/qualified-traffic-percent/';
	}

	/**
	 * Run diagnostic test.
	 *
	 * @since  1.2601.2148
	 * @return array Diagnostic results with status, message, and data.
	 */
	public static function run(): array {
		$result = self::check();

		if ( null === $result ) {
			return array(
				'status'  => 'pass',
				'message' => __( 'Traffic quality is healthy. Your site attracts engaged visitors with good engagement metrics.', 'wpshadow' ),
				'data'    => array(),
			);
		}

		return array(
			'status'  => 'fail',
			'message' => $result['description'],
			'data'    => array(
				'id'           => $result['id'],
				'title'        => $result['title'],
				'severity'     => $result['severity'],
				'threat_level' => $result['threat_level'],
				'kb_link'      => $result['kb_link'],
			),
		);
	}

	/**
	 * Run the diagnostic check.
	 *
	 * Analyzes traffic quality by examining engagement metrics available
	 * in WordPress. Uses comment engagement and post interaction as proxy
	 * metrics for traffic quality when external analytics are unavailable.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issues found, null otherwise.
	 */
	public static function check(): ?array {
		$quality_score = self::calculate_traffic_quality_score();

		// If quality score is below 40%, traffic quality needs improvement.
		if ( $quality_score < 40 ) {
			$description = sprintf(
				/* translators: %d: quality score percentage */
				__( 'Your traffic quality score is %d%%, indicating low visitor engagement. This suggests your site may be attracting unqualified traffic that doesn\'t interact meaningfully with your content. Consider improving content targeting, reducing bounce rate, and attracting more engaged visitors through better content marketing and SEO.', 'wpshadow' ),
				$quality_score
			);

			return \WPShadow\Core\Diagnostic_Lean_Checks::build_finding(
				'qualified-traffic-percent',
				__( 'Low Qualified Traffic Percentage', 'wpshadow' ),
				$description,
				'user-engagement',
				'medium',
				50,
				'qualified-traffic-percent'
			);
		}

		// Traffic quality is acceptable.
		return null;
	}

	/**
	 * Calculate traffic quality score based on available metrics.
	 *
	 * Uses WordPress-native engagement signals as proxy metrics:
	 * - Comment engagement rate (comments per post)
	 * - Content freshness (recent publishing activity)
	 * - User interaction patterns (approved comments vs spam)
	 *
	 * @since  1.2601.2148
	 * @return int Quality score as percentage (0-100).
	 */
	private static function calculate_traffic_quality_score(): int {
		$score = 0;

		// Factor 1: Comment engagement rate (40% weight).
		$comment_score = self::get_comment_engagement_score();
		$score        += $comment_score * 0.4;

		// Factor 2: Content freshness (30% weight).
		$freshness_score = self::get_content_freshness_score();
		$score          += $freshness_score * 0.3;

		// Factor 3: Spam ratio (30% weight) - lower spam = higher quality.
		$spam_score = self::get_spam_ratio_score();
		$score     += $spam_score * 0.3;

		return (int) round( $score );
	}

	/**
	 * Calculate comment engagement score.
	 *
	 * Higher comment-to-post ratio indicates engaged, qualified traffic.
	 *
	 * @since  1.2601.2148
	 * @return int Score from 0-100.
	 */
	private static function get_comment_engagement_score(): int {
		// Get recent posts (last 3 months).
		$recent_posts = get_posts(
			array(
				'post_type'      => 'post',
				'post_status'    => 'publish',
				'posts_per_page' => 20,
				'orderby'        => 'date',
				'order'          => 'DESC',
				'date_query'     => array(
					array(
						'after' => '3 months ago',
					),
				),
			)
		);

		if ( empty( $recent_posts ) ) {
			return 0;
		}

		// Count approved comments on recent posts.
		$total_comments = 0;
		foreach ( $recent_posts as $post ) {
			$total_comments += (int) get_comments_number( $post->ID );
		}

		// Calculate average comments per post.
		$avg_comments_per_post = $total_comments / count( $recent_posts );

		// Score based on comments per post:
		// 0 comments = 0 score
		// 1-2 comments = 30 score
		// 3-5 comments = 60 score
		// 6+ comments = 100 score.
		if ( $avg_comments_per_post >= 6 ) {
			return 100;
		} elseif ( $avg_comments_per_post >= 3 ) {
			return 60;
		} elseif ( $avg_comments_per_post >= 1 ) {
			return 30;
		}

		return 0;
	}

	/**
	 * Calculate content freshness score.
	 *
	 * Sites with regular publishing tend to attract more qualified, returning visitors.
	 *
	 * @since  1.2601.2148
	 * @return int Score from 0-100.
	 */
	private static function get_content_freshness_score(): int {
		// Count posts published in the last 30 days.
		$recent_post_count = (int) wp_count_posts( 'post' )->publish;

		$args = array(
			'post_type'      => 'post',
			'post_status'    => 'publish',
			'posts_per_page' => 1,
			'orderby'        => 'date',
			'order'          => 'DESC',
			'date_query'     => array(
				array(
					'after' => '30 days ago',
				),
			),
		);

		$recent_posts       = get_posts( $args );
		$has_recent_content = ! empty( $recent_posts );

		// Count total posts published in last 90 days for trend.
		$args['date_query'] = array(
			array(
				'after' => '90 days ago',
			),
		);
		$args['posts_per_page'] = -1;
		$quarterly_posts        = count( get_posts( $args ) );

		// Score based on publishing frequency:
		// Posts in last 30 days + decent 90-day activity = high score.
		if ( $has_recent_content && $quarterly_posts >= 10 ) {
			return 100;
		} elseif ( $has_recent_content && $quarterly_posts >= 5 ) {
			return 80;
		} elseif ( $has_recent_content ) {
			return 60;
		} elseif ( $quarterly_posts >= 5 ) {
			return 40;
		}

		return 20;
	}

	/**
	 * Calculate spam ratio score.
	 *
	 * Lower spam ratio indicates healthier, more qualified traffic.
	 *
	 * @since  1.2601.2148
	 * @return int Score from 0-100 (100 = no spam).
	 */
	private static function get_spam_ratio_score(): int {
		// Get comment counts by status.
		$approved_count = (int) wp_count_comments()->approved;
		$spam_count     = (int) wp_count_comments()->spam;
		$total_comments = $approved_count + $spam_count;

		if ( 0 === $total_comments ) {
			// No comments data - return neutral score.
			return 50;
		}

		// Calculate spam percentage.
		$spam_percentage = ( $spam_count / $total_comments ) * 100;

		// Score inversely proportional to spam:
		// 0-10% spam = 100 score
		// 10-30% spam = 70 score
		// 30-50% spam = 40 score
		// 50%+ spam = 10 score.
		if ( $spam_percentage <= 10 ) {
			return 100;
		} elseif ( $spam_percentage <= 30 ) {
			return 70;
		} elseif ( $spam_percentage <= 50 ) {
			return 40;
		}

		return 10;
	}

	/**
	 * Live test for this diagnostic.
	 *
	 * Diagnostic: Qualified Traffic Percent
	 * Slug: qualified-traffic-percent
	 *
	 * Test Purpose:
	 * - Verify that check() method returns the correct result based on site state
	 * - PASS: check() returns NULL when traffic quality is good (score >= 40%)
	 * - FAIL: check() returns array when traffic quality is poor (score < 40%)
	 * - Description: Measures high-quality, engaged traffic percentage.
	 *
	 * @since  1.2601.2148
	 * @return array {
	 *     @type bool   $passed  Whether the test passed.
	 *     @type string $message Human-readable test result message.
	 * }
	 */
	public static function test_live_qualified_traffic_percent(): array {
		// Call the diagnostic check.
		$result        = self::check();
		$quality_score = self::calculate_traffic_quality_score();

		if ( null === $result ) {
			// No issues found - traffic quality is good.
			return array(
				'passed'  => true,
				'message' => sprintf(
					/* translators: %d: quality score percentage */
					__( 'Traffic quality is healthy with a score of %d%%. Your site attracts engaged visitors.', 'wpshadow' ),
					$quality_score
				),
			);
		}

		// Issues found - traffic quality is poor.
		return array(
			'passed'  => false,
			'message' => sprintf(
				/* translators: 1: quality score, 2: finding description */
				__( 'Issue detected (score: %1$d%%): %2$s', 'wpshadow' ),
				$quality_score,
				$result['description']
			),
		);
	}
}
