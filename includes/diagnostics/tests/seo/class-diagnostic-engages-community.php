<?php
/**
 * Diagnostic: Community Participation
 *
 * Tests if site actively participates in and builds community.
 * Community engagement builds brand loyalty and word-of-mouth marketing.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.7034.1430
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Community Participation Diagnostic Class
 *
 * Checks if site has community features and actively engages
 * with users beyond basic content publishing.
 *
 * Detection methods:
 * - Community forum presence
 * - Membership features
 * - User profiles and badges
 * - Community events or activities
 *
 * @since 1.7034.1430
 */
class Diagnostic_Engages_Community extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'engages-community';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Community Participation';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests if site actively participates in and builds community';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'user-engagement';

	/**
	 * Run the diagnostic check.
	 *
	 * Scoring system (5 points):
	 * - 1 point: Forum or community platform active
	 * - 1 point: Membership/user profiles enabled
	 * - 1 point: User-generated content features
	 * - 1 point: Community events or challenges
	 * - 1 point: Active community participation (recent activity)
	 *
	 * @since  1.7034.1430
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$score     = 0;
		$max_score = 5;
		$details   = array();

		// Check for forum plugins.
		$forum_plugins = array(
			'bbpress/bbpress.php'                        => 'bbPress',
			'buddypress/bp-loader.php'                   => 'BuddyPress',
			'wpforo/wpforo.php'                          => 'wpForo',
			'asgaros-forum/asgaros-forum.php'            => 'Asgaros Forum',
		);

		foreach ( $forum_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$score++;
				$details['forum_platform'] = $name;
				break;
			}
		}

		// Check for membership plugins.
		$membership_plugins = array(
			'paid-memberships-pro/paid-memberships-pro.php' => 'Paid Memberships Pro',
			'memberpress/memberpress.php'                => 'MemberPress',
			'restrict-content-pro/restrict-content-pro.php' => 'Restrict Content Pro',
			'ultimate-member/ultimate-member.php'        => 'Ultimate Member',
		);

		foreach ( $membership_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$score++;
				$details['membership_system'] = $name;
				break;
			}
		}

		// Check for user profile/social features.
		if ( is_plugin_active( 'buddypress/bp-loader.php' ) ) {
			// BuddyPress provides comprehensive community features.
			$score++;
			$details['social_features'] = 'BuddyPress';
		}

		// Check for user-generated content.
		$ugc_plugins = array(
			'user-submitted-posts/user-submitted-posts.php' => 'User Submitted Posts',
			'frontend-post/frontend-post.php'            => 'Frontend Post',
			'wp-user-frontend/wpuf.php'                  => 'WP User Frontend',
		);

		foreach ( $ugc_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$score++;
				$details['user_generated_content'] = $name;
				break;
			}
		}

		// Check for recent community activity.
		$community_active = false;
		
		// Check for recent comments.
		$recent_comments = get_comments(
			array(
				'number'     => 1,
				'status'     => 'approve',
				'date_query' => array(
					array(
						'after' => '7 days ago',
					),
				),
			)
		);

		if ( ! empty( $recent_comments ) ) {
			$community_active = true;
		}

		// Check for recent forum topics (if bbPress).
		if ( is_plugin_active( 'bbpress/bbpress.php' ) ) {
			$recent_topics = get_posts(
				array(
					'post_type'      => 'topic',
					'posts_per_page' => 1,
					'date_query'     => array(
						array(
							'after' => '7 days ago',
						),
					),
				)
			);

			if ( ! empty( $recent_topics ) ) {
				$community_active = true;
			}
		}

		if ( $community_active ) {
			$score++;
			$details['recent_activity'] = true;
		}

		// Calculate percentage score.
		$percentage = ( $score / $max_score ) * 100;

		// Pass if score is 60% or higher.
		if ( $percentage >= 60 ) {
			return null;
		}

		// Build finding.
		$severity     = $percentage < 30 ? 'low' : 'info';
		$threat_level = (int) ( 40 - $percentage );

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %d: percentage score */
				__( 'Community engagement score: %d%%. Building community creates loyal advocates for your brand.', 'wpshadow' ),
				(int) $percentage
			),
			'severity'     => $severity,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/community-building',
			'details'      => $details,
			'why_matters'  => self::get_why_matters(),
		);
	}

	/**
	 * Get the "Why This Matters" educational content.
	 *
	 * @since  1.7034.1430
	 * @return string Explanation of why this diagnostic matters.
	 */
	private static function get_why_matters() {
		return __(
			'Community turns casual visitors into brand advocates. When users can connect with each other, share experiences, and contribute content, they become invested in your success. Communities create user-generated content (reducing your workload), provide peer-to-peer support (reducing support tickets), and generate word-of-mouth marketing. A strong community becomes your competitive moat.',
			'wpshadow'
		);
	}
}
