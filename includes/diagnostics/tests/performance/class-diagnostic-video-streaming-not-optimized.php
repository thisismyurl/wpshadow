<?php
/**
 * Video Streaming Not Optimized Diagnostic
 *
 * Detects when videos are served as single files without adaptive
 * streaming, causing buffering and wasting bandwidth.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Video Streaming Not Optimized Diagnostic Class
 *
 * Checks if videos use adaptive streaming (HLS, DASH). Single-file
 * delivery doesn't adapt to bandwidth, causing poor UX.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Video_Streaming_Not_Optimized extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'video-streaming-not-optimized';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Video Streaming Not Optimized';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects videos served without adaptive bitrate streaming';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * Checks if videos use adaptive streaming. Adaptive bitrate
	 * reduces buffering and bandwidth costs.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Don't flag if Media-Video is already active.
		if ( Upgrade_Path_Helper::has_pro_product( 'wpadmin-media-video' ) ) {
			return null;
		}

		// Check for video streaming plugins.
		if ( self::has_streaming_plugin() ) {
			return null;
		}

		// Count videos in media library.
		global $wpdb;
		$total_videos = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts}
			WHERE post_type = 'attachment'
			AND post_mime_type LIKE 'video/%'"
		);

		// Don't flag if no videos.
		if ( $total_videos === 0 ) {
			return null;
		}

		// Check for HLS/DASH manifests in media library.
		$has_streaming = self::has_streaming_files();
		if ( $has_streaming ) {
			return null;
		}

		// Count large videos (>50MB).
		$large_videos = self::count_large_videos();

		// Don't flag if no large videos (streaming less beneficial).
		if ( $large_videos === 0 && $total_videos < 10 ) {
			return null;
		}

		return array(
			'id'                           => self::$slug,
			'title'                        => self::$title,
			'description'                  => sprintf(
				/* translators: %d: number of videos */
				__( 'Your %d videos are served as single files without adaptive streaming. Adaptive bitrate streaming reduces buffering and bandwidth costs by up to 40%%.', 'wpshadow' ),
				$total_videos
			),
			'severity'                     => $large_videos > 5 ? 'medium' : 'low',
			'threat_level'                 => min( 50, 25 + $large_videos ),
			'auto_fixable'                 => false,
			'total_videos'                 => (int) $total_videos,
			'large_videos'                 => $large_videos,
			'bandwidth_savings_potential'  => '40%',
			'adaptive_streaming_enabled'   => false,
			'kb_link'                      => 'https://wpshadow.com/kb/adaptive-streaming?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
		);
	}

	/**
	 * Check if streaming plugin is already active.
	 *
	 * @since 0.6093.1200
	 * @return bool True if streaming plugin detected.
	 */
	private static function has_streaming_plugin() {
		$streaming_plugins = array(
			'presto-player/presto-player.php',
			'video-embed-thumbnail-generator/video-embed-thumbnail-generator.php',
			'fv-wordpress-flowplayer/flowplayer.php',
		);

		foreach ( $streaming_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				return true;
			}
		}

		// Check for Vimeo/YouTube embeds (external streaming).
		if ( class_exists( 'Jetpack_VideoPress' ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Check if HLS/DASH files exist in media library.
	 *
	 * @since 0.6093.1200
	 * @return bool True if streaming files detected.
	 */
	private static function has_streaming_files() {
		$uploads_dir = wp_upload_dir();
		if ( ! isset( $uploads_dir['basedir'] ) ) {
			return false;
		}

		// Check for .m3u8 (HLS) or .mpd (DASH) files.
		$hls_files = glob( $uploads_dir['basedir'] . '/**/*.m3u8' );
		$dash_files = glob( $uploads_dir['basedir'] . '/**/*.mpd' );

		return ! empty( $hls_files ) || ! empty( $dash_files );
	}

	/**
	 * Count large video files (>50MB).
	 *
	 * @since 0.6093.1200
	 * @return int Number of large videos.
	 */
	private static function count_large_videos() {
		$uploads_dir = wp_upload_dir();
		if ( ! isset( $uploads_dir['basedir'] ) || ! is_dir( $uploads_dir['basedir'] ) ) {
			return 0;
		}

		$large_count = 0;
		$video_extensions = array( 'mp4', 'mov', 'avi', 'wmv', 'flv', 'webm' );

		foreach ( $video_extensions as $ext ) {
			$pattern = $uploads_dir['basedir'] . '/**/*.' . $ext;
			$files = glob( $pattern );

			if ( ! empty( $files ) ) {
				foreach ( $files as $file ) {
					if ( file_exists( $file ) && filesize( $file ) > 52428800 ) { // 50MB.
						$large_count++;
					}
				}
			}
		}

		return $large_count;
	}
}
