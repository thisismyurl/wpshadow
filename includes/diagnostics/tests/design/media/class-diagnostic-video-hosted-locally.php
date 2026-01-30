<?php
/**
 * Video Files Hosted Locally Diagnostic
 *
 * Detects video files served from uploads directory instead of
 * dedicated video hosting or CDN, wasting bandwidth and server resources.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Media
 * @since      1.6028.1430
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Video Hosted Locally Diagnostic Class
 *
 * Identifies self-hosted video files that should be moved to
 * dedicated video hosting platforms like YouTube or Vimeo.
 *
 * @since 1.6028.1430
 */
class Diagnostic_Video_Hosted_Locally extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'video-hosted-locally';

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
	protected static $description = 'Detects video files served from uploads directory instead of video hosting';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media-bandwidth';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6028.1430
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$local_videos = self::find_local_videos();

		if ( $local_videos['count'] >= 3 ) {
			$total_size_mb = round( $local_videos['total_size'] / 1024 / 1024, 2 );

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: 1: number of videos, 2: total size in MB */
					__( 'Found %1$d video files (%2$s MB) hosted locally, wasting bandwidth', 'wpshadow' ),
					$local_videos['count'],
					$total_size_mb
				),
				'severity'     => $local_videos['count'] > 10 ? 'medium' : 'low',
				'threat_level' => min( 50, $local_videos['count'] * 5 ),
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/video-hosting-optimization',
				'meta'         => array(
					'video_count'    => $local_videos['count'],
					'total_size_mb'  => $total_size_mb,
					'largest_file'   => $local_videos['largest'],
					'video_files'    => array_slice( $local_videos['files'], 0, 10 ),
				),
				'details'      => array(
					'finding'        => sprintf(
						/* translators: 1: number of videos, 2: total size */
						__( '%1$d video files (%2$s MB) consuming server bandwidth', 'wpshadow' ),
						$local_videos['count'],
						$total_size_mb
					),
					'impact'         => __( 'Self-hosted videos consume massive bandwidth, slow page loads, and strain server resources. Video hosting platforms provide streaming optimization, CDN delivery, and adaptive bitrates.', 'wpshadow' ),
					'recommendation' => __( 'Migrate videos to YouTube, Vimeo, or dedicated video CDN', 'wpshadow' ),
					'solution_free'  => array(
						'label' => __( 'YouTube Embedding', 'wpshadow' ),
						'steps' => array(
							__( '1. Create free YouTube channel', 'wpshadow' ),
							__( '2. Upload videos to YouTube', 'wpshadow' ),
							__( '3. Embed using YouTube video blocks', 'wpshadow' ),
							__( '4. Delete local video files', 'wpshadow' ),
						),
					),
					'solution_premium' => array(
						'label' => __( 'Vimeo Pro Hosting', 'wpshadow' ),
						'steps' => array(
							__( '1. Subscribe to Vimeo Pro ($20/mo)', 'wpshadow' ),
							__( '2. Upload videos with privacy controls', 'wpshadow' ),
							__( '3. Use Vimeo embed codes', 'wpshadow' ),
							__( '4. Enable advanced analytics', 'wpshadow' ),
						),
					),
					'solution_advanced' => array(
						'label' => __( 'Video CDN with Encoding', 'wpshadow' ),
						'steps' => array(
							__( '1. Set up Cloudflare Stream or AWS CloudFront', 'wpshadow' ),
							__( '2. Migrate videos to video CDN', 'wpshadow' ),
							__( '3. Configure adaptive bitrate streaming', 'wpshadow' ),
							__( '4. Implement video.js player', 'wpshadow' ),
						),
					),
					'test_steps'     => array(
						__( '1. Monitor server bandwidth before/after', 'wpshadow' ),
						__( '2. Test video load times', 'wpshadow' ),
						__( '3. Verify adaptive quality on mobile', 'wpshadow' ),
						__( '4. Check video analytics', 'wpshadow' ),
					),
				),
			);
		}

		return null;
	}

	/**
	 * Find local video files.
	 *
	 * @since  1.6028.1430
	 * @return array Video file information.
	 */
	private static function find_local_videos() {
		global $wpdb;

		$video_extensions = array( 'mp4', 'webm', 'ogv', 'mov', 'avi', 'wmv', 'm4v' );
		$extension_pattern = implode( '|', $video_extensions );

		// Query video attachments.
		$videos = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT ID, post_title, guid 
				FROM {$wpdb->posts} 
				WHERE post_type = 'attachment' 
				AND post_mime_type LIKE %s 
				LIMIT 100",
				'video/%'
			)
		);

		$result = array(
			'count'      => 0,
			'total_size' => 0,
			'files'      => array(),
			'largest'    => 0,
		);

		foreach ( $videos as $video ) {
			$file_path = get_attached_file( $video->ID );
			if ( $file_path && file_exists( $file_path ) ) {
				$size = filesize( $file_path );
				
				$result['count']++;
				$result['total_size'] += $size;
				$result['files'][] = array(
					'id'    => $video->ID,
					'title' => $video->post_title,
					'url'   => wp_get_attachment_url( $video->ID ),
					'size'  => $size,
				);

				if ( $size > $result['largest'] ) {
					$result['largest'] = $size;
				}
			}
		}

		return $result;
	}
}
