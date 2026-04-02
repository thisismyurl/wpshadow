<?php
/**
 * Video Captions Missing Diagnostic
 *
 * Checks if embedded videos have captions or transcripts available.
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
 * Video Captions Diagnostic Class
 *
 * Validates that videos have captions for deaf and hard-of-hearing users.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Video_Captions_Missing extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'video-captions-missing';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Videos Missing Captions or Transcripts';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if embedded videos have captions or transcripts';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'accessibility';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues        = array();
		$videos_found  = 0;
		$videos_no_cap = 0;

		// Check recent posts for video embeds.
		$posts = get_posts(
			array(
				'numberposts' => 50,
				'post_status' => 'publish',
				'post_type'   => 'any',
			)
		);

		foreach ( $posts as $post ) {
			$content = $post->post_content;

			// Check HTML5 video tags.
			if ( preg_match_all( '/<video[^>]*>(.*?)<\/video>/is', $content, $video_matches ) ) {
				foreach ( $video_matches[1] as $video_content ) {
					$videos_found++;
					// Check for <track kind="captions">.
					if ( ! preg_match( '/<track[^>]*kind=["\'](?:captions|subtitles)["\']/', $video_content ) ) {
						$videos_no_cap++;
					}
				}
			}

			// Check YouTube embeds without captions.
			if ( preg_match_all( '/youtube\.com\/embed\/[^"\'>]+/i', $content, $youtube_matches ) ) {
				foreach ( $youtube_matches[0] as $youtube_url ) {
					$videos_found++;
					// Check if cc_load_policy=1 is set.
					if ( strpos( $youtube_url, 'cc_load_policy=1' ) === false ) {
						$videos_no_cap++;
					}
				}
			}

			// Check Vimeo embeds.
			if ( preg_match_all( '/vimeo\.com\/(?:video\/)?([0-9]+)/i', $content, $vimeo_matches ) ) {
				$videos_found += count( $vimeo_matches[0] );
				// Can't detect captions from embed URL, flag as potential issue.
				$videos_no_cap += count( $vimeo_matches[0] );
			}

			// Check for audio tags without transcript links.
			if ( preg_match_all( '/<audio[^>]*>/i', $content, $audio_matches ) ) {
				$videos_found++;
				// Look for transcript link nearby (within 500 characters).
				$audio_pos = strpos( $content, $audio_matches[0][0] );
				$context   = substr( $content, max( 0, $audio_pos - 250 ), 500 );
				if ( ! preg_match( '/transcript/i', $context ) ) {
					$videos_no_cap++;
				}
			}
		}

		if ( $videos_no_cap > 0 ) {
			$issues[] = sprintf(
				/* translators: 1: number of videos without captions, 2: total videos */
				__( 'Found %1$d videos (out of %2$d) without captions or transcripts', 'wpshadow' ),
				$videos_no_cap,
				$videos_found
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Your videos exclude deaf and hard-of-hearing visitors—like having a conversation in a crowded room where some people can\'t hear. About 5% of the world\'s population is deaf or hard of hearing, and they can\'t access your video content without captions. Plus, captions help everyone in noisy environments (cafes, public transit) and improve SEO since search engines can index the text.', 'wpshadow' ) . ' ' . implode( ' ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/video-captions',
			);
		}

		return null;
	}
}
