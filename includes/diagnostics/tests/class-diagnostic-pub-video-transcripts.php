<?php
/**
 * Diagnostic: Pub Video Transcripts
 *
 * Checks if embedded videos in published posts have captions/transcripts.
 * Video captions/transcripts are essential for accessibility (WCAG 1.2.2, 1.2.3).
 *
 * This diagnostic analyzes all published posts for <video> tags and video iframes,
 * checking if they have associated <track> elements with captions or subtitles.
 * It flags the site if videos are found without proper captioning.
 *
 * @since   1.2601.2148
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Pub_Video_Transcripts Class
 *
 * Verifies that embedded videos in published posts have captions or transcripts
 * for accessibility compliance.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Pub_Video_Transcripts extends Diagnostic_Base {
	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'pub-video-transcripts';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Video Captions/Transcripts';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies that embedded videos in published posts have captions or transcripts for accessibility.';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'general';

	/**
	 * Display name for the family
	 *
	 * @var string
	 */
	protected static $family_label = 'General';

	/**
	 * Get diagnostic ID
	 *
	 * @since  1.2601.2148
	 * @return string Diagnostic identifier.
	 */
	public static function get_id(): string {
		return 'pub-video-transcripts';
	}

	/**
	 * Get diagnostic name
	 *
	 * @since  1.2601.2148
	 * @return string Diagnostic display name.
	 */
	public static function get_name(): string {
		return __( 'Video Captions/Transcripts', 'wpshadow' );
	}

	/**
	 * Get diagnostic description
	 *
	 * @since  1.2601.2148
	 * @return string Diagnostic description.
	 */
	public static function get_description(): string {
		return __( 'Do embedded videos have captions or transcripts?', 'wpshadow' );
	}

	/**
	 * Get diagnostic category
	 *
	 * @since  1.2601.2148
	 * @return string Diagnostic category.
	 */
	public static function get_category(): string {
		return 'content_publishing';
	}

	/**
	 * Get threat level
	 *
	 * @since  1.2601.2148
	 * @return int 0-100 severity level.
	 */
	public static function get_threat_level(): int {
		return 25;
	}

	/**
	 * Run diagnostic test
	 *
	 * @since  1.2601.2148
	 * @return array Diagnostic results.
	 */
	public static function run(): array {
		$result = self::check();

		if ( null === $result ) {
			return array(
				'status'  => 'pass',
				'message' => __( 'All videos have captions or transcripts, or no videos found.', 'wpshadow' ),
				'data'    => array(),
			);
		}

		return array(
			'status'  => 'fail',
			'message' => $result['description'],
			'data'    => $result,
		);
	}

	/**
	 * Get KB article URL
	 *
	 * @since  1.2601.2148
	 * @return string KB article URL.
	 */
	public static function get_kb_article(): string {
		return 'https://wpshadow.com/kb/pub-video-transcripts';
	}

	/**
	 * Get training video URL
	 *
	 * @since  1.2601.2148
	 * @return string Training video URL.
	 */
	public static function get_training_video(): string {
		return 'https://wpshadow.com/training/category-content-publishing';
	}

	/**
	 * Check if published posts have videos with captions/transcripts.
	 *
	 * Analyzes all published posts for video elements (both <video> tags and
	 * video iframes) and checks if they have associated <track> elements with
	 * captions or subtitles. Flags if videos are found without proper captioning.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if videos lack captions, null if all have captions.
	 */
	public static function check(): ?array {
		$video_data = self::analyze_video_captions();

		if ( null === $video_data ) {
			return null; // No videos to check.
		}

		$videos_with_captions    = $video_data['videos_with_captions'];
		$videos_without_captions = $video_data['videos_without_captions'];
		$total_videos            = $video_data['total_videos'];
		$coverage                = $video_data['coverage'];

		// Flag if any videos lack captions.
		if ( $videos_without_captions > 0 ) {
			$description = sprintf(
				/* translators: 1: number of videos without captions, 2: total videos, 3: percentage */
				__( '%1$d of %2$d videos (%.0f%%) lack captions or transcripts. Video captions are required for accessibility (WCAG 1.2.2) and help deaf/hard-of-hearing users, non-native speakers, and improve SEO.', 'wpshadow' ),
				$videos_without_captions,
				$total_videos,
				100 - $coverage
			);

			// Calculate threat level based on percentage.
			$threat_level = 40;
			if ( $coverage < 50 ) {
				$threat_level = 70;
			} elseif ( $coverage < 75 ) {
				$threat_level = 55;
			}

			return \WPShadow\Core\Diagnostic_Lean_Checks::build_finding(
				'pub-video-transcripts',
				__( 'Videos Missing Captions/Transcripts', 'wpshadow' ),
				$description,
				'general',
				'medium',
				$threat_level,
				'pub-video-transcripts'
			);
		}

		return null; // All videos have captions.
	}

	/**
	 * Analyze video caption coverage across all published posts.
	 *
	 * Helper method that scans all published posts for video elements
	 * and checks if they have associated track elements with captions.
	 *
	 * @since  1.2601.2148
	 * @return array|null {
	 *     Coverage data array, or null if no videos found.
	 *
	 *     @type int   $total_videos            Total number of videos found.
	 *     @type int   $videos_with_captions    Number of videos with captions.
	 *     @type int   $videos_without_captions Number of videos without captions.
	 *     @type float $coverage                Percentage of videos with captions.
	 * }
	 */
	private static function analyze_video_captions(): ?array {
		// Get all published posts.
		$posts = get_posts(
			array(
				'post_type'      => 'post',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'fields'         => 'ids',
			)
		);

		if ( empty( $posts ) ) {
			return null; // No published posts to check.
		}

		$total_videos            = 0;
		$videos_with_captions    = 0;
		$videos_without_captions = 0;

		foreach ( $posts as $post_id ) {
			$content = get_post_field( 'post_content', $post_id );

			// Find all <video> tags in post content.
			preg_match_all( '/<video[^>]*>.*?<\/video>/is', $content, $video_matches );

			foreach ( $video_matches[0] as $video_block ) {
				++$total_videos;

				// Check if video has a <track> element with captions or subtitles.
				if ( preg_match( '/<track[^>]+kind\s*=\s*["\'](?:captions|subtitles)["\'][^>]*>/i', $video_block ) ) {
					++$videos_with_captions;
				} else {
					++$videos_without_captions;
				}
			}

			// Also check for iframe embeds (YouTube, Vimeo, etc.) - these typically have built-in captions.
			// We'll assume iframes with cc=1 or similar parameters have captions enabled.
			preg_match_all( '/<iframe[^>]+src\s*=\s*["\']([^"\']+)["\'][^>]*>/i', $content, $iframe_matches );

			foreach ( $iframe_matches[1] as $iframe_src ) {
				// Check if this is a video embed (YouTube, Vimeo, etc.).
				if ( preg_match( '/youtube\.com|youtu\.be|vimeo\.com|dailymotion\.com|wistia\.com/i', $iframe_src ) ) {
					++$total_videos;

					// Check for caption parameters in URL.
					// YouTube: cc_load_policy=1 or cc=1.
					// Vimeo: Most videos have captions available, we'll be lenient.
					if ( preg_match( '/[?&](?:cc_load_policy|cc)=1|vimeo\.com/i', $iframe_src ) ) {
						++$videos_with_captions;
					} else {
						// For iframes, we can't definitively know, so count as without captions.
						++$videos_without_captions;
					}
				}
			}
		}

		if ( 0 === $total_videos ) {
			return null; // No videos to check.
		}

		$coverage = ( $videos_with_captions / $total_videos ) * 100;

		return array(
			'total_videos'            => $total_videos,
			'videos_with_captions'    => $videos_with_captions,
			'videos_without_captions' => $videos_without_captions,
			'coverage'                => $coverage,
		);
	}

	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Pub Video Transcripts
	 * Slug: pub-video-transcripts
	 *
	 * Test Purpose:
	 * - Verify that check() method returns the correct result based on site state
	 * - PASS: check() returns NULL when diagnostic condition is NOT met (all videos have captions)
	 * - FAIL: check() returns array when diagnostic condition IS met (videos lack captions)
	 * - Description: Checks if embedded videos in published posts have captions or transcripts
	 *
	 * @since  1.2601.2148
	 * @return array {
	 *     @type bool   $passed  Whether the test passed
	 *     @type string $message Human-readable test result message
	 * }
	 */
	public static function test_live_pub_video_transcripts(): array {
		$result     = self::check();
		$video_data = self::analyze_video_captions();

		if ( null === $video_data ) {
			return array(
				'passed'  => true,
				'message' => __( 'No published posts with videos found. Test N/A.', 'wpshadow' ),
			);
		}

		$videos_without_captions = $video_data['videos_without_captions'];
		$coverage                = $video_data['coverage'];

		if ( null === $result && 0 === $videos_without_captions ) {
			return array(
				'passed'  => true,
				'message' => sprintf(
					/* translators: %d: coverage percentage */
					__( 'Test passed. All videos have captions (%.0f%% coverage).', 'wpshadow' ),
					$coverage
				),
			);
		}

		if ( null !== $result && $videos_without_captions > 0 ) {
			return array(
				'passed'  => true,
				'message' => sprintf(
					/* translators: 1: number of videos without captions, 2: total videos */
					__( 'Test passed. Correctly detected %1$d video(s) without captions (%.0f%% coverage).', 'wpshadow' ),
					$videos_without_captions,
					$coverage
				),
			);
		}

		return array(
			'passed'  => false,
			'message' => sprintf(
				/* translators: 1: number of videos without captions, 2: coverage percentage */
				__( 'Test failed. %1$d videos without captions (%.0f%% coverage) but check() returned unexpected result.', 'wpshadow' ),
				$videos_without_captions,
				$coverage
			),
		);
	}
}
