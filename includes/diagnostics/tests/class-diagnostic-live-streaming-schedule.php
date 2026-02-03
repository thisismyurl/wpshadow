<?php
/**
 * Live Streaming Schedule Diagnostic
 *
 * Tests whether the site maintains a regular live streaming schedule (at least monthly).
 *
 * @since   1.26034.0415
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Live Streaming Schedule Diagnostic Class
 *
 * Live streaming increases engagement by 1200% vs pre-recorded content.
 * Regular streams build authentic connections and loyal communities.
 *
 * @since 1.26034.0415
 */
class Diagnostic_Live_Streaming_Schedule extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'live-streaming-schedule';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Live Streaming Schedule';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests whether the site maintains a regular live streaming schedule (at least monthly)';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'video-marketing';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26034.0415
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();
		$livestream_score = 0;
		$max_score = 5;

		// Check for live stream content.
		$livestream_content = self::check_livestream_content();
		if ( $livestream_content ) {
			$livestream_score++;
		} else {
			$issues[] = __( 'No live streaming activity detected', 'wpshadow' );
		}

		// Check for consistent schedule.
		$consistent_schedule = self::check_consistent_schedule();
		if ( $consistent_schedule ) {
			$livestream_score++;
		} else {
			$issues[] = __( 'No regular live streaming schedule (monthly minimum)', 'wpshadow' );
		}

		// Check for stream announcements.
		$stream_announcements = self::check_stream_announcements();
		if ( $stream_announcements ) {
			$livestream_score++;
		} else {
			$issues[] = __( 'Live streams not announced in advance', 'wpshadow' );
		}

		// Check for stream replays.
		$stream_replays = self::check_stream_replays();
		if ( $stream_replays ) {
			$livestream_score++;
		} else {
			$issues[] = __( 'Live stream replays not saved or promoted', 'wpshadow' );
		}

		// Check for interactive elements.
		$interactive_elements = self::check_interactive_elements();
		if ( $interactive_elements ) {
			$livestream_score++;
		} else {
			$issues[] = __( 'Live streams lack Q&A, polls, or audience interaction', 'wpshadow' );
		}

		// Determine severity based on live streaming activity.
		$livestream_percentage = ( $livestream_score / $max_score ) * 100;

		if ( $livestream_percentage < 40 ) {
			$severity = 'low';
			$threat_level = 25;
		} elseif ( $livestream_percentage < 70 ) {
			$severity = 'low';
			$threat_level = 15;
		} else {
			return null;
		}

		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %d: Live streaming percentage */
				__( 'Live streaming activity at %d%%. ', 'wpshadow' ),
				(int) $livestream_percentage
			) . implode( '. ', $issues ) . ' ' . __( 'Live streaming increases engagement by 1200%', 'wpshadow' );

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => $description,
				'severity'     => $severity,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/live-streaming-schedule',
			);
		}

		return null;
	}

	/**
	 * Check livestream content.
	 *
	 * @since  1.26034.0415
	 * @return bool True if content exists, false otherwise.
	 */
	private static function check_livestream_content() {
		// Check for live stream references.
		$keywords = array( 'live stream', 'going live', 'livestream', 'live video' );

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
	 * Check consistent schedule.
	 *
	 * @since  1.26034.0415
	 * @return bool True if scheduled, false otherwise.
	 */
	private static function check_consistent_schedule() {
		// Check for recent live stream posts.
		$query = new \WP_Query(
			array(
				's'              => 'live stream going live',
				'post_type'      => 'any',
				'posts_per_page' => 10,
				'post_status'    => 'publish',
				'date_query'     => array(
					array(
						'after' => '3 months ago',
					),
				),
			)
		);

		// 3+ streams in 3 months = monthly.
		return ( $query->found_posts >= 3 );
	}

	/**
	 * Check stream announcements.
	 *
	 * @since  1.26034.0415
	 * @return bool True if announced, false otherwise.
	 */
	private static function check_stream_announcements() {
		// Check for announcement content.
		$keywords = array( 'join us live', 'streaming tomorrow', 'upcoming live stream', 'save the date' );

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
	 * Check stream replays.
	 *
	 * @since  1.26034.0415
	 * @return bool True if replays exist, false otherwise.
	 */
	private static function check_stream_replays() {
		// Check for replay references.
		$keywords = array( 'stream replay', 'missed the live', 'watch replay', 'recorded stream' );

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
	 * Check interactive elements.
	 *
	 * @since  1.26034.0415
	 * @return bool True if interactive, false otherwise.
	 */
	private static function check_interactive_elements() {
		// Check for interaction references.
		$keywords = array( 'Q&A', 'questions', 'chat', 'poll', 'audience' );

		foreach ( $keywords as $keyword ) {
			$query = new \WP_Query(
				array(
					's'              => $keyword . ' live',
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
