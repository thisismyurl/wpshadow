<?php
/**
 * Videos Without Captions Diagnostic
 *
 * Detects video content that lacks captions/subtitles, violating WCAG 2.1 Level A
 * requirement for synchronized captions on all prerecorded audio content.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Accessibility
 * @since      1.6034.2145
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Videos Without Captions Diagnostic Class
 *
 * Identifies embedded and hosted video content that lacks closed captions
 * or subtitles, making content inaccessible to deaf or hard-of-hearing users.
 *
 * **Why This Matters:**
 * - WCAG 2.1 Level A requirement (SC 1.2.2 Captions Prerecorded)
 * - Legal requirement for ADA/Section 508 compliance
 * - 15% of US population has hearing loss
 * - 85% of Facebook videos watched without sound
 * - Better SEO (captions are indexed by search engines)
 *
 * **What's Checked:**
 * - YouTube embeds (checks for cc_load_policy=1 or &cc=1)
 * - Vimeo embeds (checks for texttrack parameter)
 * - HTML5 <video> elements (checks for <track> elements)
 * - Video file attachments without caption metadata
 *
 * @since 1.6034.2145
 */
class Diagnostic_Videos_Without_Captions extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'videos-without-captions';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Videos Without Captions';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects video content lacking closed captions or subtitles required for deaf/hard-of-hearing users';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'accessibility';

	/**
	 * Run the diagnostic check
	 *
	 * Scans posts and pages for video embeds and checks for caption indicators:
	 * - YouTube: cc_load_policy=1 or &cc=1 parameter
	 * - Vimeo: texttrack parameter
	 * - HTML5: <track kind="captions"> element
	 * - Video attachments: caption metadata
	 *
	 * @since  1.6034.2145
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		// Get all posts with video content
		$posts = get_posts(
			array(
				'post_type'      => array( 'post', 'page' ),
				'post_status'    => 'publish',
				'posts_per_page' => 100,
			)
		);

		$videos_without_captions = array();

		foreach ( $posts as $post ) {
			$content = $post->post_content;

			// Check for YouTube embeds without captions enabled
			if ( preg_match_all( '/<iframe[^>]*src=["\']([^"\']*youtube\.com\/embed\/[^"\']*)["\'][^>]*>/i', $content, $youtube_matches ) ) {
				foreach ( $youtube_matches[1] as $youtube_url ) {
					// Check if captions are explicitly enabled
					if ( strpos( $youtube_url, 'cc_load_policy=1' ) === false && strpos( $youtube_url, '&cc=1' ) === false ) {
						$videos_without_captions[] = array(
							'post_id'    => $post->ID,
							'post_title' => $post->post_title,
							'type'       => 'YouTube embed',
							'url'        => $youtube_url,
							'issue'      => 'No cc_load_policy=1 parameter',
						);
					}
				}
			}

			// Check for Vimeo embeds without texttrack
			if ( preg_match_all( '/<iframe[^>]*src=["\']([^"\']*player\.vimeo\.com\/video\/[^"\']*)["\'][^>]*>/i', $content, $vimeo_matches ) ) {
				foreach ( $vimeo_matches[1] as $vimeo_url ) {
					if ( strpos( $vimeo_url, 'texttrack=' ) === false ) {
						$videos_without_captions[] = array(
							'post_id'    => $post->ID,
							'post_title' => $post->post_title,
							'type'       => 'Vimeo embed',
							'url'        => $vimeo_url,
							'issue'      => 'No texttrack parameter',
						);
					}
				}
			}

			// Check for HTML5 video without track elements
			if ( preg_match_all( '/<video[^>]*>(.*?)<\/video>/is', $content, $video_matches ) ) {
				foreach ( $video_matches[0] as $video_html ) {
					if ( stripos( $video_html, '<track' ) === false ) {
						$videos_without_captions[] = array(
							'post_id'    => $post->ID,
							'post_title' => $post->post_title,
							'type'       => 'HTML5 video',
							'url'        => '',
							'issue'      => 'Missing <track> element',
						);
					}
				}
			}
		}

		// Check video attachments
		$video_attachments = get_posts(
			array(
				'post_type'      => 'attachment',
				'post_mime_type' => 'video',
				'posts_per_page' => 50,
				'post_status'    => 'inherit',
			)
		);

		foreach ( $video_attachments as $video ) {
			// Check if caption file is associated
			$caption_file = get_post_meta( $video->ID, '_wp_attachment_video_caption', true );
			if ( empty( $caption_file ) ) {
				$videos_without_captions[] = array(
					'post_id'    => $video->ID,
					'post_title' => $video->post_title,
					'type'       => 'Video attachment',
					'url'        => wp_get_attachment_url( $video->ID ),
					'issue'      => 'No caption file metadata',
				);
			}
		}

		if ( empty( $videos_without_captions ) ) {
			return null; // All videos have caption indicators
		}

		$count = count( $videos_without_captions );

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %d: number of videos */
				__( '%d video(s) detected without closed captions. This content is inaccessible to deaf and hard-of-hearing users.', 'wpshadow' ),
				$count
			),
			'severity'     => 'high',
			'threat_level' => 80,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/accessibility-videos-without-captions',
			'details'      => array(
				'video_count'      => $count,
				'sample_videos'    => array_slice( $videos_without_captions, 0, 10 ),
				'wcag_requirement' => 'Level A: Success Criterion 1.2.2 (Captions Prerecorded)',
			),
		);
	}
}
