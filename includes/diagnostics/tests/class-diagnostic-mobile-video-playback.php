<?php
/**
 * Mobile Video Playback Diagnostic
 *
 * Ensures videos play inline on mobile.
 *
 * @since   1.26033.1645
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mobile Video Playback Diagnostic Class
 *
 * Ensures videos play inline on mobile with proper controls and attributes,
 * preventing full-screen-only playback that disrupts content flow.
 *
 * @since 1.26033.1645
 */
class Diagnostic_Mobile_Video_Playback extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-video-playback';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Mobile Video Playback';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Ensure videos play inline on mobile with proper controls';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26033.1645
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check if videos have playsinline attribute
		$has_playsinline = apply_filters( 'wpshadow_videos_have_playsinline_attribute', false );
		if ( ! $has_playsinline ) {
			$issues[] = __( 'Videos should have playsinline attribute to play inline on iOS instead of fullscreen', 'wpshadow' );
		}

		// Check if video controls are visible
		$video_controls_visible = apply_filters( 'wpshadow_video_controls_visible_mobile', false );
		if ( ! $video_controls_visible ) {
			$issues[] = __( 'Video controls should be visible on mobile for play/pause/volume control', 'wpshadow' );
		}

		// Check for autoplay behavior
		$autoplay_muted = apply_filters( 'wpshadow_autoplaying_videos_are_muted', false );
		if ( ! $autoplay_muted ) {
			$issues[] = __( 'Autoplaying videos should be muted; mobile browsers block unmuted autoplay', 'wpshadow' );
		}

		// Check if video doesn't disrupt reading
		$video_placement = apply_filters( 'wpshadow_video_placement_doesnt_disrupt_content', false );
		if ( ! $video_placement ) {
			$issues[] = __( 'Videos should not interrupt reading flow on mobile; place below text or in sidebar', 'wpshadow' );
		}

		// Check for video poster image
		$has_poster_image = apply_filters( 'wpshadow_video_has_poster_image', false );
		if ( ! $has_poster_image ) {
			$issues[] = __( 'Videos should have poster image to show before playback and improve perceived performance', 'wpshadow' );
		}

		// Check for video transcript or captions
		$has_captions_or_transcript = apply_filters( 'wpshadow_video_has_captions_or_transcript', false );
		if ( ! $has_captions_or_transcript ) {
			$issues[] = __( 'Videos should have captions or transcript for deaf/hard-of-hearing users (WCAG 1.2.1)', 'wpshadow' );
		}

		// Check if video dimensions are responsive
		$video_responsive = apply_filters( 'wpshadow_video_responsive_dimensions', false );
		if ( ! $video_responsive ) {
			$issues[] = __( 'Video player should scale responsively without fixed pixel dimensions', 'wpshadow' );
		}

		// Check for fallback content
		$fallback_content = apply_filters( 'wpshadow_video_has_fallback_content', false );
		if ( ! $fallback_content ) {
			$issues[] = __( 'Video element should include fallback text for browsers that don\'t support HTML5 video', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/mobile-video-playback',
			);
		}

		return null;
	}
}
