<?php
/**
 * Export Slow High-Resolution Media Diagnostic
 *
 * Detects performance degradation when exporting content with large media files.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6030.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Export Slow High-Resolution Media Diagnostic Class
 *
 * Checks for slow media export performance.
 *
 * @since 1.6030.2148
 */
class Diagnostic_Export_Slow_High_Resolution_Media extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'export-slow-high-resolution-media';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Slow Export with High-Resolution Media';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects performance issues with large media exports';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'import-export';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6030.2148
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;
		
		$issues = array();

		// Count total media.
		$total_media = $wpdb->get_var(
			"SELECT COUNT(*) 
			FROM {$wpdb->posts} 
			WHERE post_type = 'attachment'"
		);

		if ( $total_media < 100 ) {
			return null;
		}

		// Count large media files (> 5MB).
		$large_media = $wpdb->get_results(
			"SELECT COUNT(*) as count, SUM(meta_value) as total_size 
			FROM {$wpdb->postmeta} 
			WHERE meta_key = '_wp_attached_file' 
			AND meta_value != ''",
			ARRAY_A
		);

		// Get actual file sizes.
		$upload_dir = wp_upload_dir();
		$large_files = 0;
		$total_size = 0;

		$attachments = $wpdb->get_results(
			"SELECT meta_value 
			FROM {$wpdb->postmeta} 
			WHERE meta_key = '_wp_attached_file' 
			LIMIT 100",
			ARRAY_A
		);

		foreach ( $attachments as $attachment ) {
			$file_path = $upload_dir['basedir'] . '/' . $attachment['meta_value'];
			
			if ( file_exists( $file_path ) ) {
				$size = filesize( $file_path );
				$total_size += $size;
				
				if ( $size > 5242880 ) { // 5MB.
					++$large_files;
				}
			}
		}

		if ( $large_files > 10 ) {
			$issues[] = sprintf(
				/* translators: %d: number of large files */
				__( '%d media files exceed 5MB (slows export)', 'wpshadow' ),
				$large_files
			);
		}

		// Check average media size.
		if ( count( $attachments ) > 0 ) {
			$average_size = $total_size / count( $attachments );
			
			if ( $average_size > 2097152 ) { // 2MB average.
				$issues[] = sprintf(
					/* translators: %s: average size */
					__( 'Average media size %s (export will be slow)', 'wpshadow' ),
					size_format( $average_size )
				);
			}
		}

		// Check for unprocessed images.
		$unprocessed_images = $wpdb->get_var(
			"SELECT COUNT(*) 
			FROM {$wpdb->posts} 
			WHERE post_type = 'attachment' 
			AND post_mime_type LIKE 'image/%' 
			AND ID NOT IN (
				SELECT post_id 
				FROM {$wpdb->postmeta} 
				WHERE meta_key = '_wp_attachment_metadata'
			)"
		);

		if ( $unprocessed_images > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of unprocessed images */
				__( '%d images without metadata (may reprocess during export)', 'wpshadow' ),
				$unprocessed_images
			);
		}

		// Check for image dimensions.
		$large_dimensions = $wpdb->get_results(
			"SELECT COUNT(*) as count 
			FROM {$wpdb->postmeta} 
			WHERE meta_key = '_wp_attachment_metadata' 
			AND (
				meta_value LIKE '%\"width\";i:5000%' 
				OR meta_value LIKE '%\"width\";i:6000%' 
				OR meta_value LIKE '%\"width\";i:7000%' 
				OR meta_value LIKE '%\"height\";i:5000%' 
				OR meta_value LIKE '%\"height\";i:6000%' 
				OR meta_value LIKE '%\"height\";i:7000%'
			)",
			ARRAY_A
		);

		if ( ! empty( $large_dimensions ) && $large_dimensions[0]['count'] > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of high-resolution images */
				__( '%d images with 5000+ pixel dimensions', 'wpshadow' ),
				$large_dimensions[0]['count']
			);
		}

		// Check for thumbnail generation.
		$missing_thumbnails = $wpdb->get_var(
			"SELECT COUNT(*) 
			FROM {$wpdb->posts} p 
			LEFT JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = '_wp_attachment_metadata' 
			WHERE p.post_type = 'attachment' 
			AND p.post_mime_type LIKE 'image/%' 
			AND (pm.meta_value IS NULL OR pm.meta_value NOT LIKE '%sizes%')"
		);

		if ( $missing_thumbnails > 10 ) {
			$issues[] = sprintf(
				/* translators: %d: number of images */
				__( '%d images missing thumbnails (may regenerate during export)', 'wpshadow' ),
				$missing_thumbnails
			);
		}

		// Check memory limit for image processing.
		$memory_limit = wp_convert_hr_to_bytes( ini_get( 'memory_limit' ) );
		
		if ( $memory_limit > 0 && $memory_limit < 134217728 && $large_files > 5 ) {
			$issues[] = sprintf(
				/* translators: %s: memory limit */
				__( 'memory_limit %s too low for large image processing', 'wpshadow' ),
				size_format( $memory_limit )
			);
		}

		// Check for GD vs Imagick.
		$image_editor = _wp_image_editor_choose();
		
		if ( 'WP_Image_Editor_GD' === $image_editor && $large_files > 10 ) {
			$issues[] = __( 'Using GD for images (Imagick more efficient for large files)', 'wpshadow' );
		}

		// Check max_execution_time.
		$max_execution = (int) ini_get( 'max_execution_time' );
		
		if ( $max_execution > 0 && $max_execution < 300 && $total_media > 500 ) {
			$issues[] = sprintf(
				/* translators: 1: execution time, 2: media count */
				__( 'max_execution_time %1$ds insufficient for %2$d media files', 'wpshadow' ),
				$max_execution,
				$total_media
			);
		}

		// Check for external media storage.
		$external_media_plugins = array(
			'amazon-s3-and-cloudfront/wordpress-s3.php',
			'wp-offload-media-lite/wp-offload-media-lite.php',
		);

		$has_external_media = false;
		foreach ( $external_media_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_external_media = true;
				break;
			}
		}

		if ( $has_external_media ) {
			$issues[] = __( 'External media storage active (export requires download)', 'wpshadow' );
		}

		// Check for PDF files.
		$pdf_count = $wpdb->get_var(
			"SELECT COUNT(*) 
			FROM {$wpdb->posts} 
			WHERE post_type = 'attachment' 
			AND post_mime_type = 'application/pdf'"
		);

		if ( $pdf_count > 100 ) {
			$issues[] = sprintf(
				/* translators: %d: PDF count */
				__( '%d PDF files (large files slow export)', 'wpshadow' ),
				$pdf_count
			);
		}

		// Check for video files.
		$video_count = $wpdb->get_var(
			"SELECT COUNT(*) 
			FROM {$wpdb->posts} 
			WHERE post_type = 'attachment' 
			AND post_mime_type LIKE 'video/%'"
		);

		if ( $video_count > 10 ) {
			$issues[] = sprintf(
				/* translators: %d: video count */
				__( '%d video files (very large files)', 'wpshadow' ),
				$video_count
			);
		}

		// Check for audio files.
		$audio_count = $wpdb->get_var(
			"SELECT COUNT(*) 
			FROM {$wpdb->posts} 
			WHERE post_type = 'attachment' 
			AND post_mime_type LIKE 'audio/%'"
		);

		if ( $audio_count > 50 ) {
			$issues[] = sprintf(
				/* translators: %d: audio count */
				__( '%d audio files (may slow export)', 'wpshadow' ),
				$audio_count
			);
		}

		// Check for media with many metadata entries.
		$metadata_heavy = $wpdb->get_var(
			"SELECT COUNT(*) 
			FROM {$wpdb->postmeta} 
			WHERE meta_key = '_wp_attachment_metadata' 
			AND LENGTH(meta_value) > 10000"
		);

		if ( $metadata_heavy > 10 ) {
			$issues[] = sprintf(
				/* translators: %d: number of files */
				__( '%d media files with complex metadata (> 10KB each)', 'wpshadow' ),
				$metadata_heavy
			);
		}

		// Check for export attachment inclusion.
		$export_attachments = apply_filters( 'wxr_export_skip_attachments', false );
		
		if ( ! $export_attachments && $total_media > 1000 ) {
			$issues[] = __( 'Export includes all attachments (consider filtering media)', 'wpshadow' );
		}

		// Check upload directory size.
		$upload_size = 0;
		
		if ( function_exists( 'disk_total_space' ) && function_exists( 'disk_free_space' ) ) {
			$total_space = @disk_total_space( $upload_dir['basedir'] );
			$free_space = @disk_free_space( $upload_dir['basedir'] );
			
			if ( false !== $total_space && false !== $free_space ) {
				$upload_size = $total_space - $free_space;
				
				if ( $upload_size > 10737418240 ) { // 10GB.
					$issues[] = sprintf(
						/* translators: %s: upload size */
						__( 'Upload directory %s (export will take significant time)', 'wpshadow' ),
						size_format( $upload_size )
					);
				}
			}
		}

		// Check for CDN integration.
		$cdn_plugins = array(
			'w3-total-cache/w3-total-cache.php',
			'wp-super-cache/wp-cache.php',
			'cdn-enabler/cdn-enabler.php',
		);

		$has_cdn = false;
		foreach ( $cdn_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_cdn = true;
				break;
			}
		}

		if ( $has_cdn ) {
			$issues[] = __( 'CDN active (media URLs may reference external servers)', 'wpshadow' );
		}

		// Check for duplicate media.
		$duplicate_media = $wpdb->get_results(
			"SELECT meta_value, COUNT(*) as count 
			FROM {$wpdb->postmeta} 
			WHERE meta_key = '_wp_attached_file' 
			GROUP BY meta_value 
			HAVING count > 1 
			LIMIT 10",
			ARRAY_A
		);

		if ( ! empty( $duplicate_media ) ) {
			$issues[] = sprintf(
				/* translators: %d: number of duplicates */
				__( '%d duplicate media entries (increases export size)', 'wpshadow' ),
				count( $duplicate_media )
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => implode( '. ', $issues ),
				'severity'    => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/export-slow-high-resolution-media',
			);
		}

		return null;
	}
}
