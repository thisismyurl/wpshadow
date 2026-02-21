<?php
/**
 * Short-Form Video Strategy Treatment
 *
 * Tests whether the site has an active strategy for YouTube Shorts, Instagram Reels, and TikToks.
 *
 * @since   1.6034.0410
 * @package WPShadow\Treatments
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Short-Form Video Strategy Treatment Class
 *
 * Short-form videos generate 2.5x more engagement than traditional content.
 * Shorts, Reels, and TikToks are essential for discoverability in 2026.
 *
 * @since 1.6034.0410
 */
class Treatment_Short_Form_Video_Strategy extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'short-form-video-strategy';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Short-Form Video Strategy';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Tests whether the site has an active strategy for YouTube Shorts, Instagram Reels, and TikToks';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'video-marketing';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6034.0410
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Short_Form_Video_Strategy' );
	}

	/**
	 * Check YouTube Shorts.
	 *
	 * @since  1.6034.0410
	 * @return bool True if Shorts exist, false otherwise.
	 */
	private static function check_youtube_shorts() {
		// Check for Shorts references.
		$query = new \WP_Query(
			array(
				's'              => 'youtube.com/shorts #shorts',
				'post_type'      => 'any',
				'posts_per_page' => 1,
				'post_status'    => 'publish',
			)
		);

		return $query->have_posts();
	}

	/**
	 * Check Instagram Reels.
	 *
	 * @since  1.6034.0410
	 * @return bool True if Reels exist, false otherwise.
	 */
	private static function check_instagram_reels() {
		// Check for Reels references.
		$query = new \WP_Query(
			array(
				's'              => 'instagram.com/reel reels',
				'post_type'      => 'any',
				'posts_per_page' => 1,
				'post_status'    => 'publish',
			)
		);

		return $query->have_posts();
	}

	/**
	 * Check TikTok presence.
	 *
	 * @since  1.6034.0410
	 * @return bool True if TikTok active, false otherwise.
	 */
	private static function check_tiktok_presence() {
		// Check for TikTok links.
		$query = new \WP_Query(
			array(
				's'              => 'tiktok.com @tiktok',
				'post_type'      => 'any',
				'posts_per_page' => 1,
				'post_status'    => 'publish',
			)
		);

		return $query->have_posts();
	}

	/**
	 * Check consistent posting.
	 *
	 * @since  1.6034.0410
	 * @return bool True if consistent, false otherwise.
	 */
	private static function check_consistent_posting() {
		// Check for recent short-form video posts.
		$query = new \WP_Query(
			array(
				's'              => 'shorts reels tiktok vertical video',
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

		// 12+ posts per month = ~3x weekly.
		return ( $query->found_posts >= 12 );
	}

	/**
	 * Check trending content.
	 *
	 * @since  1.6034.0410
	 * @return bool True if using trends, false otherwise.
	 */
	private static function check_trending_content() {
		// Check for trending hashtags.
		$keywords = array( 'trending', 'viral', 'hashtag', 'challenge', 'sound' );

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

	/**
	 * Check vertical format.
	 *
	 * @since  1.6034.0410
	 * @return bool True if optimized, false otherwise.
	 */
	private static function check_vertical_format() {
		// Check for vertical video references.
		$query = new \WP_Query(
			array(
				's'              => 'vertical 9:16 portrait mobile-first',
				'post_type'      => 'any',
				'posts_per_page' => 1,
				'post_status'    => 'publish',
			)
		);

		return $query->have_posts();
	}
}
