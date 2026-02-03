<?php
/**
 * Comment Engagement and Community Health
 *
 * Validates comment section engagement metrics and community activity.
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
 * Diagnostic_Comment_Engagement Class
 *
 * Checks comment section engagement and community health indicators.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Comment_Engagement extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'comment-engagement';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Comment Engagement and Community Health';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Analyzes comment engagement metrics and community activity patterns';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'comments';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		// Get engagement metrics
		$total_posts = wp_count_posts()->publish;
		$total_comments = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->comments} WHERE comment_approved = 1"
		);
		$avg_comments_per_post = $total_posts > 0 ? intval( $total_comments / $total_posts ) : 0;

		// Get comment activity
		$recent_comments = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->comments} WHERE comment_approved = 1 AND comment_date > DATE_SUB(NOW(), INTERVAL 30 DAY)"
		);

		// Get oldest comment
		$oldest_comment_time = $wpdb->get_var(
			"SELECT MIN(comment_date) FROM {$wpdb->comments} WHERE comment_approved = 1"
		);

		// Pattern 1: Very low comment-to-post ratio (declining engagement)
		if ( $total_posts > 10 && $avg_comments_per_post < 0.5 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Comment-to-post ratio very low (minimal engagement)', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 20,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/comment-engagement',
				'details'      => array(
					'issue' => 'low_comment_ratio',
					'total_posts' => intval( $total_posts ),
					'total_comments' => intval( $total_comments ),
					'ratio' => $avg_comments_per_post,
					'message' => sprintf(
						/* translators: %d: ratio */
						__( 'Average %d comments per post (below healthy threshold)', 'wpshadow' ),
						$avg_comments_per_post
					),
					'engagement_benchmarks' => array(
						'< 0.5' => 'Minimal engagement (concerning)',
						'0.5 - 2' => 'Low but growing (needs work)',
						'2 - 5' => 'Healthy (good community)',
						'5+' => 'Highly engaged (excellent)',
					),
					'causes_of_low_engagement' => array(
						'Content not promoting discussion',
						'Barriers to commenting (too many fields, slow form)',
						'Comments disabled on old posts',
						'Moderation delays (users don\'t see comments published)',
						'No responding to comments (users feel ignored)',
					),
					'strategies_to_improve' => array(
						'Write posts with discussion-starter questions',
						'Simplify comment form (remove unnecessary fields)',
						'Respond to comments quickly (shows you care)',
						'Highlight best comments (encourage participation)',
						'Enable comments on all posts (invite engagement)',
					),
					'community_value' => __( 'Comments are SEO signals + user engagement indicators', 'wpshadow' ),
					'long_term_impact' => __( 'Low engagement = lower search rankings over time', 'wpshadow' ),
					'recommendation' => __( 'Review content strategy and comment form barriers, encourage reader dialogue', 'wpshadow' ),
				),
			);
		}

		// Pattern 2: No recent comment activity (dead community)
		if ( $total_comments > 0 && $recent_comments < 1 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'No comment activity in past 30 days (community going dormant)', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 25,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/comment-engagement',
				'details'      => array(
					'issue' => 'no_recent_activity',
					'last_activity_days_ago' => intval( $recent_comments ),
					'message' => __( 'No comments received in the last 30 days', 'wpshadow' ),
					'community_signals' => array(
						'No engagement = algorithm deprioritizes site',
						'Google favors active, engaged sites',
						'Readers sense dead community (less likely to return)',
						'Content stagnation signals low quality',
					),
					'revival_strategies' => array(
						'Publish fresh content with questions',
						'Share posts on social media (drive traffic)',
						'Reply to old comments (restart conversations)',
						'Engage in other communities (build relationships)',
						'Lower barriers to commenting',
					),
					'content_calendar' => 'Regular posting (1-2x per week) maintains engagement',
					'social_amplification' => 'Share blog posts to Facebook/Twitter (drives commenters)',
					'engagement_loop' => array(
						'Publish quality content',
						'Promote on social media',
						'Respond to comments quickly',
						'Highlight best commenters',
						'Encourage more participation',
					),
					'seo_impact' => __( 'Active communities rank higher; dormant sites lose ranking position', 'wpshadow' ),
					'recommendation' => __( 'Increase content frequency and actively engage with existing community', 'wpshadow' ),
				),
			);
		}

		// Pattern 3: Comments disabled on individual posts (missing participation opportunities)
		$posts_with_comments_disabled = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts} WHERE comment_status = 'closed' AND post_type = 'post' AND post_status = 'publish'"
		);

		if ( $total_posts > 10 && $posts_with_comments_disabled > intval( $total_posts * 0.3 ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Comments disabled on many posts (blocking engagement)', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/comment-engagement',
				'details'      => array(
					'issue' => 'comments_disabled_many_posts',
					'posts_disabled' => intval( $posts_with_comments_disabled ),
					'percentage' => intval( ( $posts_with_comments_disabled / $total_posts ) * 100 ),
					'message' => sprintf(
						/* translators: %d: percentage */
						__( '%d%% of published posts have comments disabled', 'wpshadow' ),
						intval( ( $posts_with_comments_disabled / $total_posts ) * 100 )
					),
					'missed_opportunities' => __( 'Readers want to discuss content but cannot', 'wpshadow' ),
					'reasons_comments_disabled' => array(
						'Post too old (auto-close after X days)',
						'Post controversial (admin prefers no discussion)',
						'Post outdated (but still valuable)',
						'Default setting is closed (forgot to enable)',
					),
					'problems_with_closed_comments' => array(
						'Readers bypass site (go to social media to discuss)',
						'Discussions happen elsewhere (no SEO benefit)',
						'Perceived as controlling/closed community',
						'Losing valuable user feedback',
						'Lower engagement signals',
					),
					'best_practice' => 'Keep comments open on all posts (permanent record of discussion)',
					'moderation_alternative' => 'Don\'t close comments; moderate heavily instead (spam filter + approval)',
					'reopening_old_comments' => 'Consider reopening comments on evergreen posts (timeless content)',
					'recommendation' => __( 'Enable comments on all posts; rely on moderation instead of closing', 'wpshadow' ),
				),
			);
		}

		// Pattern 4: High spam-to-legitimate ratio (moderation not working)
		$spam_comments = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->comments} WHERE comment_approved = 'spam'"
		);

		if ( $total_comments > 0 ) {
			$spam_ratio = $spam_comments / ( $total_comments + $spam_comments );

			if ( $spam_ratio > 0.2 ) { // More than 20% spam
				return array(
					'id'           => self::$slug,
					'title'        => self::$title,
					'description'  => __( 'High spam ratio (moderation system not effective)', 'wpshadow' ),
					'severity'     => 'medium',
					'threat_level' => 50,
					'auto_fixable' => false,
					'kb_link'      => 'https://wpshadow.com/kb/comment-engagement',
					'details'      => array(
						'issue' => 'high_spam_ratio',
						'spam_count' => intval( $spam_comments ),
						'legitimate_count' => intval( $total_comments ),
						'spam_percentage' => intval( $spam_ratio * 100 ),
						'message' => sprintf(
							/* translators: %d: percentage */
							__( '%d%% of submissions are spam (system not filtering effectively)', 'wpshadow' ),
							intval( $spam_ratio * 100 )
						),
						'health_indicators' => array(
							'< 10% spam' => 'Normal, system working well',
							'10-20% spam' => 'Acceptable, could be better',
							'20-50% spam' => 'System not working (concerning)',
							'> 50% spam' => 'Critical failure (most submissions are spam)',
						),
						'why_moderation_failing' => array(
							'Spam plugin not configured properly',
							'Outdated spam filter (new spam types not recognized)',
							'No CAPTCHA or bot protection',
							'Moderation settings too lenient',
							'Form vulnerability (no verification)',
						),
						'improvement_steps' => array(
							'1. Verify spam filter is active (Akismet)',
							'2. Check filter accuracy (too aggressive or too lenient?)',
							'3. Add CAPTCHA to comment form',
							'4. Require name + email to reduce bots',
							'5. Enable comment moderation for first-timers',
						),
						'filter_calibration' => 'Akismet learns from your markings (mark spam/not-spam)',
						'seo_damage' => __( 'Spam links can harm SEO (bad backlink signals)', 'wpshadow' ),
						'recommendation' => __( 'Improve spam detection and blocking mechanisms immediately', 'wpshadow' ),
					),
				);
			}
		}

		// Pattern 5: Very large comment volume with no management system
		if ( $total_comments > 500 ) {
			$comment_moderation_plugin = false;

			$plugins = array(
				'comment-moderation',
				'advanced-comment-system',
				'custom-comment-form',
			);

			foreach ( $plugins as $plugin ) {
				if ( is_plugin_active( $plugin . '/' . $plugin . '.php' ) ) {
					$comment_moderation_plugin = true;
					break;
				}
			}

			if ( ! $comment_moderation_plugin ) {
				return array(
					'id'           => self::$slug,
					'title'        => self::$title,
					'description'  => __( 'Large comment volume without organized moderation system', 'wpshadow' ),
					'severity'     => 'low',
					'threat_level' => 35,
					'auto_fixable' => false,
					'kb_link'      => 'https://wpshadow.com/kb/comment-engagement',
					'details'      => array(
						'issue' => 'large_volume_no_system',
						'total_comments' => intval( $total_comments ),
						'message' => sprintf(
							/* translators: %d: comment count */
							__( '%d comments accumulated without formal management system', 'wpshadow' ),
							intval( $total_comments )
						),
						'management_challenges' => array(
							'Hard to track which comments need response',
							'Easy to miss important discussions',
							'Difficult to identify spam patterns',
							'Admin overwhelmed by volume',
						),
						'organizational_solutions' => array(
							'Comment dashboard plugin (view/respond centrally)',
							'Comment threading/grouping (organize by post)',
							'Automatic response system (thankyou messages)',
							'Comment notification emails (stay on top)',
						),
						'best_practices_for_scale' => array(
							'Set email notifications for all comments',
							'Create rapid response team (for active posts)',
							'Batch moderation (review comments weekly)',
							'Community managers (trusted users who can pre-moderate)',
						),
						'community_building' => __( 'Active moderation turns comments into real community (not just spam receptacle)', 'wpshadow' ),
						'brand_voice' => __( 'Responding to comments shows brand cares (builds loyalty)', 'wpshadow' ),
						'recommendation' => __( 'Implement comment management system to handle volume systematically', 'wpshadow' ),
					),
				);
			}
		}

		// Pattern 6: Highly engaged but no system to amplify best comments
		if ( $avg_comments_per_post > 5 && ! self::has_featured_comments_system() ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'High engagement but no system to highlight best comments', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 15,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/comment-engagement',
				'details'      => array(
					'issue' => 'no_featured_comments',
					'message' => __( 'Site has active commenting but doesn\'t highlight quality comments', 'wpshadow' ),
					'missed_opportunity' => __( 'Best insights get buried under 100+ comments', 'wpshadow' ),
					'reader_experience' => array(
						'Reader sees: thousands of comments, can\'t find gems',
						'Takes hours to find the actually useful discussion',
						'Users skip comment section (too overwhelming)',
						'valuable insights lost in noise',
					),
					'featured_comments_benefits' => array(
						'Elevate best insights (readers see them first)',
						'Reward thoughtful commenters (encourage more)',
						'Set community standard (quality over quantity)',
						'Reduce time to find valuable discussion',
					),
					'implementation_options' => array(
						'Manual: Mark comments as "Featured" in admin',
						'Semi-auto: Pin top-rated comments',
						'Auto: Show comments with most replies/likes',
						'Expert-moderated: Admin selects gems',
					),
					'engagement_loop' => 'Feature great comments → encourage more quality → better discussions',
					'community_standards' => __( 'Featuring comments sets expectations for quality discussion', 'wpshadow' ),
					'recommendation' => __( 'Consider implementing featured/highlighted comment system to reward quality contributions', 'wpshadow' ),
				),
			);
		}

		return null; // No issues found
	}

	/**
	 * Check for featured comments system.
	 *
	 * @since  1.2601.2148
	 * @return bool True if system exists.
	 */
	private static function has_featured_comments_system() {
		// Check for comment rating plugins
		$plugins = array(
			'wp-comment-form-customizer',
			'ultimate-comment-system',
		);

		foreach ( $plugins as $plugin ) {
			if ( is_plugin_active( $plugin . '/' . $plugin . '.php' ) ) {
				return true;
			}
		}

		// Check for featured comments option
		if ( get_option( 'featured_comments_enabled', false ) ) {
			return true;
		}

		return false;
	}
}
