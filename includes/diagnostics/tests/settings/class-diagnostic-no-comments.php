<?php
/**
 * Diagnostic: No Comments on Old Posts
 *
 * Detects sites with zero comments on 50+ posts, suggesting low engagement
 * or disabled comments. Comments signal content quality to search engines.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * No Comments Diagnostic Class
 *
 * Checks for posts with zero comments, indicating low engagement.
 *
 * Detection methods:
 * - Comment count across posts
 * - Comment status (enabled/disabled)
 * - Posts older than 3 months with zero comments
 *
 * @since 1.6093.1200
 */
class Diagnostic_No_Comments extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-comments';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'No Comments on Old Posts';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Zero comments on 50+ posts suggests low engagement or disabled comments';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'user-engagement';

	/**
	 * Run the diagnostic check.
	 *
	 * Scoring system (4 points):
	 * - 1 point: Comments enabled
	 * - 1 point: <30% posts have zero comments
	 * - 1 point: <50% posts have zero comments
	 * - 1 point: Comment plugin active
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$score     = 0;
		$max_score = 4;

		// Check if comments are enabled globally.
		$comments_enabled = 'open' === get_option( 'default_comment_status', 'open' );
		if ( $comments_enabled ) {
			$score++;
		}

		// Check for comment enhancement plugins.
		$comment_plugins = array(
			'disqus-comment-system/disqus.php'   => 'Disqus',
			'jetpack/jetpack.php'                => 'Jetpack Comments',
			'wpDiscuz/class.WpdiscuzCore.php'    => 'wpDiscuz',
			'thrive-comments/thrive-comments.php' => 'Thrive Comments',
		);

		foreach ( $comment_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$score++;
				break;
			}
		}

		// Get posts older than 3 months.
		$posts = get_posts(
			array(
				'post_type'      => 'post',
				'posts_per_page' => 100,
				'date_query'     => array(
					array(
						'before' => '3 months ago',
					),
				),
			)
		);

		if ( empty( $posts ) ) {
			// No old posts to check.
			return null;
		}

		$posts_with_zero_comments = 0;
		$zero_comment_posts       = array();

		foreach ( $posts as $post ) {
			$comment_count = get_comments_number( $post->ID );
			if ( 0 === $comment_count ) {
				$posts_with_zero_comments++;
				if ( count( $zero_comment_posts ) < 10 ) {
					$zero_comment_posts[] = array(
						'post_id' => $post->ID,
						'title'   => $post->post_title,
						'date'    => $post->post_date,
						'url'     => get_permalink( $post->ID ),
					);
				}
			}
		}

		$zero_comment_percentage = ( $posts_with_zero_comments / count( $posts ) ) * 100;

		// Scoring based on percentage.
		if ( $zero_comment_percentage < 30 ) {
			$score += 2;
		} elseif ( $zero_comment_percentage < 50 ) {
			$score++;
		}

		// Pass if score is high.
		if ( $score >= ( $max_score * 0.7 ) ) {
			return null;
		}

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: 1: percentage of posts with zero comments, 2: number of posts checked */
				__( '%1$d%% of your posts (%2$d/%3$d) have zero comments. Comments signal content quality and user engagement to search engines. Zero comments might indicate: disabled comments, spam-heavy moderation, no engagement hooks, uninteresting content, or technical issues. Active discussions boost dwell time, generate fresh content, and build community. Sites with active comments rank 15-20%% higher on average.', 'wpshadow' ),
				round( $zero_comment_percentage ),
				$posts_with_zero_comments,
				count( $posts )
			),
			'severity'    => 'medium',
			'threat_level' => 35,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/no-comments',
			'zero_comment_posts' => $zero_comment_posts,
			'stats'       => array(
				'total_posts'         => count( $posts ),
				'zero_comments'       => $posts_with_zero_comments,
				'percentage'          => round( $zero_comment_percentage, 1 ),
				'comments_enabled'    => $comments_enabled,
			),
			'recommendation' => __( 'Enable comments globally. Add engagement hooks (questions) at end of posts. Respond to comments promptly. Consider comment plugins (Disqus, wpDiscuz) for better UX. Moderate actively but don\'t over-filter.', 'wpshadow' ),
		);
	}
}
