<?php
/**
 * Forum or Community Platform Treatment
 *
 * Tests whether the site maintains a dedicated community platform (forum, Discord, Slack) with active users.
 *
 * @since   1.6034.0435
 * @package WPShadow\Treatments
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Forum or Community Platform Treatment Class
 *
 * Active community platforms increase user retention by 300% and lifetime value
 * by 500%. Dedicated community spaces foster authentic connections.
 *
 * @since 1.6034.0435
 */
class Treatment_Maintains_Community_Platform extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'maintains-community-platform';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Forum or Community Platform';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Tests whether the site maintains a dedicated community platform (forum, Discord, Slack) with active users';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'community-building';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6034.0435
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Maintains_Community_Platform' );
	}

	/**
	 * Check forum plugin.
	 *
	 * @since  1.6034.0435
	 * @return bool True if exists, false otherwise.
	 */
	private static function check_forum_plugin() {
		// Check popular forum plugins.
		$forum_plugins = array(
			'bbpress/bbpress.php',
			'buddypress/bp-loader.php',
			'wpforo/wpforo.php',
			'asgaros-forum/asgaros-forum.php',
		);

		foreach ( $forum_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check Discord integration.
	 *
	 * @since  1.6034.0435
	 * @return bool True if integrated, false otherwise.
	 */
	private static function check_discord_integration() {
		// Check for Discord links.
		$query = new \WP_Query(
			array(
				's'              => 'discord.gg discord.com/invite',
				'post_type'      => 'any',
				'posts_per_page' => 1,
				'post_status'    => 'publish',
			)
		);

		return $query->have_posts();
	}

	/**
	 * Check Slack community.
	 *
	 * @since  1.6034.0435
	 * @return bool True if exists, false otherwise.
	 */
	private static function check_slack_community() {
		// Check for Slack references.
		$query = new \WP_Query(
			array(
				's'              => 'slack.com join slack community slack',
				'post_type'      => 'any',
				'posts_per_page' => 1,
				'post_status'    => 'publish',
			)
		);

		return $query->have_posts();
	}

	/**
	 * Check recent activity.
	 *
	 * @since  1.6034.0435
	 * @return bool True if active, false otherwise.
	 */
	private static function check_recent_activity() {
		// Check for recent forum/community posts.
		$post_types = array( 'topic', 'reply', 'forum', 'discussion' );

		foreach ( $post_types as $post_type ) {
			if ( post_type_exists( $post_type ) ) {
				$query = new \WP_Query(
					array(
						'post_type'      => $post_type,
						'posts_per_page' => 1,
						'post_status'    => 'publish',
						'date_query'     => array(
							array(
								'after' => '7 days ago',
							),
						),
					)
				);

				if ( $query->have_posts() ) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Check member count.
	 *
	 * @since  1.6034.0435
	 * @return bool True if adequate, false otherwise.
	 */
	private static function check_member_count() {
		// Count registered users (basic proxy for community size).
		$user_count = count_users();
		return ( $user_count['total_users'] >= 50 );
	}

	/**
	 * Check engagement rate.
	 *
	 * @since  1.6034.0435
	 * @return bool True if engaged, false otherwise.
	 */
	private static function check_engagement_rate() {
		// Check for active community content.
		$query = new \WP_Query(
			array(
				'post_type'      => 'any',
				'posts_per_page' => 10,
				'post_status'    => 'publish',
				'date_query'     => array(
					array(
						'after' => '1 month ago',
					),
				),
			)
		);

		// If 10+ posts in a month, community is moderately engaged.
		return ( $query->found_posts >= 10 );
	}
}
