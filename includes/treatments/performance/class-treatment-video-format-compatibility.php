<?php
/**
 * Video Format Compatibility Treatment
 *
 * Tests supported video formats (MP4, WebM, OGG) and browser compatibility.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.7034.0910
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Video Format Compatibility Treatment Class
 *
 * Validates that WordPress is configured to support multiple video formats
 * (MP4, WebM, OGG) for maximum browser compatibility.
 *
 * @since 1.7034.0910
 */
class Treatment_Video_Format_Compatibility extends Treatment_Base {

	/**
	 * Treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'video-format-compatibility';

	/**
	 * Treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Video Format Compatibility';

	/**
	 * Treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Validates supported video formats (MP4, WebM, OGG) and browser playback';

	/**
	 * Treatment family
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the treatment check.
	 *
	 * Tests if WordPress allows commonly supported video formats and
	 * checks for FFmpeg/codec availability for transcoding.
	 *
	 * @since  1.7034.0910
	 * @return array|null Finding array if issue detected, null if all clear.
	 */
	public static function check() {
		// Get allowed MIME types for video.
		$allowed_mimes = get_allowed_mime_types();

		// Check for specific video format support.
		$supports_mp4 = false;
		$supports_webm = false;
		$supports_ogg = false;
		$supports_flv = false;
		$supports_mov = false;

		foreach ( $allowed_mimes as $ext => $mime ) {
			if ( strpos( $mime, 'video/' ) === 0 ) {
				if ( strpos( $ext, 'mp4' ) !== false || strpos( $mime, 'mp4' ) !== false ) {
					$supports_mp4 = true;
				}
				if ( strpos( $ext, 'webm' ) !== false || strpos( $mime, 'webm' ) !== false ) {
					$supports_webm = true;
				}
				if ( strpos( $ext, 'ogg' ) !== false || strpos( $mime, 'ogg' ) !== false ) {
					$supports_ogg = true;
				}
				if ( strpos( $ext, 'flv' ) !== false ) {
					$supports_flv = true;
				}
				if ( strpos( $ext, 'mov' ) !== false ) {
					$supports_mov = true;
				}
			}
		}

		// Check for FFmpeg availability (needed for transcoding).
		$ffmpeg_available = false;
		if ( function_exists( 'shell_exec' ) ) {
			$ffmpeg_check = @shell_exec( 'which ffmpeg' ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
			$ffmpeg_available = ! empty( $ffmpeg_check );
		}

		// Check for ImageMagick.
		$imagick_available = extension_loaded( 'imagick' );

		// Get WordPress video upload settings.
		$upload_dir = wp_upload_dir();
		$video_dir = isset( $upload_dir['basedir'] ) ? $upload_dir['basedir'] : '';

		// Check for recently uploaded videos.
		global $wpdb;
		$recent_videos = $wpdb->get_results(
			"SELECT post_mime_type, COUNT(*) as count 
			 FROM {$wpdb->posts} 
			 WHERE post_type = 'attachment' 
			 AND post_mime_type LIKE 'video/%'
			 AND post_date > DATE_SUB(NOW(), INTERVAL 30 DAY)
			 GROUP BY post_mime_type",
			ARRAY_A
		);

		$video_format_usage = array();
		if ( ! empty( $recent_videos ) ) {
			foreach ( $recent_videos as $video ) {
				$video_format_usage[ $video['post_mime_type'] ] = $video['count'];
			}
		}

		// Check for issues.
		$issues = array();

		// Issue 1: No MP4 support (most common format).
		if ( ! $supports_mp4 ) {
			$issues[] = array(
				'type'        => 'no_mp4_support',
				'description' => __( 'MP4 video format is not allowed for upload', 'wpshadow' ),
			);
		}

		// Issue 2: Only one format supported (poor compatibility).
		$format_count = (int) $supports_mp4 + (int) $supports_webm + (int) $supports_ogg;
		if ( $format_count < 2 ) {
			$issues[] = array(
				'type'        => 'limited_formats',
				'description' => sprintf(
					/* translators: %d: number of supported video formats */
					__( 'Only %d video format(s) supported; browsers require multiple formats for compatibility', 'wpshadow' ),
					$format_count
				),
			);
		}

		// Issue 3: No transcoding capability for format conversion.
		if ( ! $ffmpeg_available && ! $imagick_available ) {
			$issues[] = array(
				'type'        => 'no_transcoding',
				'description' => __( 'No video transcoding capability (FFmpeg or ImageMagick not available)', 'wpshadow' ),
			);
		}

		// Issue 4: Using old formats (FLV) instead of modern ones.
		if ( $supports_flv && ! $supports_webm ) {
			$issues[] = array(
				'type'        => 'outdated_formats',
				'description' => __( 'Site supports outdated FLV format but not modern WebM format', 'wpshadow' ),
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Video format compatibility has issues that may limit browser playback or require manual format conversion', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 60,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/video-format-compatibility',
				'details'      => array(
					'supports_mp4'          => $supports_mp4,
					'supports_webm'         => $supports_webm,
					'supports_ogg'          => $supports_ogg,
					'supports_flv'          => $supports_flv,
					'supports_mov'          => $supports_mov,
					'format_count'          => $format_count,
					'ffmpeg_available'      => $ffmpeg_available,
					'imagick_available'     => $imagick_available,
					'video_format_usage'    => $video_format_usage,
					'total_video_count'     => count( (array) $recent_videos ),
					'issues_detected'       => $issues,
					'recommendation'        => __( 'Enable MP4, WebM, and OGG formats; install FFmpeg for transcoding', 'wpshadow' ),
					'browser_support_notes' => array(
						'MP4'  => 'IE, Chrome, Safari (H.264 codec)',
						'WebM' => 'Chrome, Firefox, Opera (VP8/VP9 codec)',
						'OGG'  => 'Firefox, Opera, Chrome (Theora codec)',
					),
					'testing_steps'         => array(
						__( '1. Upload test videos in each format (MP4, WebM, OGG)', 'wpshadow' ),
						__( '2. Test playback in Chrome, Firefox, Safari', 'wpshadow' ),
						__( '3. Check if videos play without transcoding delay', 'wpshadow' ),
						__( '4. Verify audio sync is correct', 'wpshadow' ),
					),
				),
			);
		}

		return null;
	}
}
