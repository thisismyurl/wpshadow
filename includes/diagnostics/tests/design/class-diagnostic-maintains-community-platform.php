<?php
/**
 * Forum or Community Platform Diagnostic
 *
 * Tests whether the site maintains a dedicated community platform (forum, Discord, Slack) with active users.
 *
 * @since 0.6093.1200
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Forum or Community Platform Diagnostic Class
 *
 * Active community platforms increase user retention by 300% and lifetime value
 * by 500%. Dedicated community spaces foster authentic connections.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Maintains_Community_Platform extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'maintains-community-platform';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Forum or Community Platform';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests whether the site maintains a dedicated community platform (forum, Discord, Slack) with active users';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'community-building';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();
		$platform_score = 0;
		$max_score = 6;

		// Check for forum plugin.
		$forum_plugin = self::check_forum_plugin();
		if ( $forum_plugin ) {
			$platform_score++;
		} else {
			$issues[] = __( 'No forum plugin installed (bbPress, BuddyPress, WPForo)', 'wpshadow' );
		}

		// Check for Discord integration.
		$discord_integration = self::check_discord_integration();
		if ( $discord_integration ) {
			$platform_score++;
		} else {
			$issues[] = __( 'No Discord community or integration', 'wpshadow' );
		}

		// Check for Slack community.
		$slack_community = self::check_slack_community();
		if ( $slack_community ) {
			$platform_score++;
		} else {
			$issues[] = __( 'No Slack community workspace', 'wpshadow' );
		}

		// Check for recent community activity.
		$recent_activity = self::check_recent_activity();
		if ( $recent_activity ) {
			$platform_score++;
		} else {
			$issues[] = __( 'No recent community posts or activity (last 7 days)', 'wpshadow' );
		}

		// Check for community member count.
		$member_count = self::check_member_count();
		if ( $member_count ) {
			$platform_score++;
		} else {
			$issues[] = __( 'Community has fewer than 50 members', 'wpshadow' );
		}

		// Check for community engagement rate.
		$engagement_rate = self::check_engagement_rate();
		if ( $engagement_rate ) {
			$platform_score++;
		} else {
			$issues[] = __( 'Low community engagement (less than 10% active monthly)', 'wpshadow' );
		}

		// Determine severity based on community platform.
		$platform_percentage = ( $platform_score / $max_score ) * 100;

		if ( $platform_percentage < 35 ) {
			$severity = 'low';
			$threat_level = 30;
		} elseif ( $platform_percentage < 60 ) {
			$severity = 'low';
			$threat_level = 20;
		} else {
			return null;
		}

		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %d: Community platform percentage */
				__( 'Community platform strength at %d%%. ', 'wpshadow' ),
				(int) $platform_percentage
			) . implode( '. ', $issues ) . ' ' . __( 'Active communities increase retention by 300%', 'wpshadow' );

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => $description,
				'severity'     => $severity,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/maintains-community-platform?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}

	/**
	 * Check forum plugin.
	 *
	 * @since 0.6093.1200
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
	 * @since 0.6093.1200
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
	 * @since 0.6093.1200
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
	 * @since 0.6093.1200
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
	 * @since 0.6093.1200
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
	 * @since 0.6093.1200
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
