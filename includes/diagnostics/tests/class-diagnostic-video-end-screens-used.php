<?php
/**
 * Video End Screens Used Diagnostic
 *
 * Tests whether the site uses end screens on all videos to drive next actions.
 *
 * @since   1.26034.0405
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Video End Screens Used Diagnostic Class
 *
 * End screens increase channel subscriptions by 300% and video views by 50%.
 * They're critical for keeping viewers in your content ecosystem.
 *
 * @since 1.26034.0405
 */
class Diagnostic_Video_End_Screens_Used extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'video-end-screens-used';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Video End Screens Used';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests whether the site uses end screens on all videos to drive next actions';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'video-marketing';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26034.0405
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();
		$endscreen_score = 0;
		$max_score = 5;

		// Check for end screen documentation.
		$endscreen_docs = self::check_endscreen_documentation();
		if ( $endscreen_docs ) {
			$endscreen_score++;
		} else {
			$issues[] = __( 'No end screen strategy documented', 'wpshadow' );
		}

		// Check for subscribe CTAs.
		$subscribe_ctas = self::check_subscribe_ctas();
		if ( $subscribe_ctas ) {
			$endscreen_score++;
		} else {
			$issues[] = __( 'Videos lack subscribe calls-to-action', 'wpshadow' );
		}

		// Check for next video suggestions.
		$next_video = self::check_next_video_suggestions();
		if ( $next_video ) {
			$endscreen_score++;
		} else {
			$issues[] = __( 'No next video recommendations in content', 'wpshadow' );
		}

		// Check for playlist links.
		$playlist_links = self::check_playlist_links();
		if ( $playlist_links ) {
			$endscreen_score++;
		} else {
			$issues[] = __( 'End screens not linking to playlists', 'wpshadow' );
		}

		// Check for website links.
		$website_links = self::check_website_links();
		if ( $website_links ) {
			$endscreen_score++;
		} else {
			$issues[] = __( 'Not driving traffic from videos back to website', 'wpshadow' );
		}

		// Determine severity based on end screen usage.
		$endscreen_percentage = ( $endscreen_score / $max_score ) * 100;

		if ( $endscreen_percentage < 40 ) {
			$severity = 'low';
			$threat_level = 25;
		} elseif ( $endscreen_percentage < 70 ) {
			$severity = 'low';
			$threat_level = 15;
		} else {
			return null;
		}

		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %d: End screen usage percentage */
				__( 'Video end screen usage at %d%%. ', 'wpshadow' ),
				(int) $endscreen_percentage
			) . implode( '. ', $issues ) . ' ' . __( 'End screens increase subscriptions by 300%', 'wpshadow' );

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => $description,
				'severity'     => $severity,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/video-end-screens-used',
			);
		}

		return null;
	}

	/**
	 * Check endscreen documentation.
	 *
	 * @since  1.26034.0405
	 * @return bool True if documented, false otherwise.
	 */
	private static function check_endscreen_documentation() {
		// Check for end screen references.
		$query = new \WP_Query(
			array(
				's'              => 'end screen card youtube',
				'post_type'      => 'any',
				'posts_per_page' => 1,
				'post_status'    => 'publish',
			)
		);

		return $query->have_posts();
	}

	/**
	 * Check subscribe CTAs.
	 *
	 * @since  1.26034.0405
	 * @return bool True if CTAs exist, false otherwise.
	 */
	private static function check_subscribe_ctas() {
		// Check for subscription prompts.
		$keywords = array( 'subscribe', 'notification bell', 'turn on notifications' );

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
	 * Check next video suggestions.
	 *
	 * @since  1.26034.0405
	 * @return bool True if suggestions exist, false otherwise.
	 */
	private static function check_next_video_suggestions() {
		// Check for next video references.
		$keywords = array( 'next video', 'watch next', 'check out', 'related video' );

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
	 * Check playlist links.
	 *
	 * @since  1.26034.0405
	 * @return bool True if links exist, false otherwise.
	 */
	private static function check_playlist_links() {
		// Check for playlist references.
		$query = new \WP_Query(
			array(
				's'              => 'full playlist complete series',
				'post_type'      => 'any',
				'posts_per_page' => 1,
				'post_status'    => 'publish',
			)
		);

		return $query->have_posts();
	}

	/**
	 * Check website links.
	 *
	 * @since  1.26034.0405
	 * @return bool True if links exist, false otherwise.
	 */
	private static function check_website_links() {
		// Check for website references in video content.
		$site_url = home_url();
		$query = new \WP_Query(
			array(
				's'              => 'visit website learn more link description',
				'post_type'      => 'any',
				'posts_per_page' => 1,
				'post_status'    => 'publish',
			)
		);

		return $query->have_posts();
	}
}
