<?php
/**
 * WCAG 1.4.2 Audio Control Treatment
 *
 * Validates that auto-playing audio can be paused or stopped.
 *
 * @since   1.6035.1200
 * @package WPShadow\Treatments
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WCAG Auto-playing Audio Control Treatment Class
 *
 * Checks for auto-playing audio that interferes with screen readers (WCAG 1.4.2 Level A).
 *
 * @since 1.6035.1200
 */
class Treatment_WCAG_Audio_Control extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'wcag-audio-control';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Audio Control (WCAG 1.4.2)';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Validates that auto-playing audio can be paused, stopped, or controlled';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'accessibility';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6035.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check recent posts for autoplay media.
		$posts = get_posts(
			array(
				'numberposts' => 20,
				'post_status' => 'publish',
				'post_type'   => 'any',
			)
		);

		$autoplay_count    = 0;
		$autoplay_examples = array();

		foreach ( $posts as $post ) {
			$content = $post->post_content;

			// Check for HTML5 audio/video with autoplay.
			if ( preg_match_all( '/<(audio|video)[^>]*autoplay[^>]*>/i', $content, $matches ) ) {
				$autoplay_count += count( $matches[0] );
				$autoplay_examples[] = $post->post_title;
			}

			// Check for YouTube embeds with autoplay.
			if ( preg_match( '/youtube\.com\/embed\/[^"\'>]*[?&]autoplay=1/', $content ) ) {
				$autoplay_count++;
				$autoplay_examples[] = $post->post_title . ' (YouTube)';
			}

			// Check for Vimeo embeds with autoplay.
			if ( preg_match( '/vimeo\.com\/video\/[^"\'>]*[?&]autoplay=1/', $content ) ) {
				$autoplay_count++;
				$autoplay_examples[] = $post->post_title . ' (Vimeo)';
			}
		}

		if ( $autoplay_count > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of autoplay instances */
				__( 'Found %d instances of auto-playing audio/video. This interferes with screen readers', 'wpshadow' ),
				$autoplay_count
			);
		}

		// Check for background music plugins.
		$active_plugins = get_option( 'active_plugins', array() );
		$audio_plugins  = array(
			'wp-background-music',
			'music-player',
			'audio-player',
			'soundcloud',
		);

		$has_audio_plugin = false;
		foreach ( $active_plugins as $plugin ) {
			foreach ( $audio_plugins as $audio_plugin ) {
				if ( strpos( $plugin, $audio_plugin ) !== false ) {
					$has_audio_plugin = true;
					break 2;
				}
			}
		}

		if ( $has_audio_plugin ) {
			$issues[] = __( 'Audio player plugin detected. Ensure it doesn\'t auto-play on page load or provides clear pause controls', 'wpshadow' );
		}

		// Check theme header/footer for embedded audio.
		$theme_files = array(
			get_template_directory() . '/header.php',
			get_template_directory() . '/footer.php',
		);

		foreach ( $theme_files as $theme_file ) {
			if ( file_exists( $theme_file ) ) {
				$content = file_get_contents( $theme_file );

				if ( preg_match( '/<(audio|video)[^>]*autoplay[^>]*>/i', $content ) ) {
					$issues[] = __( 'Theme has auto-playing media in header or footer. This continuously interferes with screen readers', 'wpshadow' );
					break;
				}
			}
		}

		// Check for JavaScript-triggered audio.
		$js_files = array();
		$theme_js = get_template_directory() . '/js';

		if ( is_dir( $theme_js ) ) {
			$files = glob( $theme_js . '/*.js' );
			if ( is_array( $files ) ) {
				$js_files = array_merge( $js_files, $files );
			}
		}

		$common_js_locations = array(
			get_template_directory() . '/assets/js',
			get_template_directory() . '/dist/js',
		);

		foreach ( $common_js_locations as $location ) {
			if ( is_dir( $location ) ) {
				$files = glob( $location . '/*.js' );
				if ( is_array( $files ) ) {
					$js_files = array_merge( $js_files, $files );
				}
			}
		}

		$has_js_audio = false;
		foreach ( $js_files as $js_file ) {
			if ( ! file_exists( $js_file ) ) {
				continue;
			}

			$content = file_get_contents( $js_file );

			// Check for Audio API usage with autoplay.
			if ( preg_match( '/new\s+Audio\(|\.play\(\)/', $content ) ) {
				$has_js_audio = true;
				break;
			}
		}

		if ( $has_js_audio ) {
			$issues[] = __( 'JavaScript audio playback detected. Ensure it only plays on user interaction, not automatically', 'wpshadow' );
		}

		// Check for notification sounds.
		if ( $has_js_audio ) {
			$issues[] = __( 'If audio is used for notifications, provide controls to disable or adjust volume', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Auto-playing audio is like someone shouting at you when you walk into a room. For screen reader users (about 2% of web users), auto-playing audio creates a nightmare: their screen reader is trying to read the page while your video or music plays simultaneously. It\'s like trying to have a phone conversation in a loud nightclub—impossible to understand anything. Plus, auto-play violates user expectations and most mobile browsers block it anyway.', 'wpshadow' ) . ' ' . implode( ' ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 60,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/wcag-audio-control',
			);
		}

		return null;
	}
}
