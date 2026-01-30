<?php
/**
 * Video Caption Availability Diagnostic
 *
 * Checks embedded videos for caption/subtitle tracks.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26028.1905
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Video Caption Availability Class
 *
 * Tests video caption availability.
 *
 * @since 1.26028.1905
 */
class Diagnostic_Video_Caption_Availability extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'video-caption-availability';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Video Caption Availability';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks embedded videos for caption/subtitle tracks';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'accessibility';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26028.1905
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$caption_check = self::check_video_captions();
		
		if ( $caption_check['videos_without_captions'] > 0 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: 1: number without captions, 2: total videos */
					__( '%1$d of %2$d videos lack captions (ADA/WCAG compliance required)', 'wpshadow' ),
					$caption_check['videos_without_captions'],
					$caption_check['total_videos']
				),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/video-caption-availability',
				'meta'         => array(
					'total_videos'              => $caption_check['total_videos'],
					'videos_without_captions'   => $caption_check['videos_without_captions'],
					'youtube_embeds'            => $caption_check['youtube_embeds'],
					'vimeo_embeds'              => $caption_check['vimeo_embeds'],
					'self_hosted'               => $caption_check['self_hosted'],
				),
			);
		}

		return null;
	}

	/**
	 * Check video caption availability.
	 *
	 * @since  1.26028.1905
	 * @return array Check results.
	 */
	private static function check_video_captions() {
		global $wpdb;

		$check = array(
			'total_videos'            => 0,
			'videos_without_captions' => 0,
			'youtube_embeds'          => 0,
			'vimeo_embeds'            => 0,
			'self_hosted'             => 0,
		);

		// Sample recent posts with videos.
		$posts = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT post_content
				FROM {$wpdb->posts}
				WHERE post_status = %s
				AND post_type IN ('post', 'page')
				AND (post_content LIKE %s OR post_content LIKE %s OR post_content LIKE %s)
				ORDER BY post_date DESC
				LIMIT 30",
				'publish',
				'%youtube%',
				'%vimeo%',
				'%<video%'
			)
		);

		foreach ( $posts as $post ) {
			$content = $post->post_content;

			// Check for YouTube embeds.
			preg_match_all( '/youtube\.com\/embed\/([a-zA-Z0-9_-]+)/i', $content, $youtube_matches );
			if ( ! empty( $youtube_matches[0] ) ) {
				foreach ( $youtube_matches[0] as $embed ) {
					++$check['youtube_embeds'];
					++$check['total_videos'];

					// Check if cc_load_policy=1 is set.
					if ( false === strpos( $embed, 'cc_load_policy=1' ) ) {
						++$check['videos_without_captions'];
					}
				}
			}

			// Check for Vimeo embeds.
			preg_match_all( '/vimeo\.com\/video\/([0-9]+)/i', $content, $vimeo_matches );
			if ( ! empty( $vimeo_matches[0] ) ) {
				$check['vimeo_embeds'] += count( $vimeo_matches[0] );
				$check['total_videos'] += count( $vimeo_matches[0] );
				// Vimeo caption detection requires API, assume missing.
				$check['videos_without_captions'] += count( $vimeo_matches[0] );
			}

			// Check for self-hosted videos.
			preg_match_all( '/<video[^>]*>.*?<\/video>/is', $content, $video_matches );
			if ( ! empty( $video_matches[0] ) ) {
				foreach ( $video_matches[0] as $video_html ) {
					++$check['self_hosted'];
					++$check['total_videos'];

					// Check for <track> element.
					if ( false === strpos( $video_html, '<track' ) ) {
						++$check['videos_without_captions'];
					}
				}
			}
		}

		return $check;
	}
}
