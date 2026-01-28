<?php
/**
 * Video Files Hosted Locally Diagnostic
 *
 * Detects video files served from local uploads directory instead of CDN
 * or dedicated video hosting, wasting bandwidth and server resources.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Media
 * @since      1.6028.2149
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Video_Files_Local_Hosting Class
 *
 * Identifies video files hosted locally that should be on CDN or video platform.
 * Self-hosted videos consume bandwidth and degrade performance.
 *
 * @since 1.6028.2149
 */
class Diagnostic_Video_Files_Local_Hosting extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'video-files-local-hosting';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Video Files Hosted Locally (Not CDN)';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects video files served locally instead of from CDN';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6028.2149
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$cached = get_transient( 'wpshadow_diagnostic_video_local' );
		if ( false !== $cached ) {
			return $cached;
		}

		$video_analysis = self::analyze_video_hosting();

		if ( empty( $video_analysis['local_videos'] ) || count( $video_analysis['local_videos'] ) < 3 ) {
			set_transient( 'wpshadow_diagnostic_video_local', null, 12 * HOUR_IN_SECONDS );
			return null;
		}

		$video_count = count( $video_analysis['local_videos'] );
		$severity    = $video_count > 10 ? 'medium' : 'low';
		$threat_level = min( 70, 30 + ( $video_count * 2 ) );

		$total_size_mb = $video_analysis['total_size'] / ( 1024 * 1024 );

		$finding = array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: 1: number of videos, 2: total size */
				__( 'Found %1$d video files (%2$s MB) hosted locally instead of on CDN or video platform', 'wpshadow' ),
				$video_count,
				number_format( $total_size_mb, 1 )
			),
			'severity'       => $severity,
			'threat_level'   => $threat_level,
			'auto_fixable'   => false,
			'kb_link'        => 'https://wpshadow.com/kb/video-hosting',
			'meta'           => array(
				'video_count'      => $video_count,
				'total_size_bytes' => $video_analysis['total_size'],
				'total_size_mb'    => round( $total_size_mb, 2 ),
				'video_files'      => array_slice( $video_analysis['local_videos'], 0, 10 ),
			),
			'details'        => array(
				sprintf(
					/* translators: %d: number of videos */
					__( 'Number of locally hosted videos: %d', 'wpshadow' ),
					$video_count
				),
				sprintf(
					/* translators: %s: size */
					__( 'Total size: %s MB', 'wpshadow' ),
					number_format( $total_size_mb, 1 )
				),
				__( 'Self-hosted videos consume server bandwidth and impact performance', 'wpshadow' ),
			),
			'recommendations' => array(
				__( 'Upload videos to YouTube, Vimeo, or Wistia for better performance', 'wpshadow' ),
				__( 'Use video CDN like Cloudinary or Bunny.net', 'wpshadow' ),
				__( 'Implement lazy loading for video embeds', 'wpshadow' ),
				__( 'Consider video compression before self-hosting if necessary', 'wpshadow' ),
			),
		);

		set_transient( 'wpshadow_diagnostic_video_local', $finding, 12 * HOUR_IN_SECONDS );
		return $finding;
	}

	/**
	 * Analyze video hosting configuration.
	 *
	 * Scans media library for video files and checks hosting location.
	 *
	 * @since  1.6028.2149
	 * @return array Video hosting analysis.
	 */
	private static function analyze_video_hosting() {
		global $wpdb;

		$video_extensions = array( 'mp4', 'avi', 'mov', 'wmv', 'flv', 'webm', 'mkv' );

		$video_posts = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT ID, guid, post_mime_type 
				FROM {$wpdb->posts} 
				WHERE post_type = %s 
				AND post_mime_type LIKE %s 
				LIMIT 100",
				'attachment',
				'video/%'
			)
		);

		$local_videos = array();
		$total_size   = 0;
		$upload_dir   = wp_upload_dir();
		$upload_url   = $upload_dir['baseurl'];

		foreach ( $video_posts as $video ) {
			// Check if hosted locally (not external CDN/platform).
			if ( strpos( $video->guid, $upload_url ) === 0 ) {
				$file_path = get_attached_file( $video->ID );
				$file_size = $file_path && file_exists( $file_path ) ? filesize( $file_path ) : 0;

				$local_videos[] = array(
					'id'        => $video->ID,
					'url'       => $video->guid,
					'mime_type' => $video->post_mime_type,
					'size'      => $file_size,
				);

				$total_size += $file_size;
			}
		}

		return array(
			'local_videos' => $local_videos,
			'total_size'   => $total_size,
		);
	}
}
