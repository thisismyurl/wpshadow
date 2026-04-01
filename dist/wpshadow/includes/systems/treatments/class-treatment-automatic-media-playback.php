<?php
/**
 * Treatment: Remove Automatic Media Playback
 *
 * Removes autoplay attributes from video and audio elements
 * to comply with WCAG 2.1 and improve user experience.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Automatic_Media_Playback Class
 *
 * Removes autoplay from media elements.
 *
 * @since 0.6093.1200
 */
class Treatment_Automatic_Media_Playback extends Treatment_Base {

	/**
	 * Get the finding ID this treatment addresses.
	 *
	 * @since 0.6093.1200
	 * @return string Finding ID.
	 */
	public static function get_finding_id() {
		return 'automatic-media-playback';
	}

	/**
	 * Apply the treatment.
	 *
	 * Removes autoplay attributes from video, audio, and iframe elements.
	 *
	 * @since 0.6093.1200
	 * @return array {
	 *     Result array.
	 *
	 *     @type bool   $success Whether treatment succeeded.
	 *     @type string $message Human-readable result message.
	 *     @type array  $details Additional details about changes made.
	 * }
	 */
	public static function apply() {
		$posts = get_posts(
			array(
				'post_type'      => array( 'post', 'page' ),
				'posts_per_page' => -1,
				'post_status'    => 'publish',
			)
		);

		$updated_count   = 0;
		$autoplay_fixed  = 0;
		$updated_content = array();

		foreach ( $posts as $post ) {
			$content  = $post->post_content;
			$original = $content;
			$changes  = 0;

			// Remove autoplay from video and audio tags.
			$content = preg_replace(
				'/<(video|audio)([^>]*)\s+autoplay([^>]*)>/i',
				'<$1$2$3>',
				$content,
				-1,
				$video_audio_count
			);
			$changes += $video_audio_count;

			// Remove autoplay=1 from YouTube/Vimeo iframes.
			$content = preg_replace(
				'/(<iframe[^>]*(?:youtube\.com|vimeo\.com)[^>]*)autoplay=1([^>]*>)/i',
				'$1autoplay=0$2',
				$content,
				-1,
				$iframe_count
			);
			$changes += $iframe_count;

			// Also handle &autoplay=1 in URL parameters.
			$content = preg_replace(
				'/(<iframe[^>]*(?:youtube\.com|vimeo\.com)[^>]*)&amp;autoplay=1([^>]*>)/i',
				'$1&amp;autoplay=0$2',
				$content,
				-1,
				$iframe_amp_count
			);
			$changes += $iframe_amp_count;

			if ( $content !== $original ) {
				wp_update_post(
					array(
						'ID'           => $post->ID,
						'post_content' => $content,
					)
				);
				$updated_count++;
				$autoplay_fixed += $changes;
				$updated_content[] = array(
					'id'      => $post->ID,
					'title'   => $post->post_title,
					'type'    => $post->post_type,
					'changes' => $changes,
				);
			}
		}

		if ( $updated_count > 0 ) {
			return array(
				'success' => true,
				'message' => sprintf(
					/* translators: 1: number of posts updated, 2: number of autoplay instances removed */
					__( 'Removed autoplay from %2$d media elements across %1$d posts/pages. Now WCAG 2.1 compliant!', 'wpshadow' ),
					$updated_count,
					$autoplay_fixed
				),
				'details' => array(
					'posts_updated'     => $updated_count,
					'autoplay_removed'  => $autoplay_fixed,
					'updated_content'   => array_slice( $updated_content, 0, 10 ),
				),
			);
		}

		return array(
			'success' => false,
			'message' => __( 'No autoplay attributes found. All media elements are already compliant.', 'wpshadow' ),
		);
	}
}
