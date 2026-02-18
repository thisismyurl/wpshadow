<?php
/**
 * Comment Display and Threading Configuration
 *
 * Validates comment display settings and threaded reply functionality.
 *
 * @since   1.6030.2148
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Comment_Threading Class
 *
 * Checks comment display settings and threading configuration.
 *
 * @since 1.6030.2148
 */
class Diagnostic_Comment_Threading extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'comment-threading';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Comment Threading and Display';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates comment display settings and threaded replies';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'comments';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6030.2148
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Get comment settings
		$thread_comments = get_option( 'thread_comments', 1 );
		$thread_comments_depth = intval( get_option( 'thread_comments_depth', 5 ) );
		$comments_per_page = intval( get_option( 'comments_per_page', 50 ) );
		$default_comments_page = get_option( 'default_comments_page', 'newest' );
		$page_comments = get_option( 'page_comments', 0 );
		$comment_moderation = get_option( 'comment_moderation', 0 );

		// Pattern 1: Threading disabled (reduces user engagement)
		if ( ! $thread_comments ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Comment threading (replies) is disabled', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 25,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/comment-threading',
				'details'      => array(
					'issue' => 'threading_disabled',
					'message' => __( 'Nested replies to comments are not enabled', 'wpshadow' ),
					'engagement_impact' => array(
						'Sites with threading: 45% higher comment engagement',
						'Users prefer replying to specific comments',
						'Threading creates conversations (not flat threads)',
						'Replies to comments increase user interaction by 40%+',
					),
					'user_experience' => __( 'Flat comment structure feels basic and less interactive', 'wpshadow' ),
					'conversation_quality' => __( 'Threading creates focused discussions vs. scattered comments', 'wpshadow' ),
					'why_important' => array(
						'Increases perceived community engagement',
						'Improves comment quality (focused replies)',
						'Makes it easier to follow conversations',
						'Encourages more commenting (social feature)',
					),
					'themes_affected' => __( 'Some themes don\'t display nested comments properly', 'wpshadow' ),
					'testing_needed' => __( 'After enabling: Test that child comments display indented under parent', 'wpshadow' ),
					'recommendation' => __( 'Enable nested comments in Settings > Discussion', 'wpshadow' ),
				),
			);
		}

		// Pattern 2: Threading depth too shallow (only 1-2 levels)
		if ( $thread_comments && $thread_comments_depth < 3 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Comment threading depth is too shallow', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 20,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/comment-threading',
				'details'      => array(
					'issue' => 'threading_depth_shallow',
					'current_depth' => $thread_comments_depth,
					'message' => sprintf(
						/* translators: %d: depth level */
						__( 'Comment threading depth set to %d (very limited)', 'wpshadow' ),
						$thread_comments_depth
					),
					'depth_levels' => array(
						'1 level' => 'Flat, no nesting at all',
						'2 levels' => 'Basic threading (limited)',
						'3-5 levels' => 'Ideal for most discussions',
						'10+ levels' => 'Deep but can get confusing',
					),
					'recommended_depth' => '5 levels (balances nesting and readability)',
					'conversation_flow' => __( 'More levels = deeper conversations possible', 'wpshadow' ),
					'ui_impact' => __( 'Deep threading can squeeze comments sideways (narrow on mobile)', 'wpshadow' ),
					'best_practice' => __( 'Set to 5 levels (standard depth for most sites)', 'wpshadow' ),
					'recommendation' => __( 'Increase threading depth to 3-5 levels in Settings > Discussion', 'wpshadow' ),
				),
			);
		}

		// Pattern 3: Too many comments per page (performance issue)
		if ( $page_comments && $comments_per_page > 100 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Comments per page setting too high (performance risk)', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 45,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/comment-threading',
				'details'      => array(
					'issue' => 'high_comments_per_page',
					'current_setting' => $comments_per_page,
					'message' => sprintf(
						/* translators: %d: comments per page */
						__( '%d comments per page loads too much content at once', 'wpshadow' ),
						$comments_per_page
					),
					'performance_impact' => array(
						'Each additional 50 comments adds ~0.5-2 seconds load time',
						'Large comment sections slow down page rendering',
						'Mobile devices especially affected',
						'Increased server memory usage',
					),
					'page_load_estimate' => sprintf(
						/* translators: %d: setting value */
						__( '%d comments = likely 3-8 second additional load time', 'wpshadow' ),
						$comments_per_page
					),
					'recommended_value' => '25-50 comments per page (optimal balance)',
					'seo_impact' => __( 'Slow pages rank lower in Google', 'wpshadow' ),
					'user_experience' => __( 'Users don\'t read all 100+ comments anyway', 'wpshadow' ),
					'recommendation' => __( 'Reduce to 25-50 comments/page for better performance', 'wpshadow' ),
				),
			);
		}

		// Pattern 4: Comments page not set to newest first
		if ( 'oldest' === $default_comments_page ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Comments page set to show oldest first (outdated UX)', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 20,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/comment-threading',
				'details'      => array(
					'issue' => 'oldest_comments_first',
					'message' => __( 'Comment page defaults to oldest comments (not newest)', 'wpshadow' ),
					'user_experience' => array(
						'Users want to see newest discussion first',
						'Oldest first makes new comments hard to find',
						'Feels like reading an outdated thread',
						'Modern expectation: newest content first',
					),
					'navigation_friction' => __( 'Users must navigate to last page to see fresh comments', 'wpshadow' ),
					'engagement_loss' => __( 'New commenters don\'t see their comment on first page', 'wpshadow' ),
					'standard_practice' => __( 'Facebook, YouTube, Reddit all show newest first', 'wpshadow' ),
					'recommendation' => __( 'Change to "newest comments first" in Settings > Discussion', 'wpshadow' ),
				),
			);
		}

		// Pattern 5: Page comments enabled but total comments low
		if ( $page_comments ) {
			global $wpdb;
			$approved_comments = $wpdb->get_var(
				"SELECT COUNT(*) FROM {$wpdb->comments} WHERE comment_approved = 1"
			);

			if ( $approved_comments < 50 ) {
				return array(
					'id'           => self::$slug,
					'title'        => self::$title,
					'description'  => __( 'Comment pagination enabled but not needed yet', 'wpshadow' ),
					'severity'     => 'low',
					'threat_level' => 15,
					'auto_fixable' => false,
					'kb_link'      => 'https://wpshadow.com/kb/comment-threading',
					'details'      => array(
						'issue' => 'premature_pagination',
						'approved_comments' => intval( $approved_comments ),
						'message' => sprintf(
							/* translators: %d: comment count */
							__( 'Only %d approved comments total (pagination not needed yet)', 'wpshadow' ),
							intval( $approved_comments )
						),
						'when_pagination_helps' => '100+ comments per post (creates navigation burden)',
						'premature_pagination_issues' => array(
							'Creates unnecessary page breaks',
							'Reduces comments visible per post',
							'Adds navigation friction',
							'Distributes comments across multiple pages',
						),
						'recommendation_when_to_enable' => __( 'Enable pagination when reaching 100+ comments on single posts', 'wpshadow' ),
						'current_status' => __( 'Site is still building comment volume', 'wpshadow' ),
						'future_consideration' => __( 'Can enable pagination later as site grows', 'wpshadow' ),
					),
				);
			}
		}

		// Pattern 6: Comment moderation settings too strict
		if ( $comment_moderation ) {
			$moderation_keys = get_option( 'moderation_keys', '' );
			$blacklist_keys = get_option( 'blacklist_keys', '' );

			if ( strlen( $moderation_keys ) > 500 || strlen( $blacklist_keys ) > 500 ) {
				return array(
					'id'           => self::$slug,
					'title'        => self::$title,
					'description'  => __( 'Comment moderation word lists are very large', 'wpshadow' ),
					'severity'     => 'low',
					'threat_level' => 25,
					'auto_fixable' => false,
					'kb_link'      => 'https://wpshadow.com/kb/comment-threading',
					'details'      => array(
						'issue' => 'excessive_moderation_words',
						'moderation_words_count' => strlen( $moderation_keys ) > 0 ? 'yes' : 'no',
						'blacklist_words_count' => strlen( $blacklist_keys ) > 0 ? 'yes' : 'no',
						'message' => __( 'Very large moderation/blacklist word lists', 'wpshadow' ),
						'risks_of_large_lists' => array(
							'Higher false positive rate (legitimate comments marked spam)',
							'Database query performance impact',
							'Too many filters can block good comments',
							'Maintenance burden (managing word lists)',
						),
						'false_positive_rate' => __( 'Large word lists can block 5-15% of legitimate comments', 'wpshadow' ),
						'better_approach' => 'Use AI-powered anti-spam (Akismet) instead of word lists',
						'modern_alternative' => __( 'Machine learning catches spam better than keyword matching', 'wpshadow' ),
						'recommendation' => __( 'Consider reducing word list, use anti-spam plugin instead', 'wpshadow' ),
					),
				);
			}
		}

		return null; // No issues found
	}
}
