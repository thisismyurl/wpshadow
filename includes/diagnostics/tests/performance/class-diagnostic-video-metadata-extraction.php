<?php
/**
 * Video Metadata Extraction Diagnostic
 *
 * Tests if video metadata (duration, dimensions) is extracted and stored correctly.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Video Metadata Extraction Diagnostic Class
 *
 * Validates that WordPress can extract and store video metadata such as
 * duration, width, height, and other important video attributes.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Video_Metadata_Extraction extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'video-metadata-extraction';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Video Metadata Extraction';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates video metadata (duration, dimensions) extraction and storage';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the diagnostic check.
	 *
	 * Tests if WordPress can extract video metadata using available
	 * libraries (getID3, FFmpeg, WordPress metadata parser).
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue detected, null if all clear.
	 */
	public static function check() {
		// Check for metadata extraction libraries.
		$has_getid3 = extension_loaded( 'getid3' ) || class_exists( 'getID3' );

		// Check for FFmpeg.
		$ffmpeg_available = false;
		if ( function_exists( 'shell_exec' ) ) {
			$ffmpeg_check = @shell_exec( 'which ffmpeg' ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
			$ffmpeg_available = ! empty( $ffmpeg_check );
		}

		// Check for WordPress wp_read_video_metadata function.
		$has_wp_metadata = function_exists( 'wp_read_video_metadata' );

		// Get recently uploaded videos with metadata.
		global $wpdb;
		$videos_with_metadata = $wpdb->get_results(
			"SELECT p.ID, p.post_title, pm.meta_key, pm.meta_value
			 FROM {$wpdb->posts} p
			 LEFT JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
			 WHERE p.post_type = 'attachment'
			 AND p.post_mime_type LIKE 'video/%'
			 AND p.post_date > DATE_SUB(NOW(), INTERVAL 30 DAY)
			 AND pm.meta_key IN ('_wp_attachment_metadata')
			 ORDER BY p.ID DESC
			 LIMIT 20",
			ARRAY_A
		);

		// Analyze metadata quality.
		$metadata_quality = array(
			'total_videos'           => 0,
			'with_duration'          => 0,
			'with_dimensions'        => 0,
			'with_file_size'         => 0,
			'complete_metadata'      => 0,
			'missing_metadata'       => 0,
			'corrupted_metadata'     => 0,
		);

		foreach ( (array) $videos_with_metadata as $video ) {
			$metadata_quality['total_videos']++;

			if ( ! empty( $video['meta_value'] ) ) {
				$metadata = maybe_unserialize( $video['meta_value'] );

				if ( is_array( $metadata ) ) {
					if ( ! empty( $metadata['length'] ) || ! empty( $metadata['duration'] ) ) {
						$metadata_quality['with_duration']++;
					}
					if ( ! empty( $metadata['width'] ) && ! empty( $metadata['height'] ) ) {
						$metadata_quality['with_dimensions']++;
					}
					if ( ! empty( $metadata['filesize'] ) ) {
						$metadata_quality['with_file_size']++;
					}

					// Check if all key metadata is present.
					if ( ! empty( $metadata['length'] ) && 
						 ! empty( $metadata['width'] ) && 
						 ! empty( $metadata['height'] ) ) {
						$metadata_quality['complete_metadata']++;
					}
				} else {
					$metadata_quality['corrupted_metadata']++;
				}
			} else {
				$metadata_quality['missing_metadata']++;
			}
		}

		// Calculate extraction success rate.
		$total_videos = $metadata_quality['total_videos'];
		$extraction_rate = $total_videos > 0 ? 
						  round( ( $metadata_quality['complete_metadata'] / $total_videos ) * 100, 2 ) : 0;

		// Check media library settings.
		$organise_uploads_by_date = get_option( 'uploads_use_yearmonth_folders' );

		// Check for video processing plugins.
		$video_plugins = array();
		$plugin_checks = array(
			'video-elementor/video-elementor.php' => 'Video Elementor',
			'video-gallery/video-gallery.php'     => 'Video Gallery',
			'wp-video-library-plugin/index.php'   => 'WP Video Library',
		);

		foreach ( $plugin_checks as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$video_plugins[] = $name;
			}
		}

		// Check for issues.
		$issues = array();

		// Issue 1: No metadata extraction capability.
		if ( ! $has_getid3 && ! $ffmpeg_available && ! $has_wp_metadata ) {
			$issues[] = array(
				'type'        => 'no_extraction_capability',
				'description' => __( 'No video metadata extraction capability available', 'wpshadow' ),
			);
		}

		// Issue 2: Poor metadata extraction rate.
		if ( $total_videos > 0 && $extraction_rate < 50 ) {
			$issues[] = array(
				'type'        => 'poor_extraction_rate',
				'description' => sprintf(
					/* translators: %d: extraction rate percentage */
					__( 'Video metadata extraction success rate is only %d%% (should be >80%%)', 'wpshadow' ),
					$extraction_rate
				),
			);
		}

		// Issue 3: Missing duration metadata.
		if ( $total_videos > 0 && $metadata_quality['with_duration'] < ( $total_videos * 0.8 ) ) {
			$issues[] = array(
				'type'        => 'missing_duration',
				'description' => __( 'Duration metadata missing from most videos', 'wpshadow' ),
			);
		}

		// Issue 4: Missing dimension metadata.
		if ( $total_videos > 0 && $metadata_quality['with_dimensions'] < ( $total_videos * 0.8 ) ) {
			$issues[] = array(
				'type'        => 'missing_dimensions',
				'description' => __( 'Video dimension (width/height) metadata missing from most videos', 'wpshadow' ),
			);
		}

		// Issue 5: Corrupted metadata entries.
		if ( $metadata_quality['corrupted_metadata'] > 0 ) {
			$issues[] = array(
				'type'        => 'corrupted_metadata',
				'description' => sprintf(
					/* translators: %d: number of corrupted metadata entries */
					__( '%d video(s) have corrupted metadata that cannot be read', 'wpshadow' ),
					$metadata_quality['corrupted_metadata']
				),
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Video metadata extraction has issues that may prevent proper video information display and processing', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 40,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/video-metadata-extraction',
				'details'      => array(
					'has_getid3'               => $has_getid3,
					'ffmpeg_available'         => $ffmpeg_available,
					'has_wp_metadata'          => $has_wp_metadata,
					'total_videos'             => $total_videos,
					'with_duration'            => $metadata_quality['with_duration'],
					'with_dimensions'          => $metadata_quality['with_dimensions'],
					'with_file_size'           => $metadata_quality['with_file_size'],
					'complete_metadata'        => $metadata_quality['complete_metadata'],
					'missing_metadata'         => $metadata_quality['missing_metadata'],
					'corrupted_metadata'       => $metadata_quality['corrupted_metadata'],
					'extraction_success_rate'  => $extraction_rate . '%',
					'active_video_plugins'     => $video_plugins,
					'organise_uploads_by_date' => $organise_uploads_by_date,
					'issues_detected'          => $issues,
					'recommendation'           => __( 'Install FFmpeg or enable getID3 for better metadata extraction', 'wpshadow' ),
					'metadata_fields'          => array(
						'duration' => 'Video length in seconds',
						'width'    => 'Video width in pixels',
						'height'   => 'Video height in pixels',
						'bitrate'  => 'Video bitrate in kbps',
						'framerate' => 'Video frame rate in fps',
						'codec'    => 'Video codec used',
					),
				),
			);
		}

		return null;
	}
}
