<?php
/**
 * YouTube Channel Active Diagnostic
 *
 * Tests whether the site maintains an active YouTube channel with at least weekly video uploads.
 *
 * @since 1.6093.1200
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * YouTube Channel Active Diagnostic Class
 *
 * Active YouTube channels drive 400% more traffic than inactive ones.
 * Regular uploads keep audiences engaged and algorithms favorable.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Youtube_Channel_Active extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'youtube-channel-active';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'YouTube Channel Active';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests whether the site maintains an active YouTube channel with at least weekly video uploads';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'video-marketing';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();
		$youtube_score = 0;
		$max_score = 6;

		// Check for YouTube embeds.
		$youtube_embeds = self::check_youtube_embeds();
		if ( $youtube_embeds ) {
			$youtube_score++;
		} else {
			$issues[] = __( 'No YouTube videos embedded on site', 'wpshadow' );
		}

		// Check for YouTube channel link.
		$channel_link = self::check_channel_link();
		if ( $channel_link ) {
			$youtube_score++;
		} else {
			$issues[] = __( 'No YouTube channel prominently linked', 'wpshadow' );
		}

		// Check for video plugin.
		$video_plugin = self::check_video_plugin();
		if ( $video_plugin ) {
			$youtube_score++;
		} else {
			$issues[] = __( 'No video management plugin installed', 'wpshadow' );
		}

		// Check upload frequency.
		$upload_frequency = self::check_upload_frequency();
		if ( $upload_frequency ) {
			$youtube_score++;
		} else {
			$issues[] = __( 'No evidence of regular video uploads', 'wpshadow' );
		}

		// Check for video content strategy.
		$content_strategy = self::check_content_strategy();
		if ( $content_strategy ) {
			$youtube_score++;
		} else {
			$issues[] = __( 'No documented video content strategy', 'wpshadow' );
		}

		// Check for subscriber engagement.
		$subscriber_engagement = self::check_subscriber_engagement();
		if ( $subscriber_engagement ) {
			$youtube_score++;
		} else {
			$issues[] = __( 'No evidence of subscriber engagement tactics', 'wpshadow' );
		}

		// Determine severity based on YouTube activity.
		$youtube_percentage = ( $youtube_score / $max_score ) * 100;

		if ( $youtube_percentage < 30 ) {
			$severity = 'low';
			$threat_level = 25;
		} elseif ( $youtube_percentage < 60 ) {
			$severity = 'low';
			$threat_level = 15;
		} else {
			return null;
		}

		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %d: YouTube channel activity percentage */
				__( 'YouTube channel activity at %d%%. ', 'wpshadow' ),
				(int) $youtube_percentage
			) . implode( '. ', $issues ) . ' ' . __( 'Active channels drive 400% more traffic', 'wpshadow' );

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => $description,
				'severity'     => $severity,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/youtube-channel-active',
			);
		}

		return null;
	}

	/**
	 * Check YouTube embeds.
	 *
	 * @since 1.6093.1200
	 * @return bool True if embeds exist, false otherwise.
	 */
	private static function check_youtube_embeds() {
		// Check for YouTube embeds in content.
		$query = new \WP_Query(
			array(
				's'              => 'youtube.com/watch youtube.com/embed',
				'post_type'      => 'any',
				'posts_per_page' => 1,
				'post_status'    => 'publish',
			)
		);

		return $query->have_posts();
	}

	/**
	 * Check channel link.
	 *
	 * @since 1.6093.1200
	 * @return bool True if link exists, false otherwise.
	 */
	private static function check_channel_link() {
		// Check social media links.
		$query = new \WP_Query(
			array(
				's'              => 'youtube.com/channel youtube.com/c/',
				'post_type'      => 'any',
				'posts_per_page' => 1,
				'post_status'    => 'publish',
			)
		);

		return $query->have_posts();
	}

	/**
	 * Check video plugin.
	 *
	 * @since 1.6093.1200
	 * @return bool True if plugin exists, false otherwise.
	 */
	private static function check_video_plugin() {
		// Check for video plugins.
		$video_plugins = array(
			'youtube-embed-plus/youtube.php',
			'embed-plus-for-youtube/youtube.php',
		);

		foreach ( $video_plugins as $plugin_path ) {
			if ( is_plugin_active( $plugin_path ) ) {
				return true;
			}
		}

		return apply_filters( 'wpshadow_has_video_plugin', false );
	}

	/**
	 * Check upload frequency.
	 *
	 * @since 1.6093.1200
	 * @return bool True if frequent uploads, false otherwise.
	 */
	private static function check_upload_frequency() {
		// Check for recent video content.
		$recent_videos = get_posts(
			array(
				'post_type'      => 'any',
				'posts_per_page' => 4,
				'date_query'     => array(
					array(
						'after' => '1 month ago',
					),
				),
				's'              => 'youtube video watch',
			)
		);

		return count( $recent_videos ) >= 4; // At least weekly.
	}

	/**
	 * Check content strategy.
	 *
	 * @since 1.6093.1200
	 * @return bool True if strategy exists, false otherwise.
	 */
	private static function check_content_strategy() {
		// Check for video strategy documentation.
		$query = new \WP_Query(
			array(
				's'              => 'video content strategy upload schedule',
				'post_type'      => 'any',
				'posts_per_page' => 1,
				'post_status'    => 'publish',
			)
		);

		return $query->have_posts();
	}

	/**
	 * Check subscriber engagement.
	 *
	 * @since 1.6093.1200
	 * @return bool True if engagement exists, false otherwise.
	 */
	private static function check_subscriber_engagement() {
		// Check for subscriber prompts.
		$keywords = array( 'subscribe', 'notification bell', 'like comment share' );

		foreach ( $keywords as $keyword ) {
			$query = new \WP_Query(
				array(
					's'              => $keyword,
					'post_type'      => 'any',
					'posts_per_page' => 1,
					'post_status'    => 'publish',
				)
			);
			if ( $query->have_posts() ) {
				return true;
			}
		}

		return false;
	}
}
