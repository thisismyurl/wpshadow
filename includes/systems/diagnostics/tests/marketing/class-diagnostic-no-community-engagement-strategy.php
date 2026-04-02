<?php
/**
 * No Community Engagement Strategy Diagnostic
 *
 * Checks if community engagement strategy is in place.
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
 * Community Engagement Strategy Diagnostic
 *
 * Engaged communities drive 3x higher customer lifetime value and provide
 * free word-of-mouth marketing worth 10x paid advertising.
 *
 * @since 1.6093.1200
 */
class Diagnostic_No_Community_Engagement_Strategy extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'no-community-engagement-strategy';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Community Engagement Strategy';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks if community engagement strategy is in place';

	/**
	 * Diagnostic family.
	 *
	 * @var string
	 */
	protected static $family = 'marketing';

	/**
	 * Run diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		if ( ! self::has_community_engagement() ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'No community engagement strategy detected. Building a community drives 3x higher customer lifetime value and provides word-of-mouth worth 10x paid ads. Create: 1) Forum/discussion space (Facebook Group, Discord, Circle), 2) Regular engagement (weekly AMAs, challenges, polls), 3) User-generated content (customer stories, photos), 4) Recognition program (featured members, expert badges), 5) Community guidelines (rules of engagement), 6) Community manager role (dedicated owner). Community creates moat competitors can\'t replicate.', 'wpshadow' ),
				'severity'    => 'medium',
				'threat_level' => 40,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/community-engagement-strategy',
				'details'     => array(
					'issue'               => __( 'No community engagement strategy detected', 'wpshadow' ),
					'recommendation'      => __( 'Build community space and engagement program to connect customers', 'wpshadow' ),
					'business_impact'     => __( 'Missing 3x customer lifetime value multiplier from engaged community', 'wpshadow' ),
					'community_types'     => self::get_community_types(),
					'engagement_tactics'  => self::get_engagement_tactics(),
				),
			);
		}

		return null;
	}

	/**
	 * Check if community engagement exists.
	 *
	 * @since 1.6093.1200
	 * @return bool True if community detected, false otherwise.
	 */
	private static function has_community_engagement() {
		// Check for community-related content
		$community_posts = self::count_posts_by_keywords(
			array(
				'community',
				'forum',
				'discussion',
				'user group',
				'member community',
				'facebook group',
				'discord',
			)
		);

		if ( $community_posts > 0 ) {
			return true;
		}

		// Check for community plugins
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$all_plugins = get_plugins();
		$community_keywords = array(
			'forum',
			'community',
			'bbpress',
			'buddypress',
			'peepso',
			'discord',
			'discussion',
		);

		foreach ( $all_plugins as $plugin_file => $plugin_data ) {
			$plugin_name = strtolower( $plugin_data['Name'] );
			foreach ( $community_keywords as $keyword ) {
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
	 * @since 1.6093.1200
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
	 * Get community platform types.
	 *
	 * @since 1.6093.1200
	 * @return array Community types with descriptions.
	 */
	private static function get_community_types() {
		return array(
			'facebook_group'  => __( 'Private Facebook Group (easy to start, high engagement)', 'wpshadow' ),
			'discord_server'  => __( 'Discord Server (real-time chat, gaming/tech audiences)', 'wpshadow' ),
			'circle'          => __( 'Circle.so (all-in-one community platform, professional)', 'wpshadow' ),
			'slack_community' => __( 'Slack Workspace (B2B, professional networking)', 'wpshadow' ),
			'forum'           => __( 'Forum (bbPress, Discourse - long-form discussions)', 'wpshadow' ),
			'linkedin_group'  => __( 'LinkedIn Group (B2B, professional development)', 'wpshadow' ),
		);
	}

	/**
	 * Get community engagement tactics.
	 *
	 * @since 1.6093.1200
	 * @return array Engagement tactics with descriptions.
	 */
	private static function get_engagement_tactics() {
		return array(
			'weekly_themes'     => __( 'Weekly discussion themes (e.g., "Win Wednesday", "Tip Tuesday")', 'wpshadow' ),
			'ama_sessions'      => __( 'Regular Ask Me Anything sessions with team/experts', 'wpshadow' ),
			'challenges'        => __( 'Community challenges with prizes (30-day challenge)', 'wpshadow' ),
			'user_spotlights'   => __( 'Feature member of the month, success stories', 'wpshadow' ),
			'exclusive_content' => __( 'Community-only content, early access, sneak peeks', 'wpshadow' ),
			'polls_surveys'     => __( 'Regular polls to involve community in decisions', 'wpshadow' ),
			'events'            => __( 'Virtual or in-person meetups, webinars, workshops', 'wpshadow' ),
			'badges_levels'     => __( 'Recognition system (newcomer, expert, ambassador)', 'wpshadow' ),
		);
	}
}
