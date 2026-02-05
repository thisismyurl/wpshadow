<?php
/**
 * No Comments on Old Posts Treatment
 *
 * Detects lack of engagement on older content, indicating
 * missed opportunities to re-engage visitors.
 *
 * @package    WPShadow
 * @subpackage Treatments\Engagement
 * @since      1.6034.2217
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * No Comments on Old Posts Treatment Class
 *
 * Analyzes comment activity on older posts to identify stale
 * content that could benefit from refresh or promotion.
 *
 * **Why This Matters:**
 * - Comments signal ongoing relevance
 * - Fresh comments boost SEO (updated signals)
 * - Inactive old content loses rankings
 * - Engagement drives conversions
 * - Social proof builds trust
 *
 * **Strategies to Increase Comments:**
 * - Ask questions in conclusion
 * - Respond to every comment
 * - Promote old posts on social
 * - Update and re-share content
 * - Add discussion prompts
 * - Enable email notifications
 *
 * @since 1.6034.2217
 */
class Treatment_No_Comments_On_Old_Posts extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-comments-on-old-posts';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'No Comments on Old Posts';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Old content lacks engagement, missing opportunities to re-activate visitors';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'engagement';

	/**
	 * Run the treatment check
	 *
	 * @since  1.6034.2217
	 * @return array|null Finding array if old posts lack comments, null otherwise.
	 */
	public static function check() {
		// Check if comments are enabled
		if ( ! get_option( 'default_comment_status' ) ) {
			return null; // Comments disabled globally
		}

		// Get posts older than 6 months
		$six_months_ago = date( 'Y-m-d H:i:s', strtotime( '-6 months' ) );

		$old_posts = get_posts(
			array(
				'post_type'      => 'post',
				'post_status'    => 'publish',
				'posts_per_page' => 50,
				'date_query'     => array(
					array(
						'before' => $six_months_ago,
					),
				),
			)
		);

		if ( count( $old_posts ) < 10 ) {
			return null; // Need sufficient old content
		}

		$posts_without_recent_comments = array();
		$three_months_ago_timestamp = strtotime( '-3 months' );

		foreach ( $old_posts as $post ) {
			// Get recent comments (last 3 months)
			$recent_comments = get_comments(
				array(
					'post_id' => $post->ID,
					'status'  => 'approve',
					'date_query' => array(
						array(
							'after' => '3 months ago',
						),
					),
				)
			);

			if ( empty( $recent_comments ) ) {
				$posts_without_recent_comments[] = array(
					'id'             => $post->ID,
					'title'          => $post->post_title,
					'published'      => get_the_date( '', $post ),
					'total_comments' => get_comments_number( $post->ID ),
				);
			}
		}

		if ( empty( $posts_without_recent_comments ) ) {
			return null; // Old posts are still getting comments
		}

		$percentage = ( count( $posts_without_recent_comments ) / count( $old_posts ) ) * 100;

		// Only flag if > 70% of old posts lack recent comments
		if ( $percentage < 70 ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: 1: number of posts, 2: percentage */
				__( '%1$d old post(s) (%2$d%%) have no recent comments. Update and promote older content to re-engage visitors.', 'wpshadow' ),
				count( $posts_without_recent_comments ),
				round( $percentage )
			),
			'severity'     => 'low',
			'threat_level' => 40,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/content-engagement',
			'details'      => array(
				'posts_without_recent_comments' => count( $posts_without_recent_comments ),
				'percentage'                    => round( $percentage, 1 ),
				'sample_posts'                  => array_slice( $posts_without_recent_comments, 0, 10 ),
				'strategies'                    => array(
					'Update content with new information',
					'Re-share on social media',
					'Add discussion questions',
					'Respond to old comments',
					'Link from newer posts',
				),
			),
		);
	}
}
