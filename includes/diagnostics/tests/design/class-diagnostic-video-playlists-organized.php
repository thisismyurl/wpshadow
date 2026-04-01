<?php
/**
 * Video Playlists Organized Diagnostic
 *
 * Tests whether the site organizes videos into strategic playlists that increase watch sessions.
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
 * Video Playlists Organized Diagnostic Class
 *
 * Playlists increase watch time by 150% and session length by 3x.
 * Strategic organization keeps viewers engaged longer.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Video_Playlists_Organized extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'video-playlists-organized';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Video Playlists Organized';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests whether the site organizes videos into strategic playlists that increase watch sessions';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'video-marketing';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();
		$playlist_score = 0;
		$max_score = 5;

		// Check for playlist embeds.
		$playlist_embeds = self::check_playlist_embeds();
		if ( $playlist_embeds ) {
			$playlist_score++;
		} else {
			$issues[] = __( 'No YouTube playlists embedded on site', 'wpshadow' );
		}

		// Check for categorized videos.
		$categorized_videos = self::check_categorized_videos();
		if ( $categorized_videos ) {
			$playlist_score++;
		} else {
			$issues[] = __( 'Videos not organized into categories or series', 'wpshadow' );
		}

		// Check for beginner series.
		$beginner_series = self::check_beginner_series();
		if ( $beginner_series ) {
			$playlist_score++;
		} else {
			$issues[] = __( 'No beginner/getting started video series', 'wpshadow' );
		}

		// Check for strategic ordering.
		$strategic_ordering = self::check_strategic_ordering();
		if ( $strategic_ordering ) {
			$playlist_score++;
		} else {
			$issues[] = __( 'Playlists not strategically ordered for learning paths', 'wpshadow' );
		}

		// Check for playlist promotion.
		$playlist_promotion = self::check_playlist_promotion();
		if ( $playlist_promotion ) {
			$playlist_score++;
		} else {
			$issues[] = __( 'Playlists not promoted in video descriptions', 'wpshadow' );
		}

		// Determine severity based on playlist organization.
		$playlist_percentage = ( $playlist_score / $max_score ) * 100;

		if ( $playlist_percentage < 40 ) {
			$severity = 'low';
			$threat_level = 20;
		} elseif ( $playlist_percentage < 70 ) {
			$severity = 'low';
			$threat_level = 10;
		} else {
			return null;
		}

		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %d: Playlist organization percentage */
				__( 'Video playlist organization at %d%%. ', 'wpshadow' ),
				(int) $playlist_percentage
			) . implode( '. ', $issues ) . ' ' . __( 'Playlists increase watch time by 150%', 'wpshadow' );

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => $description,
				'severity'     => $severity,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/video-playlists-organized?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}

	/**
	 * Check playlist embeds.
	 *
	 * @since 0.6093.1200
	 * @return bool True if playlists exist, false otherwise.
	 */
	private static function check_playlist_embeds() {
		// Check for YouTube playlist links.
		$query = new \WP_Query(
			array(
				's'              => 'youtube.com/playlist list=',
				'post_type'      => 'any',
				'posts_per_page' => 1,
				'post_status'    => 'publish',
			)
		);

		return $query->have_posts();
	}

	/**
	 * Check categorized videos.
	 *
	 * @since 0.6093.1200
	 * @return bool True if categorized, false otherwise.
	 */
	private static function check_categorized_videos() {
		// Check for video categories.
		$keywords = array( 'tutorial series', 'video course', 'lesson 1', 'episode' );

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
	 * Check beginner series.
	 *
	 * @since 0.6093.1200
	 * @return bool True if series exists, false otherwise.
	 */
	private static function check_beginner_series() {
		// Check for beginner content.
		$keywords = array( 'beginner', 'getting started', 'introduction to', 'basics' );

		foreach ( $keywords as $keyword ) {
			$query = new \WP_Query(
				array(
					's'              => $keyword,
					'post_type'      => 'any',
					'posts_per_page' => 2,
					'post_status'    => 'publish',
				)
			);
			if ( $query->found_posts >= 2 ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check strategic ordering.
	 *
	 * @since 0.6093.1200
	 * @return bool True if ordered, false otherwise.
	 */
	private static function check_strategic_ordering() {
		// Check for sequential content.
		$query = new \WP_Query(
			array(
				's'              => 'part 1 part 2 step 1 step 2',
				'post_type'      => 'any',
				'posts_per_page' => 1,
				'post_status'    => 'publish',
			)
		);

		return $query->have_posts();
	}

	/**
	 * Check playlist promotion.
	 *
	 * @since 0.6093.1200
	 * @return bool True if promoted, false otherwise.
	 */
	private static function check_playlist_promotion() {
		// Check for playlist references.
		$query = new \WP_Query(
			array(
				's'              => 'full playlist watch series complete course',
				'post_type'      => 'any',
				'posts_per_page' => 1,
				'post_status'    => 'publish',
			)
		);

		return $query->have_posts();
	}
}
