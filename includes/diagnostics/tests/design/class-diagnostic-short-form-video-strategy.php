<?php
/**
 * Short-Form Video Strategy Diagnostic
 *
 * Tests whether the site has an active strategy for YouTube Shorts, Instagram Reels, and TikToks.
 *
 * @since   1.6034.0410
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Short-Form Video Strategy Diagnostic Class
 *
 * Short-form videos generate 2.5x more engagement than traditional content.
 * Shorts, Reels, and TikToks are essential for discoverability in 2026.
 *
 * @since 1.6034.0410
 */
class Diagnostic_Short_Form_Video_Strategy extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'short-form-video-strategy';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Short-Form Video Strategy';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests whether the site has an active strategy for YouTube Shorts, Instagram Reels, and TikToks';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'video-marketing';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6034.0410
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();
		$shortform_score = 0;
		$max_score = 6;

		// Check for YouTube Shorts.
		$youtube_shorts = self::check_youtube_shorts();
		if ( $youtube_shorts ) {
			$shortform_score++;
		} else {
			$issues[] = __( 'No YouTube Shorts published', 'wpshadow' );
		}

		// Check for Instagram Reels.
		$instagram_reels = self::check_instagram_reels();
		if ( $instagram_reels ) {
			$shortform_score++;
		} else {
			$issues[] = __( 'No Instagram Reels strategy', 'wpshadow' );
		}

		// Check for TikTok presence.
		$tiktok_presence = self::check_tiktok_presence();
		if ( $tiktok_presence ) {
			$shortform_score++;
		} else {
			$issues[] = __( 'No TikTok account or content', 'wpshadow' );
		}

		// Check for consistent posting.
		$consistent_posting = self::check_consistent_posting();
		if ( $consistent_posting ) {
			$shortform_score++;
		} else {
			$issues[] = __( 'Not posting short-form content consistently (3x weekly minimum)', 'wpshadow' );
		}

		// Check for trending audio/hashtags.
		$trending_content = self::check_trending_content();
		if ( $trending_content ) {
			$shortform_score++;
		} else {
			$issues[] = __( 'Not leveraging trending audio or hashtags', 'wpshadow' );
		}

		// Check for vertical format optimization.
		$vertical_format = self::check_vertical_format();
		if ( $vertical_format ) {
			$shortform_score++;
		} else {
			$issues[] = __( 'Videos not optimized for 9:16 vertical format', 'wpshadow' );
		}

		// Determine severity based on short-form strategy.
		$shortform_percentage = ( $shortform_score / $max_score ) * 100;

		if ( $shortform_percentage < 35 ) {
			$severity = 'low';
			$threat_level = 30;
		} elseif ( $shortform_percentage < 60 ) {
			$severity = 'low';
			$threat_level = 20;
		} else {
			return null;
		}

		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %d: Short-form video strategy percentage */
				__( 'Short-form video strategy at %d%%. ', 'wpshadow' ),
				(int) $shortform_percentage
			) . implode( '. ', $issues ) . ' ' . __( 'Short-form videos generate 2.5x more engagement', 'wpshadow' );

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => $description,
				'severity'     => $severity,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/short-form-video-strategy',
			);
		}

		return null;
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
